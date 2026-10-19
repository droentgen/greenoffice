<?php
/** Year chips. Expects $years, $year and $yearLinkBase (page URL that may already contain ?query). */
$sep = str_contains($yearLinkBase, '?') ? '&' : '?';
?>
<nav aria-label="เลือกปี พ.ศ." class="self-start flex gap-1 flex-wrap items-center bg-white p-1.5 rounded-full border border-line">
  <span class="px-3 text-sm text-muted inline-flex items-center gap-1.5"><svg aria-hidden="true" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>ปี พ.ศ.</span>
  <?php foreach ($years as $y): ?>
    <a href="<?= e(url($yearLinkBase . $sep . 'year=' . $y)) ?>" class="min-h-[44px] inline-flex items-center px-4 rounded-full font-semibold <?= $y === $year ? 'bg-ku text-white' : 'text-ink hover:bg-mint' ?>"<?= $y === $year ? ' aria-current="true"' : '' ?>><?= $y ?></a>
  <?php endforeach; ?>
</nav>
