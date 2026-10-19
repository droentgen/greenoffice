<?php
/**
 * จัดการข้อกำหนด 7 หมวด — edit/add/delete the criteria structure itself
 * (ประเด็น → ตัวชี้วัด → รายการย่อย), separate from รายงานผล (reports.php)
 * which fills in results against this structure for a given year.
 *
 * Deleting an issue or indicator cascades (FK ON DELETE CASCADE) and removes
 * every year's reports/evidence under it — callers must confirm first.
 */
require __DIR__ . '/../../app/bootstrap.php';
$u = require_login(['editor']);
$pageTitle = 'จัดการข้อกำหนด 7 หมวด';
$active = 'criteria';

$cat = max(1, min(7, (int)input('cat', 1)));
$here = 'admin/criteria.php?cat=' . $cat;

if (is_post()) {
    csrf_check();
    $action = $_POST['action'] ?? '';
    if ($action === 'update_category') {
        q('UPDATE categories SET name = ?, description = ? WHERE id = ?',
            [mb_substr(trim((string)$_POST['name']), 0, 255), mb_substr(trim((string)$_POST['description']), 0, 255), $cat]);
        flash('บันทึกชื่อหมวดแล้ว');
    } elseif ($action === 'add_issue') {
        $code = trim((string)($_POST['code'] ?? ''));
        $title = trim((string)($_POST['title'] ?? ''));
        if ($code === '' || $title === '') {
            flash('กรุณาใส่รหัสและข้อความประเด็น', 'error');
        } else {
            $sort = (int)(val('SELECT COALESCE(MAX(sort), -1) FROM criteria_issues WHERE category_id = ?', [$cat]) + 1);
            q('INSERT INTO criteria_issues (category_id, code, title, sort) VALUES (?,?,?,?)', [$cat, $code, $title, $sort]);
            flash('เพิ่มประเด็นแล้ว');
        }
    } elseif ($action === 'update_issue') {
        q('UPDATE criteria_issues SET code = ?, title = ?, sort = ? WHERE id = ? AND category_id = ?',
            [trim((string)$_POST['code']), trim((string)$_POST['title']), (int)$_POST['sort'], (int)$_POST['id'], $cat]);
        flash('บันทึกประเด็นแล้ว');
    } elseif ($action === 'delete_issue') {
        q('DELETE FROM criteria_issues WHERE id = ? AND category_id = ?', [(int)$_POST['id'], $cat]);
        flash('ลบประเด็นแล้ว (รวมตัวชี้วัดและรายงานผลทุกปีภายใต้ประเด็นนี้)');
    } elseif ($action === 'add_indicator') {
        $issueId = (int)($_POST['issue_id'] ?? 0);
        $code = trim((string)($_POST['code'] ?? ''));
        $title = trim((string)($_POST['title'] ?? ''));
        $ownsIssue = (int)val('SELECT COUNT(*) FROM criteria_issues WHERE id = ? AND category_id = ?', [$issueId, $cat]);
        if (!$ownsIssue || $code === '' || $title === '') {
            flash('กรุณาใส่รหัสและข้อความตัวชี้วัด', 'error');
        } else {
            $sort = (int)(val('SELECT COALESCE(MAX(sort), -1) FROM criteria_indicators WHERE issue_id = ?', [$issueId]) + 1);
            try {
                q('INSERT INTO criteria_indicators (issue_id, code, title, sort) VALUES (?,?,?,?)', [$issueId, $code, $title, $sort]);
                flash('เพิ่มตัวชี้วัดแล้ว');
            } catch (PDOException $ex) {
                flash('รหัสตัวชี้วัด "' . $code . '" ซ้ำกับที่มีอยู่แล้ว', 'error');
            }
        }
    } elseif ($action === 'update_indicator') {
        $id = (int)$_POST['id'];
        $owns = (int)val('SELECT COUNT(*) FROM criteria_indicators d JOIN criteria_issues i ON i.id = d.issue_id WHERE d.id = ? AND i.category_id = ?', [$id, $cat]);
        if ($owns) {
            try {
                q('UPDATE criteria_indicators SET code = ?, title = ?, sort = ? WHERE id = ?', [trim((string)$_POST['code']), trim((string)$_POST['title']), (int)$_POST['sort'], $id]);
                flash('บันทึกตัวชี้วัดแล้ว');
            } catch (PDOException $ex) {
                flash('รหัสตัวชี้วัดนี้ซ้ำกับที่มีอยู่แล้ว', 'error');
            }
        }
    } elseif ($action === 'delete_indicator') {
        $id = (int)$_POST['id'];
        $owns = (int)val('SELECT COUNT(*) FROM criteria_indicators d JOIN criteria_issues i ON i.id = d.issue_id WHERE d.id = ? AND i.category_id = ?', [$id, $cat]);
        if ($owns) {
            q('DELETE FROM criteria_indicators WHERE id = ?', [$id]);
            flash('ลบตัวชี้วัดแล้ว (รวมรายงานผล/หลักฐานทุกปีของข้อนี้)');
        }
    } elseif ($action === 'add_subitem') {
        $indId = (int)($_POST['indicator_id'] ?? 0);
        $text = trim((string)($_POST['text'] ?? ''));
        $owns = (int)val('SELECT COUNT(*) FROM criteria_indicators d JOIN criteria_issues i ON i.id = d.issue_id WHERE d.id = ? AND i.category_id = ?', [$indId, $cat]);
        if (!$owns || $text === '') {
            flash('กรุณาใส่ข้อความรายการย่อย', 'error');
        } else {
            $idx = (int)(val('SELECT COALESCE(MAX(idx), -1) FROM criteria_subitems WHERE indicator_id = ?', [$indId]) + 1);
            q('INSERT INTO criteria_subitems (indicator_id, idx, text) VALUES (?,?,?)', [$indId, $idx, $text]);
            flash('เพิ่มรายการย่อยแล้ว');
        }
    } elseif ($action === 'update_subitem') {
        q('UPDATE criteria_subitems SET text = ? WHERE id = ?', [trim((string)$_POST['text']), (int)$_POST['id']]);
        flash('บันทึกรายการย่อยแล้ว');
    } elseif ($action === 'delete_subitem') {
        q('DELETE FROM criteria_subitems WHERE id = ?', [(int)$_POST['id']]);
        flash('ลบรายการย่อยแล้ว');
    }
    redirect($here . (isset($_POST['issue_open']) ? '#iss' . (int)$_POST['issue_open'] : ''));
}

