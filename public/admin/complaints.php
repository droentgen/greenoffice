<?php
/**
 * ข้อร้องเรียน — inbox of feedback from the website form, publishing to การดำเนินการเรื่องร้องเรียน,
 * and the steps of แผนการจัดการข้อร้องเรียน (tab "plan").
 */
require __DIR__ . '/../../app/bootstrap.php';
$u = require_login(['editor']);
$pageTitle = 'ข้อร้องเรียน';
$active = 'complaints';
$tab = ($_GET['tab'] ?? '') === 'plan' ? 'plan' : 'inbox';

if (is_post()) {
    csrf_check();
    $a = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);
    if ($a === 'update') {
        $status = array_key_exists($_POST['status'] ?? '', COMPLAINT_STATUS) ? $_POST['status'] : 'received';
        q('UPDATE complaints SET status = ?, result_text = ?, is_public = ?, public_title = ?, public_detail = ? WHERE id = ?', [
            $status, trim((string)$_POST['result_text']), isset($_POST['is_public']) ? 1 : 0,
            mb_substr(trim((string)$_POST['public_title']), 0, 255), trim((string)$_POST['public_detail']), $id,
        ]);
        flash('บันทึกแล้ว');
        redirect('admin/complaints.php?id=' . $id);
    } elseif ($a === 'delete') {
        q('DELETE FROM complaints WHERE id = ?', [$id]);
        flash('ลบเรื่องแล้ว');
    } elseif ($a === 'save_step') {
        $vals = [(int)$_POST['step_no'], mb_substr(trim((string)$_POST['title']), 0, 150), mb_substr(trim((string)$_POST['detail']), 0, 500), mb_substr(trim((string)$_POST['timeframe']), 0, 80), mb_substr(trim((string)$_POST['owner']), 0, 150)];
        if ($vals[1] !== '') {
            $id ? q('UPDATE complaint_plan_steps SET step_no = ?, title = ?, detail = ?, timeframe = ?, owner = ? WHERE id = ?', [...$vals, $id])
                : q('INSERT INTO complaint_plan_steps (step_no, title, detail, timeframe, owner) VALUES (?,?,?,?,?)', $vals);
            flash('บันทึกขั้นตอนแล้ว');
        }
        redirect('admin/complaints.php?tab=plan');
    } elseif ($a === 'delete_step') {
        q('DELETE FROM complaint_plan_steps WHERE id = ?', [$id]);
        redirect('admin/complaints.php?tab=plan');
    }
    redirect('admin/complaints.php');
}

$filter = array_key_exists($_GET['status'] ?? '', COMPLAINT_STATUS) ? $_GET['status'] : '';
$one = isset($_GET['id']) ? row('SELECT * FROM complaints WHERE id = ?', [(int)$_GET['id']]) : null;
$list = $filter ? rows('SELECT * FROM complaints WHERE status = ? ORDER BY created_at DESC', [$filter]) : rows('SELECT * FROM complaints ORDER BY created_at DESC');
$steps = rows('SELECT * FROM complaint_plan_steps ORDER BY step_no');

require __DIR__ . '/../../app/views/admin_top.php';
?>
<nav class="flex gap-2 flex-wrap">
  <a href="<?= e(url('admin/complaints.php')) ?>" class="btn border-2 border-ku <?= $tab === 'inbox' ? 'bg-ku text-white' : 'bg-white text-ku' ?>">กล่องรับเรื่อง</a>
  <a href="<?= e(url('admin/complaints.php?tab=plan')) ?>" class="btn border-2 border-ku <?= $tab === 'plan' ? 'bg-ku text-white' : 'bg-white text-ku' ?>">แผนการจัดการข้อร้องเรียน</a>
  <a href="<?= e(url('admin/documents.php?section=complaint_plan')) ?>" class="btn bg-white text-ku border-2 border-[#DDE3D9]">ไฟล์แผนฉบับเต็ม (PDF)</a>
  <a href="<?= e(url('complaints.php')) ?>" target="_blank" class="btn text-ku underline">ดูประกาศหน้าเว็บ ↗</a>
</nav>

