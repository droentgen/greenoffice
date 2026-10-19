<?php
/** การดำเนินการเรื่องร้องเรียน — only items the admin marked as public. Never shows the reporter's name or contact. */
require __DIR__ . '/../app/bootstrap.php';

$pageTitle = 'การดำเนินการเรื่องร้องเรียน';
$active = '';
$items = rows('SELECT id, ticket_no, topic, status, public_title, created_at FROM complaints WHERE is_public = 1 ORDER BY created_at DESC, id DESC');

require __DIR__ . '/../app/views/public_top.php';
?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 pt-12 pb-16 flex flex-col gap-6">
  <div>
    <span class="eyebrow">การดำเนินการเรื่องร้องเรียน</span>
    <h1 class="h-page mt-1.5 mb-2">ทุกเรื่องที่แจ้งมา เราตามให้จนจบ</h1>
    <p class="text-[17px] text-[#3E5553]">สรุปข้อร้องเรียนด้านสิ่งแวดล้อมและสถานะการแก้ไข (ไม่เปิดเผยข้อมูลผู้แจ้ง)</p>
  </div>
  <?php if (!$items): ?>
    <div class="card p-6 text-muted">ยังไม่มีการดำเนินการเรื่องร้องเรียน</div>
  <?php else: ?>
  <div class="grid gap-4 [grid-template-columns:repeat(auto-fit,minmax(300px,1fr))]">
    <?php foreach ($items as $c): $st = COMPLAINT_STATUS[$c['status']]; ?>
      <a href="<?= e(url('complaint-view.php?id=' . $c['id'])) ?>" class="hover-lift card p-6 flex flex-col gap-2.5 text-ink no-underline">
        <span class="flex justify-between gap-2 items-center"><span class="text-sm text-muted">เลขที่ <?= e($c['ticket_no']) ?></span><span class="chip" style="background:<?= e($st['bg']) ?>;color:<?= e($st['fg']) ?>"><?= e($st['label']) ?></span></span>
        <span class="font-display text-xl leading-snug"><?= e($c['public_title'] ?: 'ข้อร้องเรียนด้าน' . $c['topic']) ?></span>
        <span class="text-sm text-muted">ด้าน<?= e($c['topic']) ?> · รับเรื่อง <?= e(thai_date($c['created_at'])) ?></span>
        <span class="text-sm font-semibold text-ku">ดูรายละเอียด →</span>
      </a>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/../app/views/public_bottom.php'; ?>
