<?php
/**
 * รายงานผล & หลักฐาน — enter results for each indicator of the 7 categories.
 * Two ways: as a table (whole category at once) or one indicator at a time.
 * Evidence is attached as links (e.g. Google Drive / SharePoint).
 */
require __DIR__ . '/../../app/bootstrap.php';
$u = require_login(['staff', 'auditor', 'editor']);
$pageTitle = 'รายงานผล & หลักฐาน 7 หมวด';
$active = 'reports';

$years = report_years();
$year = in_array((int)input('year'), $years, true) ? (int)input('year') : $years[0];
$cat = max(1, min(7, (int)input('cat', 1)));
$mode = input('mode') === 'item' ? 'item' : 'table';
$here = 'admin/reports.php?cat=' . $cat . '&year=' . $year . '&mode=' . $mode;

/** Insert or update one report. $checks = null keeps the existing tick boxes. */
function save_report(int $indId, int $year, string $text, ?array $checks, int $uid): void
{
    $existing = row('SELECT id, checks_json FROM criteria_reports WHERE indicator_id = ? AND year_be = ?', [$indId, $year]);
    $checksJson = $checks === null ? ($existing['checks_json'] ?? '[]') : json_encode(array_values(array_unique(array_map('intval', $checks))));
    if ($existing) {
        q('UPDATE criteria_reports SET report_text = ?, checks_json = ?, updated_by = ? WHERE id = ?', [$text, $checksJson, $uid, $existing['id']]);
    } elseif ($text !== '' || $checksJson !== '[]') {
        q('INSERT INTO criteria_reports (indicator_id, year_be, report_text, checks_json, updated_by) VALUES (?,?,?,?,?)', [$indId, $year, $text, $checksJson, $uid]);
    }
}

function add_evidence(int $indId, int $year, string $label, string $rawUrl, int $uid): bool
{
    $url = clean_url($rawUrl);
    if (!$url) {
        return false;
    }
    if ($label === '') {
        $n = (int)val('SELECT COUNT(*) FROM criteria_evidence WHERE indicator_id = ? AND year_be = ?', [$indId, $year]) + 1;
        $label = 'หลักฐาน ' . $n . ' · ' . preg_replace('/^www\./', '', (string)parse_url($url, PHP_URL_HOST));
    }
    q('INSERT INTO criteria_evidence (indicator_id, year_be, label, url, created_by) VALUES (?,?,?,?,?)', [$indId, $year, mb_substr($label, 0, 255), $url, $uid]);
    return true;
}

// Indicators that belong to this category (used to validate posted ids)
$catIndIds = array_map('intval', array_column(rows('SELECT d.id FROM criteria_indicators d JOIN criteria_issues i ON i.id = d.issue_id WHERE i.category_id = ?', [$cat]), 'id'));

if (is_post()) {
    csrf_check();
    $bad = 0;
    if (($_POST['action'] ?? '') === 'save_table') {
        foreach ((array)($_POST['report'] ?? []) as $id => $text) {
            if (in_array((int)$id, $catIndIds, true)) {
                save_report((int)$id, $year, trim((string)$text), null, $u['id']);
            }
        }
        foreach ((array)($_POST['new_url'] ?? []) as $id => $link) {
            if (trim((string)$link) !== '' && in_array((int)$id, $catIndIds, true)) {
                add_evidence((int)$id, $year, '', (string)$link, $u['id']) || $bad++;
            }
        }
    } elseif (($_POST['action'] ?? '') === 'save_item') {
        $id = (int)($_POST['indicator_id'] ?? 0);
        if (in_array($id, $catIndIds, true)) {
            save_report($id, $year, trim((string)($_POST['report'] ?? '')), (array)($_POST['checks'] ?? []), $u['id']);
            if (trim((string)($_POST['new_url'] ?? '')) !== '') {
                add_evidence($id, $year, trim((string)($_POST['new_label'] ?? '')), (string)$_POST['new_url'], $u['id']) || $bad++;
            }
        }
        $here .= '&ind=' . $id;
    }
    if (!empty($_POST['delete_link'])) {
        q('DELETE FROM criteria_evidence WHERE id = ? AND year_be = ?', [(int)$_POST['delete_link'], $year]);
    }
    flash($bad ? 'บันทึกแล้ว แต่มีลิงก์ ' . $bad . ' รายการไม่ถูกต้อง (ต้องขึ้นต้นด้วย http:// หรือ https://)' : 'บันทึกรายงานผลแล้ว — แสดงบนหน้า “การดำเนินงาน” ทันที', $bad ? 'error' : 'success');
    redirect($here . (isset($_POST['anchor']) ? '#' . preg_replace('/[^a-z0-9-]/', '', (string)$_POST['anchor']) : ''));
}

