<<<<<<< HEAD
# plan
ระบบส่งแผนการจัดการเรียนรู้สำหรับครู
=======
# 📚 ระบบส่งแผนการจัดการเรียนรู้และบันทึกหลังแผน

ระบบจัดการแผนการสอนสำหรับโรงเรียน พัฒนาด้วย PHP และ MySQL

## ✨ คุณสมบัติหลัก

### 👨‍🏫 สำหรับครู
- ส่งแผนการจัดการเรียนรู้ (PDF)
- อัปโหลดบันทึกหลังแผน (ไม่บังคับ)
- ดูประวัติการส่งแผนทั้งหมด
- ตรวจสอบผลการประเมินและข้อเสนอแนะ
- แก้ไขแผนที่ถูกประเมินแล้ว (รีเซตสถานะเป็นรอตรวจ)

### 👨‍💼 สำหรับผู้ประเมิน
- ประเมินแผนในกลุ่มสาระของตนเอง
- ให้คะแนนตามเกณฑ์ 7 ข้อ (1-5 คะแนน)
- เขียนข้อเสนอแนะ
- เลือกสถานะ: รับทราบแล้ว หรือ แก้ไขใหม่
- แก้ไขการประเมินได้

### 🔧 สำหรับผู้ดูแลระบบ
- เปิด/ปิดระบบอัปโหลด
- กำหนดช่วงเวลาเปิดส่งแผน
- ดูสถิติรวมของระบบ

## 📋 ข้อกำหนดระบบ

- PHP 7.4 หรือสูงกว่า
- MySQL 5.7 หรือสูงกว่า
- Apache/Nginx
- ขนาดอัปโหลดไฟล์สูงสุด 20 MB

## 🚀 การติดตั้ง

### 1. เตรียมไฟล์

```bash
# โคลนโปรเจค
git clone [repository-url]
cd lesson-plan-system

# หรือคัดลอกไฟล์ทั้งหมดไปที่ htdocs (XAMPP) หรือ www (WAMP)
```

### 2. สร้างฐานข้อมูล

```sql
-- เปิด phpMyAdmin
-- สร้างฐานข้อมูลชื่อ: lesson_plan_system
CREATE DATABASE lesson_plan_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- นำเข้าไฟล์ database_schema.sql
```

### 3. ตั้งค่าการเชื่อมต่อฐานข้อมูล

แก้ไขไฟล์ `config/database.php`:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');        // ชื่อผู้ใช้ MySQL
define('DB_PASS', '');            // รหัสผ่าน MySQL
define('DB_NAME', 'lesson_plan_system');
```

### 4. ตั้งค่าเส้นทาง

แก้ไขไฟล์ `config/config.php`:

```php
define('BASE_URL', 'http://localhost/lesson-plan-system');
```

### 5. สร้างโฟลเดอร์สำหรับเก็บไฟล์

```bash
mkdir -p uploads/plans
mkdir -p uploads/reflections
chmod 755 uploads
chmod 755 uploads/plans
chmod 755 uploads/reflections
```

**Windows:**
- สร้างโฟลเดอร์ `uploads/plans` และ `uploads/reflections` ด้วยตนเอง

### 6. ตั้งค่า PHP

แก้ไข `php.ini`:

```ini
upload_max_filesize = 20M
post_max_size = 25M
max_execution_time = 300
```

รีสตาร์ท Apache/Nginx

### 7. เข้าใช้งานระบบ

เปิดเบราว์เซอร์: `http://localhost/lesson-plan-system`

## 👤 บัญชีทดสอบ

### ผู้ดูแลระบบ (Admin)
- **รหัสครู:** 000
- **รหัสผ่าน:** 123456

### ครูผู้สอน (Teacher)
- **รหัสครู:** 001
- **รหัสผ่าน:** 123456

### ผู้ประเมิน (Evaluator)
- **รหัสครู:** 002
- **รหัสผ่าน:** 123456

## 📁 โครงสร้างโปรเจค

```
lesson-plan-system/
├── config/
│   ├── database.php       # การเชื่อมต่อฐานข้อมูล
│   └── config.php         # ตั้งค่าระบบ
├── includes/
│   ├── header.php         # Header + Navbar
│   └── footer.php         # Footer
├── uploads/
│   ├── plans/             # เก็บไฟล์แผน PDF
│   └── reflections/       # เก็บไฟล์บันทึกหลังแผน PDF
├── teacher/
│   ├── dashboard.php      # หน้าหลักครู
│   ├── submit_plan.php    # ส่งแผน
│   ├── my_plans.php       # ประวัติการส่ง
│   └── plan_details.php   # รายละเอียดแผน
├── evaluator/
│   ├── dashboard.php      # หน้าหลักผู้ประเมิน
│   ├── evaluate.php       # ประเมินแผน
│   └── plan_details.php   # รายละเอียดแผน
├── admin/
│   └── settings.php       # ตั้งค่าระบบ
├── api/
│   ├── get_teachers.php   # ดึงข้อมูลครู
│   └── get_subjects.php   # ดึงข้อมูลวิชา
├── index.php              # หน้า Login
├── logout.php             # ออกจากระบบ
└── database_schema.sql    # โครงสร้างฐานข้อมูล
```