$catRow = row('SELECT * FROM categories WHERE id = ?', [$cat]);
$issues = rows('SELECT * FROM criteria_issues WHERE category_id = ? ORDER BY sort, id', [$cat]);
$issueIds = array_column($issues, 'id') ?: [0];
$inIss = implode(',', array_map('intval', $issueIds));
$indicators = rows("SELECT * FROM criteria_indicators WHERE issue_id IN ($inIss) ORDER BY sort, id");
$indIds = array_column($indicators, 'id') ?: [0];
$inInd = implode(',', array_map('intval', $indIds));
$subs = rows("SELECT * FROM criteria_subitems WHERE indicator_id IN ($inInd) ORDER BY idx");
$subsByInd = [];
foreach ($subs as $s) {
    $subsByInd[$s['indicator_id']][] = $s;
}
$indByIssue = [];
foreach ($indicators as $d) {
    $d['subs'] = $subsByInd[$d['id']] ?? [];
    $indByIssue[$d['issue_id']][] = $d;
}
foreach ($issues as &$iss) {
    $iss['indicators'] = $indByIssue[$iss['id']] ?? [];
}
unset($iss);

require __DIR__ . '/../../app/views/admin_top.php';
?>
<div class="bg-white rounded-3xl px-5 py-4 flex flex-col gap-3.5">
  <div class="grid gap-2 [grid-template-columns:repeat(auto-fit,minmax(118px,1fr))]">
    <?php for ($i = 1; $i <= 7; $i++): $on = $i === $cat; ?>
      <a href="<?= e(url('admin/criteria.php?cat=' . $i)) ?>" class="min-h-[48px] px-3 py-2 rounded-2xl border flex items-center justify-center font-semibold <?= $on ? 'bg-ku text-white border-ku' : 'bg-white border-[#DDE3D9]' ?>">หมวด <?= $i ?></a>
    <?php endfor; ?>
  </div>
</div>

<form method="post" class="bg-white rounded-3xl p-6 flex flex-col gap-3">
  <?= csrf_field() ?>
  <input type="hidden" name="action" value="update_category">
  <h2 class="font-display font-medium text-xl">หมวดที่ <?= $cat ?></h2>
  <label class="label">ชื่อหมวด<input type="text" name="name" value="<?= e($catRow['name']) ?>" class="input"></label>
  <label class="label">คำอธิบาย<input type="text" name="description" value="<?= e($catRow['description']) ?>" class="input"></label>
  <button class="btn-outline self-start">บันทึกชื่อหมวด</button>
</form>

