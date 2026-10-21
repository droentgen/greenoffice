<?php
/** Grid of news cards. Expects $news (array of rows). */
$tagStyle = ['กิจกรรม' => ['#E2F1DC', '#2F6B1F', '#CFE7C2'], 'ข่าวสาร' => ['#DCEEF0', '#00605E', '#BFE0DD'], 'ประกาศ' => ['#FFF0C9', '#7A5000', '#FFE29A'], 'อบรม/สัมมนา' => ['#ECE6F7', '#553C8F', '#DCD0F0']];
?>
<?php if (!$news): ?>
  <div class="card p-6 text-muted">ยังไม่มีข่าว</div>
<?php else: ?>
<div class="grid gap-4 [grid-template-columns:repeat(auto-fit,minmax(250px,1fr))]">
  <?php foreach ($news as $n): $ts = $tagStyle[$n['category']] ?? $tagStyle['ข่าวสาร']; ?>
    <a href="<?= e(url('news-view.php?id=' . $n['id'])) ?>" class="hover-lift bg-white rounded-3xl overflow-hidden flex flex-col text-ink no-underline">
      <?php if ($n['image_path']): ?>
        <img src="<?= e(upload_url($n['image_path'])) ?>" alt="" class="h-40 w-full object-cover">
      <?php else: ?>
        <div class="h-40 flex items-center justify-center" style="background:<?= e($ts[2]) ?>;color:<?= e($ts[1]) ?>"><span class="ico-bg"><?= leaf_icon('w-14 h-14') ?></span></div>
      <?php endif; ?>
      <div class="p-5 flex flex-col gap-2 flex-1">
        <span class="chip self-start" style="background:<?= e($ts[0]) ?>;color:<?= e($ts[1]) ?>"><?= e($n['category']) ?></span>
        <h3 class="text-[17px] font-semibold leading-snug"><?= e($n['title']) ?></h3>
        <p class="text-sm text-muted"><?= e($n['summary']) ?></p>
        <span class="mt-auto pt-1 text-xs text-muted"><?= e(thai_date($n['published_at'])) ?></span>
      </div>
    </a>
  <?php endforeach; ?>
</div>
<?php endif; ?>
