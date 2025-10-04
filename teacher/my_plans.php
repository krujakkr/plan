<?php
require_once '../config/config.php';
require_once '../config/database.php';

check_role(['teacher']);

$page_title = 'ประวัติการส่งแผน';
$userId = $_SESSION['user_id'];

// กรองตามกลุ่มสาระ
$filterGroup = $_GET['group'] ?? '';

// สร้าง SQL query
$sql = "SELECT * FROM reports WHERE userId = ?";
$params = [$userId];
$types = "i";

if ($filterGroup) {
    $sql .= " AND subjectGroup = ?";
    $params[] = $filterGroup;
    $types .= "s";
}

$sql .= " ORDER BY submittedAt DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$plans = $stmt->get_result();

include '../includes/header.php';
?>

<div class="max-w-7xl mx-auto">
    <!-- Header -->
    <div class="bg-white rounded-xl shadow-md p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 flex items-center">
                    <i class="fas fa-history text-blue-600 mr-3"></i>
                    ประวัติการส่งแผน
                </h1>
                <p class="text-gray-600 mt-2">แผนการจัดการเรียนรู้ทั้งหมดของคุณ</p>
            </div>
            <a href="submit_plan.php" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg transition duration-200 shadow-md">
                <i class="fas fa-plus mr-2"></i>ส่งแผนใหม่
            </a>
        </div>
    </div>

    <!-- Filter -->
    <div class="bg-white rounded-xl shadow-md p-6 mb-6">
        <label class="block text-gray-700 font-medium mb-3">
            <i class="fas fa-filter text-blue-600 mr-2"></i>กรองตามกลุ่มสาระ
        </label>
        <div class="flex flex-wrap gap-2">
            <a href="my_plans.php" class="px-4 py-2 rounded-lg transition duration-200 <?php echo !$filterGroup ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'; ?>">
                ทั้งหมด
            </a>
            <?php foreach (SUBJECT_GROUPS as $group): ?>
            <a href="my_plans.php?group=<?php echo urlencode($group); ?>" class="px-4 py-2 rounded-lg transition duration-200 <?php echo $filterGroup == $group ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'; ?>">
                <?php echo $group; ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl shadow-md overflow-hidden">
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
                            <span class="font-medium text-blue-600"><?php echo $plan['subjectCode']; ?></span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-700"><?php echo $plan['subjectName']; ?></td>
                        <td class="px-6 py-4 text-sm text-gray-700"><?php echo $plan['teacherName']; ?></td>
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
                                <span class="font-bold text-blue-600"><?php echo $totalScore; ?>/35</span>
                                <span class="text-gray-500">(<?php echo number_format($percentage, 2); ?>%)</span>
                            </div>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <a href="plan_details.php?id=<?php echo $plan['id']; ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg inline-block transition duration-200">
                                <i class="fas fa-eye mr-1"></i>ดูรายละเอียด
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="text-center py-12 text-gray-500">
            <i class="fas fa-inbox text-6xl mb-4 opacity-50"></i>
            <p class="text-lg mb-2">ไม่พบข้อมูลแผนการเรียนรู้</p>
            <a href="submit_plan.php" class="text-blue-600 hover:text-blue-700 mt-2 inline-block">
                <i class="fas fa-plus mr-2"></i>เริ่มส่งแผนเลย
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>