<div class="flex flex-col gap-4">
  <?php foreach ($issues as $iss): ?>
    <div id="iss<?= (int)$iss['id'] ?>" class="bg-white rounded-3xl p-5 flex flex-col gap-3.5">
      <form method="post" class="flex flex-wrap gap-2.5 items-end bg-[#F4F9F8] rounded-2xl p-3.5">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="update_issue">
        <input type="hidden" name="id" value="<?= (int)$iss['id'] ?>">
        <label class="label w-24">รหัส<input type="text" name="code" value="<?= e($iss['code']) ?>" class="input"></label>
        <label class="label flex-[1_1_320px]">ข้อความประเด็น<textarea name="title" rows="2" class="input leading-relaxed"><?= e($iss['title']) ?></textarea></label>
        <label class="label w-20">ลำดับ<input type="number" name="sort" value="<?= (int)$iss['sort'] ?>" class="input"></label>
        <button class="btn-sm btn-outline">บันทึก</button>
        <button type="submit" formaction="<?= e(url('admin/criteria.php?cat=' . $cat)) ?>" name="action" value="delete_issue" data-confirm="ลบประเด็นนี้? จะลบตัวชี้วัดและรายงานผล/หลักฐานทุกปีภายใต้ประเด็นนี้ไปด้วย" class="btn-sm btn-danger">ลบประเด็นนี้</button>
      </form>

      <div class="flex flex-col gap-2.5 pl-3 border-l-2 border-line">
        <?php foreach ($iss['indicators'] as $d): ?>
          <div class="bg-[#FAFBF8] rounded-2xl p-3.5 flex flex-col gap-2.5">
            <form method="post" class="flex flex-wrap gap-2.5 items-end">
              <?= csrf_field() ?>
              <input type="hidden" name="action" value="update_indicator">
              <input type="hidden" name="id" value="<?= (int)$d['id'] ?>">
              <label class="label w-28">รหัส<input type="text" name="code" value="<?= e($d['code']) ?>" class="input"></label>
              <label class="label flex-[1_1_300px]">ข้อความตัวชี้วัด<textarea name="title" rows="2" class="input leading-relaxed"><?= e($d['title']) ?></textarea></label>
              <label class="label w-20">ลำดับ<input type="number" name="sort" value="<?= (int)$d['sort'] ?>" class="input"></label>
              <button class="btn-sm btn-outline">บันทึก</button>
              <button type="submit" formaction="<?= e(url('admin/criteria.php?cat=' . $cat)) ?>" name="action" value="delete_indicator" data-confirm="ลบตัวชี้วัดนี้? จะลบรายงานผล/หลักฐานทุกปีของข้อนี้ไปด้วย" class="btn-sm btn-danger">ลบ</button>
            </form>

            <div class="flex flex-col gap-1.5 pl-4 border-l border-line">
              <?php foreach ($d['subs'] as $s): ?>
                <form method="post" class="flex gap-2 items-center">
                  <?= csrf_field() ?>
                  <input type="hidden" name="action" value="update_subitem">
                  <input type="hidden" name="id" value="<?= (int)$s['id'] ?>">
                  <input type="text" name="text" value="<?= e($s['text']) ?>" class="input text-sm flex-1 min-w-[160px]">
                  <button class="btn-sm btn-outline">บันทึก</button>
                  <button type="submit" formaction="<?= e(url('admin/criteria.php?cat=' . $cat)) ?>" name="action" value="delete_subitem" data-confirm="ลบรายการย่อยนี้?" class="btn-sm btn-danger">ลบ</button>
                </form>
              <?php endforeach; ?>
              <form method="post" class="flex gap-2 items-center">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="add_subitem">
                <input type="hidden" name="indicator_id" value="<?= (int)$d['id'] ?>">
                <input type="text" name="text" placeholder="เพิ่มรายการย่อยใหม่…" class="input text-sm flex-1 min-w-[160px]">
                <button class="btn-sm btn bg-mint text-ku">+ เพิ่ม</button>
              </form>
              <p class="text-xs text-muted">หมายเหตุ: แก้ไข/ลบ/เพิ่มรายการย่อยอาจกระทบการติ๊กข้อย่อยที่รายงานไว้แล้วในปีก่อน ๆ</p>
            </div>
          </div>
        <?php endforeach; ?>

        <form method="post" class="flex flex-wrap gap-2.5 items-end">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="add_indicator">
          <input type="hidden" name="issue_id" value="<?= (int)$iss['id'] ?>">
          <label class="label w-28">รหัสใหม่<input type="text" name="code" placeholder="เช่น <?= e($iss['code']) ?>.<?= count($iss['indicators']) + 1 ?>" class="input"></label>
          <label class="label flex-[1_1_300px]">ข้อความตัวชี้วัดใหม่<input type="text" name="title" class="input"></label>
          <button class="btn-sm btn bg-mint text-ku">+ เพิ่มตัวชี้วัด</button>
        </form>
      </div>
    </div>
  <?php endforeach; ?>

  <form method="post" class="bg-white rounded-3xl p-5 flex flex-wrap gap-2.5 items-end">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="add_issue">
    <label class="label w-28">รหัสประเด็นใหม่<input type="text" name="code" placeholder="เช่น <?= $cat ?>.<?= count($issues) + 1 ?>" class="input"></label>
    <label class="label flex-[1_1_320px]">ข้อความประเด็นใหม่<input type="text" name="title" class="input"></label>
    <button class="btn-primary">+ เพิ่มประเด็นใหม่</button>
  </form>
</div>
<?php require __DIR__ . '/../../app/views/admin_bottom.php'; ?>
