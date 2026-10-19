<?php
/** ผู้ใช้งานและรายงาน — manage accounts (admin only) + export CSV reports. Everyone can change their own password. */
require __DIR__ . '/../../app/bootstrap.php';
$u = require_login(['editor', 'staff', 'auditor']);
$isAdmin = $u['role'] === 'admin';
$pageTitle = 'ผู้ใช้งานและรายงาน';
$active = 'users';

// ---- CSV exports (open in Excel) ----
if (isset($_GET['export'])) {
    $type = $_GET['export'];
    $out = fopen('php://output', 'w');
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="green-office-' . preg_replace('/[^a-z]/', '', $type) . '-' . date('Ymd') . '.csv"');
    fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM so Excel shows Thai correctly
    if ($type === 'stats') {
        fputcsv($out, ['ปี พ.ศ.', 'เดือน', 'น้ำมันเชื้อเพลิง (ลิตร)', 'ก๊าซเรือนกระจก (tCO2e)', 'ไฟฟ้า (kWh)', 'น้ำ (ลบ.ม.)', 'กระดาษ (รีม)', 'หมายเหตุ']);
        foreach (rows('SELECT * FROM resource_monthly ORDER BY year_be, month') as $r) {
            fputcsv($out, [$r['year_be'], THAI_MONTHS[$r['month']], $r['fuel_l'], round(ghg_of($r), 2), $r['elec_kwh'], $r['water_m3'], $r['paper_ream'], $r['note']]);
        }
    } elseif ($type === 'reports') {
        $year = (int)($_GET['year'] ?? report_years()[0]);
        fputcsv($out, ['หมวด', 'ประเด็น', 'ตัวชี้วัด', 'รายงานผล ปี ' . $year, 'หลักฐาน']);
        foreach (categories() as $c) {
            foreach (category_table((int)$c['id'], $year) as $iss) {
                foreach ($iss['indicators'] as $d) {
                    fputcsv($out, [$c['id'], $iss['code'] . ' ' . $iss['title'], $d['code'] . ' ' . $d['title'], $d['report'], implode("\n", array_map(fn($e) => $e['label'] . ' ' . $e['url'], $d['evidence']))]);
                }
            }
        }
    } elseif ($type === 'game') {
        $g = game_stats();
        fputcsv($out, ['เรื่อง', 'จำนวนคน (ทำประจำ/บางครั้ง)', 'ร้อยละ', 'ผู้เล่นทั้งหมด']);
        foreach ($g['topics'] as $t) {
            fputcsv($out, [$t['name'], $t['count'], $t['pct'], $g['players']]);
        }
    } elseif ($type === 'complaints') {
        fputcsv($out, ['เลขที่', 'วันที่', 'ประเภท', 'ด้าน', 'สถานที่', 'รายละเอียด', 'ความพึงพอใจ', 'สถานะ', 'ผลการดำเนินงาน']);
        foreach (rows('SELECT * FROM complaints ORDER BY created_at') as $c) {
            fputcsv($out, [$c['ticket_no'], $c['created_at'], $c['type'], $c['topic'], $c['place'], $c['detail'], $c['rating'], COMPLAINT_STATUS[$c['status']]['label'], $c['result_text']]);
        }
    }
    exit;
}

