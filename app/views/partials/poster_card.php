<?php /** One pinned poster (<li>) on a .poster-board grid. Expects $p (posters row). Opens in the lightbox (poster_lightbox.php). */ ?>
<?php $src = upload_url($p['image_path']); ?>
<li data-poster-item data-name="<?= e(mb_strtolower($p['title'])) ?>">
  <button type="button" class="poster" data-poster data-src="<?= e($src) ?>" data-title="<?= e($p['title']) ?>" data-date="<?= e(thai_date($p['posted_at'])) ?>" aria-label="ดูโปสเตอร์ขนาดใหญ่: <?= e($p['title']) ?>">
    <span class="poster-pin" aria-hidden="true"></span>
    <span class="poster-paper">
      <span class="relative block overflow-hidden rounded-md">
        <img src="<?= e($src) ?>" alt="" loading="lazy" class="w-full aspect-[3/4] object-cover bg-cream">
        <span class="poster-zoom" aria-hidden="true"><svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5M11 8v6M8 11h6"/></svg></span>
      </span>
      <span class="block mt-2 text-sm font-semibold leading-snug text-ink line-clamp-2"><?= e($p['title']) ?></span>
      <?php if ($p['posted_at']): ?><span class="block text-xs text-muted mt-0.5"><?= e(thai_date($p['posted_at'])) ?></span><?php endif; ?>
    </span>
  </button>
</li>