<?php if ($tab === 'plan'): ?>
  <div class="bg-white rounded-3xl p-6 flex flex-col gap-3">
    <?php foreach (array_merge($steps, [['id' => 0, 'step_no' => count($steps) + 1, 'title' => '', 'detail' => '', 'timeframe' => '', 'owner' => '']]) as $s): ?>
      <form method="post" class="flex flex-wrap gap-2 items-end border-t border-[#EDF0EA] pt-3">
        <?= csrf_field() ?><input type="hidden" name="action" value="save_step"><input type="hidden" name="id" value="<?= (int)$s['id'] ?>">
        <label class="label w-16">ขั้น<input type="number" name="step_no" value="<?= (int)$s['step_no'] ?>" class="input"></label>
        <label class="label flex-[1_1_160px]"><?= $s['id'] ? 'ชื่อขั้นตอน' : 'เพิ่มขั้นตอน' ?><input type="text" name="title" value="<?= e($s['title']) ?>" class="input"></label>
        <label class="label flex-[2_1_240px]">รายละเอียด<input type="text" name="detail" value="<?= e($s['detail']) ?>" class="input"></label>
        <label class="label flex-[1_1_120px]">กรอบเวลา<input type="text" name="timeframe" value="<?= e($s['timeframe']) ?>" class="input"></label>
        <label class="label flex-[1_1_160px]">ผู้รับผิดชอบ<input type="text" name="owner" value="<?= e($s['owner']) ?>" class="input"></label>
        <button class="btn-sm btn-outline min-h-[46px]">บันทึก</button>
        <?php if ($s['id']): ?></form><form method="post" class="flex items-end"><?= csrf_field() ?><input type="hidden" name="action" value="delete_step"><input type="hidden" name="id" value="<?= (int)$s['id'] ?>"><button data-confirm="ลบขั้นตอนนี้?" class="btn-sm btn-danger min-h-[46px]">ลบ</button><?php endif; ?>
      </form>
    <?php endforeach; ?>
  </div>

<?php elseif ($one): $st = COMPLAINT_STATUS[$one['status']]; ?>
  <a href="<?= e(url('admin/complaints.php')) ?>" class="self-start text-ku font-semibold">← กลับรายการ</a>
  <div class="flex flex-wrap gap-4 items-start">
    <div class="flex-[1_1_320px] bg-white rounded-3xl p-6 flex flex-col gap-3">
      <div class="flex justify-between gap-2"><span class="font-display text-xl"><?= e($one['ticket_no']) ?></span><span class="chip" style="background:<?= e($st['bg']) ?>;color:<?= e($st['fg']) ?>"><?= e($st['label']) ?></span></div>
      <dl class="grid grid-cols-[110px_1fr] gap-x-3 gap-y-2 text-sm">
        <dt class="text-muted">ประเภท</dt><dd><?= e($one['type']) ?></dd>
        <dt class="text-muted">ด้าน</dt><dd><?= e($one['topic']) ?></dd>
        <dt class="text-muted">สถานที่</dt><dd><?= e($one['place'] ?: '-') ?></dd>
        <dt class="text-muted">ความพึงพอใจ</dt><dd><?= $one['rating'] ? (int)$one['rating'] . ' / 5' : '-' ?></dd>
        <dt class="text-muted">ผู้แจ้ง</dt><dd><?= e($one['name'] ?: 'ไม่ระบุ') ?></dd>
        <dt class="text-muted">ติดต่อกลับ</dt><dd><?= e($one['contact'] ?: '-') ?></dd>
        <dt class="text-muted">วันที่แจ้ง</dt><dd><?= e(thai_date($one['created_at'])) ?></dd>
      </dl>
      <div class="bg-[#F4F9F8] rounded-2xl p-4 whitespace-pre-line"><?= e($one['detail']) ?></div>
    </div>
    <form method="post" class="flex-[2_1_420px] bg-white rounded-3xl p-6 flex flex-col gap-3.5">
      <?= csrf_field() ?><input type="hidden" name="action" value="update"><input type="hidden" name="id" value="<?= (int)$one['id'] ?>">
      <label class="label">สถานะ<select name="status" class="input"><?php foreach (COMPLAINT_STATUS as $k => $s): ?><option value="<?= $k ?>" <?= $one['status'] === $k ? 'selected' : '' ?>><?= e($s['label']) ?></option><?php endforeach; ?></select></label>
      <label class="label">ผลการดำเนินงาน<textarea name="result_text" rows="3" class="input"><?= e($one['result_text']) ?></textarea></label>
      <fieldset class="border border-line rounded-2xl p-4 flex flex-col gap-3">
        <legend class="px-1.5 font-semibold text-sm">การดำเนินการเรื่องร้องเรียน (หน้าเว็บ)</legend>
        <label class="flex items-center gap-2"><input type="checkbox" name="is_public" <?= $one['is_public'] ? 'checked' : '' ?> class="w-5 h-5 accent-ku"> แสดงเรื่องนี้ในการดำเนินการเรื่องร้องเรียน</label>
        <label class="label">หัวข้อที่แสดง<input type="text" name="public_title" value="<?= e($one['public_title']) ?>" maxlength="255" class="input"></label>
        <label class="label">รายละเอียดที่แสดง (ไม่ใส่ชื่อหรือข้อมูลติดต่อผู้แจ้ง)<textarea name="public_detail" rows="3" class="input"><?= e($one['public_detail'] ?? $one['detail']) ?></textarea></label>
      </fieldset>
      <div class="flex gap-2.5"><button class="btn-primary">บันทึก</button></div>
    </form>
  </div>

