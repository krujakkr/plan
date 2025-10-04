<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'ระบบส่งแผนการจัดการเรียนรู้'; ?></title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            font-family: 'Sarabun', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-50">
    
    <!-- Navbar -->
    <?php if (isset($_SESSION['user_id'])): ?>
    <nav class="bg-blue-600 text-white shadow-lg">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between h-16">
                <!-- Logo -->
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-white rounded-full p-1 flex items-center justify-center">
                        <img src="https://knw.ac.th/wp-content/uploads/2025/02/knw_svg_logo.svg" alt="โลโก้โรงเรียน" class="w-full h-full object-contain">
                    </div>
                    <div>
                        <p class="text-lg font-bold leading-tight">ระบบส่งแผนการจัดการเรียนรู้</p>
                        <p class="text-xs text-blue-100">โรงเรียนแก่นนครวิทยาลัย</p>
                    </div>
                </div>
                
                <!-- User Info -->
                <div class="flex items-center space-x-4">
                    <div class="hidden md:block text-right">
                        <p class="font-medium"><?php echo $_SESSION['name']; ?></p>
                        <p class="text-sm text-blue-200">
                            <?php 
                            $role_text = [
                                'teacher' => 'ครูผู้สอน',
                                'evaluator' => 'ผู้ประเมิน',
                                'admin' => 'ผู้ดูแลระบบ'
                            ];
                            echo $role_text[$_SESSION['role']] ?? '';
                            ?>
                        </p>
                    </div>
                    <a href="../logout.php" class="bg-red-500 hover:bg-red-600 px-4 py-2 rounded-lg transition duration-200">
                        <i class="fas fa-sign-out-alt mr-2"></i>ออกจากระบบ
                    </a>
                </div>
            </div>
        </div>
    </nav>
    <?php endif; ?>
    
    <!-- Main Container -->
    <div class="<?php echo isset($_SESSION['user_id']) ? 'flex' : ''; ?>">
        
        <!-- Sidebar -->
        <?php if (isset($_SESSION['user_id'])): ?>
        <aside class="w-64 bg-white shadow-lg min-h-screen">
            <div class="p-4">
                
                <?php if ($_SESSION['role'] == 'teacher'): ?>
                <!-- เมนูครู -->
                <nav class="space-y-2">
                    <?php $current_page = basename($_SERVER['PHP_SELF']); ?>
                    <a href="./dashboard.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-blue-50 text-gray-700 hover:text-blue-600 transition duration-200 <?php echo $current_page == 'dashboard.php' ? 'bg-blue-50 text-blue-600' : ''; ?>">
                        <i class="fas fa-home w-5"></i>
                        <span>หน้าหลัก</span>
                    </a>
                    <a href="./submit_plan.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-blue-50 text-gray-700 hover:text-blue-600 transition duration-200 <?php echo $current_page == 'submit_plan.php' ? 'bg-blue-50 text-blue-600' : ''; ?>">
                        <i class="fas fa-upload w-5"></i>
                        <span>ส่งแผนการเรียนรู้</span>
                    </a>
                    <a href="./my_plans.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-blue-50 text-gray-700 hover:text-blue-600 transition duration-200 <?php echo $current_page == 'my_plans.php' ? 'bg-blue-50 text-blue-600' : ''; ?>">
                        <i class="fas fa-file-alt w-5"></i>
                        <span>ประวัติการส่งแผน</span>
                    </a>
                </nav>
                
                <?php elseif ($_SESSION['role'] == 'evaluator'): ?>
                <!-- เมนูผู้ประเมิน -->
                <nav class="space-y-2">
                    <?php $current_page = basename($_SERVER['PHP_SELF']); ?>
                    <a href="./dashboard.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-green-50 text-gray-700 hover:text-green-600 transition duration-200 <?php echo $current_page == 'dashboard.php' ? 'bg-green-50 text-green-600' : ''; ?>">
                        <i class="fas fa-home w-5"></i>
                        <span>หน้าหลัก</span>
                    </a>
                    <a href="./dashboard.php?status=รอตรวจ" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-green-50 text-gray-700 hover:text-green-600 transition duration-200">
                        <i class="fas fa-clipboard-check w-5"></i>
                        <span>รายการรอตรวจ</span>
                    </a>
                </nav>
                
                <?php elseif ($_SESSION['role'] == 'admin'): ?>
                <!-- เมนูผู้ดูแลระบบ -->
                <nav class="space-y-2">
                    <?php $current_page = basename($_SERVER['PHP_SELF']); ?>
                    <a href="./settings.php" class="flex items-center space-x-3 px-4 py-3 rounded-lg hover:bg-purple-50 text-gray-700 hover:text-purple-600 transition duration-200 <?php echo $current_page == 'settings.php' ? 'bg-purple-50 text-purple-600' : ''; ?>">
                        <i class="fas fa-cog w-5"></i>
                        <span>ตั้งค่าระบบ</span>
                    </a>
                </nav>
                <?php endif; ?>
                
            </div>
        </aside>
        <?php endif; ?>
        
        <!-- Main Content -->
        <main class="flex-1 p-6">