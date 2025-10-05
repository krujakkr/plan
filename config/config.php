<?php
session_start();

// ตั้งค่า Timezone
date_default_timezone_set('Asia/Bangkok');

// ตั้งค่าเส้นทางโครงการ
// define('BASE_URL', 'http://localhost/plan');
define('BASE_URL', 'https://plan.knw.ac.th');
define('UPLOAD_PATH', __DIR__ . '/../uploads/');

// ตั้งค่าการอัปโหลดไฟล์
define('MAX_FILE_SIZE', 20 * 1024 * 1024); // 20 MB
define('ALLOWED_EXTENSIONS', ['pdf']);

// กลุ่มสาระการเรียนรู้
define('SUBJECT_GROUPS', [
    'ภาษาไทย',
    'คณิตศาสตร์',
    'วิทยาศาสตร์และเทคโนโลยี',
    'สังคมศึกษา',
    'สุขศึกษาและพละศึกษา',
    'ศิลปะ',
    'การงานอาชีพ',
    'ภาษาต่างประเทศ',
    'พัฒนาผู้เรียน'
]);

// ระดับชั้น
define('GRADE_LEVELS', ['ม.1', 'ม.2', 'ม.3', 'ม.4', 'ม.5', 'ม.6']);

// รูปแบบการสอน
define('TEACHING_METHODS', [
    'Active Learning',
    'สะเต็มศึกษา',
    'PISA',
    'หลักปรัชญาเศรษฐกิจพอเพียง',
    'ค่านิยม 12 ประการ',
    'เน้นสมรรถนะ',
    'ต้านทุจริตศึกษา',
    'บูรณาการหน้าที่พลเมือง',
    'อื่นๆ'
]);

// เกณฑ์การประเมิน
define('EVALUATION_CRITERIA', [
    '1. องค์ประกอบของแผนการจัดการเรียนรู้ครบถ้วน',
    '2. มาตรฐานการเรียนรู้/ตัวชี้วัด/ผลการเรียนรู้ สอดคล้องกับหน่วยการเรียนรู้',
    '3. สาระสำคัญ/ความคิดรวบยอด ครอบคลุมเนื้อหา',
    '4. จุดประสงค์การเรียนรู้ (K, P, A) ชัดเจน วัดผลได้',
    '5. กิจกรรมการเรียนรู้เน้นผู้เรียนเป็นสำคัญ',
    '6. การวัดและประเมินผลสอดคล้องกับจุดประสงค์',
    '7. สื่อและแหล่งเรียนรู้เหมาะสมกับกิจกรรม'
]);

// ฟังก์ชันแปลง bytes เป็น MB
function formatBytes($bytes) {
    return number_format($bytes / 1024 / 1024, 2);
}

// ฟังก์ชันสร้างชื่อไฟล์ที่ไม่ซ้ำ
function generateUniqueFileName($originalName) {
    $ext = pathinfo($originalName, PATHINFO_EXTENSION);
    return uniqid() . '_' . time() . '.' . $ext;
}

// ฟังก์ชันตรวจสอบไฟล์
function validateFile($file, $type = 'pdf') {
    $errors = [];
    
    // ตรวจสอบว่ามีไฟล์หรือไม่
    if ($file['error'] == UPLOAD_ERR_NO_FILE) {
        return ['error' => true, 'message' => 'กรุณาเลือกไฟล์'];
    }
    
    // ตรวจสอบข้อผิดพลาดในการอัปโหลด
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['error' => true, 'message' => 'เกิดข้อผิดพลาดในการอัปโหลดไฟล์'];
    }
    
    // ตรวจสอบขนาดไฟล์
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['error' => true, 'message' => 'ขนาดไฟล์เกิน ' . formatBytes(MAX_FILE_SIZE) . ' MB'];
    }
    
    // ตรวจสอบนามสกุลไฟล์
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ALLOWED_EXTENSIONS)) {
        return ['error' => true, 'message' => 'กรุณาอัปโหลดไฟล์ PDF เท่านั้น'];
    }
    
    // ตรวจสอบ MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if ($mime !== 'application/pdf') {
        return ['error' => true, 'message' => 'ไฟล์ไม่ใช่ PDF'];
    }
    
    return ['error' => false];
}
?>