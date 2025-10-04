<?php
require_once '../config/config.php';
require_once '../config/database.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_GET['subjectGroup'])) {
    echo json_encode(['error' => 'กรุณาระบุกลุ่มสาระ'], JSON_UNESCAPED_UNICODE);
    exit();
}

$subjectGroup = clean_input($_GET['subjectGroup']);

// ดึงรายชื่อครูตามกลุ่มสาระ
$sql = "SELECT teacherId, teacherName FROM TeacherDatabase WHERE subjectGroup = ? ORDER BY teacherName";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $subjectGroup);
$stmt->execute();
$result = $stmt->get_result();

$teachers = [];
while ($row = $result->fetch_assoc()) {
    $teachers[] = $row;
}

echo json_encode($teachers, JSON_UNESCAPED_UNICODE);
?>