if (is_post()) {
    csrf_check();
    $a = $_POST['action'] ?? '';
    if ($a === 'my_password') {
        $me = row('SELECT password_hash FROM users WHERE id = ?', [$u['id']]);
        $new = (string)$_POST['new_password'];
        if (!password_verify((string)$_POST['current_password'], $me['password_hash'])) {
            flash('รหัสผ่านปัจจุบันไม่ถูกต้อง', 'error');
        } elseif (mb_strlen($new) < 8) {
            flash('รหัสผ่านใหม่ต้องยาวอย่างน้อย 8 ตัวอักษร', 'error');
        } else {
            q('UPDATE users SET password_hash = ? WHERE id = ?', [password_hash($new, PASSWORD_DEFAULT), $u['id']]);
            flash('เปลี่ยนรหัสผ่านแล้ว');
        }
    } elseif ($isAdmin && $a === 'add') {
        $name = trim((string)$_POST['username']);
        $pw = (string)$_POST['password'];
        if (!preg_match('/^[a-zA-Z0-9._-]{3,50}$/', $name)) {
            flash('ชื่อผู้ใช้ใช้ได้เฉพาะ a-z 0-9 . _ - (3-50 ตัว)', 'error');
        } elseif (mb_strlen($pw) < 8) {
            flash('รหัสผ่านต้องยาวอย่างน้อย 8 ตัวอักษร', 'error');
        } elseif (val('SELECT COUNT(*) FROM users WHERE username = ?', [$name])) {
            flash('มีชื่อผู้ใช้นี้แล้ว', 'error');
        } else {
            q('INSERT INTO users (username, password_hash, display_name, role, unit) VALUES (?,?,?,?,?)', [
                $name, password_hash($pw, PASSWORD_DEFAULT), mb_substr(trim((string)$_POST['display_name']), 0, 120),
                array_key_exists($_POST['role'] ?? '', ROLES) ? $_POST['role'] : 'staff', mb_substr(trim((string)$_POST['unit']), 0, 150),
            ]);
            flash('เพิ่มผู้ใช้แล้ว');
        }
    } elseif ($isAdmin && $a === 'update') {
        $id = (int)$_POST['id'];
        $role = array_key_exists($_POST['role'] ?? '', ROLES) ? $_POST['role'] : 'staff';
        if ($id === (int)$u['id'] && $role !== 'admin') {
            flash('เปลี่ยนสิทธิ์ของตัวเองออกจากผู้ดูแลระบบไม่ได้', 'error');
        } else {
            q('UPDATE users SET display_name = ?, role = ?, unit = ?, is_active = ? WHERE id = ?', [
                mb_substr(trim((string)$_POST['display_name']), 0, 120), $role, mb_substr(trim((string)$_POST['unit']), 0, 150),
                ($id === (int)$u['id'] || isset($_POST['is_active'])) ? 1 : 0, $id,
            ]);
            if (mb_strlen((string)($_POST['reset_password'] ?? '')) >= 8) {
                q('UPDATE users SET password_hash = ? WHERE id = ?', [password_hash((string)$_POST['reset_password'], PASSWORD_DEFAULT), $id]);
            }
            flash('บันทึกแล้ว');
        }
    } elseif ($isAdmin && $a === 'delete' && (int)$_POST['id'] !== (int)$u['id']) {
        q('DELETE FROM users WHERE id = ?', [(int)$_POST['id']]);
        flash('ลบผู้ใช้แล้ว');
    }
    redirect('admin/users.php');
}

$users = $isAdmin ? rows('SELECT * FROM users ORDER BY id') : [];
$isDefaultPw = $isAdmin && password_verify('green2569', (string)val('SELECT password_hash FROM users WHERE id = ?', [$u['id']]));
require __DIR__ . '/../../app/views/admin_top.php';
?>
<?php if ($isDefaultPw): ?>
  <div role="alert" class="rounded-2xl bg-[#FFF2C2] text-[#6B4E00] px-4 py-3">บัญชีนี้ยังใช้รหัสผ่านเริ่มต้น — กรุณาเปลี่ยนรหัสผ่านด้านล่างก่อนใช้งานจริง</div>
