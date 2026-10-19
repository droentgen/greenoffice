# Green Office Digital Platform — คณะเกษตร มหาวิทยาลัยเกษตรศาสตร์

เว็บไซต์สำนักงานสีเขียว (Public Website) + ระบบหลังบ้าน (Admin & Assessment Portal)
เขียนด้วย **PHP 8 + PostgreSQL (Supabase)** แบบไม่ใช้ framework และ **Tailwind CSS** — แก้ไขง่ายด้วย VS Code หรือ Claude Code

## สิ่งที่ต้องมี
- PHP 8.1 ขึ้นไป (มี `pdo_pgsql`, `mbstring`, `fileinfo`)
- โปรเจกต์ Supabase (ฟรี) หรือ PostgreSQL 14 ขึ้นไปสำหรับรันเอง

## ตั้งค่า Supabase
1. สร้างโปรเจกต์ใหม่ที่ https://supabase.com/
2. ไปที่ Project Settings › Database แล้วคัดลอก connection info (host, port, database, user, password — ใช้ค่าจาก connection pooler ถ้ามี)
3. สร้าง Storage bucket สำหรับไฟล์อัปโหลด (รูปข่าว/เอกสาร): Storage → New bucket → ตั้งชื่อ `uploads` → เปิด **Public bucket**
4. ไปที่ Project Settings › API แล้วคัดลอกคีย์ **service_role** (ไม่ใช่ anon/publishable) — ใช้สำหรับอัปโหลดไฟล์จากฝั่งเซิร์ฟเวอร์เท่านั้น เก็บเป็นความลับเหมือนรหัสผ่าน DB
5. คัดลอก `config/config.sample.php` เป็น `config/config.php` แล้วใส่ค่าที่คัดลอกมาทั้งหมด (`db` และ `storage`)
6. สร้างตาราง + ข้อมูลเริ่มต้น เลือกวิธีใดวิธีหนึ่ง
   - **ผ่าน Supabase SQL Editor**: วางเนื้อหา `database/schema.sql` แล้วรัน → วางเนื้อหา `database/seed.sql` แล้วรัน
   - **ผ่าน Terminal**: `php database/install.php` (สร้างตารางและใส่ข้อมูลให้อัตโนมัติ, เพิ่ม `--no-sample` ถ้าไม่ต้องการข้อมูลตัวอย่าง)
7. รัน `php -S localhost:8000 -t public` แล้วเปิด http://localhost:8000

## ขึ้นเว็บจริงด้วย Railway
โปรเจกต์นี้มี `Dockerfile` พร้อมใช้ (PHP 8.2 + Apache + `pdo_pgsql`, web root ชี้ไปที่ `public/` ให้แล้ว)

1. ที่ https://railway.app/ → New Project → Deploy from GitHub repo → เลือก repo นี้ (Railway จะเจอ `Dockerfile` แล้วใช้ build ให้เอง)
2. ไปที่ service → Variables แล้วเพิ่ม environment variables:
   - `DB_HOST` เช่น `aws-0-ap-northeast-1.pooler.supabase.com`
   - `DB_PORT` = `5432`
   - `DB_NAME` = `postgres`
   - `DB_USER` เช่น `postgres.<project-ref>`
   - `DB_PASS` = รหัสผ่านฐานข้อมูล
   - `SUPABASE_URL` เช่น `https://<project-ref>.supabase.co`
   - `SUPABASE_SERVICE_KEY` = คีย์ service_role (Project Settings › API)
   - `SUPABASE_BUCKET` = `uploads` (ไม่ต้องตั้งก็ได้ ค่า default คือ `uploads`)
   - `APP_DEBUG` = `false` (ไม่ต้องตั้งก็ได้ ค่า default คือ false)
3. Railway จะ deploy อัตโนมัติ — `docker-entrypoint.sh` จะสร้าง `config/config.php` จาก environment variables เหล่านี้ตอนคอนเทนเนอร์เริ่มทำงาน (ไฟล์นี้จึงไม่เคยถูก commit หรือ bake เข้าไปใน image)
4. เปิด URL ที่ Railway ให้มา — ควรขึ้นหน้าแรกของเว็บได้ทันที (ไม่ใช่หน้าดาวน์โหลดไฟล์ .php แบบที่เกิดกับ Vercel เพราะ Vercel ไม่มี PHP runtime)

