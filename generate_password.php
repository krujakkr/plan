<?php
// ไฟล์สำหรับ generate password hash
// ใช้ไฟล์นี้เพื่อสร้าง hash ของรหัสผ่าน

$passwords = [
    '123456' // รหัสผ่านตัวอย่าง
];

echo "<h2>Password Hash Generator</h2>";
echo "<table border='1' cellpadding='10'>";
echo "<tr><th>รหัสผ่าน</th><th>Hash</th></tr>";

foreach ($passwords as $password) {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    echo "<tr>";
    echo "<td>" . htmlspecialchars($password) . "</td>";
    echo "<td>" . htmlspecialchars($hash) . "</td>";
    echo "</tr>";
}

echo "</table>";

// ทดสอบการ verify
echo "<hr>";
echo "<h3>ทดสอบการ Verify</h3>";
$testPassword = '123456';
$testHash = password_hash($testPassword, PASSWORD_DEFAULT);

echo "รหัสผ่าน: $testPassword<br>";
echo "Hash: $testHash<br>";
echo "ผลการทดสอบ: " . (password_verify($testPassword, $testHash) ? 'ถูกต้อง ✓' : 'ไม่ถูกต้อง ✗');

echo "<hr>";
echo "<h3>SQL UPDATE Commands</h3>";
echo "<p>คัดลอก SQL ด้านล่างนี้ไปรันใน phpMyAdmin:</p>";
echo "<textarea rows='10' cols='100'>";
echo "-- อัพเดท password สำหรับ users ทั้งหมด (รหัสผ่าน: 123456)\n";
$newHash = password_hash('123456', PASSWORD_DEFAULT);
echo "UPDATE users SET password = '$newHash' WHERE teacherId = '000';\n";
echo "UPDATE users SET password = '$newHash' WHERE teacherId = '001';\n";
echo "UPDATE users SET password = '$newHash' WHERE teacherId = '002';\n";
echo "</textarea>";
?>