<?php endif; ?>
<div class="flex flex-wrap gap-4 items-start">
  <?php if ($isAdmin): ?>
  <div class="flex-[2_1_520px] bg-white rounded-3xl p-6 flex flex-col gap-3">
    <h2 class="font-display font-medium text-xl">ผู้ใช้งาน</h2>
    <?php foreach ($users as $x): ?>
      <form method="post" class="flex flex-wrap gap-2 items-end border-t border-[#EDF0EA] pt-3">
        <?= csrf_field() ?><input type="hidden" name="action" value="update"><input type="hidden" name="id" value="<?= (int)$x['id'] ?>">
        <div class="w-32"><div class="text-xs text-muted">ชื่อผู้ใช้</div><div class="font-semibold min-h-[46px] flex items-center"><?= e($x['username']) ?></div></div>
        <label class="label flex-[1_1_150px]">ชื่อที่แสดง<input type="text" name="display_name" value="<?= e($x['display_name']) ?>" class="input"></label>
        <label class="label flex-[1_1_150px]">หน่วยงาน<input type="text" name="unit" value="<?= e($x['unit']) ?>" class="input"></label>
        <label class="label">สิทธิ์<select name="role" class="input"><?php foreach (ROLES as $k => $l): ?><option value="<?= $k ?>" <?= $x['role'] === $k ? 'selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?></select></label>
        <label class="label w-40">ตั้งรหัสผ่านใหม่<input type="password" name="reset_password" autocomplete="new-password" placeholder="เว้นว่าง = ไม่เปลี่ยน" class="input"></label>
        <label class="flex items-center gap-1.5 text-sm min-h-[46px]"><input type="checkbox" name="is_active" <?= $x['is_active'] ? 'checked' : '' ?> class="w-5 h-5 accent-ku"> ใช้งาน</label>
        <button class="btn-sm btn-outline min-h-[46px]">บันทึก</button>
        <?php if ((int)$x['id'] !== (int)$u['id']): ?></form><form method="post" class="flex items-end"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$x['id'] ?>"><button data-confirm="ลบผู้ใช้นี้?" class="btn-sm btn-danger min-h-[46px]">ลบ</button><?php endif; ?>
      </form>
    <?php endforeach; ?>
    <form method="post" class="flex flex-wrap gap-2 items-end border-t-2 border-ku/20 pt-4 mt-2">
      <?= csrf_field() ?><input type="hidden" name="action" value="add">
      <label class="label w-36">ชื่อผู้ใช้ใหม่<input type="text" name="username" required class="input"></label>
      <label class="label flex-[1_1_150px]">ชื่อที่แสดง<input type="text" name="display_name" class="input"></label>
      <label class="label flex-[1_1_150px]">หน่วยงาน<input type="text" name="unit" class="input"></label>
      <label class="label">สิทธิ์<select name="role" class="input"><?php foreach (ROLES as $k => $l): ?><option value="<?= $k ?>" <?= $k === 'staff' ? 'selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?></select></label>
      <label class="label w-40">รหัสผ่าน (8+)<input type="password" name="password" required minlength="8" autocomplete="new-password" class="input"></label>
      <button class="btn-primary min-h-[46px]">+ เพิ่มผู้ใช้</button>
    </form>
  </div>
  <?php endif; ?>

  <div class="flex-[1_1_300px] flex flex-col gap-4">
    <form method="post" class="bg-white rounded-3xl p-6 flex flex-col gap-3">
      <?= csrf_field() ?><input type="hidden" name="action" value="my_password">
      <h2 class="font-display font-medium text-xl">เปลี่ยนรหัสผ่านของฉัน</h2>
      <label class="label">รหัสผ่านปัจจุบัน<input type="password" name="current_password" required autocomplete="current-password" class="input"></label>
      <label class="label">รหัสผ่านใหม่ (อย่างน้อย 8 ตัว)<input type="password" name="new_password" required minlength="8" autocomplete="new-password" class="input"></label>
      <button class="btn-primary">เปลี่ยนรหัสผ่าน</button>
    </form>
    <div class="bg-white rounded-3xl p-6 flex flex-col gap-2.5">
      <h2 class="font-display font-medium text-xl">ส่งออกรายงาน (เปิดใน Excel)</h2>
      <a href="<?= e(url('admin/users.php?export=stats')) ?>" class="btn border border-[#CFD6CC] bg-[#FAFBF8] justify-start rounded-2xl">สถิติทรัพยากรรายเดือน (CSV)</a>
      <a href="<?= e(url('admin/users.php?export=reports&year=' . report_years()[0])) ?>" class="btn border border-[#CFD6CC] bg-[#FAFBF8] justify-start rounded-2xl">รายงานผล 7 หมวด ปี <?= report_years()[0] ?> (CSV)</a>
      <a href="<?= e(url('admin/users.php?export=game')) ?>" class="btn border border-[#CFD6CC] bg-[#FAFBF8] justify-start rounded-2xl">การมีส่วนร่วมจากเกม Intro (CSV)</a>
      <a href="<?= e(url('admin/users.php?export=complaints')) ?>" class="btn border border-[#CFD6CC] bg-[#FAFBF8] justify-start rounded-2xl">ข้อร้องเรียนทั้งหมด (CSV)</a>
    </div>
  </div>
</div>
<?php require __DIR__ . '/../../app/views/admin_bottom.php'; ?>