## ขึ้นเว็บจริงด้วย Vercel
โปรเจกต์นี้มี `Dockerfile.vercel` (FrankenPHP + `pdo_pgsql`) + `Caddyfile` + `vercel.json` พร้อมใช้ — Vercel รองรับ PHP ผ่าน Docker/container runtime แบบนี้โดยเฉพาะ (วิธีเก่าที่อัปโหลดไฟล์ .php ตรงๆใช้ไม่ได้ เพราะ Vercel ไม่มี PHP runtime ให้ในตัว)

1. ลิงก์ GitHub repo นี้เข้า Vercel project (Vercel จะเจอ `vercel.json` แล้ว build จาก `Dockerfile.vercel` ให้เอง)
2. Settings → Environment Variables → เพิ่มตัวแปรชุดเดียวกับ Railway ด้านบนทั้งหมด (`DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS`, `SUPABASE_URL`, `SUPABASE_SERVICE_KEY`, `SUPABASE_BUCKET`)
3. Deploy แล้วเปิด URL ที่ได้มา

**เรื่อง session/CSRF และไฟล์อัปโหลด**: เนื่องจาก Vercel อาจรันแต่ละ request บน container คนละตัว โปรเจกต์นี้จึงเก็บ PHP session ไว้ใน Postgres (ตาราง `app_sessions`, ดู `app/session_db.php`) และเก็บไฟล์อัปโหลดไว้ใน Supabase Storage (ดู `storage_put()`/`storage_delete()`/`upload_url()` ใน `app/helpers.php`) แทนดิสก์ในเครื่อง ไม่ต้องตั้งค่าเพิ่มอะไร — ใช้ environment variables ชุดข้างบนครบแล้วทำงานได้เลย

## เข้าระบบหลังบ้าน
- URL: `/admin/`
- ชื่อผู้ใช้ **admin** · รหัสผ่าน **green2569**
- ⚠️ เปลี่ยนรหัสผ่านทันทีหลังเข้าใช้ครั้งแรก (ระบบหลังบ้าน › ผู้ใช้งานและรายงาน) — ระบบจะเตือนจนกว่าจะเปลี่ยน

## โครงสร้างโฟลเดอร์
```
green-office/
├─ public/                 ← หน้าเว็บทั้งหมด (ตั้งเป็น Document Root บนเซิร์ฟเวอร์จริง)
│  ├─ index.php            หน้าแรก + เกม Green Check-in + ตัวเลขวิ่ง
│  ├─ about.php            เกี่ยวกับเรา (แท็บ นโยบาย/เป้าหมาย/คณะทำงาน/มาตรการ แสดงรูปหรือ PDF, คลิปสรุป)
│  ├─ operations.php       การดำเนินงาน 7 หมวด
│  ├─ category.php         ตารางตัวชี้วัดแต่ละหมวด กรองตามปี
│  ├─ stats.php            สถิติการใช้ทรัพยากร
│  ├─ news.php, news-view.php
│  ├─ feedback.php         ข้อคิดเห็นและข้อร้องเรียนทางด้านสิ่งแวดล้อม (แบบสอบถาม)
│  ├─ complaints.php, complaint-view.php   ประกาศข้อร้องเรียน
│  ├─ complaint-plan.php   แผนการจัดการข้อร้องเรียน
│  ├─ api/game.php         บันทึกผลเกม
│  ├─ admin/               ระบบหลังบ้าน (ทุกหน้า)
│  ├─ assets/              css, js, รูปโลโก้
│  └─ uploads/             ไฟล์ที่อัปโหลด (PDF/รูป)
├─ app/                    โค้ดส่วนกลาง (ห้ามเปิดจากเว็บ)
│  ├─ bootstrap.php        โหลด config, session, ฐานข้อมูล
│  ├─ db.php               ฟังก์ชัน q(), rows(), row(), val()
│  ├─ helpers.php          e(), url(), csrf, flash, upload, setting()
│  ├─ auth.php             เข้าสู่ระบบ/สิทธิ์ผู้ใช้
│  ├─ data.php             query ที่ใช้หลายหน้า (สถิติ, ตัวชี้วัด, ข้อร้องเรียน)
│  └─ views/               header/footer ของหน้าเว็บและหลังบ้าน, มาสคอต
├─ config/                 config.php (ไม่อยู่ใน git)
└─ database/               schema.sql, seed.sql, install.php, criteria.json
```

