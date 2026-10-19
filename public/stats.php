<?php
require __DIR__ . '/../app/bootstrap.php';

$pageTitle = 'สถิติการใช้ทรัพยากร';
$active = 'stats';

$meta = resource_meta();
$key = array_key_exists($_GET['r'] ?? '', $meta) ? $_GET['r'] : 'fuel';
$m = $meta[$key];
[$cur, $prev] = latest_two_months();

$years = report_years();
$year = in_array((int)($_GET['year'] ?? 0), $years, true) ? (int)$_GET['year'] : ($cur ? (int)$cur['year_be'] : $years[0]);
$months = months_of_year($year);

$values = array_map(fn($row) => $row ? resource_value($row, $key) : null, $months);
$withData = array_filter($values, fn($v) => $v !== null);
$max = $withData ? max($withData) : 0;
$sum = array_sum($withData);
$avg = $withData ? $sum / count($withData) : 0;
$minIdx = null;
if ($withData) {
    $minVal = min($withData);
    foreach ($values as $i => $v) {
        if ($v === $minVal) { $minIdx = $i; break; }
    }
}
$curVal = $cur ? resource_value($cur, $key) : 0;
[$badge, $bbg, $bfg] = ($cur && $prev) ? change_badge($curVal, resource_value($prev, $key)) : ['—', '#EDF0EA', '#4A5F5D'];

require __DIR__ . '/../app/views/public_top.php';
?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 pt-14 pb-16 flex flex-col gap-7">
  <div>
    <span class="eyebrow">สถิติการใช้ทรัพยากร · ม.ค.-ธ.ค. ปี <?= $year ?></span>
    <h1 class="h-page mt-1.5">ตัวเลขไม่โกหก — ดูกันชัด ๆ รายเดือน</h1>
  </div>
  <div class="flex flex-wrap gap-3 items-center justify-between">
    <nav aria-label="ประเภททรัพยากร" class="flex gap-2 flex-wrap">
      <?php $no = 0; foreach ($meta as $k => $mm): $no++; ?>
        <a href="<?= e(url('stats.php?r=' . $k . '&year=' . $year)) ?>" class="btn border-2 <?= $k === $key ? 'bg-ku border-ku text-white' : 'bg-white border-[#DDE3D9] text-ink' ?>"<?= $k === $key ? ' aria-current="page"' : '' ?>>0<?= $no ?> <?= e($mm['name']) ?></a>
      <?php endforeach; ?>
    </nav>
    <?php $yearLinkBase = 'stats.php?r=' . $key; include __DIR__ . '/../app/views/partials/year_filter.php'; ?>
  </div>
  <?php if (!$withData): ?>
    <div class="card p-6 text-muted">ยังไม่มีข้อมูลสถิติในปี <?= $year ?></div>
  <?php else: ?>
  <div class="flex flex-wrap gap-5">
    <div class="flex-[3_1_560px] min-w-0 card p-7">
      <div class="flex justify-between flex-wrap gap-3 items-end">
        <div>
          <div class="text-muted"><?= e($m['name']) ?> · <?= e(month_label($cur)) ?></div>
          <div class="flex items-baseline gap-2"><span class="font-display font-semibold text-[52px] leading-tight" style="color:<?= e($m['ac']) ?>" data-count="<?= round($curVal, $m['dec']) ?>" data-dec="<?= $m['dec'] ?>">0</span><span class="text-[#3E5553]"><?= e($m['unit']) ?></span></div>
        </div>
        <span class="chip text-sm px-3 py-1.5" style="background:<?= e($bbg) ?>;color:<?= e($bfg) ?>"><?= e($badge) ?> เทียบเดือนก่อน</span>
      </div>
      <div class="overflow-x-auto mt-5">
        <div class="min-w-[560px] h-[260px] flex items-end gap-2.5 border-b-2 border-line pt-6" role="img" aria-label="กราฟแท่ง<?= e($m['name']) ?>รายเดือน ม.ค.-ธ.ค. <?= $year ?>">
          <?php foreach ($months as $i => $row): $v = $values[$i]; $monthNo = $i + 1; $isCur = $row && $cur && (int)$cur['year_be'] === $year && (int)$cur['month'] === $monthNo; ?>
            <div class="flex-1 h-full flex flex-col justify-end items-center gap-1.5" title="<?= e(THAI_MONTHS_SHORT[$monthNo] . ': ' . ($v !== null ? num($v, $m['dec']) . ' ' . $m['unit'] : 'ยังไม่มีข้อมูล')) ?>">
              <?php if ($isCur): ?><span class="text-[11px] text-[#3E5553] whitespace-nowrap"><?= num($v, $m['dec']) ?></span><?php endif; ?>
              <div class="grow-h w-full rounded-t-[10px] rounded-b" data-h="<?= ($max && $v !== null) ? round($v / $max * 92, 1) : 0 ?>" style="background:<?= $isCur ? e($m['ac']) : '#CFE0DC' ?>"></div>
            </div>
          <?php endforeach; ?>
        </div>
        <div class="min-w-[560px] flex gap-2.5 pt-2">
          <?php foreach ($months as $i => $row): ?><span class="flex-1 text-center text-xs text-muted"><?= e(THAI_MONTHS_SHORT[$i + 1]) ?></span><?php endforeach; ?>
        </div>
      </div>
    </div>
    <div class="flex-[1_1_260px] flex flex-col gap-3.5">
      <div class="hover-lift rounded-[22px] p-5" style="background:<?= e($m['bg']) ?>">
        <div class="text-sm text-[#3E5553]">เฉลี่ยต่อเดือน</div>
        <div class="font-display text-[26px]"><span data-count="<?= round($avg, $m['dec']) ?>" data-dec="<?= $m['dec'] ?>">0</span> <?= e($m['unit']) ?></div>
      </div>
      <div class="hover-lift rounded-[22px] p-5" style="background:<?= e($m['bg']) ?>">
        <div class="text-sm text-[#3E5553]">เดือนที่ใช้น้อยที่สุด</div>
        <div class="font-display text-[26px]"><?php if ($minIdx !== null): ?><?= e(month_label($months[$minIdx], true)) ?> · <span data-count="<?= round($values[$minIdx], $m['dec']) ?>" data-dec="<?= $m['dec'] ?>">0</span> <?= e($m['unit']) ?><?php else: ?>-<?php endif; ?></div>
      </div>
      <div class="hover-lift rounded-[22px] p-5" style="background:<?= e($m['bg']) ?>">
        <div class="text-sm text-[#3E5553]">รวม <?= count($withData) ?> เดือน</div>
        <div class="font-display text-[26px]"><span data-count="<?= round($sum, $m['dec']) ?>" data-dec="<?= $m['dec'] ?>">0</span> <?= e($m['unit']) ?></div>
      </div>
      <div class="bg-ink text-white rounded-[22px] p-5"><div class="font-display text-lime">ทิปจากน้องกล้า</div><p class="mt-1.5 text-mint"><?= e($m['tip']) ?></p></div>
    </div>
  </div>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/../app/views/public_bottom.php'; ?>
