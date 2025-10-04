<?php
require_once '../config/config.php';
require_once '../config/database.php';

check_role(['teacher']);

$page_title = 'หน้าหลัก - ครู';

// ดึงสถิติของครูคนนี้
$userId = $_SESSION['user_id'];

// นับจำนวนแผนทั้งหมด
$sql_total = "SELECT COUNT(*) as total FROM reports WHERE userId = ?";
$stmt = $conn->prepare($sql_total);
$stmt->bind_param("i", $userId);
$stmt->execute();
$total_plans = $stmt->get_result()->fetch_assoc()['total'];

// นับจำนวนรอตรวจ
$sql_pending = "SELECT COUNT(*) as total FROM reports WHERE userId = ? AND status = 'รอตรวจ'";
$stmt = $conn->prepare($sql_pending);
$stmt->bind_param("i", $userId);
$stmt->execute();
$pending_plans = $stmt->get_result()->fetch_assoc()['total'];

// นับจำนวนรับทราบแล้ว
$sql_approved = "SELECT COUNT(*) as total FROM reports WHERE userId = ? AND status = 'รับทราบแล้ว'";
$stmt = $conn->prepare($sql_approved);
$stmt->bind_param("i", $userId);
$stmt->execute();
$approved_plans = $stmt->get_result()->fetch_assoc()['total'];

// นับจำนวนแก้ไขใหม่
$sql_revise = "SELECT COUNT(*) as total FROM reports WHERE userId = ? AND status = 'แก้ไขใหม่'";
$stmt = $conn->prepare($sql_revise);
$stmt->bind_param("i", $userId);
$stmt->execute();
$revise_plans = $stmt->get_result()->fetch_assoc()['total'];

// ดึงแผนล่าสุด 5 รายการ
$sql_recent = "SELECT * FROM reports WHERE userId = ? ORDER BY submittedAt DESC LIMIT 5";
$stmt = $conn->prepare($sql_recent);
$stmt->bind_param("i", $userId);
$stmt->execute();
$recent_plans = $stmt->get_result();

// ตรวจสอบว่าระบบเปิดให้ส่งแผนหรือไม่
$upload_status = is_upload_enabled();

include '../includes/header.php';
?>