<?php else: ?>
  <div class="bg-white rounded-3xl p-6 flex flex-col gap-3">
    <div class="flex gap-1.5 flex-wrap">
      <a href="<?= e(url('admin/complaints.php')) ?>" class="btn-sm btn border <?= $filter === '' ? 'bg-ku text-white border-ku' : 'border-[#DDE3D9]' ?>">ทั้งหมด</a>
      <?php foreach (COMPLAINT_STATUS as $k => $s): ?><a href="<?= e(url('admin/complaints.php?status=' . $k)) ?>" class="btn-sm btn border <?= $filter === $k ? 'bg-ku text-white border-ku' : 'border-[#DDE3D9]' ?>"><?= e($s['label']) ?></a><?php endforeach; ?>
    </div>
    <div class="overflow-x-auto">
      <table class="w-full min-w-[720px]">
        <thead><tr class="text-left text-sm text-muted"><th class="p-2.5">เลขที่</th><th class="p-2.5">ประเภท / ด้าน</th><th class="p-2.5">รายละเอียด</th><th class="p-2.5">วันที่</th><th class="p-2.5">สถานะ</th><th class="p-2.5">ประกาศ</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($list as $c): $st = COMPLAINT_STATUS[$c['status']]; ?>
          <tr class="border-t border-[#EDF0EA] hover:bg-[#F4F9F8] align-top">
            <td class="p-2.5 whitespace-nowrap"><?= e($c['ticket_no']) ?></td>
            <td class="p-2.5 text-sm"><?= e($c['type']) ?><br><span class="text-muted"><?= e($c['topic']) ?></span></td>
            <td class="p-2.5 text-sm"><?= e(mb_strimwidth($c['detail'], 0, 110, '…')) ?></td>
            <td class="p-2.5 text-sm text-muted whitespace-nowrap"><?= e(thai_date($c['created_at'])) ?></td>
            <td class="p-2.5"><span class="chip" style="background:<?= e($st['bg']) ?>;color:<?= e($st['fg']) ?>"><?= e($st['label']) ?></span></td>
            <td class="p-2.5 text-sm"><?= $c['is_public'] ? 'แสดง' : '-' ?></td>
            <td class="p-2.5 text-right whitespace-nowrap">
              <a href="<?= e(url('admin/complaints.php?id=' . $c['id'])) ?>" class="btn-sm btn-outline">จัดการ</a>
              <form method="post" class="inline"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$c['id'] ?>"><button data-confirm="ลบเรื่องนี้?" class="btn-sm btn-danger">ลบ</button></form>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$list): ?><tr><td colspan="7" class="p-4 text-muted">ไม่มีเรื่องในสถานะนี้</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php endif; ?>
<?php require __DIR__ . '/../../app/views/admin_bottom.php'; ?>