$catRow = row('SELECT * FROM categories WHERE id = ?', [$cat]);
$table = category_table($cat, $year);
$prog = category_progress($year);
$flat = [];
foreach ($table as $iss) {
    foreach ($iss['indicators'] as $d) {
        $d['issue_code'] = $iss['code'];
        $d['issue_title'] = $iss['title'];
        $flat[] = $d;
    }
}
$selId = (int)input('ind');
$sel = null;
foreach ($flat as $k => $d) {
    if ($d['id'] == $selId) {
        $sel = $d;
        $selIdx = $k;
    }
}
if (!$sel && $flat) {
    $sel = $flat[0];
    $selIdx = 0;
}

require __DIR__ . '/../../app/views/admin_top.php';
?>
<div class="bg-white rounded-3xl px-5 py-4 flex flex-col gap-3.5">
  <div class="flex justify-between items-center gap-3 flex-wrap">
    <div class="flex gap-1.5 flex-wrap items-center">
      <span class="text-sm text-muted pr-1">ปีงบประมาณ</span>
      <?php foreach ($years as $y): ?>
        <a href="<?= e(url('admin/reports.php?cat=' . $cat . '&mode=' . $mode . '&year=' . $y)) ?>" class="btn-sm btn border <?= $y === $year ? 'bg-ku text-white border-ku' : 'bg-white border-[#DDE3D9]' ?>"><?= $y ?></a>
      <?php endforeach; ?>
    </div>
    <div class="flex gap-1 bg-[#EDF0EA] p-1 rounded-full">
      <?php foreach (['table' => 'กรอกแบบตาราง', 'item' => 'กรอกทีละข้อ'] as $m => $label): ?>
        <a href="<?= e(url('admin/reports.php?cat=' . $cat . '&year=' . $year . '&mode=' . $m)) ?>" class="btn-sm btn <?= $m === $mode ? 'bg-white text-ku' : 'text-[#3E5553]' ?>"><?= e($label) ?></a>
      <?php endforeach; ?>
    </div>
  </div>
  <div class="grid gap-2 [grid-template-columns:repeat(auto-fit,minmax(118px,1fr))]">
    <?php for ($i = 1; $i <= 7; $i++): $p = $prog[$i] ?? ['total' => 0, 'done' => 0]; $on = $i === $cat; ?>
      <a href="<?= e(url('admin/reports.php?cat=' . $i . '&year=' . $year . '&mode=' . $mode)) ?>" class="min-h-[56px] px-3 py-2 rounded-2xl border flex flex-col leading-snug <?= $on ? 'bg-ku text-white border-ku' : 'bg-white border-[#DDE3D9]' ?>">
        <span class="font-semibold">หมวด <?= $i ?></span><span class="text-xs <?= $on ? 'text-[#CFE8E4]' : 'text-muted' ?>">รายงานแล้ว <?= $p['done'] ?>/<?= $p['total'] ?></span>
      </a>
    <?php endfor; ?>
  </div>
</div>

<div class="flex justify-between items-center gap-3 flex-wrap">
  <h2 class="font-display font-medium text-[22px]">หมวดที่ <?= $cat ?> <?= e($catRow['name']) ?></h2>
  <a href="<?= e(url('category.php?n=' . $cat . '&year=' . $year)) ?>" target="_blank" class="text-sm text-ku font-semibold underline">ดูหน้าสาธารณะ ↗</a>
</div>

