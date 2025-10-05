<?php
require_once 'config/config.php';

// ตรวจสอบว่า login แล้วและมีหลาย roles
if (!isset($_SESSION['user_id']) || !isset($_SESSION['available_roles'])) {
    header("Location: index.php");
    exit();
}

// ถ้ามี role เดียว ไม่ควรมาหน้านี้
if (count($_SESSION['available_roles']) <= 1) {
    header("Location: index.php");
    exit();
}

// ประมวลผลเมื่อเลือก role
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $selected_role = $_POST['role'] ?? '';
    
    // ตรวจสอบว่า role ที่เลือกถูกต้อง
    if (in_array($selected_role, $_SESSION['available_roles'])) {
        $_SESSION['role'] = $selected_role;
        unset($_SESSION['available_roles']);
        
        // Redirect ตาม role
        switch($selected_role) {
            case 'teacher':
                header("Location: teacher/dashboard.php");
                break;
            case 'evaluator':
                header("Location: evaluator/dashboard.php");
                break;
            case 'admin':
                header("Location: admin/settings.php");
                break;
            default:
                header("Location: index.php");
        }
        exit();
    } else {
        $error = 'บทบาทที่เลือกไม่ถูกต้อง';
    }
}

$roleNames = [
    'teacher' => 'ครู',
    'evaluator' => 'ผู้ประเมิน',
    'admin' => 'ผู้ดูแลระบบ'
];
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เลือกบทบาท - ระบบส่งแผนการจัดการเรียนรู้</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            font-family: 'Sarabun', sans-serif;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-500 to-purple-600 min-h-screen flex items-center justify-center p-4">
    
    <div class="w-full max-w-md">
        <div class="bg-white rounded-2xl shadow-2xl overflow-hidden">
            
            <!-- Header -->
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 p-8 text-white text-center">
                <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg">
                    <i class="fas fa-user-check text-blue-600 text-3xl"></i>
                </div>
                <h1 class="text-2xl font-bold mb-2">เลือกบทบาทการเข้าใช้งาน</h1>
                <p class="text-blue-100">สวัสดี <?php echo htmlspecialchars($_SESSION['name']); ?></p>
            </div>
            
            <!-- Content -->
            <div class="p-8">
                <?php if (isset($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <?php echo $error; ?>
                </div>
                <?php endif; ?>
                
                <p class="text-gray-700 mb-6 text-center">
                    คุณมีหลายบทบาทในระบบ<br>
                    กรุณาเลือกบทบาทที่ต้องการใช้งาน
                </p>
                
                <form method="POST" class="space-y-4">
                    <?php foreach ($_SESSION['available_roles'] as $role): ?>
                    <button 
                        type="submit" 
                        name="role" 
                        value="<?php echo htmlspecialchars($role); ?>"
                        class="w-full bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white py-4 px-6 rounded-lg text-lg font-semibold transition duration-200 transform hover:scale-105 shadow-lg flex items-center justify-center"
                    >
                        <i class="fas fa-<?php 
                            echo $role == 'teacher' ? 'chalkboard-teacher' : 
                                 ($role == 'evaluator' ? 'clipboard-check' : 'user-shield'); 
                        ?> mr-3"></i>
                        เข้าใช้ในฐานะ<?php echo $roleNames[$role] ?? $role; ?>
                    </button>
                    <?php endforeach; ?>
                </form>
                
                <!-- Info -->
                <div class="mt-6 p-4 bg-blue-50 rounded-lg">
                    <p class="text-sm text-gray-600 flex items-start">
                        <i class="fas fa-info-circle text-blue-600 mr-2 mt-1"></i>
                        <span>คุณสามารถออกจากระบบและเข้าใหม่เพื่อเปลี่ยนบทบาทได้ตลอดเวลา</span>
                    </p>
                </div>
                
                <a 
                    href="logout.php" 
                    class="block text-center mt-6 text-gray-600 hover:text-gray-800 font-medium"
                >
                    <i class="fas fa-sign-out-alt mr-2"></i>ออกจากระบบ
                </a>
            </div>
            
        </div>
        
        <!-- Footer -->
        <div class="text-center mt-6 text-white">
            <p class="text-sm">&copy; <?php echo date('Y'); ?> โรงเรียนแก่นนครวิทยาลัย</p>
        </div>
    </div>
    
</body>
</html>