## แก้ไขบ่อย ๆ ทำที่ไหน
| อยากแก้ | ไฟล์ |
|---|---|
| สี / ฟอนต์ ทั้งเว็บ | `app/views/partials/head.php` (tailwind.config) |
| เมนูด้านบน / ส่วนท้ายทุกหน้า | `app/views/public_top.php`, `app/views/public_bottom.php` |
| คำถามเกม Intro | `public/index.php` (ตัวแปร `$questions`) และ `GAME_TOPICS` ใน `app/data.php` |
| ภารกิจสุ่มในแถบฮีโร่ | `$tips` ใน `app/views/public_bottom.php` |
| ที่อยู่ เบอร์ อีเมล คลิป ค่า EF | ระบบหลังบ้าน › ตั้งค่าเว็บไซต์ (ไม่ต้องแก้โค้ด) |
| เมนูหลังบ้าน / สิทธิ์ | `app/views/admin_top.php` (`$menu`) |
| ตารางฐานข้อมูล | `database/schema.sql` |

## สิทธิ์ผู้ใช้
| สิทธิ์ | ทำอะไรได้ |
|---|---|
| ผู้ดูแลระบบ (admin) | ทุกอย่าง รวมผู้ใช้และตั้งค่า |
| ผู้จัดการเนื้อหา (editor) | ข่าว เอกสาร แผนกิจกรรม รายงานผล ข้อร้องเรียน |
| ผู้กรอกข้อมูล (staff) | สถิติ แผนกิจกรรม รายงานผล |
| ผู้ตรวจประเมิน (auditor) | รายงานผล ตรวจประเมินและติดตามแก้ไข |

## ขึ้นเซิร์ฟเวอร์จริง
1. ตั้ง Document Root ไปที่ `public/` แล้วใส่ `'base_url' => ''` ใน config (ถ้าวางทั้งโฟลเดอร์ใน web root ไฟล์ `.htaccess` จะกันโฟลเดอร์ `app/ config/ database/` ให้)
2. ตั้ง `'debug' => false`
3. ใช้ HTTPS และเปลี่ยนรหัสผ่าน admin
4. ให้สิทธิ์เขียนโฟลเดอร์ `public/uploads/`
5. ลบข้อมูลตัวอย่าง: ติดตั้งใหม่ด้วย `php database/install.php --no-sample`
6. Tailwind ตอนนี้โหลดจาก CDN (สะดวกตอนพัฒนา) — ถ้าต้องการเร็วขึ้นบนเซิร์ฟเวอร์จริง ให้ build CSS ด้วย Tailwind CLI แล้วเปลี่ยน `<script src="https://cdn.tailwindcss.com">` เป็น `<link>` ไฟล์ CSS
7. ฐานข้อมูล Supabase เข้าถึงได้จากอินเทอร์เน็ตอยู่แล้ว ตรวจสอบว่า Network Restrictions ของโปรเจกต์ (Supabase › Database) อนุญาต IP ของเซิร์ฟเวอร์ที่ใช้รันเว็บ

## ความปลอดภัยที่มีให้แล้ว
รหัสผ่านเข้ารหัส bcrypt · CSRF token ทุกฟอร์ม · Prepared statements ทุก query · Escape HTML ทุกจุด · ตรวจชนิดไฟล์อัปโหลดจากเนื้อไฟล์จริง และกันรันสคริปต์ในโฟลเดอร์ uploads · จำกัดการเดารหัสผ่าน (5 ครั้ง/60 วินาที) · ข้อมูลผู้แจ้งข้อร้องเรียนไม่แสดงบนหน้าเว็บ

## ข้อมูลตัวอย่าง
สถิติรายเดือน ข่าว รายงานผลบางข้อ ข้อร้องเรียน 3 เรื่อง และข้อสังเกตการตรวจ เป็น **ข้อมูลตัวอย่าง** สำหรับทดลองระบบ
ข้อความในวงเล็บ เช่น `[เบอร์โทรสำนักงาน]` คือช่องที่รอใส่ข้อมูลจริง
ตัวชี้วัด 73 ข้อใน 7 หมวด มาจากตารางเกณฑ์สำนักงานสีเขียวที่แนบมา (`database/criteria.json`)
