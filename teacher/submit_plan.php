<?php
require_once '../config/config.php';
require_once '../config/database.php';

check_role(['teacher']);

$page_title = 'ส่งแผนการจัดการเรียนรู้';

// ตรวจสอบว่าระบบเปิดให้ส่งแผนหรือไม่
if (!is_upload_enabled()) {
    header("Location: dashboard.php");
    exit();
}

// ตรวจสอบว่าครูมีกลุ่มสาระหรือไม่
if (empty($_SESSION['subjectGroup'])) {
    header("Location: dashboard.php");
    exit();
}

// ดึงข้อมูลจาก session
$subjectGroup = $_SESSION['subjectGroup']; // กลุ่มสาระของครู
$teacherName = $_SESSION['name']; // ชื่อครูจาก session

$success = '';
$error = '';

// ดึงข้อมูลจาก session
$subjectGroup = $_SESSION['subjectGroup']; // กลุ่มสาระของครู
$teacherName = $_SESSION['name']; // ชื่อครูจาก session

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // รับข้อมูลจากฟอร์ม
    $subjectCode = clean_input($_POST['subjectCode'] ?? '');
    $subjectName = clean_input($_POST['subjectName'] ?? '');
    $gradeLevel = clean_input($_POST['gradeLevel'] ?? '');
    $teachingMethods = $_POST['teachingMethod'] ?? [];
    
    // ตรวจสอบข้อมูลพื้นฐาน
    if (empty($subjectCode) || empty($gradeLevel) || empty($teachingMethods)) {
        $error = 'กรุณากรอกข้อมูลให้ครบถ้วน';
    } else {
        // ตรวจสอบไฟล์แผน (บังคับ)
        if (!isset($_FILES['pdfFile']) || $_FILES['pdfFile']['error'] == UPLOAD_ERR_NO_FILE) {
            $error = 'กรุณาอัปโหลดไฟล์แผนการจัดการเรียนรู้';
        } else {
            $planValidation = validateFile($_FILES['pdfFile']);
            
            if ($planValidation['error']) {
                $error = $planValidation['message'];
            } else {
                // อัปโหลดไฟล์แผน
                $planFileName = generateUniqueFileName($_FILES['pdfFile']['name']);
                $planPath = UPLOAD_PATH . 'plans/' . $planFileName;
                
                if (!move_uploaded_file($_FILES['pdfFile']['tmp_name'], $planPath)) {
                    $error = 'เกิดข้อผิดพลาดในการอัปโหลดไฟล์แผน';
                }
            }
        }
        
        // ตรวจสอบไฟล์บันทึกหลังแผน (ไม่บังคับ)
        $reflectionFileName = null;
        if (isset($_FILES['reflectionFile']) && $_FILES['reflectionFile']['error'] != UPLOAD_ERR_NO_FILE) {
            $reflectionValidation = validateFile($_FILES['reflectionFile']);
            
            if ($reflectionValidation['error']) {
                $error = $reflectionValidation['message'];
            } else {
                $reflectionFileName = generateUniqueFileName($_FILES['reflectionFile']['name']);
                $reflectionPath = UPLOAD_PATH . 'reflections/' . $reflectionFileName;
                
                if (!move_uploaded_file($_FILES['reflectionFile']['tmp_name'], $reflectionPath)) {
                    $error = 'เกิดข้อผิดพลาดในการอัปโหลดไฟล์บันทึกหลังแผน';
                }
            }
        }
        
        // บันทึกข้อมูลลงฐานข้อมูล
        if (empty($error)) {
            $userId = $_SESSION['user_id'];
            $teachingMethodStr = implode(', ', $teachingMethods);
            
            // ตรวจสอบว่ามีแผนของวิชานี้อยู่แล้วหรือไม่
            $check_sql = "SELECT id FROM reports WHERE userId = ? AND subjectCode = ? AND gradeLevel = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("iss", $userId, $subjectCode, $gradeLevel);
            $check_stmt->execute();
            $existing = $check_stmt->get_result();
            
            if ($existing->num_rows > 0) {
                // อัพเดทแผนเดิม (รีเซตสถานะเป็นรอตรวจ)
                $existingId = $existing->fetch_assoc()['id'];
                
                $update_sql = "UPDATE reports SET 
                    subjectGroup = ?, 
                    teacherName = ?, 
                    subjectName = ?, 
                    teachingMethod = ?, 
                    pdfFile = ?, 
                    reflectionFile = ?, 
                    status = 'รอตรวจ',
                    evaluation = NULL,
                    comments = NULL,
                    evaluatorId = NULL,
                    evaluatorName = NULL,
                    evaluatedAt = NULL,
                    submittedAt = NOW()
                    WHERE id = ?";
                
                $stmt = $conn->prepare($update_sql);
                $stmt->bind_param("ssssssi", 
                    $subjectGroup, 
                    $teacherName, 
                    $subjectName, 
                    $teachingMethodStr, 
                    $planFileName, 
                    $reflectionFileName,
                    $existingId
                );
                
                if ($stmt->execute()) {
                    $success = 'ส่งแผนใหม่สำเร็จ! (อัพเดทแผนเดิม)';
                } else {
                    $error = 'เกิดข้อผิดพลาดในการบันทึกข้อมูล';
                }
            } else {
                // เพิ่มแผนใหม่
                $insert_sql = "INSERT INTO reports (userId, subjectGroup, teacherName, subjectCode, subjectName, gradeLevel, teachingMethod, pdfFile, reflectionFile) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                
                $stmt = $conn->prepare($insert_sql);
                $stmt->bind_param("issssssss", 
                    $userId, 
                    $subjectGroup, 
                    $teacherName, 
                    $subjectCode, 
                    $subjectName, 
                    $gradeLevel, 
                    $teachingMethodStr, 
                    $planFileName, 
                    $reflectionFileName
                );
                
                if ($stmt->execute()) {
                    $success = 'ส่งแผนการจัดการเรียนรู้สำเร็จ!';
                } else {
                    $error = 'เกิดข้อผิดพลาดในการบันทึกข้อมูล';
                }
            }
        }
    }
}

