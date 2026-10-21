<?php
/**
 * Page-number menu: ‹ 1 … 4 5 6 … 12 ›
 * Expects $page (current, 1-based), $pages (total) and $pageUrl (fn(int $n): string).
 */
if ($pages <= 1) {
    return;
}
// Always show first, last and two around the current page; gaps become "…".
$nums = array_unique(array_filter([1, $page - 2, $page - 1, $page, $page + 1, $page + 2, $pages], fn($n) => $n >= 1 && $n <= $pages));
sort($nums);
$arrow = 'w-11 h-11 rounded-full inline-flex items-center justify-center text-xl font-semibold transition';
?>
<nav aria-label="เลขหน้า" class="flex justify-center items-center gap-1.5 flex-wrap pt-2">
  <?php if ($page > 1): ?>
    <a href="<?= e($pageUrl($page - 1)) ?>" class="<?= $arrow ?> bg-white text-ku border-2 border-ku hover:bg-mint" aria-label="หน้าก่อนหน้า" rel="prev">‹</a>
  <?php else: ?>
    <span class="<?= $arrow ?> bg-white text-[#C3CCC6] border-2 border-line" aria-hidden="true">‹</span>
  <?php endif; ?>
  <?php $prev = 0; foreach ($nums as $n): ?>
    <?php if ($n - $prev > 1): ?><span class="w-8 text-center text-muted" aria-hidden="true">…</span><?php endif; ?>
    <?php if ($n === $page): ?>
      <span class="w-11 h-11 rounded-full inline-flex items-center justify-center font-semibold bg-ku text-white shadow-lg shadow-ku/30" aria-current="page"><?= $n ?></span>
    <?php else: ?>
      <a href="<?= e($pageUrl($n)) ?>" class="w-11 h-11 rounded-full inline-flex items-center justify-center font-semibold bg-white text-ku hover:bg-mint transition hover:-translate-y-0.5" aria-label="หน้า <?= $n ?>"><?= $n ?></a>
    <?php endif; ?>
  <?php $prev = $n; endforeach; ?>
  <?php if ($page < $pages): ?>
    <a href="<?= e($pageUrl($page + 1)) ?>" class="<?= $arrow ?> bg-white text-ku border-2 border-ku hover:bg-mint" aria-label="หน้าถัดไป" rel="next">›</a>
  <?php else: ?>
    <span class="<?= $arrow ?> bg-white text-[#C3CCC6] border-2 border-line" aria-hidden="true">›</span>
  <?php endif; ?>
</nav>
<p class="text-center text-sm text-muted">หน้า <?= $page ?> จาก <?= $pages ?></p>
