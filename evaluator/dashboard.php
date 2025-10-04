<?php
require_once '../config/config.php';
require_once '../config/database.php';

check_role(['evaluator']);

$page_title = 'หน้าหลัก - ผู้ประเมิน';
$evaluatorGroup = $_SESSION['subjectGroup'];

// กรองตามสถานะ
$filterStatus = $_GET['status'] ?? '';

// สถิติ
$sql_total = "SELECT COUNT(*) as total FROM reports WHERE subjectGroup = ?";
$stmt = $conn->prepare($sql_total);
$stmt->bind_param("s", $evaluatorGroup);
$stmt->execute();
$total_plans = $stmt->get_result()->fetch_assoc()['total'];

$sql_pending = "SELECT COUNT(*) as total FROM reports WHERE subjectGroup = ? AND status = 'รอตรวจ'";
$stmt = $conn->prepare($sql_pending);
$stmt->bind_param("s", $evaluatorGroup);
$stmt->execute();
$pending_plans = $stmt->get_result()->fetch_assoc()['total'];

$sql_approved = "SELECT COUNT(*) as total FROM reports WHERE subjectGroup = ? AND status = 'รับทราบแล้ว'";
$stmt = $conn->prepare($sql_approved);
$stmt->bind_param("s", $evaluatorGroup);
$stmt->execute();
$approved_plans = $stmt->get_result()->fetch_assoc()['total'];

$sql_revise = "SELECT COUNT(*) as total FROM reports WHERE subjectGroup = ? AND status = 'แก้ไขใหม่'";
$stmt = $conn->prepare($sql_revise);
$stmt->bind_param("s", $evaluatorGroup);
$stmt->execute();
$revise_plans = $stmt->get_result()->fetch_assoc()['total'];

// ดึงรายการแผน
$sql = "SELECT r.*, u.name as senderName 
        FROM reports r 
        JOIN users u ON r.userId = u.id 
        WHERE r.subjectGroup = ?";
$params = [$evaluatorGroup];
$types = "s";

if ($filterStatus) {
    $sql .= " AND r.status = ?";
    $params[] = $filterStatus;
    $types .= "s";
}

$sql .= " ORDER BY r.submittedAt DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$plans = $stmt->get_result();

include '../includes/header.php';
?>

