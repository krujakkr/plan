<?php
require_once '../config/config.php';
require_once '../config/database.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_GET['subjectGroup'])) {
    echo json_encode(['error' => 'กรุณาระบุกลุ่มสาระ'], JSON_UNESCAPED_UNICODE);
    exit();
}

$subjectGroup = clean_input($_GET['subjectGroup']);

// ดึงรายวิชาตามกลุ่มสาระ
$sql = "SELECT subjectCode, subjectName FROM SubjectDatabase WHERE subjectGroup = ? ORDER BY subjectCode";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $subjectGroup);
$stmt->execute();
$result = $stmt->get_result();

$subjects = [];
while ($row = $result->fetch_assoc()) {
    $subjects[] = $row;
}

echo json_encode($subjects, JSON_UNESCAPED_UNICODE);
?>