<?php
/**
 * ไฟล์ตัวอย่างการตั้งค่าฐานข้อมูล
 * 
 * คัดลอกไฟล์นี้เป็น database.php แล้วแก้ไขค่าตามสภาพแวดล้อมของคุณ
 * 
 * สำหรับ Development:
 *   cp database.sample.php database.php
 * 
 * สำหรับ Production:
 *   แก้ไขค่าด้านล่างให้ตรงกับ server จริง
 */

// ตั้งค่าการเชื่อมต่อฐานข้อมูล
define('DB_HOST', 'localhost');           // Database host
define('DB_USER', 'your_db_username');    // Database username
define('DB_PASS', 'your_db_password');    // Database password
define('DB_NAME', 'lesson_plan_system');  // Database name

// สร้างการเชื่อมต่อ
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // ตั้งค่า charset เป็น utf8mb4
    $conn->set_charset("utf8mb4");
    
    // ตรวจสอบการเชื่อมต่อ
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
} catch (Exception $e) {
    // Production: ซ่อน error message
    die("เกิดข้อผิดพลาดในการเชื่อมต่อฐานข้อมูล");
    
    // Development: แสดง error message
    // die("เกิดข้อผิดพลาดในการเชื่อมต่อฐานข้อมูล: " . $e->getMessage());
}

// ฟังก์ชันป้องกัน SQL Injection
function clean_input($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $conn->real_escape_string($data);
}

// ฟังก์ชันตรวจสอบ Session
function check_login() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../index.php");
        exit();
    }
}

// ฟังก์ชันตรวจสอบสิทธิ์
function check_role($allowed_roles) {
    check_login();
    if (!in_array($_SESSION['role'], $allowed_roles)) {
        header("Location: ../index.php");
        exit();
    }
}

// ฟังก์ชันตรวจสอบว่าระบบเปิดให้ upload หรือไม่
function is_upload_enabled() {
    global $conn;
    $sql = "SELECT upload_enabled, start_date, end_date FROM system_settings WHERE id = 1";
    $result = $conn->query($sql);
    
    if ($result && $row = $result->fetch_assoc()) {
        $today = date('Y-m-d');
        return $row['upload_enabled'] == 1 && 
               $today >= $row['start_date'] && 
               $today <= $row['end_date'];
    }
    return false;
}
?>