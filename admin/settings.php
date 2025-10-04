<?php
require_once '../config/config.php';
require_once '../config/database.php';

check_role(['admin']);

$page_title = 'ตั้งค่าระบบ';

$success = '';
$error = '';

// ดึงการตั้งค่าปัจจุบัน
$sql = "SELECT * FROM system_settings WHERE id = 1";
$result = $conn->query($sql);
$settings = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $upload_enabled = isset($_POST['upload_enabled']) ? 1 : 0;
    $start_date = clean_input($_POST['start_date'] ?? '');
    $end_date = clean_input($_POST['end_date'] ?? '');
    
    if (empty($start_date) || empty($end_date)) {
        $error = 'กรุณาระบุวันที่เริ่มต้นและวันที่สิ้นสุด';
    } elseif ($start_date > $end_date) {
        $error = 'วันที่เริ่มต้นต้องไม่มากกว่าวันที่สิ้นสุด';
    } else {
        $update_sql = "UPDATE system_settings SET 
                       upload_enabled = ?, 
                       start_date = ?, 
                       end_date = ? 
                       WHERE id = 1";
        
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("iss", $upload_enabled, $start_date, $end_date);
        
        if ($stmt->execute()) {
            $success = 'บันทึกการตั้งค่าสำเร็จ!';
            
            // โหลดข้อมูลใหม่
            $result = $conn->query($sql);
            $settings = $result->fetch_assoc();
        } else {
            $error = 'เกิดข้อผิดพลาดในการบันทึกข้อมูล';
        }
    }
}

// สถิติรวม
$sql_total_plans = "SELECT COUNT(*) as total FROM reports";
$total_plans = $conn->query($sql_total_plans)->fetch_assoc()['total'];

$sql_total_users = "SELECT COUNT(*) as total FROM users WHERE role = 'teacher'";
$total_teachers = $conn->query($sql_total_users)->fetch_assoc()['total'];

$sql_total_evaluators = "SELECT COUNT(*) as total FROM users WHERE role = 'evaluator'";
$total_evaluators = $conn->query($sql_total_evaluators)->fetch_assoc()['total'];

// สถิติตามกลุ่มสาระ
$sql_by_group = "SELECT subjectGroup, COUNT(*) as total FROM reports GROUP BY subjectGroup ORDER BY total DESC";
$stats_by_group = $conn->query($sql_by_group);

// สถิติตามสถานะ
$sql_pending = "SELECT COUNT(*) as total FROM reports WHERE status = 'รอตรวจ'";
$pending = $conn->query($sql_pending)->fetch_assoc()['total'];

$sql_approved = "SELECT COUNT(*) as total FROM reports WHERE status = 'รับทราบแล้ว'";
$approved = $conn->query($sql_approved)->fetch_assoc()['total'];

$sql_revise = "SELECT COUNT(*) as total FROM reports WHERE status = 'แก้ไขใหม่'";
$revise = $conn->query($sql_revise)->fetch_assoc()['total'];

include '../includes/header.php';
?>