<?php if ($mode === 'table'): ?>
<form method="post" class="flex flex-col gap-4">
  <?= csrf_field() ?>
  <input type="hidden" name="action" value="save_table">
  <div class="bg-white rounded-3xl border border-line overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full min-w-[1040px] border-collapse text-sm">
        <thead><tr class="bg-ku-deep text-white text-left"><th class="px-3.5 py-3 w-[180px]">ประเด็น</th><th class="px-3.5 py-3">ตัวชี้วัด</th><th class="px-3.5 py-3 w-[30%]">รายงานผล</th><th class="px-3.5 py-3 w-[24%]">หลักฐาน (ลิงก์)</th></tr></thead>
        <tbody>
        <?php foreach ($table as $iss): foreach ($iss['indicators'] as $k => $d): ?>
          <tr id="i<?= $d['id'] ?>" class="border-t border-line align-top">
            <?php if ($k === 0): ?><th rowspan="<?= count($iss['indicators']) ?>" class="px-3.5 py-3.5 bg-[#F4F9F8] border-r border-line text-left font-normal"><div class="font-display text-ku text-[17px]"><?= e($iss['code']) ?></div><div class="font-semibold leading-snug"><?= e($iss['title']) ?></div></th><?php endif; ?>
            <td class="px-3.5 py-3 border-r border-[#EDF0EA] leading-relaxed">
              <span class="inline-block w-2.5 h-2.5 rounded-full mr-1.5" style="background:<?= e($d['status']['dot']) ?>" title="<?= e($d['status']['label']) ?>"></span><b class="text-ku"><?= e($d['code']) ?></b> <?= e($d['title']) ?>
              <a href="<?= e(url('admin/reports.php?cat=' . $cat . '&year=' . $year . '&mode=item&ind=' . $d['id'])) ?>" class="block mt-1 text-xs text-ku underline">ติ๊กรายการย่อย (<?= count($d['checks']) ?>/<?= count($d['subs']) ?>) →</a>
            </td>
            <td class="p-2.5 border-r border-[#EDF0EA]">
              <textarea name="report[<?= $d['id'] ?>]" rows="4" aria-label="รายงานผลข้อ <?= e($d['code']) ?>" placeholder="พิมพ์ผลการดำเนินงาน…" class="input text-sm leading-relaxed resize-y"><?= e($d['report']) ?></textarea>
            </td>
            <td class="p-2.5">
              <div class="flex flex-col gap-1.5">
                <?php foreach ($d['evidence'] as $ev): ?>
                  <span class="flex items-center gap-1 bg-mint rounded-xl pl-2.5 pr-0.5 py-0.5">
                    <a href="<?= e($ev['url']) ?>" target="_blank" rel="noopener" class="flex-1 min-w-0 truncate text-ku"><?= e($ev['label']) ?></a>
                    <button name="delete_link" value="<?= (int)$ev['id'] ?>" aria-label="ลบลิงก์ <?= e($ev['label']) ?>" data-confirm="ลบลิงก์หลักฐานนี้?" class="w-9 h-9 rounded-lg text-[#962B38] text-xl hover:bg-white">×</button>
                  </span>
                <?php endforeach; ?>
                <input type="url" name="new_url[<?= $d['id'] ?>]" placeholder="วางลิงก์ใหม่ เช่น Google Drive" aria-label="ลิงก์หลักฐานข้อ <?= e($d['code']) ?>" class="input text-sm min-h-[40px]">
              </div>
            </td>
          </tr>
        <?php endforeach; endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <div class="sticky bottom-3 flex justify-end">
    <button class="btn-sun shadow-xl font-display font-normal text-lg px-8 min-h-[54px]">บันทึกทั้งหมวด</button>
  </div>
</form>

