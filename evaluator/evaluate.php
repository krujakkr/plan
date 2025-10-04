<?php
require_once '../config/config.php';
require_once '../config/database.php';

check_role(['evaluator']);

$page_title = 'ประเมินแผน';
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
$currentEvaluation = $plan['evaluation'] ? json_decode($plan['evaluation'], true) : null;

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $scores = $_POST['score'] ?? [];
    $comments = clean_input($_POST['comments'] ?? '');
    $newStatus = clean_input($_POST['status'] ?? '');
    
    // ตรวจสอบคะแนน
    if (count($scores) != 7) {
        $error = 'กรุณาให้คะแนนครบทุกข้อ';
    } else {
        $validScores = true;
        foreach ($scores as $score) {
            if ($score < 1 || $score > 5) {
                $validScores = false;
                break;
            }
        }
        
        if (!$validScores) {
            $error = 'คะแนนต้องอยู่ระหว่าง 1-5';
        } elseif (empty($newStatus) || !in_array($newStatus, ['รับทราบแล้ว', 'แก้ไขใหม่'])) {
            $error = 'กรุณาเลือกสถานะ';
        } else {
            // คำนวณคะแนนรวม
            $totalScore = array_sum($scores);
            $percentage = ($totalScore / 35) * 100;
            
            // สร้าง JSON สำหรับเก็บผลการประเมิน
            $evaluation = json_encode([
                'scores' => $scores,
                'totalScore' => $totalScore,
                'percentage' => $percentage
            ]);
            
            // บันทึกผลการประเมิน
            $evaluatorId = $_SESSION['user_id'];
            $evaluatorName = $_SESSION['name'];
            
            $update_sql = "UPDATE reports SET 
                evaluation = ?,
                comments = ?,
                status = ?,
                evaluatorId = ?,
                evaluatorName = ?,
                evaluatedAt = NOW()
                WHERE id = ?";
            
            $stmt = $conn->prepare($update_sql);
            $stmt->bind_param("sssisi", $evaluation, $comments, $newStatus, $evaluatorId, $evaluatorName, $planId);
            
            if ($stmt->execute()) {
                $success = 'บันทึกผลการประเมินสำเร็จ!';
                
                // โหลดข้อมูลใหม่
                $sql = "SELECT r.*, u.name as senderName 
                        FROM reports r 
                        JOIN users u ON r.userId = u.id 
                        WHERE r.id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $planId);
                $stmt->execute();
                $plan = $stmt->get_result()->fetch_assoc();
                $currentEvaluation = json_decode($plan['evaluation'], true);
            } else {
                $error = 'เกิดข้อผิดพลาดในการบันทึกข้อมูล';
            }
        }
    }
}

include '../includes/header.php';
?>

