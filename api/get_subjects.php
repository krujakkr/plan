<?php
// แสดง error ทั้งหมด (ใช้ตอน debug เท่านั้น)
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

try {
    // ตรวจสอบว่าไฟล์ config มีอยู่หรือไม่
    if (!file_exists('../config/config.php')) {
        throw new Exception('ไม่พบไฟล์ config.php');
    }
    if (!file_exists('../config/database.php')) {
        throw new Exception('ไม่พบไฟล์ database.php');
    }
    
    require_once '../config/config.php';
    require_once '../config/database.php';
    
    // ตรวจสอบการเชื่อมต่อฐานข้อมูล
    if (!isset($conn)) {
        throw new Exception('ไม่สามารถเชื่อมต่อฐานข้อมูลได้');
    }
    
    if ($conn->connect_error) {
        throw new Exception('Database connection failed: ' . $conn->connect_error);
    }
    
    // รับ subjectGroup จาก query string
    $subjectGroup = isset($_GET['subjectGroup']) ? trim($_GET['subjectGroup']) : '';
    
    // ตรวจสอบว่ามีตาราง subjectdatabase หรือไม่
    $checkTable = $conn->query("SHOW TABLES LIKE 'subjectdatabase'");
    if ($checkTable->num_rows === 0) {
        throw new Exception('ไม่พบตาราง subjectdatabase ในฐานข้อมูล');
    }
    
    // Query ข้อมูล
    if (empty($subjectGroup)) {
        // ถ้าไม่ระบุกลุ่มสาระ ให้ส่งทั้งหมด
        $sql = "SELECT subjectCode, subjectName, subjectGroup 
                FROM subjectdatabase 
                ORDER BY subjectCode";
        $result = $conn->query($sql);
    } else {
        // ถ้าระบุกลุ่มสาระ ให้กรองตามนั้น
        $sql = "SELECT subjectCode, subjectName, subjectGroup 
                FROM subjectdatabase 
                WHERE subjectGroup = ?
                ORDER BY subjectCode";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception('Prepare statement failed: ' . $conn->error);
        }
        
        $stmt->bind_param("s", $subjectGroup);
        $stmt->execute();
        $result = $stmt->get_result();
    }
    
    if (!$result) {
        throw new Exception('Query execution failed: ' . $conn->error);
    }
    
    $subjects = [];
    while ($row = $result->fetch_assoc()) {
        $subjects[] = [
            'subjectCode' => $row['subjectCode'],
            'subjectName' => $row['subjectName'],
            'subjectGroup' => $row['subjectGroup']
        ];
    }
    
    // ส่งข้อมูลกลับ
    echo json_encode($subjects, JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => $e->getMessage(),
        'file' => __FILE__,
        'line' => $e->getLine()
    ], JSON_UNESCAPED_UNICODE);
}
?>