<?php
/** Year chips. Expects $years, $year and $yearLinkBase (page URL that may already contain ?query). */
$sep = str_contains($yearLinkBase, '?') ? '&' : '?';
?>
<nav aria-label="เลือกปีงบประมาณ" class="self-start flex gap-1 flex-wrap items-center bg-white p-1.5 rounded-full border border-line">
  <span class="px-3 text-sm text-muted">ปีงบประมาณ</span>
  <?php foreach ($years as $y): ?>
    <a href="<?= e(url($yearLinkBase . $sep . 'year=' . $y)) ?>" class="min-h-[44px] inline-flex items-center px-4 rounded-full font-semibold <?= $y === $year ? 'bg-ku text-white' : 'text-ink hover:bg-mint' ?>"<?= $y === $year ? ' aria-current="true"' : '' ?>><?= $y ?></a>
  <?php endforeach; ?>
</nav>
