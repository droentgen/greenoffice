<?php
require __DIR__ . '/../app/bootstrap.php';

$pageTitle = 'ข่าวสารและกิจกรรม';
$active = 'news';

$cats = NEWS_CATS;
$cat = in_array($_GET['cat'] ?? '', $cats, true) ? $_GET['cat'] : '';
$news = $cat
    ? rows("SELECT * FROM news WHERE status = 'published' AND category = ? ORDER BY published_at DESC, id DESC", [$cat])
    : rows("SELECT * FROM news WHERE status = 'published' ORDER BY published_at DESC, id DESC");

require __DIR__ . '/../app/views/public_top.php';
?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 pt-14 pb-16 flex flex-col gap-6">
  <div>
    <span class="eyebrow">ข่าวสารและกิจกรรม</span>
    <h1 class="h-page mt-1.5">มีอะไรกรีน ๆ เกิดขึ้นในคณะบ้าง</h1>
  </div>
  <nav aria-label="ประเภทข่าว" class="flex gap-2 flex-wrap">
    <?php foreach (array_merge([''], $cats) as $c): ?>
      <a href="<?= e(url('news.php' . ($c ? '?cat=' . urlencode($c) : ''))) ?>" class="btn border-2 border-ku <?= $c === $cat ? 'bg-ku text-white' : 'bg-white text-ku hover:bg-mint' ?>"><?= e($c ?: 'ทั้งหมด') ?></a>
    <?php endforeach; ?>
  </nav>
  <?php include __DIR__ . '/../app/views/partials/news_cards.php'; ?>
</div>
<?php require __DIR__ . '/../app/views/public_bottom.php'; ?>
