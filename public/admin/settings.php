<?php
/** ตั้งค่าเว็บไซต์ — contact details, video, external form, emission factors, report years. */
require __DIR__ . '/../../app/bootstrap.php';
$u = require_login();
if ($u['role'] !== 'admin') {
    http_response_code(403);
    exit('เฉพาะผู้ดูแลระบบ');
}
$pageTitle = 'ตั้งค่าเว็บไซต์';
$active = 'settings';

$fields = [
    'ข้อมูลติดต่อ (ส่วนท้ายทุกหน้า: พูดคุยกับเรา)' => [
        'contact_address' => ['ที่อยู่', 'textarea'],
        'contact_web'     => ['เว็บไซต์', 'url'],
        'contact_phone'   => ['เบอร์โทร', 'text'],
        'contact_email'   => ['อีเมล', 'text'],
        'complaint_box'   => ['จุดตั้งกล่องรับความคิดเห็น', 'text'],
    ],
    'หน้าเว็บ' => [
        'site_name'         => ['ชื่อเว็บไซต์', 'text'],
        'hero_tag'          => ['แฮชแท็กหน้าแรก', 'text'],
        'video_url'         => ['คลิปสรุปการดำเนินงาน (ลิงก์ YouTube หรือไฟล์ .mp4)', 'url'],
        'video_title'       => ['ชื่อคลิป', 'text'],
        'feedback_form_url' => ['แบบสอบถามภายนอก เช่น Google Form (ไม่บังคับ)', 'url'],
    ],
    'การคำนวณและรายงาน' => [
        'ef_fuel'      => ['ค่าการปล่อยก๊าซ น้ำมัน (kgCO₂e/ลิตร)', 'number'],
        'ef_elec'      => ['ค่าการปล่อยก๊าซ ไฟฟ้า (kgCO₂e/kWh)', 'number'],
        'report_years' => ['ปีที่แสดงในตัวกรอง (คั่นด้วย , เช่น 2567,2568,2569)', 'text'],
    ],
];

if (is_post()) {
    csrf_check();
    foreach ($fields as $group) {
        foreach ($group as $k => [$label, $type]) {
            $v = trim((string)($_POST[$k] ?? ''));
            if ($type === 'url' && $v !== '' && !clean_url($v)) {
                flash($label . ': ลิงก์ต้องขึ้นต้นด้วย http:// หรือ https://', 'error');
                continue;
            }
            if ($type === 'number') {
                $v = (string)(float)$v;
            }
            save_setting($k, $v);
        }
    }
    flash('บันทึกการตั้งค่าแล้ว');
    redirect('admin/settings.php');
}

require __DIR__ . '/../../app/views/admin_top.php';
?>
<form method="post" class="flex flex-col gap-4 max-w-4xl">
  <?= csrf_field() ?>
  <?php foreach ($fields as $title => $group): ?>
    <fieldset class="bg-white rounded-3xl p-6 grid gap-3.5 [grid-template-columns:repeat(auto-fit,minmax(260px,1fr))]">
      <legend class="sr-only"><?= e($title) ?></legend>
      <h2 class="font-display font-medium text-xl col-span-full"><?= e($title) ?></h2>
      <?php foreach ($group as $k => [$label, $type]): ?>
        <label class="label <?= $type === 'textarea' ? 'col-span-full' : '' ?>"><?= e($label) ?>
          <?php if ($type === 'textarea'): ?>
            <textarea name="<?= $k ?>" rows="3" class="input"><?= e(setting($k)) ?></textarea>
          <?php else: ?>
            <input type="<?= $type === 'number' ? 'number' : ($type === 'url' ? 'url' : 'text') ?>" <?= $type === 'number' ? 'step="0.0001"' : '' ?> name="<?= $k ?>" value="<?= e(setting($k)) ?>" class="input">
          <?php endif; ?>
        </label>
      <?php endforeach; ?>
    </fieldset>
  <?php endforeach; ?>
  <p class="text-sm text-muted">ค่าการปล่อยก๊าซเรือนกระจกควรอ้างอิงค่าล่าสุดจากองค์การบริหารจัดการก๊าซเรือนกระจก (อบก.)</p>
  <button class="btn-primary self-start px-8">บันทึกการตั้งค่า</button>
</form>
<?php require __DIR__ . '/../../app/views/admin_bottom.php'; ?>
