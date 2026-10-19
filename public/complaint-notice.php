<?php
/** ประกาศข้อร้องเรียน — a single uploaded notice document; managed in admin › เอกสาร (หมวดเดียวกับหน้านี้). */
require __DIR__ . '/../app/bootstrap.php';

$pageTitle = 'ประกาศข้อร้องเรียน';
$active = '';
$doc = row("SELECT * FROM documents WHERE section = 'complaint_notice' AND is_published = 1 ORDER BY sort, id LIMIT 1");

require __DIR__ . '/../app/views/public_top.php';
?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 pt-12 pb-16 flex flex-col gap-6">
  <div class="flex justify-between items-end gap-4 flex-wrap">
    <div>
      <span class="eyebrow">ประกาศข้อร้องเรียน</span>
      <h1 class="h-page mt-1.5 mb-2"><?= e($doc['title'] ?? 'ประกาศข้อร้องเรียนด้านสิ่งแวดล้อม') ?></h1>
      <p class="text-[17px] text-[#3E5553] max-w-3xl">ประกาศเกี่ยวกับการจัดการข้อร้องเรียนด้านสิ่งแวดล้อมของคณะ</p>
    </div>
    <?php if ($doc): ?><a href="<?= e(doc_href($doc)) ?>" target="_blank" rel="noopener" class="btn-primary">เปิดเต็มจอ</a><?php endif; ?>
  </div>
  <?php if (!$doc): ?>
    <div class="card p-6 text-muted">ยังไม่มีประกาศข้อร้องเรียน</div>
  <?php elseif ($doc['file_type'] === 'pdf'): ?>
    <iframe src="<?= e(doc_href($doc)) ?>" title="<?= e($doc['title']) ?>" class="w-full h-[720px] rounded-3xl border border-line bg-[#E9ECE6]"></iframe>
  <?php elseif ($doc['file_type'] === 'image'): ?>
    <img src="<?= e(doc_href($doc)) ?>" alt="<?= e($doc['title']) ?>" class="w-full rounded-3xl">
  <?php else: ?>
    <div class="card p-8 text-center"><a href="<?= e(doc_href($doc)) ?>" target="_blank" rel="noopener" class="btn-primary">เปิดเอกสาร</a></div>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/../app/views/public_bottom.php'; ?>
