<?php
session_start();

// ทำลาย session
session_unset();
session_destroy();

// ลบ cookie (ถ้ามี)
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Redirect ไปหน้า login
header("Location: /index.php");
exit();
?>