include '../includes/header.php';
?>

<div class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="bg-white rounded-xl shadow-md p-6 mb-6">
        <h1 class="text-2xl font-bold text-gray-800 flex items-center">
            <i class="fas fa-upload text-blue-600 mr-3"></i>
            ส่งแผนการจัดการเรียนรู้
        </h1>
        <p class="text-gray-600 mt-2">กรุณากรอกข้อมูลและอัปโหลดไฟล์แผนการสอน</p>
    </div>

    <!-- Alert Messages -->
    <?php if ($success): ?>
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-lg">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <i class="fas fa-check-circle mr-3 text-xl"></i>
                <span><?php echo $success; ?></span>
            </div>
            <a href="my_plans.php" class="text-green-800 hover:text-green-900 font-medium">
                ดูประวัติ →
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

    <!-- ข้อมูลครูและกลุ่มสาระ -->
    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6 rounded-lg">
        <div class="flex items-center">
            <i class="fas fa-user-circle text-blue-600 text-2xl mr-3"></i>
            <div>
                <p class="font-medium text-gray-800"><?php echo $teacherName; ?></p>
                <p class="text-sm text-gray-600">กลุ่มสาระ: <?php echo $subjectGroup; ?></p>
            </div>
        </div>
    </div>

    <!-- Form -->
    <form method="POST" enctype="multipart/form-data" class="bg-white rounded-xl shadow-md p-6 space-y-6">
        
        <!-- รหัสวิชา -->
        <div>
            <label class="block text-gray-700 font-medium mb-2">
                <i class="fas fa-list-alt text-blue-600 mr-2"></i>เลือกรายวิชา <span class="text-red-500">*</span>
            </label>
            <select name="subjectCode" id="subjectCode" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                <option value="">-- เลือกรายวิชา --</option>
            </select>
            <input type="hidden" name="subjectName" id="subjectName">
        </div>

        <!-- ระดับชั้น -->
        <div>
            <label class="block text-gray-700 font-medium mb-2">
                <i class="fas fa-layer-group text-blue-600 mr-2"></i>ระดับชั้น <span class="text-red-500">*</span>
            </label>
            <div class="grid grid-cols-3 md:grid-cols-6 gap-3">
                <?php foreach (GRADE_LEVELS as $level): ?>
                <label class="flex items-center justify-center p-3 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-blue-500 hover:bg-blue-50 transition">
                    <input type="radio" name="gradeLevel" value="<?php echo $level; ?>" class="mr-2" required>
                    <span class="font-medium"><?php echo $level; ?></span>
                </label>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- รูปแบบการสอน -->
        <div>
            <label class="block text-gray-700 font-medium mb-2">
                <i class="fas fa-chalkboard-teacher text-blue-600 mr-2"></i>รูปแบบการสอนที่สนองนโยบาย สพฐ. <span class="text-red-500">*</span>
            </label>
            <p class="text-sm text-gray-500 mb-3">เลือกได้มากกว่า 1 ตัวเลือก</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <?php foreach (TEACHING_METHODS as $method): ?>
                <label class="flex items-center p-3 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-blue-500 hover:bg-blue-50 transition">
                    <input type="checkbox" name="teachingMethod[]" value="<?php echo $method; ?>" class="mr-3 w-5 h-5 text-blue-600">
                    <span><?php echo $method; ?></span>
                </label>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- อัปโหลดไฟล์แผน -->
        <div>
            <label class="block text-gray-700 font-medium mb-2">
                <i class="fas fa-file-pdf text-red-600 mr-2"></i>แผนการจัดการเรียนรู้ (PDF) <span class="text-red-500">*</span>
            </label>
            <p class="text-sm text-gray-500 mb-3">ไฟล์ PDF เท่านั้น ขนาดไม่เกิน 20 MB</p>
            <input type="file" name="pdfFile" id="pdfFile" accept=".pdf" class="w-full px-4 py-3 border-2 border-dashed border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 cursor-pointer" required onchange="validateFileSize(this); showFileName(this, 'pdfFileName');">
            <p id="pdfFileName" class="text-sm text-gray-600 mt-2"></p>
        </div>

        <!-- อัปโหลดไฟล์บันทึกหลังแผน -->
        <div>
            <label class="block text-gray-700 font-medium mb-2">
                <i class="fas fa-file-pdf text-green-600 mr-2"></i>บันทึกหลังแผนการจัดการเรียนรู้ (PDF)
            </label>
            <p class="text-sm text-gray-500 mb-3">ไม่บังคับ - ไฟล์ PDF เท่านั้น ขนาดไม่เกิน 20 MB</p>
            <input type="file" name="reflectionFile" id="reflectionFile" accept=".pdf" class="w-full px-4 py-3 border-2 border-dashed border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 cursor-pointer" onchange="validateFileSize(this); showFileName(this, 'reflectionFileName');">
            <p id="reflectionFileName" class="text-sm text-gray-600 mt-2"></p>
        </div>

        <!-- ปุ่มส่ง -->
        <div class="flex space-x-4">
            <button type="submit" class="flex-1 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-bold py-3 px-6 rounded-lg transition duration-200 transform hover:scale-105 shadow-lg">
                <i class="fas fa-paper-plane mr-2"></i>ส่งแผนการเรียนรู้
            </button>
            <a href="dashboard.php" class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-3 px-6 rounded-lg transition duration-200 text-center">
                <i class="fas fa-times mr-2"></i>ยกเลิก
            </a>
        </div>
    </form>
