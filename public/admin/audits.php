<?php
/** ตรวจประเมินและติดตามแก้ไข — internal audit findings and corrective actions. */
require __DIR__ . '/../../app/bootstrap.php';
$u = require_login(['auditor']);
$pageTitle = 'ตรวจประเมินและติดตามแก้ไข';
$active = 'audits';
$statuses = ['open' => ['รอแก้ไข', '#FDE2DD', '#7A2A1C'], 'fixing' => ['กำลังแก้ไข', '#FFF2C2', '#6B4E00'], 'closed' => ['ปิดแล้ว', '#DDF2E6', '#1E6B45']];

if (is_post()) {
    csrf_check();
    $id = (int)($_POST['id'] ?? 0);
    if (($_POST['action'] ?? '') === 'delete') {
        q('DELETE FROM audit_findings WHERE id = ?', [$id]);
        flash('ลบแล้ว');
    } else {
        $vals = [max(1, min(7, (int)$_POST['category_no'])), trim((string)$_POST['finding']), trim((string)$_POST['action_text']),
            mb_substr(trim((string)$_POST['owner']), 0, 150), $_POST['due_date'] ?: null,
            array_key_exists($_POST['status'] ?? '', $statuses) ? $_POST['status'] : 'open'];
        if ($vals[1] !== '') {
            $id ? q('UPDATE audit_findings SET category_no = ?, finding = ?, action = ?, owner = ?, due_date = ?, status = ? WHERE id = ?', [...$vals, $id])
                : q('INSERT INTO audit_findings (category_no, finding, action, owner, due_date, status) VALUES (?,?,?,?,?,?)', $vals);
            flash('บันทึกแล้ว');
        }
    }
    redirect('admin/audits.php');
}

$list = rows("SELECT * FROM audit_findings ORDER BY FIELD(status,'open','fixing','closed'), due_date IS NULL, due_date, id");
$counts = array_count_values(array_column($list, 'status'));
$edit = isset($_GET['edit']) ? row('SELECT * FROM audit_findings WHERE id = ?', [(int)$_GET['edit']]) : null;
$f = $edit ?? ['id' => 0, 'category_no' => 1, 'finding' => '', 'action' => '', 'owner' => '', 'due_date' => '', 'status' => 'open'];

require __DIR__ . '/../../app/views/admin_top.php';
?>
<div class="grid gap-3 [grid-template-columns:repeat(auto-fit,minmax(180px,1fr))]">
  <?php foreach ($statuses as $k => [$l, $bg, $fg]): ?>
    <div class="rounded-[20px] p-4" style="background:<?= $bg ?>;color:<?= $fg ?>"><div class="text-sm"><?= e($l) ?></div><div class="font-display text-[34px]"><?= (int)($counts[$k] ?? 0) ?></div></div>
  <?php endforeach; ?>
</div>
<form method="post" class="bg-white rounded-3xl p-5 grid gap-3 [grid-template-columns:repeat(auto-fit,minmax(200px,1fr))] items-end">
  <?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$f['id'] ?>">
  <label class="label">หมวด<select name="category_no" class="input"><?php for ($i = 1; $i <= 7; $i++): ?><option value="<?= $i ?>" <?= $f['category_no'] == $i ? 'selected' : '' ?>>หมวด <?= $i ?></option><?php endfor; ?></select></label>
  <label class="label">ข้อสังเกตจากการตรวจ<input type="text" name="finding" value="<?= e($f['finding']) ?>" required class="input"></label>
  <label class="label">แนวทางแก้ไข<input type="text" name="action_text" value="<?= e($f['action']) ?>" class="input"></label>
  <label class="label">ผู้รับผิดชอบ<input type="text" name="owner" value="<?= e($f['owner']) ?>" class="input"></label>
  <label class="label">กำหนด<input type="date" name="due_date" value="<?= e($f['due_date']) ?>" class="input"></label>
  <label class="label">สถานะ<select name="status" class="input"><?php foreach ($statuses as $k => [$l]): ?><option value="<?= $k ?>" <?= $f['status'] === $k ? 'selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?></select></label>
  <div class="flex gap-2"><button class="btn-primary"><?= $f['id'] ? 'บันทึกการแก้ไข' : '+ เพิ่มข้อสังเกต' ?></button><?php if ($f['id']): ?><a href="<?= e(url('admin/audits.php')) ?>" class="btn-outline">ยกเลิก</a><?php endif; ?></div>
</form>
<div class="bg-white rounded-3xl p-6 overflow-x-auto">
  <table class="w-full min-w-[760px]">
    <thead><tr class="text-left text-sm text-muted"><th class="p-2.5">หมวด</th><th class="p-2.5">ข้อสังเกต</th><th class="p-2.5">แนวทางแก้ไข</th><th class="p-2.5">ผู้รับผิดชอบ</th><th class="p-2.5">กำหนด</th><th class="p-2.5">สถานะ</th><th></th></tr></thead>
    <tbody>
      <?php foreach ($list as $r): [$l, $bg, $fg] = $statuses[$r['status']]; ?>
        <tr class="border-t border-[#EDF0EA] hover:bg-[#F4F9F8]">
          <td class="p-2.5 font-semibold whitespace-nowrap">หมวด <?= (int)$r['category_no'] ?></td><td class="p-2.5"><?= e($r['finding']) ?></td><td class="p-2.5 text-[#3E5553]"><?= e($r['action']) ?></td>
          <td class="p-2.5 text-sm"><?= e($r['owner']) ?></td><td class="p-2.5 text-sm text-muted whitespace-nowrap"><?= e(thai_date($r['due_date'])) ?></td>
          <td class="p-2.5"><span class="chip" style="background:<?= $bg ?>;color:<?= $fg ?>"><?= e($l) ?></span></td>
          <td class="p-2.5 text-right whitespace-nowrap"><a href="<?= e(url('admin/audits.php?edit=' . $r['id'])) ?>" class="btn-sm btn-outline">แก้ไข</a>
            <form method="post" class="inline"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><button data-confirm="ลบรายการนี้?" class="btn-sm btn-danger">ลบ</button></form></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php require __DIR__ . '/../../app/views/admin_bottom.php'; ?>
