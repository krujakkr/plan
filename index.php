<?php
require_once 'config/config.php';
require_once 'config/database.php';

// ถ้า login แล้วให้ redirect ไปหน้าที่เหมาะสม
if (isset($_SESSION['user_id'])) {
    $base = dirname($_SERVER['PHP_SELF']);
    switch ($_SESSION['role']) {
        case 'teacher':
            header("Location: " . $base . "/teacher/dashboard.php");
            break;
        case 'evaluator':
            header("Location: " . $base . "/evaluator/dashboard.php");
            break;
        case 'admin':
            header("Location: " . $base . "/admin/settings.php");
            break;
    }
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $teacherId = clean_input($_POST['teacherId'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($teacherId) || empty($password)) {
        $error = 'กรุณากรอกรหัสครูและรหัสผ่าน';
    } else {
        // ตรวจสอบข้อมูลผู้ใช้
        $sql = "SELECT * FROM users WHERE teacherId = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $teacherId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            
            // ตรวจสอบรหัสผ่าน
            if (password_verify($password, $user['password'])) {
                // Login สำเร็จ
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['teacherId'] = $user['teacherId'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['subjectGroup'] = $user['subjectGroup'];
                
                // Redirect ตาม role
                $base = dirname($_SERVER['PHP_SELF']);
                switch ($user['role']) {
                    case 'teacher':
                        header("Location: " . $base . "/teacher/dashboard.php");
                        break;
                    case 'evaluator':
                        header("Location: " . $base . "/evaluator/dashboard.php");
                        break;
                    case 'admin':
                        header("Location: " . $base . "/admin/settings.php");
                        break;
                }
                exit();
            } else {
                $error = 'รหัสผ่านไม่ถูกต้อง';
            }
        } else {
            $error = 'ไม่พบรหัสครูในระบบ';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ - ระบบส่งแผนการจัดการเรียนรู้</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font -->
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            font-family: 'Sarabun', sans-serif;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-500 to-purple-600 min-h-screen flex items-center justify-center p-4">
    
    <div class="w-full max-w-md">
        <!-- Card -->
        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
            
            <!-- Header -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 p-8 text-white text-center">
                <div class="w-24 h-24 bg-white rounded-full flex items-center justify-center mx-auto mb-4 p-2 shadow-lg">
                    <img src="https://knw.ac.th/wp-content/uploads/2025/02/knw_svg_logo.svg" alt="โลโก้โรงเรียน" class="w-full h-full object-contain">
                </div>
                <h1 class="text-2xl font-bold mb-2">ระบบส่งแผนการจัดการเรียนรู้</h1>
                <p class="text-blue-100">และบันทึกหลังแผน</p>
                <p class="text-sm text-blue-200 mt-2">โรงเรียนแก่นนครวิทยาลัย</p>
            </div>
            
            <!-- Form -->
            <div class="p-8">
                <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6 flex items-center">
                    <i class="fas fa-exclamation-circle mr-3"></i>
                    <span><?php echo $error; ?></span>
                </div>
                <?php endif; ?>
                
                <form method="POST" action="" class="space-y-6">
                    <!-- รหัสครู -->
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">
                            <i class="fas fa-user mr-2 text-blue-600"></i>รหัสครู (3 หลัก)
                        </label>
                        <input 
                            type="text" 
                            name="teacherId" 
                            maxlength="3"
                            pattern="[0-9]{3}"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="ตัวอย่าง: 001"
                            required
                            autofocus
                        >
                    </div>
                    
                    <!-- รหัสผ่าน -->
                    <div>
                        <label class="block text-gray-700 font-medium mb-2">
                            <i class="fas fa-lock mr-2 text-blue-600"></i>รหัสผ่าน (เลขท้ายบัตรประชาชน 6 หลัก)
                        </label>
                        <div class="relative">
                            <input 
                                type="password" 
                                name="password" 
                                id="password"
                                maxlength="6"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent pr-12"
                                placeholder="••••••"
                                required
                            >
                            <button 
                                type="button"
                                onclick="togglePassword()"
                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700"
                            >
                                <i class="fas fa-eye" id="toggleIcon"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- ปุ่ม Login -->
                    <button 
                        type="submit"
                        class="w-full bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-bold py-3 px-4 rounded-lg transition duration-200 transform hover:scale-105 shadow-lg"
                    >
                        <i class="fas fa-sign-in-alt mr-2"></i>เข้าสู่ระบบ
                    </button>
                </form>
                
                <!-- Info -->
                <div class="mt-6 p-4 bg-blue-50 rounded-lg">
                    <p class="text-sm text-gray-600 flex items-start">
                        <i class="fas fa-info-circle text-blue-600 mr-2 mt-1"></i>
                        <span>หากไม่สามารถเข้าสู่ระบบได้ กรุณาติดต่อครูจักรพงษ์</span>
                    </p>
                </div>
            </div>
            
        </div>
        
        <!-- Footer -->
        <div class="text-center mt-6 text-white">
            <p class="text-sm">&copy; <?php echo date('Y'); ?> โรงเรียนแก่นนครวิทยาลัย</p>
        </div>
    </div>
    
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
        
        // Auto-format รหัสครูให้เป็นตัวเลขเท่านั้น
        document.querySelector('input[name="teacherId"]').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
        
        // Auto-format รหัสผ่านให้เป็นตัวเลขเท่านั้น
        document.querySelector('input[name="password"]').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    </script>
    
</body>
</html>