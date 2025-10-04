<?php
require_once '../config/config.php';
require_once '../config/database.php';

check_role(['evaluator']);

$page_title = 'รายละเอียดแผน';
$evaluatorGroup = $_SESSION['subjectGroup'];

// ตรวจสอบ ID
if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$planId = (int)$_GET['id'];

// ดึงข้อมูลแผน (ต้องเป็นกลุ่มสาระของผู้ประเมินเท่านั้น)
$sql = "SELECT r.*, u.name as senderName 
        FROM reports r 
        JOIN users u ON r.userId = u.id 
        WHERE r.id = ? AND r.subjectGroup = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $planId, $evaluatorGroup);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header("Location: dashboard.php");
    exit();
}

$plan = $result->fetch_assoc();
$evaluation = $plan['evaluation'] ? json_decode($plan['evaluation'], true) : null;

include '../includes/header.php';
?>

<div class="max-w-5xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <a href="dashboard.php" class="text-green-600 hover:text-green-700 inline-flex items-center mb-4">
            <i class="fas fa-arrow-left mr-2"></i>กลับ
        </a>
        <div class="bg-white rounded-xl shadow-md p-6">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold text-gray-800 flex items-center">
                    <i class="fas fa-file-alt text-green-600 mr-3"></i>
                    รายละเอียดแผนการจัดการเรียนรู้
                </h1>
                <?php if ($evaluation): ?>
                <a href="evaluate.php?id=<?php echo $plan['id']; ?>" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition duration-200">
                    <i class="fas fa-edit mr-1"></i>แก้ไขการประเมิน
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Plan Information -->
    <div class="bg-white rounded-xl shadow-md p-6 mb-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
            <i class="fas fa-info-circle text-blue-600 mr-2"></i>
            ข้อมูลแผน
        </h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-gray-50 p-4 rounded-lg">
                <p class="text-sm text-gray-500 mb-1">กลุ่มสาระการเรียนรู้</p>
                <p class="font-medium text-gray-800"><?php echo $plan['subjectGroup']; ?></p>
            </div>
            
            <div class="bg-gray-50 p-4 rounded-lg">
                <p class="text-sm text-gray-500 mb-1">ผู้สอน</p>
                <p class="font-medium text-gray-800"><?php echo $plan['teacherName']; ?></p>
            </div>
            
            <div class="bg-gray-50 p-4 rounded-lg">
                <p class="text-sm text-gray-500 mb-1">ผู้ส่งแผน</p>
                <p class="font-medium text-gray-800"><?php echo $plan['senderName']; ?></p>
            </div>
            
            <div class="bg-gray-50 p-4 rounded-lg">
                <p class="text-sm text-gray-500 mb-1">รหัสวิชา</p>
                <p class="font-medium text-gray-800"><?php echo $plan['subjectCode']; ?></p>
            </div>
            
            <div class="bg-gray-50 p-4 rounded-lg">
                <p class="text-sm text-gray-500 mb-1">ชื่อวิชา</p>
                <p class="font-medium text-gray-800"><?php echo $plan['subjectName']; ?></p>
            </div>
            
            <div class="bg-gray-50 p-4 rounded-lg">
                <p class="text-sm text-gray-500 mb-1">ระดับชั้น</p>
                <p class="font-medium text-gray-800"><?php echo $plan['gradeLevel']; ?></p>
            </div>
            
            <div class="bg-gray-50 p-4 rounded-lg">
                <p class="text-sm text-gray-500 mb-1">วันที่ส่ง</p>
                <p class="font-medium text-gray-800"><?php echo date('d/m/Y H:i น.', strtotime($plan['submittedAt'])); ?></p>
            </div>
            
            <div class="bg-gray-50 p-4 rounded-lg">
                <p class="text-sm text-gray-500 mb-1">สถานะ</p>
                <?php
                $status_colors = [
                    'รอตรวจ' => 'bg-yellow-100 text-yellow-800',
                    'รับทราบแล้ว' => 'bg-green-100 text-green-800',
                    'แก้ไขใหม่' => 'bg-red-100 text-red-800'
                ];
                $color = $status_colors[$plan['status']] ?? 'bg-gray-100 text-gray-800';
                ?>
                <span class="px-4 py-2 rounded-lg text-sm font-medium <?php echo $color; ?> inline-block">
                    <?php echo $plan['status']; ?>
                </span>
            </div>
            
            <div class="bg-gray-50 p-4 rounded-lg md:col-span-2">
                <p class="text-sm text-gray-500 mb-1">รูปแบบการสอน</p>
                <div class="flex flex-wrap gap-2 mt-2">
                    <?php
                    $methods = explode(', ', $plan['teachingMethod']);
                    foreach ($methods as $method):
                    ?>
                    <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-xs font-medium">
                        <?php echo $method; ?>
                    </span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Files -->
    <div class="bg-white rounded-xl shadow-md p-6 mb-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
            <i class="fas fa-paperclip text-green-600 mr-2"></i>
            ไฟล์แนบ
        </h2>
        
        <div class="space-y-3">
            <!-- แผนการเรียนรู้ -->
            <div class="flex items-center justify-between p-4 bg-red-50 border border-red-200 rounded-lg">
                <div class="flex items-center">
                    <i class="fas fa-file-pdf text-3xl text-red-600 mr-4"></i>
                    <div>
                        <p class="font-medium text-gray-800">แผนการจัดการเรียนรู้</p>
                        <p class="text-sm text-gray-500"><?php echo $plan['pdfFile']; ?></p>
                    </div>
                </div>
                <div class="flex space-x-2">
                    <a href="../uploads/plans/<?php echo $plan['pdfFile']; ?>" target="_blank" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition duration-200">
                        <i class="fas fa-eye mr-1"></i>เปิดดู
                    </a>
                    <a href="../uploads/plans/<?php echo $plan['pdfFile']; ?>" download class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition duration-200">
                        <i class="fas fa-download mr-1"></i>ดาวน์โหลด
                    </a>
                </div>
            </div>
            
            <!-- บันทึกหลังแผน -->
            <?php if ($plan['reflectionFile']): ?>
            <div class="flex items-center justify-between p-4 bg-green-50 border border-green-200 rounded-lg">
                <div class="flex items-center">
                    <i class="fas fa-file-pdf text-3xl text-green-600 mr-4"></i>
                    <div>
                        <p class="font-medium text-gray-800">บันทึกหลังแผน</p>
                        <p class="text-sm text-gray-500"><?php echo $plan['reflectionFile']; ?></p>
                    </div>
                </div>
                <div class="flex space-x-2">
                    <a href="../uploads/reflections/<?php echo $plan['reflectionFile']; ?>" target="_blank" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition duration-200">
                        <i class="fas fa-eye mr-1"></i>เปิดดู
                    </a>
                    <a href="../uploads/reflections/<?php echo $plan['reflectionFile']; ?>" download class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition duration-200">
                        <i class="fas fa-download mr-1"></i>ดาวน์โหลด
                    </a>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Evaluation -->
    <?php if ($evaluation): ?>
    <div class="bg-white rounded-xl shadow-md p-6 mb-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
            <i class="fas fa-star text-yellow-500 mr-2"></i>
            ผลการประเมิน
        </h2>
        
        <!-- คะแนนรวม -->
        <div class="bg-gradient-to-r from-green-500 to-green-600 text-white p-6 rounded-lg mb-6">
            <div class="text-center">
                <p class="text-lg mb-2">คะแนนรวม</p>
                <p class="text-5xl font-bold"><?php echo $evaluation['totalScore']; ?><span class="text-2xl">/35</span></p>
                <p class="text-xl mt-2">คิดเป็น <?php echo number_format($evaluation['percentage'], 2); ?>%</p>
            </div>
        </div>
        
        <!-- คะแนนรายข้อ -->
        <h3 class="font-bold text-gray-800 mb-3">คะแนนรายข้อ</h3>
        <div class="space-y-2">
            <?php foreach (EVALUATION_CRITERIA as $index => $criterion): ?>
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <span class="text-sm text-gray-700"><?php echo $criterion; ?></span>
                <div class="flex items-center space-x-2">
                    <?php
                    $score = $evaluation['scores'][$index] ?? 0;
                    for ($i = 1; $i <= 5; $i++):
                        $filled = $i <= $score;
                    ?>
                    <i class="fas fa-star <?php echo $filled ? 'text-yellow-400' : 'text-gray-300'; ?>"></i>
                    <?php endfor; ?>
                    <span class="font-bold text-green-600 ml-2"><?php echo $score; ?></span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- ข้อเสนอแนะ -->
        <?php if ($plan['comments']): ?>
        <div class="mt-6 p-4 bg-yellow-50 border-l-4 border-yellow-400 rounded-lg">
            <p class="font-bold text-gray-800 mb-2">
                <i class="fas fa-comment-dots text-yellow-600 mr-2"></i>ข้อเสนอแนะ
            </p>
            <p class="text-gray-700 whitespace-pre-line"><?php echo nl2br($plan['comments']); ?></p>
        </div>
        <?php endif; ?>
        
        <!-- ผู้ประเมิน -->
        <?php if ($plan['evaluatorName']): ?>
        <div class="mt-4 text-sm text-gray-500 text-right">
            <i class="fas fa-user-check mr-1"></i>
            ประเมินโดย: <?php echo $plan['evaluatorName']; ?>
            <?php if ($plan['evaluatedAt']): ?>
            | <?php echo date('d/m/Y H:i น.', strtotime($plan['evaluatedAt'])); ?>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
    <?php else: ?>
    <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 rounded-lg">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <i class="fas fa-clock mr-3 text-xl"></i>
                <p>แผนนี้ยังไม่ได้รับการประเมิน</p>
            </div>
            <a href="evaluate.php?id=<?php echo $plan['id']; ?>" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition duration-200">
                <i class="fas fa-clipboard-check mr-1"></i>ประเมินเลย
            </a>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>