<div class="max-w-6xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <a href="dashboard.php" class="text-green-600 hover:text-green-700 inline-flex items-center mb-4">
            <i class="fas fa-arrow-left mr-2"></i>กลับ
        </a>
        <div class="bg-white rounded-xl shadow-md p-6">
            <h1 class="text-2xl font-bold text-gray-800 flex items-center">
                <i class="fas fa-clipboard-check text-green-600 mr-3"></i>
                ประเมินแผนการจัดการเรียนรู้
            </h1>
        </div>
    </div>

    <!-- Alert Messages -->
    <?php if ($success): ?>
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-lg">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <i class="fas fa-check-circle mr-3 text-xl"></i>
                <span><?php echo $success; ?></span>
            </div>
            <a href="dashboard.php" class="text-green-800 hover:text-green-900 font-medium">
                กลับหน้าหลัก →
            </a>
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

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Plan Information -->
        <div class="bg-white rounded-xl shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                ข้อมูลแผน
            </h2>
            
            <div class="space-y-3">
                <div class="flex justify-between py-2 border-b">
                    <span class="text-gray-600">กลุ่มสาระ:</span>
                    <span class="font-medium"><?php echo $plan['subjectGroup']; ?></span>
                </div>
                <div class="flex justify-between py-2 border-b">
                    <span class="text-gray-600">รหัสวิชา:</span>
                    <span class="font-medium"><?php echo $plan['subjectCode']; ?></span>
                </div>
                <div class="flex justify-between py-2 border-b">
                    <span class="text-gray-600">ชื่อวิชา:</span>
                    <span class="font-medium"><?php echo $plan['subjectName']; ?></span>
                </div>
                <div class="flex justify-between py-2 border-b">
                    <span class="text-gray-600">ผู้สอน:</span>
                    <span class="font-medium"><?php echo $plan['teacherName']; ?></span>
                </div>
                <div class="flex justify-between py-2 border-b">
                    <span class="text-gray-600">ผู้ส่ง:</span>
                    <span class="font-medium"><?php echo $plan['senderName']; ?></span>
                </div>
                <div class="flex justify-between py-2 border-b">
                    <span class="text-gray-600">ระดับชั้น:</span>
                    <span class="font-medium"><?php echo $plan['gradeLevel']; ?></span>
                </div>
                <div class="flex justify-between py-2 border-b">
                    <span class="text-gray-600">วันที่ส่ง:</span>
                    <span class="font-medium"><?php echo date('d/m/Y H:i น.', strtotime($plan['submittedAt'])); ?></span>
                </div>
                <div class="py-2">
                    <span class="text-gray-600 block mb-2">รูปแบบการสอน:</span>
                    <div class="flex flex-wrap gap-2">
                        <?php
                        $methods = explode(', ', $plan['teachingMethod']);
                        foreach ($methods as $method):
                        ?>
                        <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-xs">
                            <?php echo $method; ?>
                        </span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <!-- Files -->
            <div class="mt-6">
                <h3 class="font-bold text-gray-800 mb-3">ไฟล์แนบ</h3>
                <div class="space-y-2">
                    <a href="../uploads/plans/<?php echo $plan['pdfFile']; ?>" target="_blank" class="flex items-center p-3 bg-red-50 border border-red-200 rounded-lg hover:bg-red-100 transition">
                        <i class="fas fa-file-pdf text-2xl text-red-600 mr-3"></i>
                        <div class="flex-1">
                            <p class="font-medium text-sm">แผนการเรียนรู้</p>
                        </div>
                        <i class="fas fa-external-link-alt text-red-600"></i>
                    </a>
                    
                    <?php if ($plan['reflectionFile']): ?>
                    <a href="../uploads/reflections/<?php echo $plan['reflectionFile']; ?>" target="_blank" class="flex items-center p-3 bg-green-50 border border-green-200 rounded-lg hover:bg-green-100 transition">
                        <i class="fas fa-file-pdf text-2xl text-green-600 mr-3"></i>
                        <div class="flex-1">
                            <p class="font-medium text-sm">บันทึกหลังแผน</p>
                        </div>
                        <i class="fas fa-external-link-alt text-green-600"></i>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Evaluation Form -->
        <div class="bg-white rounded-xl shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-star text-yellow-500 mr-2"></i>
                ประเมินแผน
            </h2>
            
            <form method="POST">
                <!-- Rating Scale Info -->
                <div class="bg-blue-50 p-4 rounded-lg mb-4">
                    <p class="text-sm font-medium text-blue-900 mb-2">เกณฑ์การให้คะแนน:</p>
                    <ul class="text-xs text-blue-800 space-y-1">
                        <li>1 = เหมาะสมน้อยที่สุด</li>
                        <li>2 = เหมาะสมน้อย</li>
                        <li>3 = เหมาะสมปานกลาง</li>
                        <li>4 = เหมาะสมมาก</li>
                        <li>5 = เหมาะสมมากที่สุด</li>
                    </ul>
                </div>
                
                <!-- Evaluation Criteria -->
                <div class="space-y-4">
                    <?php foreach (EVALUATION_CRITERIA as $index => $criterion): ?>
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <p class="text-sm text-gray-700 mb-3"><?php echo $criterion; ?></p>
                        <div class="flex justify-between items-center">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                            <label class="flex flex-col items-center cursor-pointer group">
                                <input 
                                    type="radio" 
                                    name="score[<?php echo $index; ?>]" 
                                    value="<?php echo $i; ?>" 
                                    <?php echo ($currentEvaluation && isset($currentEvaluation['scores'][$index]) && $currentEvaluation['scores'][$index] == $i) ? 'checked' : ''; ?>
                                    required
                                    class="hidden peer"
                                >
                                <div class="w-12 h-12 rounded-full border-2 border-gray-300 flex items-center justify-center group-hover:border-yellow-500 peer-checked:bg-yellow-500 peer-checked:border-yellow-500 peer-checked:text-white transition">
                                    <span class="font-bold"><?php echo $i; ?></span>
                                </div>
                            </label>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Comments -->
                <div class="mt-6">
                    <label class="block text-gray-700 font-medium mb-2">
                        <i class="fas fa-comment-dots text-yellow-500 mr-2"></i>ข้อเสนอแนะ
                    </label>
                    <textarea 
                        name="comments" 
                        rows="4" 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                        placeholder="เขียนข้อเสนอแนะ (ไม่บังคับ)"
                    ><?php echo $plan['comments'] ?? ''; ?></textarea>
                </div>
                
                <!-- Status -->
                <div class="mt-6">
                    <label class="block text-gray-700 font-medium mb-2">
                        <i class="fas fa-flag text-green-600 mr-2"></i>สถานะ <span class="text-red-500">*</span>
                    </label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="flex items-center justify-center p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-green-500 hover:bg-green-50 transition has-[:checked]:border-green-500 has-[:checked]:bg-green-50">
                            <input 
                                type="radio" 
                                name="status" 
                                value="รับทราบแล้ว" 
                                <?php echo ($plan['status'] == 'รับทราบแล้ว') ? 'checked' : ''; ?>
                                class="mr-2" 
                                required
                            >
                            <span class="font-medium">รับทราบแล้ว</span>
                        </label>
                        <label class="flex items-center justify-center p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-red-500 hover:bg-red-50 transition has-[:checked]:border-red-500 has-[:checked]:bg-red-50">
                            <input 
                                type="radio" 
                                name="status" 
                                value="แก้ไขใหม่" 
                                <?php echo ($plan['status'] == 'แก้ไขใหม่') ? 'checked' : ''; ?>
                                class="mr-2" 
                                required
                            >
                            <span class="font-medium">แก้ไขใหม่</span>
                        </label>
                    </div>
                </div>
                
                <!-- Submit Button -->
                <div class="mt-6 flex space-x-3">
                    <button type="submit" class="flex-1 bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white font-bold py-3 px-6 rounded-lg transition duration-200">
                        <i class="fas fa-save mr-2"></i>บันทึกผลการประเมิน
                    </button>
                    <a href="dashboard.php" class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-3 px-6 rounded-lg transition duration-200 text-center">
                        <i class="fas fa-times mr-2"></i>ยกเลิก
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>