<?php
require __DIR__ . '/../app/bootstrap.php';

$c = row('SELECT * FROM complaints WHERE id = ? AND is_public = 1', [(int)($_GET['id'] ?? 0)]);
if (!$c) {
    http_response_code(404);
}
$pageTitle = $c ? ($c['public_title'] ?: $c['ticket_no']) : 'ไม่พบประกาศ';
$active = '';
$stepNames = ['รับเรื่องและลงทะเบียน', 'ตรวจสอบพื้นที่', 'ดำเนินการแก้ไข', 'แจ้งผลและปิดเรื่อง'];
require __DIR__ . '/../app/views/public_top.php';
?>
<div class="max-w-4xl mx-auto px-4 sm:px-6 pt-8 pb-16 flex flex-col gap-5">
  <a href="<?= e(url('complaints.php')) ?>" class="self-start min-h-[44px] inline-flex items-center font-semibold text-ku">← ประกาศข้อร้องเรียน</a>
  <?php if (!$c): ?>
    <div class="card p-8 text-muted">ไม่พบประกาศนี้</div>
  <?php else: $st = COMPLAINT_STATUS[$c['status']]; ?>
  <div class="card p-8 flex flex-col gap-5">
    <div class="flex gap-2.5 flex-wrap items-center"><span class="text-sm text-muted">เลขที่ <?= e($c['ticket_no']) ?> · ด้าน<?= e($c['topic']) ?> · รับเรื่อง <?= e(thai_date($c['created_at'])) ?></span><span class="chip" style="background:<?= e($st['bg']) ?>;color:<?= e($st['fg']) ?>"><?= e($st['label']) ?></span></div>
    <h1 class="font-display font-semibold text-[clamp(26px,4vw,38px)] leading-tight"><?= e($c['public_title'] ?: 'ข้อร้องเรียนด้าน' . $c['topic']) ?></h1>
    <div><h2 class="font-semibold text-lg mb-1.5">รายละเอียดข้อร้องเรียน</h2><p class="text-[#3E5553] whitespace-pre-line"><?= e($c['public_detail'] ?: '—') ?></p></div>
    <div>
      <h2 class="font-semibold text-lg mb-2.5">การดำเนินการ</h2>
      <ol class="flex flex-col">
        <?php foreach ($stepNames as $k => $s): $done = $k < $st['step']; ?>
          <li class="flex gap-3.5">
            <span class="flex flex-col items-center"><span class="w-7 h-7 rounded-full text-white text-sm flex items-center justify-center flex-none <?= $done ? 'bg-[#2E9E5B]' : 'bg-[#B5C0BD]' ?>"><?= $done ? '✓' : $k + 1 ?></span><span class="flex-1 w-0.5 bg-line min-h-[18px]"></span></span>
            <span class="pb-4"><span class="block font-semibold"><?= e($s) ?></span><span class="text-sm text-muted"><?= $done ? 'เสร็จแล้ว' : 'รอดำเนินการ' ?></span></span>
          </li>
        <?php endforeach; ?>
      </ol>
    </div>
    <?php if ($c['result_text']): ?><div class="bg-[#EEF6E6] rounded-[18px] px-5 py-4"><b>ผลการดำเนินงาน:</b> <?= e($c['result_text']) ?></div><?php endif; ?>
  </div>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/../app/views/public_bottom.php'; ?>
