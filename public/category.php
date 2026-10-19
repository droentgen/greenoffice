<?php
require __DIR__ . '/../app/bootstrap.php';

$n = max(1, min(7, (int)($_GET['n'] ?? 1)));
$years = report_years();
$year = in_array((int)($_GET['year'] ?? 0), $years, true) ? (int)$_GET['year'] : $years[0];
$cat = row('SELECT * FROM categories WHERE id = ?', [$n]);
$table = category_table($n, $year);
$p = category_progress($year)[$n] ?? ['total' => 0, 'done' => 0, 'full' => 0];
$pct = $p['total'] ? round($p['done'] / $p['total'] * 100) : 0;

$pageTitle = 'หมวดที่ ' . $n;
$active = 'ops';
require __DIR__ . '/../app/views/public_top.php';
?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 pt-8 pb-16 flex flex-col gap-6">
  <nav aria-label="ตำแหน่งหน้า" class="text-sm text-muted flex gap-2 items-center flex-wrap">
    <a href="<?= e(url('operations.php?year=' . $year)) ?>" class="min-h-[44px] inline-flex items-center font-semibold text-ku">← การดำเนินงาน</a><span>/ หมวดที่ <?= $n ?></span>
  </nav>
  <div class="flex flex-wrap gap-5 justify-between items-end">
    <div class="flex gap-4 items-center flex-[1_1_520px]">
      <span class="anim-bob w-[84px] h-[84px] rounded-[26px] flex flex-col items-center justify-center font-display leading-none flex-none" style="background:<?= e($cat['color_bg']) ?>;color:<?= e($cat['color_fg']) ?>"><span class="text-[13px]">หมวด</span><span class="text-[38px]"><?= $n ?></span></span>
      <div>
        <h1 class="font-display font-semibold text-[clamp(26px,3.4vw,40px)] leading-tight"><?= e($cat['name']) ?></h1>
        <p class="text-[#3E5553] mt-1.5"><?= e($cat['description']) ?></p>
      </div>
    </div>
    <?php $yearLinkBase = 'category.php?n=' . $n; include __DIR__ . '/../app/views/partials/year_filter.php'; ?>
  </div>

  <div class="grid gap-3 [grid-template-columns:repeat(auto-fit,minmax(170px,1fr))]">
    <div class="bg-white rounded-[20px] px-4.5 px-5 py-4"><div class="text-sm text-muted">ตัวชี้วัดทั้งหมด</div><div class="font-display text-3xl"><?= $p['total'] ?></div></div>
    <div class="bg-white rounded-[20px] px-5 py-4"><div class="text-sm text-muted">รายงานผลแล้ว (ปี <?= $year ?>)</div><div class="font-display text-3xl"><?= $p['done'] ?></div></div>
    <div class="bg-white rounded-[20px] px-5 py-4"><div class="text-sm text-muted">ครบพร้อมหลักฐาน</div><div class="font-display text-3xl text-[#1E6B45]"><?= $p['full'] ?></div></div>
    <div class="bg-ku text-white rounded-[20px] px-5 py-4"><div class="text-sm text-[#CFE8E4]">ความคืบหน้า</div><div class="font-display text-3xl"><span data-count="<?= $pct ?>">0</span>%</div></div>
  </div>

  <nav aria-label="ไปหมวดอื่น" class="flex gap-1.5 flex-wrap items-center">
    <span class="text-sm text-muted pr-1">ไปหมวด</span>
    <?php for ($i = 1; $i <= 7; $i++): ?>
      <a href="<?= e(url('category.php?n=' . $i . '&year=' . $year)) ?>" class="w-11 h-11 rounded-[14px] border-2 border-ku flex items-center justify-center font-display text-[17px] transition hover:-translate-y-0.5 <?= $i === $n ? 'bg-ku text-white' : 'bg-white text-ku' ?>"<?= $i === $n ? ' aria-current="page"' : '' ?>><?= $i ?></a>
    <?php endfor; ?>
  </nav>

  <!-- Table: ประเด็น | ตัวชี้วัด | รายงานผล | หลักฐาน -->
  <div class="card overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full min-w-[940px] border-collapse text-[15px]">
        <thead>
          <tr class="bg-ku text-white text-left">
            <th scope="col" class="px-4 py-3.5 w-[220px]">ประเด็น</th>
            <th scope="col" class="px-4 py-3.5">ตัวชี้วัด</th>
            <th scope="col" class="px-4 py-3.5 w-[28%]">รายงานผล</th>
            <th scope="col" class="px-4 py-3.5 w-[18%]">หลักฐาน</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($table as $iss): $span = max(1, count($iss['indicators'])); ?>
            <?php foreach ($iss['indicators'] as $k => $d): $st = $d['status']; ?>
              <tr class="border-t border-line align-top hover:bg-[#F8FBFA]">
                <?php if ($k === 0): ?>
                  <th scope="rowgroup" rowspan="<?= $span ?>" class="px-4 py-4 bg-[#F4F9F8] border-r border-line text-left font-normal align-top">
                    <div class="font-display text-ku text-[19px]"><?= e($iss['code']) ?></div>
                    <div class="font-semibold leading-snug"><?= e($iss['title']) ?></div>
                  </th>
                <?php endif; ?>
                <td class="px-4 py-3.5 border-r border-[#EDF0EA]">
                  <div class="font-semibold leading-relaxed"><span class="text-ku"><?= e($d['code']) ?></span> <?= e($d['title']) ?></div>
                  <?php if ($d['subs']): ?>
                    <ul class="mt-2 flex flex-col gap-1">
                      <?php foreach ($d['subs'] as $s): $ok = in_array((int)$s['idx'], $d['checks'], true); ?>
                        <li class="flex gap-2 text-sm leading-relaxed text-[#3E5553]">
                          <span aria-hidden="true" class="flex-none w-[18px] h-[18px] mt-0.5 rounded-md text-white text-xs flex items-center justify-center <?= $ok ? 'bg-[#2E9E5B]' : 'bg-line' ?>"><?= $ok ? '✓' : '' ?></span>
                          <span><?= e($s['text']) ?><?= $ok ? '<span class="sr-only"> (ดำเนินการแล้ว)</span>' : '' ?></span>
                        </li>
                      <?php endforeach; ?>
                    </ul>
                  <?php endif; ?>
                </td>
                <td class="px-4 py-3.5 border-r border-[#EDF0EA] text-sm">
                  <span class="chip" style="background:<?= e($st['bg']) ?>;color:<?= e($st['fg']) ?>"><?= e($st['label']) ?></span>
                  <p class="mt-2 leading-relaxed whitespace-pre-line <?= $d['report'] ? '' : 'text-[#6B7C7A]' ?>"><?= e($d['report'] ?: 'ยังไม่มีการรายงานผลในปี ' . $year) ?></p>
                </td>
                <td class="px-4 py-3.5 text-sm">
                  <div class="flex flex-col gap-2">
                    <?php foreach ($d['evidence'] as $ev): ?>
                      <a href="<?= e($ev['url']) ?>" target="_blank" rel="noopener" class="flex gap-1.5 items-start break-words font-medium text-ku hover:underline"><span class="mt-0.5 flex-none"><?= clip_icon() ?></span><span><?= e($ev['label']) ?></span></a>
                    <?php endforeach; ?>
                    <?php if (!$d['evidence']): ?><span class="text-[#6B7C7A]">—</span><?php endif; ?>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php require __DIR__ . '/../app/views/public_bottom.php'; ?>