<div class="max-w-7xl mx-auto">
    <!-- Welcome Card -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-xl shadow-lg p-8 mb-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold mb-2">สวัสดี, <?php echo $_SESSION['name']; ?></h1>
                <p class="text-blue-100">ยินดีต้อนรับสู่ระบบส่งแผนการจัดการเรียนรู้</p>
            </div>
            <div class="hidden md:block">
                <i class="fas fa-chalkboard-teacher text-6xl opacity-50"></i>
            </div>
        </div>
    </div>

    <!-- System Status Alert -->
    <?php if (!$upload_status): ?>
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-lg">
        <div class="flex items-center">
            <i class="fas fa-exclamation-triangle mr-3 text-xl"></i>
            <div>
                <p class="font-bold">ระบบปิดการส่งแผนชั่วคราว</p>
                <p class="text-sm">กรุณาติดต่อผู้ดูแลระบบเพื่อขอเปิดช่วงเวลาส่งแผน</p>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-lg">
        <div class="flex items-center">
            <i class="fas fa-check-circle mr-3 text-xl"></i>
            <p><span class="font-bold">ระบบเปิดให้ส่งแผน</span> - สามารถอัปโหลดแผนการเรียนรู้ได้</p>
        </div>
    </div>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Plans -->
        <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-blue-500 hover:shadow-lg transition duration-200">
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

        <!-- Pending -->
        <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-yellow-500 hover:shadow-lg transition duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm mb-1">รอตรวจ</p>
                    <p class="text-3xl font-bold text-yellow-600"><?php echo $pending_plans; ?></p>
                </div>
                <div class="bg-yellow-100 p-4 rounded-full">
                    <i class="fas fa-clock text-2xl text-yellow-600"></i>
                </div>
            </div>
        </div>

        <!-- Approved -->
        <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-green-500 hover:shadow-lg transition duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm mb-1">รับทราบแล้ว</p>
                    <p class="text-3xl font-bold text-green-600"><?php echo $approved_plans; ?></p>
                </div>
                <div class="bg-green-100 p-4 rounded-full">
                    <i class="fas fa-check-circle text-2xl text-green-600"></i>
                </div>
            </div>
        </div>

        <!-- Revise -->
        <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-red-500 hover:shadow-lg transition duration-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm mb-1">แก้ไขใหม่</p>
                    <p class="text-3xl font-bold text-red-600"><?php echo $revise_plans; ?></p>
                </div>
                <div class="bg-red-100 p-4 rounded-full">
                    <i class="fas fa-edit text-2xl text-red-600"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-xl shadow-md p-6 mb-8">
        <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
            <i class="fas fa-bolt text-yellow-500 mr-2"></i>
            การดำเนินการด่วน
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <a href="submit_plan.php" class="bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white p-6 rounded-lg shadow-md hover:shadow-lg transition duration-200 flex items-center justify-between group <?php echo !$upload_status ? 'opacity-50 cursor-not-allowed' : ''; ?>" <?php echo !$upload_status ? 'onclick="return false;"' : ''; ?>>
                <div>
                    <h3 class="font-bold text-lg mb-1">ส่งแผนการเรียนรู้</h3>
                    <p class="text-blue-100 text-sm">อัปโหลดแผนการสอนใหม่</p>
                </div>
                <i class="fas fa-upload text-3xl opacity-75 group-hover:opacity-100 transition"></i>
            </a>

            <a href="my_plans.php" class="bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white p-6 rounded-lg shadow-md hover:shadow-lg transition duration-200 flex items-center justify-between group">
                <div>
                    <h3 class="font-bold text-lg mb-1">ประวัติการส่งแผน</h3>
                    <p class="text-green-100 text-sm">ดูแผนที่ส่งทั้งหมด</p>
                </div>
                <i class="fas fa-history text-3xl opacity-75 group-hover:opacity-100 transition"></i>
            </a>
        </div>
    </div>

    <!-- Recent Plans -->
    <div class="bg-white rounded-xl shadow-md p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
            <i class="fas fa-clock text-blue-500 mr-2"></i>
            แผนล่าสุด
        </h2>

        <?php if ($recent_plans->num_rows > 0): ?>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">กลุ่มสาระ</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">วิชา</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">ระดับชั้น</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">วันที่ส่ง</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">สถานะ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php while ($plan = $recent_plans->fetch_assoc()): ?>
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3 text-sm"><?php echo $plan['subjectGroup']; ?></td>
                        <td class="px-4 py-3 text-sm">
                            <div class="font-medium"><?php echo $plan['subjectCode']; ?></div>
                            <div class="text-gray-500 text-xs"><?php echo $plan['subjectName']; ?></div>
                        </td>
                        <td class="px-4 py-3 text-sm"><?php echo $plan['gradeLevel']; ?></td>
                        <td class="px-4 py-3 text-sm"><?php echo date('d/m/Y H:i', strtotime($plan['submittedAt'])); ?></td>
                        <td class="px-4 py-3 text-sm">
                            <?php
                            $status_colors = [
                                'รอตรวจ' => 'bg-yellow-100 text-yellow-800',
                                'รับทราบแล้ว' => 'bg-green-100 text-green-800',
                                'แก้ไขใหม่' => 'bg-red-100 text-red-800'
                            ];
                            $color = $status_colors[$plan['status']] ?? 'bg-gray-100 text-gray-800';
                            ?>
                            <span class="px-3 py-1 rounded-full text-xs font-medium <?php echo $color; ?>">
                                <?php echo $plan['status']; ?>
                            </span>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <div class="mt-4 text-center">
            <a href="my_plans.php" class="text-blue-600 hover:text-blue-700 font-medium text-sm inline-flex items-center">
                ดูทั้งหมด
                <i class="fas fa-arrow-right ml-2"></i>
            </a>
        </div>
        <?php else: ?>
        <div class="text-center py-8 text-gray-500">
            <i class="fas fa-inbox text-5xl mb-4 opacity-50"></i>
            <p>ยังไม่มีแผนที่ส่ง</p>
            <a href="submit_plan.php" class="text-blue-600 hover:text-blue-700 mt-2 inline-block">
                เริ่มส่งแผนเลย →
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>s