<div class="max-w-7xl mx-auto">
    <!-- Welcome Card -->
    <div class="bg-gradient-to-r from-green-600 to-green-700 rounded-xl shadow-lg p-8 mb-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold mb-2">สวัสดี, <?php echo $_SESSION['name']; ?></h1>
                <p class="text-green-100">ผู้ประเมินกลุ่มสาระ: <span class="font-bold"><?php echo $evaluatorGroup; ?></span></p>
            </div>
            <div class="hidden md:block">
                <i class="fas fa-clipboard-check text-6xl opacity-50"></i>
            </div>
        </div>
    </div>

    <!-- Statistics -->
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

        <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-yellow-500">
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

        <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-green-500">
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

        <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-red-500">
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

    <!-- Filter -->
    <div class="bg-white rounded-xl shadow-md p-6 mb-6">
        <label class="block text-gray-700 font-medium mb-3">
            <i class="fas fa-filter text-green-600 mr-2"></i>กรองตามสถานะ
        </label>
        <div class="flex flex-wrap gap-2">
            <a href="dashboard.php" class="px-4 py-2 rounded-lg transition duration-200 <?php echo !$filterStatus ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'; ?>">
                ทั้งหมด
            </a>
            <a href="dashboard.php?status=รอตรวจ" class="px-4 py-2 rounded-lg transition duration-200 <?php echo $filterStatus == 'รอตรวจ' ? 'bg-yellow-500 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'; ?>">
                <i class="fas fa-clock mr-1"></i>รอตรวจ
            </a>
            <a href="dashboard.php?status=รับทราบแล้ว" class="px-4 py-2 rounded-lg transition duration-200 <?php echo $filterStatus == 'รับทราบแล้ว' ? 'bg-green-500 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'; ?>">
                <i class="fas fa-check-circle mr-1"></i>รับทราบแล้ว
            </a>
            <a href="dashboard.php?status=แก้ไขใหม่" class="px-4 py-2 rounded-lg transition duration-200 <?php echo $filterStatus == 'แก้ไขใหม่' ? 'bg-red-500 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'; ?>">
                <i class="fas fa-edit mr-1"></i>แก้ไขใหม่
            </a>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-md overflow-hidden">
        <div class="p-6 border-b border-gray-200">
            <h2 class="text-xl font-bold text-gray-800">รายการแผนที่ต้องประเมิน</h2>
        </div>

        <?php if ($plans->num_rows > 0): ?>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">กลุ่มสาระ</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">รหัสวิชา</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">ชื่อวิชา</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">ผู้ส่ง</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">ระดับชั้น</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">วันที่ส่ง</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">สถานะ/คะแนน</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">การดำเนินการ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php while ($plan = $plans->fetch_assoc()): ?>
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 text-sm text-gray-700"><?php echo $plan['subjectGroup']; ?></td>
                        <td class="px-6 py-4 text-sm">
                            <span class="font-medium text-green-600"><?php echo $plan['subjectCode']; ?></span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-700"><?php echo $plan['subjectName']; ?></td>
                        <td class="px-6 py-4 text-sm">
                            <div class="font-medium text-gray-800"><?php echo $plan['teacherName']; ?></div>
                            <div class="text-xs text-gray-500"><?php echo $plan['senderName']; ?></div>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <span class="bg-purple-100 text-purple-800 px-3 py-1 rounded-full text-xs font-medium">
                                <?php echo $plan['gradeLevel']; ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-700">
                            <?php echo date('d/m/Y', strtotime($plan['submittedAt'])); ?>
                            <br>
                            <span class="text-xs text-gray-500"><?php echo date('H:i น.', strtotime($plan['submittedAt'])); ?></span>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <?php
                            $status_colors = [
                                'รอตรวจ' => 'bg-yellow-100 text-yellow-800',
                                'รับทราบแล้ว' => 'bg-green-100 text-green-800',
                                'แก้ไขใหม่' => 'bg-red-100 text-red-800'
                            ];
                            $color = $status_colors[$plan['status']] ?? 'bg-gray-100 text-gray-800';
                            ?>
                            <span class="px-3 py-1 rounded-full text-xs font-medium <?php echo $color; ?> block text-center mb-1">
                                <?php echo $plan['status']; ?>
                            </span>
                            <?php if ($plan['evaluation']): ?>
                            <?php
                            $eval = json_decode($plan['evaluation'], true);
                            $totalScore = $eval['totalScore'] ?? 0;
                            $percentage = $eval['percentage'] ?? 0;
                            ?>
                            <div class="text-xs text-center mt-1">
                                <span class="font-bold text-green-600"><?php echo $totalScore; ?>/35</span>
                                <span class="text-gray-500">(<?php echo number_format($percentage, 2); ?>%)</span>
                            </div>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <?php if ($plan['status'] == 'รอตรวจ' || $plan['evaluation']): ?>
                            <a href="evaluate.php?id=<?php echo $plan['id']; ?>" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg inline-block transition duration-200">
                                <i class="fas fa-<?php echo $plan['status'] == 'รอตรวจ' ? 'clipboard-check' : 'edit'; ?> mr-1"></i>
                                <?php echo $plan['status'] == 'รอตรวจ' ? 'ประเมิน' : 'แก้ไข'; ?>
                            </a>
                            <?php else: ?>
                            <a href="plan_details.php?id=<?php echo $plan['id']; ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg inline-block transition duration-200">
                                <i class="fas fa-eye mr-1"></i>ดูรายละเอียด
                            </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="text-center py-12 text-gray-500">
            <i class="fas fa-inbox text-6xl mb-4 opacity-50"></i>
            <p class="text-lg">ไม่พบแผนที่ต้องประเมิน</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>