</div>

<script>
// โหลดรายวิชาทันทีเมื่อหน้าโหลด
document.addEventListener('DOMContentLoaded', function() {
    const subjectGroup = '<?php echo addslashes($subjectGroup); ?>';
    
    // Debug: แสดงค่า subjectGroup
    console.log('Subject Group:', subjectGroup);
    
    if (!subjectGroup || subjectGroup === '') {
        showAlert('ไม่พบข้อมูลกลุ่มสาระของคุณ กรุณาติดต่อผู้ดูแลระบบ', 'error');
        return;
    }
    
    // โหลดรายวิชาในกลุ่มสาระของครู
    const url = `../api/get_subjects.php?subjectGroup=${encodeURIComponent(subjectGroup)}`;
    console.log('Fetching URL:', url);
    
    fetch(url)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            console.log('API Response:', data);
            
            if (data.error) {
                showAlert(data.error, 'error');
                return;
            }
            
            const subjectSelect = document.getElementById('subjectCode');
            subjectSelect.innerHTML = '<option value="">-- เลือกรายวิชา --</option>';
            
            if (data.length === 0) {
                showAlert('ไม่พบรายวิชาในกลุ่มสาระ ' + subjectGroup, 'error');
                return;
            }
            
            data.forEach(subject => {
                const option = document.createElement('option');
                option.value = subject.subjectCode;
                option.textContent = `${subject.subjectCode} - ${subject.subjectName}`;
                option.dataset.subjectName = subject.subjectName;
                subjectSelect.appendChild(option);
            });
        })
        .catch(error => {
            console.error('Error loading subjects:', error);
            showAlert('เกิดข้อผิดพลาดในการโหลดรายวิชา: ' + error.message, 'error');
        });
});

// เมื่อเลือกรหัสวิชา ให้เก็บชื่อวิชาด้วย
document.getElementById('subjectCode').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    document.getElementById('subjectName').value = selectedOption.dataset.subjectName || '';
});
</script>

<?php include '../includes/footer.php'; ?>