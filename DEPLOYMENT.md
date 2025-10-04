# 🚀 คู่มือการ Deploy ระบบส่งแผนการจัดการเรียนรู้

## 📋 ความต้องการของ Server

### ขั้นต่ำ:
- PHP 7.4 หรือสูงกว่า
- MySQL 5.7 หรือสูงกว่า
- Apache/Nginx
- SSL Certificate (แนะนำ)

### PHP Extensions ที่ต้องใช้:
- mysqli
- json
- fileinfo
- mbstring

---

## 🔧 ขั้นตอนการ Deploy

### 1. เตรียม Server

```bash
# ตรวจสอบ PHP version
php -v

# ตรวจสอบ extensions
php -m | grep mysqli
php -m | grep json
php -m | grep fileinfo
```

### 2. อัปโหลดไฟล์

**วิธีที่ 1: ใช้ Git (แนะนำ)**

```bash
# SSH เข้า server
ssh user@your-domain.com

# Clone repository
cd /path/to/web/root
git clone https://github.com/yourusername/plan.git
cd plan
```

**วิธีที่ 2: ใช้ FTP/SFTP**

อัปโหลดไฟล์ทั้งหมด ยกเว้น:
- ❌ `.git/`
- ❌ `uploads/plans/*.pdf` (ไฟล์เก่า)
- ❌ `uploads/reflections/*.pdf` (ไฟล์เก่า)

### 3. ตั้งค่าฐานข้อมูล

```sql
-- สร้างฐานข้อมูล
CREATE DATABASE lesson_plan_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- สร้างผู้ใช้ (แนะนำ)
CREATE USER 'plan_user'@'localhost' IDENTIFIED BY 'strong_password_here';
GRANT ALL PRIVILEGES ON lesson_plan_system.* TO 'plan_user'@'localhost';
FLUSH PRIVILEGES;

-- นำเข้าโครงสร้างฐานข้อมูล
mysql -u plan_user -p lesson_plan_system < database_schema.sql
```

### 4. แก้ไข Config

**แก้ไขไฟล์ `config/database.php`:**

```php
// Production Settings
define('DB_HOST', 'localhost');
define('DB_USER', 'plan_user');
define('DB_PASS', 'your_strong_password');
define('DB_NAME', 'lesson_plan_system');
```

**แก้ไขไฟล์ `config/config.php`:**

```php
// Production URL
define('BASE_URL', 'https://your-domain.com');

// Path (ปรับตามโครงสร้าง hosting)
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
```

### 5. ตั้งค่า Permissions

```bash
# ให้สิทธิ์โฟลเดอร์ uploads
chmod 755 uploads
chmod 755 uploads/plans
chmod 755 uploads/reflections

# ให้สิทธิ์เขียนไฟล์
chmod 644 uploads/plans/.gitkeep
chmod 644 uploads/reflections/.gitkeep
```

### 6. ตั้งค่า Apache/Nginx

**Apache (.htaccess):**

ไฟล์ `.htaccess` ที่มีอยู่แล้วควรทำงานได้ ตรวจสอบว่า:

```apache
# ใน .htaccess แก้ไข RewriteBase
RewriteBase /
```

**Nginx (nginx.conf):**

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/plan;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location /uploads {
        # ป้องกันการรัน PHP ในโฟลเดอร์ uploads
        location ~ \.php$ {
            deny all;
        }
    }
}
```

### 7. SSL Certificate (แนะนำ)

```bash
# ใช้ Let's Encrypt (ฟรี)
sudo certbot --apache -d your-domain.com

# หรือ
sudo certbot --nginx -d your-domain.com
```

### 8. ทดสอบระบบ

1. เปิด `https://your-domain.com`
2. Login ด้วยบัญชี admin
3. ทดสอบอัปโหลดไฟล์
4. ทดสอบประเมินแผน

---

## 🔒 ความปลอดภัย

### 1. ซ่อนไฟล์ที่สำคัญ

```apache
# ใน .htaccess
<FilesMatch "^(config|database)\.php$">
    Order allow,deny
    Deny from all
</FilesMatch>
```

### 2. ปิด Error Display (Production)

**ใน `config/config.php` เพิ่ม:**

```php
// Production Mode
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);
```

### 3. เปลี่ยนรหัสผ่าน Admin

```sql
-- สร้าง password hash ใหม่
-- ใช้ generate_password.php

UPDATE users 
SET password = '$2y$10$NEW_HASH_HERE' 
WHERE role = 'admin';
```

### 4. Backup ฐานข้อมูล

```bash
# Backup ทุกวัน
mysqldump -u plan_user -p lesson_plan_system > backup_$(date +%Y%m%d).sql

# หรือตั้ง cronjob
0 2 * * * mysqldump -u plan_user -p'password' lesson_plan_system > /backups/backup_$(date +\%Y\%m\%d).sql
```

---

## 📊 การตรวจสอบหลัง Deploy

### Checklist:

- [ ] ✅ เข้าหน้า Login ได้
- [ ] ✅ Login ด้วย Admin ได้
- [ ] ✅ ครูสามารถส่งแผนได้
- [ ] ✅ อัปโหลด PDF สำเร็จ
- [ ] ✅ ผู้ประเมินสามารถประเมินได้
- [ ] ✅ ดาวน์โหลด PDF ได้
- [ ] ✅ ไม่มี error แสดง
- [ ] ✅ SSL ใช้งานได้ (HTTPS)
- [ ] ✅ Responsive ทำงานบนมือถือ
- [ ] ✅ Email notification (ถ้ามี)

---

## 🐛 การแก้ปัญหา

### ปัญหา: ไฟล์อัปโหลดไม่ได้

```bash
# ตรวจสอบ permissions
ls -la uploads/
ls -la uploads/plans/

# แก้ไข
chmod 755 uploads uploads/plans uploads/reflections
chown www-data:www-data uploads -R
```

### ปัญหา: Database connection error

```bash
# ตรวจสอบ MySQL
sudo systemctl status mysql

# ทดสอบ connection
mysql -u plan_user -p lesson_plan_system
```

### ปัญหา: PHP errors

```bash
# ดู error log
tail -f /var/log/apache2/error.log
# หรือ
tail -f /var/log/nginx/error.log
```

---

## 📝 หมายเหตุ

1. **อย่าลืมลบไฟล์ทดสอบ:**
   - `generate_password.php`
   - `test_password.php`
   - `phpinfo.php` (ถ้ามี)

2. **เปลี่ยน `BASE_URL` ทุกครั้งที่ deploy:**
   - Localhost: `http://localhost/plan`
   - Production: `https://your-domain.com`

3. **Backup ก่อน Update:**
   ```bash
   # Backup database
   mysqldump -u plan_user -p lesson_plan_system > backup.sql
   
   # Backup files
   tar -czf plan_backup_$(date +%Y%m%d).tar.gz /path/to/plan
   ```

---

## 🔄 การอัปเดทระบบ

```bash
# SSH เข้า server
ssh user@your-domain.com

# ไปที่โฟลเดอร์โปรเจค
cd /path/to/plan

# Pull code ใหม่
git pull origin main

# Clear cache (ถ้ามี)
# ...

# Restart web server
sudo systemctl restart apache2
# หรือ
sudo systemctl restart nginx
```

---

## 📞 ติดต่อ ครูจักรพงษ์ แผ่นทอง โทร.0899429565

หากมีปัญหา ติดต่อ: mercedesbenz3010@gmail.com