<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="bg-gradient-to-r from-purple-600 to-purple-700 rounded-xl shadow-lg p-8 mb-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold mb-2">ตั้งค่าระบบ</h1>
                <p class="text-purple-100">จัดการระบบส่งแผนการจัดการเรียนรู้</p>
            </div>
            <div class="hidden md:block">
                <i class="fas fa-cogs text-6xl opacity-50"></i>
            </div>
        </div>
    </div>

    <!-- Alert Messages -->
    <?php if ($success): ?>
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-lg">
        <div class="flex items-center">
            <i class="fas fa-check-circle mr-3 text-xl"></i>
            <span><?php echo $success; ?></span>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($error): ?>
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-lg">
        <div class="flex items-center">
            <i class="fas fa-exclamation-circle mr-3 text-xl"></i>
            <span><?php echo $error; ?></span>
        </div>
    </div>
    <?php endif; ?>

    <!-- Statistics Overview -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm mb-1">แผนทั้งหมด</p>
                    <p class="text-3xl font-bold text-blue-600"><?php echo $total_plans; ?></p>
                </div>
                <div class="bg-blue-100 p-4 rounded-full">
                    <i class="fas fa-file-alt text-2xl text-blue-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm mb-1">ครูทั้งหมด</p>
                    <p class="text-3xl font-bold text-green-600"><?php echo $total_teachers; ?></p>
                </div>
                <div class="bg-green-100 p-4 rounded-full">
                    <i class="fas fa-users text-2xl text-green-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-purple-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm mb-1">ผู้ประเมิน</p>
                    <p class="text-3xl font-bold text-purple-600"><?php echo $total_evaluators; ?></p>
                </div>
                <div class="bg-purple-100 p-4 rounded-full">
                    <i class="fas fa-user-check text-2xl text-purple-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-yellow-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm mb-1">รอตรวจ</p>
                    <p class="text-3xl font-bold text-yellow-600"><?php echo $pending; ?></p>
                </div>
                <div class="bg-yellow-100 p-4 rounded-full">
                    <i class="fas fa-clock text-2xl text-yellow-600"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- System Settings -->
        <div class="bg-white rounded-xl shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-sliders-h text-purple-600 mr-2"></i>
                ตั้งค่าระบบอัปโหลด
            </h2>

            <form method="POST">
                <!-- Enable/Disable Upload -->
                <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                    <label class="flex items-center cursor-pointer">
                        <input 
                            type="checkbox" 
                            name="upload_enabled" 
                            <?php echo $settings['upload_enabled'] ? 'checked' : ''; ?>
                            class="w-6 h-6 text-purple-600 rounded focus:ring-purple-500"
                        >
                        <span class="ml-3 text-gray-700 font-medium">
                            <i class="fas fa-toggle-on text-purple-600 mr-2"></i>เปิดให้อัปโหลดแผน
                        </span>
                    </label>
                    <p class="text-sm text-gray-500 mt-2 ml-9">
                        เมื่อปิด ครูจะไม่สามารถส่งแผนได้
                    </p>
                </div>

                <!-- Start Date -->
                <div class="mb-4">
                    <label class="block text-gray-700 font-medium mb-2">
                        <i class="fas fa-calendar-alt text-purple-600 mr-2"></i>วันที่เริ่มต้น
                    </label>
                    <input 
                        type="date" 
                        name="start_date" 
                        value="<?php echo $settings['start_date']; ?>"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                        required
                    >
                </div>

                <!-- End Date -->
                <div class="mb-6">
                    <label class="block text-gray-700 font-medium mb-2">
                        <i class="fas fa-calendar-alt text-purple-600 mr-2"></i>วันที่สิ้นสุด
                    </label>
                    <input 
                        type="date" 
                        name="end_date" 
                        value="<?php echo $settings['end_date']; ?>"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                        required
                    >
                </div>

                <!-- Current Status -->
                <div class="mb-6 p-4 <?php echo is_upload_enabled() ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200'; ?> border rounded-lg">
                    <p class="font-medium <?php echo is_upload_enabled() ? 'text-green-800' : 'text-red-800'; ?>">
                        <i class="fas fa-<?php echo is_upload_enabled() ? 'check-circle' : 'times-circle'; ?> mr-2"></i>
                        สถานะปัจจุบัน: <?php echo is_upload_enabled() ? 'เปิดใช้งาน' : 'ปิดใช้งาน'; ?>
                    </p>
                    <?php if ($settings['start_date'] && $settings['end_date']): ?>
                    <p class="text-sm mt-1 <?php echo is_upload_enabled() ? 'text-green-700' : 'text-red-700'; ?>">
                        ช่วงเวลา: <?php echo date('d/m/Y', strtotime($settings['start_date'])); ?> - 
                        <?php echo date('d/m/Y', strtotime($settings['end_date'])); ?>
                    </p>
                    <?php endif; ?>
                </div>

                <!-- Submit Button -->
                <button 
                    type="submit"
                    class="w-full bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800 text-white font-bold py-3 px-6 rounded-lg transition duration-200"
                >
                    <i class="fas fa-save mr-2"></i>บันทึกการตั้งค่า
                </button>
            </form>
        </div>

        <!-- Statistics -->
        <div class="space-y-6">
            <!-- Status Stats -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-chart-pie text-blue-600 mr-2"></i>
                    สถิติตามสถานะ
                </h2>
                
                <div class="space-y-3">
                    <div class="flex items-center justify-between p-3 bg-yellow-50 rounded-lg">
                        <div class="flex items-center">
                            <i class="fas fa-clock text-yellow-600 text-xl mr-3"></i>
                            <span class="text-gray-700">รอตรวจ</span>
                        </div>
                        <span class="font-bold text-yellow-600 text-xl"><?php echo $pending; ?></span>
                    </div>
                    
                    <div class="flex items-center justify-between p-3 bg-green-50 rounded-lg">
                        <div class="flex items-center">
                            <i class="fas fa-check-circle text-green-600 text-xl mr-3"></i>
                            <span class="text-gray-700">รับทราบแล้ว</span>
                        </div>
                        <span class="font-bold text-green-600 text-xl"><?php echo $approved; ?></span>
                    </div>
                    
                    <div class="flex items-center justify-between p-3 bg-red-50 rounded-lg">
                        <div class="flex items-center">
                            <i class="fas fa-edit text-red-600 text-xl mr-3"></i>
                            <span class="text-gray-700">แก้ไขใหม่</span>
                        </div>
                        <span class="font-bold text-red-600 text-xl"><?php echo $revise; ?></span>
                    </div>
                </div>
            </div>

            <!-- Group Stats -->
            <div class="bg-white rounded-xl shadow-md p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-chart-bar text-green-600 mr-2"></i>
                    จำนวนแผนตามกลุ่มสาระ
                </h2>
                
                <div class="space-y-2">
                    <?php 
                    $colors = ['blue', 'green', 'purple', 'pink', 'yellow', 'indigo', 'red', 'orange', 'teal'];
                    $i = 0;
                    while ($stat = $stats_by_group->fetch_assoc()): 
                        $color = $colors[$i % count($colors)];
                        $percentage = $total_plans > 0 ? ($stat['total'] / $total_plans) * 100 : 0;
                    ?>
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-sm text-gray-700"><?php echo $stat['subjectGroup']; ?></span>
                            <span class="text-sm font-bold text-<?php echo $color; ?>-600">
                                <?php echo $stat['total']; ?> แผน
                            </span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-<?php echo $color; ?>-500 h-2 rounded-full" style="width: <?php echo $percentage; ?>%"></div>
                        </div>
                    </div>
                    <?php 
                        $i++;
                    endwhile; 
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>