<?php elseif ($sel): ?>
<div class="flex flex-wrap gap-4 items-start">
  <nav aria-label="ตัวชี้วัด" class="flex-[1_1_260px] bg-white rounded-3xl p-3 flex flex-col gap-1 max-h-[780px] overflow-y-auto">
    <?php foreach ($flat as $d): $on = $d['id'] == $sel['id']; ?>
      <a href="<?= e(url('admin/reports.php?cat=' . $cat . '&year=' . $year . '&mode=item&ind=' . $d['id'])) ?>" class="min-h-[48px] flex items-start gap-2.5 px-3 py-2.5 rounded-2xl border-2 text-sm leading-snug <?= $on ? 'border-ku bg-mint' : 'border-transparent hover:bg-[#F4F9F8]' ?>">
        <span class="flex-none w-2.5 h-2.5 rounded-full mt-1.5" style="background:<?= e($d['status']['dot']) ?>"></span>
        <span><b class="text-ku"><?= e($d['code']) ?></b> <?= e(mb_strimwidth($d['title'], 0, 80, '…')) ?></span>
      </a>
    <?php endforeach; ?>
  </nav>
  <form method="post" class="flex-[3_1_480px] min-w-0 bg-white rounded-3xl p-6 flex flex-col gap-5">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="save_item">
    <input type="hidden" name="indicator_id" value="<?= (int)$sel['id'] ?>">
    <div>
      <div class="text-sm text-muted">ประเด็น <?= e($sel['issue_code']) ?> <?= e($sel['issue_title']) ?></div>
      <h3 class="text-lg font-semibold leading-relaxed mt-1"><span class="text-ku"><?= e($sel['code']) ?></span> <?= e($sel['title']) ?></h3>
      <span class="chip mt-2" style="background:<?= e($sel['status']['bg']) ?>;color:<?= e($sel['status']['fg']) ?>"><?= e($sel['status']['label']) ?></span>
    </div>
    <?php if ($sel['subs']): ?>
      <fieldset class="border border-line rounded-2xl px-4 pb-3 pt-1">
        <legend class="px-1.5 font-semibold text-sm">รายการย่อยที่ดำเนินการแล้ว</legend>
        <?php foreach ($sel['subs'] as $s): ?>
          <label class="flex gap-2.5 items-start py-1.5 text-sm leading-relaxed cursor-pointer">
            <input type="checkbox" name="checks[]" value="<?= (int)$s['idx'] ?>" <?= in_array((int)$s['idx'], $sel['checks'], true) ? 'checked' : '' ?> class="w-5 h-5 mt-0.5 accent-ku flex-none"><span><?= e($s['text']) ?></span>
          </label>
        <?php endforeach; ?>
      </fieldset>
    <?php endif; ?>
    <label class="label">รายงานผลการดำเนินงาน
      <textarea name="report" rows="7" placeholder="อธิบายสิ่งที่ดำเนินการ ผลลัพธ์ และตัวเลขที่เกี่ยวข้อง…" class="input leading-relaxed"><?= e($sel['report']) ?></textarea>
    </label>
    <div class="flex flex-col gap-2">
      <span class="font-semibold text-sm">เอกสารหลักฐาน (ลิงก์)</span>
      <?php foreach ($sel['evidence'] as $ev): ?>
        <span class="flex items-center gap-2 bg-mint rounded-xl pl-3 pr-1 py-1">
          <span class="text-ku"><?= clip_icon() ?></span>
          <a href="<?= e($ev['url']) ?>" target="_blank" rel="noopener" class="flex-1 min-w-0 truncate text-ku"><?= e($ev['label']) ?></a>
          <button name="delete_link" value="<?= (int)$ev['id'] ?>" aria-label="ลบลิงก์ <?= e($ev['label']) ?>" data-confirm="ลบลิงก์หลักฐานนี้?" class="w-10 h-10 rounded-lg text-[#962B38] text-2xl hover:bg-white">×</button>
        </span>
      <?php endforeach; ?>
      <div class="flex gap-2 flex-wrap">
        <input type="text" name="new_label" placeholder="ชื่อเอกสาร (ไม่บังคับ)" aria-label="ชื่อเอกสารหลักฐาน" class="input flex-[1_1_180px] w-auto">
        <input type="url" name="new_url" placeholder="https://drive.google.com/…" aria-label="ลิงก์เอกสารหลักฐาน" class="input flex-[2_1_240px] w-auto">
      </div>
    </div>
    <div class="flex gap-2.5 flex-wrap border-t border-[#EDF0EA] pt-4">
      <button class="btn-sun font-display font-normal">บันทึกข้อนี้</button>
      <?php $next = $flat[($selIdx + 1) % count($flat)]; ?>
      <a href="<?= e(url('admin/reports.php?cat=' . $cat . '&year=' . $year . '&mode=item&ind=' . $next['id'])) ?>" class="btn-outline">ข้อถัดไป (<?= e($next['code']) ?>) →</a>
    </div>
  </form>
</div>
<?php endif; ?>
<?php require __DIR__ . '/../../app/views/admin_bottom.php'; ?>