## 🔧 การจัดการผู้ใช้งาน

### เพิ่มผู้ใช้ใหม่

ผู้ดูแลระบบต้องเพิ่มผู้ใช้ผ่าน phpMyAdmin:

```sql
-- สร้าง password hash
-- ใช้ไฟล์ generate_password.php หรือ

INSERT INTO users (name, teacherId, password, role, subjectGroup) VALUES
('ชื่อครู', '003', '$2y$10$...', 'teacher', 'กลุ่มสาระ');

-- role: teacher, evaluator, admin
-- subjectGroup: ต้องตรงกับกลุ่มสาระในตาราง TeacherDatabase
```

### เปลี่ยนรหัสผ่าน

```php
// ใช้ generate_password.php เพื่อสร้าง hash ใหม่
// จากนั้น UPDATE ในฐานข้อมูล

UPDATE users SET password = '$2y$10$...' WHERE teacherId = '001';
```

## 📊 ฐานข้อมูล

### ตารางหลัก

1. **users** - ผู้ใช้งานระบบ
2. **reports** - แผนการเรียนรู้ที่ส่ง
3. **TeacherDatabase** - ข้อมูลครูในแต่ละกลุ่มสาระ
4. **SubjectDatabase** - รหัสวิชาและชื่อวิชา
5. **system_settings** - การตั้งค่าระบบ

## ⚙️ การตั้งค่าระบบ

### เปิด/ปิดการส่งแผน

1. Login ด้วยบัญชี Admin
2. ไปที่ "ตั้งค่าระบบ"
3. เลือก "เปิดให้อัปโหลดแผน"
4. กำหนดวันที่เริ่มต้น - สิ้นสุด
5. กดบันทึก

## 🎨 การปรับแต่ง

### เปลี่ยนสี Theme

แก้ไขไฟล์ `includes/header.php`:

```html
<!-- ครู -->
<nav class="bg-blue-600">  <!-- เปลี่ยนเป็นสีอื่น -->

<!-- ผู้ประเมิน -->
<nav class="bg-green-600">

<!-- Admin -->
<nav class="bg-purple-600">
```

### เพิ่มกลุ่มสาระ/วิชา

แก้ไขไฟล์ `config/config.php`:

```php
define('SUBJECT_GROUPS', [
    'ภาษาไทย',
    'คณิตศาสตร์',
    // ... เพิ่มเติม
]);
```

และเพิ่มข้อมูลในฐานข้อมูล:

```sql
-- เพิ่มครู
INSERT INTO TeacherDatabase VALUES ('T011', 'ชื่อครู', 'กลุ่มสาระ');

-- เพิ่มวิชา
INSERT INTO SubjectDatabase VALUES ('ว21103', 'ชื่อวิชา', 'กลุ่มสาระ');
```

## 🐛 การแก้ไขปัญหา

### ไฟล์อัปโหลดไม่ได้

1. ตรวจสอบว่าโฟลเดอร์ `uploads` มีสิทธิ์เขียน
2. ตรวจสอบ `php.ini` - `upload_max_filesize`
3. ตรวจสอบว่าไฟล์เป็น PDF จริง

### Login ไม่ได้

1. ตรวจสอบการเชื่อมต่อฐานข้อมูล
2. ใช้ `test_password.php` เพื่อทดสอบรหัสผ่าน
3. ตรวจสอบ session_start() ใน config.php

### ภาษาไทยแสดงผิด

ตรวจสอบ charset ของฐานข้อมูล:

```sql
ALTER DATABASE lesson_plan_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

## 📝 License

Copyright © 2025 โรงเรียนมหาสารคาม

## 👨‍💻 ผู้พัฒนา

พัฒนาโดย Claude (Anthropic)

## 📞 ติดต่อสอบถาม

หากมีปัญหาการใช้งาน กรุณาติดต่อผู้ดูแลระบบ
>>>>>>> ac3f8dc (Initial commit: ระบบส่งแผนการจัดการเรียนรู้)
