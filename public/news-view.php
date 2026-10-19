<?php
require __DIR__ . '/../app/bootstrap.php';

$n = row("SELECT * FROM news WHERE id = ? AND status = 'published'", [(int)($_GET['id'] ?? 0)]);
if (!$n) {
    http_response_code(404);
}
$pageTitle = $n['title'] ?? 'ไม่พบข่าว';
$active = 'news';
require __DIR__ . '/../app/views/public_top.php';
?>
<article class="max-w-3xl mx-auto px-4 sm:px-6 pt-10 pb-16 flex flex-col gap-5">
  <a href="<?= e(url('news.php')) ?>" class="self-start min-h-[44px] inline-flex items-center font-semibold text-ku">← ข่าวสารและกิจกรรม</a>
  <?php if (!$n): ?>
    <div class="card p-8 text-muted">ไม่พบข่าวนี้ หรือข่าวยังไม่เผยแพร่</div>
  <?php else: ?>
    <span class="chip self-start bg-mint text-ku"><?= e($n['category']) ?></span>
    <h1 class="font-display font-semibold text-[clamp(28px,4vw,44px)] leading-tight"><?= e($n['title']) ?></h1>
    <div class="text-sm text-muted"><?= e(thai_date($n['published_at'])) ?></div>
    <?php if ($n['image_path']): ?><img src="<?= e(url($n['image_path'])) ?>" alt="" class="w-full rounded-3xl"><?php endif; ?>
    <div class="text-[17px] leading-loose whitespace-pre-line"><?= e($n['body']) ?></div>
  <?php endif; ?>
</article>
<?php require __DIR__ . '/../app/views/public_bottom.php'; ?>
