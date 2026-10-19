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

const VIDEO_TYPES = [
    'youtube' => 'ลิงก์ YouTube',
    'drive'   => 'ลิงก์ Google Drive',
    'mp4'     => 'อัปโหลดไฟล์ .mp4',
    'other'   => 'ลิงก์อื่น ๆ',
];

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
    $action = $_POST['action'] ?? '';
    if ($action === 'video') {
        save_setting('video_title', mb_substr(trim((string)($_POST['video_title'] ?? '')), 0, 255));
        $type = array_key_exists($_POST['video_type'] ?? '', VIDEO_TYPES) ? $_POST['video_type'] : 'youtube';
        try {
            if ($type === 'mp4') {
                $path = save_upload('video_file', 'videos', ['mp4']);
                if ($path) {
                    delete_upload(setting('video_path'));
                    save_setting('video_path', $path);
                    save_setting('video_url', '');
                } elseif (!setting('video_path')) {
                    throw new RuntimeException('กรุณาเลือกไฟล์ .mp4');
                }
            } else {
                $link = clean_url((string)($_POST['video_link'] ?? ''));
                if (!$link) {
                    throw new RuntimeException('กรุณาใส่ลิงก์ (ต้องขึ้นต้นด้วย http:// หรือ https://)');
                }
                delete_upload(setting('video_path'));
                save_setting('video_path', '');
                save_setting('video_url', $link);
            }
            save_setting('video_type', $type);
            flash('บันทึกคลิปแล้ว');
        } catch (RuntimeException $ex) {
            flash($ex->getMessage(), 'error');
        }
        redirect('admin/settings.php');
    }

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

$videoType = array_key_exists(setting('video_type', 'youtube'), VIDEO_TYPES) ? setting('video_type', 'youtube') : 'youtube';

require __DIR__ . '/../../app/views/admin_top.php';
?>
<div class="flex flex-col gap-4 max-w-4xl">
  <form method="post" enctype="multipart/form-data" class="bg-white rounded-3xl p-6 flex flex-col gap-3.5">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="video">
    <h2 class="font-display font-medium text-xl">คลิปสรุปการดำเนินงาน</h2>
    <label class="label">ชื่อคลิป<input type="text" name="video_title" value="<?= e(setting('video_title')) ?>" class="input"></label>
    <label class="label">เลือกวิธีใส่คลิป
      <select name="video_type" id="video_type" class="input">
        <?php foreach (VIDEO_TYPES as $k => $label): ?>
          <option value="<?= e($k) ?>" <?= $k === $videoType ? 'selected' : '' ?>><?= e($label) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <label class="label" data-video-field="youtube,drive,other">ลิงก์คลิป
      <input type="url" name="video_link" value="<?= e(setting('video_url')) ?>" placeholder="https://" class="input">
    </label>
    <div data-video-field="mp4">
      <label class="label">ไฟล์ .mp4 (ไม่เกิน <?= (int)($CONFIG['upload_max_mb'] ?? 10) ?> MB — คลิปยาวควรใช้ YouTube หรือ Google Drive แทน)
        <input type="file" name="video_file" accept=".mp4" class="input py-2.5">
      </label>
      <?php if (setting('video_path')): ?><p class="text-sm text-muted mt-1">ไฟล์ปัจจุบัน: <?= e(basename(setting('video_path'))) ?> — เลือกไฟล์ใหม่เพื่อแทนที่</p><?php endif; ?>
    </div>
    <p class="text-sm text-muted">YouTube/Google Drive: วางลิงก์หน้าดูวิดีโอปกติได้เลย ("ลิงก์อื่น ๆ" ใช้ได้กับเว็บที่อนุญาตให้ฝังวิดีโอ (embed) เท่านั้น)</p>
    <button class="btn-primary self-start px-8">บันทึกคลิป</button>
  </form>
  <script>
    (function () {
      var sel = document.getElementById('video_type');
      function sync() {
        document.querySelectorAll('[data-video-field]').forEach(function (el) {
          el.hidden = el.getAttribute('data-video-field').split(',').indexOf(sel.value) === -1;
        });
      }
      sel.addEventListener('change', sync);
      sync();
    })();
  </script>

  <form method="post" class="flex flex-col gap-4">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="settings">
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
</div>
<?php require __DIR__ . '/../../app/views/admin_bottom.php'; ?>
