<?php
/** แผนการจัดการข้อร้องเรียน — steps are edited in admin › ข้อร้องเรียน; the full PDF in admin › เอกสาร. */
require __DIR__ . '/../app/bootstrap.php';

$pageTitle = 'แผนการจัดการข้อร้องเรียน';
$active = '';
$steps = rows('SELECT * FROM complaint_plan_steps ORDER BY step_no');
$planDoc = row("SELECT * FROM documents WHERE section = 'complaint_plan' AND is_published = 1 ORDER BY sort, id LIMIT 1");
$colors = ['#E6F3F1', '#FFF2C2', '#DCF0FA', '#E2F1DC', '#FBE3E3', '#E8E4F7'];

require __DIR__ . '/../app/views/public_top.php';
?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 pt-12 pb-16 flex flex-col gap-6">
  <div class="flex justify-between items-end gap-4 flex-wrap">
    <div>
      <span class="eyebrow">แผนการจัดการข้อร้องเรียน</span>
      <h1 class="h-page mt-1.5 mb-2"><?= count($steps) ?> ขั้นตอน จากแจ้งเรื่อง ถึงแก้จบ</h1>
      <p class="text-[17px] text-[#3E5553] max-w-3xl">ขั้นตอนและกรอบเวลาการจัดการข้อร้องเรียนด้านสิ่งแวดล้อมของคณะ ตามเกณฑ์สำนักงานสีเขียว</p>
    </div>
    <?php if ($planDoc): ?><a href="<?= e(doc_href($planDoc)) ?>" target="_blank" rel="noopener" class="btn-primary">เปิดแผนฉบับเต็ม</a><?php endif; ?>
  </div>
  <ol class="grid gap-3.5 [grid-template-columns:repeat(auto-fit,minmax(260px,1fr))]">
    <?php foreach ($steps as $i => $s): ?>
      <li class="hover-lift rounded-3xl p-6 flex flex-col gap-2" style="background:<?= e($colors[$i % count($colors)]) ?>">
        <span class="flex justify-between items-center"><span class="font-display text-[40px] leading-none text-ku"><?= (int)$s['step_no'] ?></span><span class="chip bg-white text-ink"><?= e($s['timeframe']) ?></span></span>
        <span class="font-display text-xl"><?= e($s['title']) ?></span>
        <span class="text-[#3E5553]"><?= e($s['detail']) ?></span>
        <span class="text-sm text-muted">ผู้รับผิดชอบ: <?= e($s['owner']) ?></span>
      </li>
    <?php endforeach; ?>
  </ol>
  <?php if ($planDoc && $planDoc['file_type'] === 'pdf'): ?>
    <iframe src="<?= e(doc_href($planDoc)) ?>" title="<?= e($planDoc['title']) ?>" class="w-full h-[720px] rounded-3xl border border-line bg-[#E9ECE6]"></iframe>
  <?php elseif ($planDoc && $planDoc['file_type'] === 'image'): ?>
    <img src="<?= e(doc_href($planDoc)) ?>" alt="<?= e($planDoc['title']) ?>" class="w-full rounded-3xl">
  <?php endif; ?>
  <div class="flex flex-wrap gap-4">
    <div class="flex-[1_1_320px] card p-6">
      <h2 class="font-display font-medium text-[22px] mb-2.5">ช่องทางรับเรื่อง</h2>
      <ul class="list-disc pl-5 text-[#3E5553] leading-loose">
        <li>แบบสอบถามออนไลน์บนเว็บไซต์นี้</li>
        <li>กล่องรับความคิดเห็น <?= e(setting('complaint_box')) ?></li>
        <li>อีเมล <?= e(setting('contact_email')) ?></li>
        <li>โทร <?= e(setting('contact_phone')) ?></li>
      </ul>
    </div>
    <div class="flex-[1_1_320px] bg-ink text-white rounded-3xl p-6 flex flex-col gap-3 items-start">
      <h2 class="font-display font-medium text-[22px] text-lime">อยากแจ้งเรื่องตอนนี้เลย?</h2>
      <p class="text-mint">กรอกแบบสอบถามได้ทันที ไม่ต้องระบุตัวตน</p>
      <a href="<?= e(url('feedback.php')) ?>" class="btn-sun">ไปที่แบบสอบถาม →</a>
    </div>
  </div>
</div>
<?php require __DIR__ . '/../app/views/public_bottom.php'; ?>
