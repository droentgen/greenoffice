<?php
/**
 * Public page footer: hero call-to-action band, contact details and complaint links.
 * Contact details come from admin › ตั้งค่า.
 */
$tips = [
    'ปิดจอคอมตอนพักเที่ยง', 'พกแก้วไปซื้อกาแฟ 1 แก้ว', 'ส่งไฟล์แทนการปริ้นต์ 1 งาน', 'เดินขึ้นบันได 1 ชั้นแทนลิฟต์',
    'แยกขวดพลาสติกให้ถูกถัง', 'ตั้งแอร์ 26°C แล้วเปิดพัดลมช่วย', 'ปิดก๊อกให้สนิทเมื่อล้างมือเสร็จ', 'ชวนเพื่อน 1 คนมาเล่นเกม Green Check-in',
];
$web = setting('contact_web');
?>
</main>

<!-- Hero call-to-action band (every page) -->
<section aria-label="ชวนเป็นฮีโร่สายกรีน" class="relative overflow-hidden bg-lime text-ink">
  <div aria-hidden="true" class="absolute inset-0 dots"></div>
  <div class="relative max-w-7xl mx-auto px-4 sm:px-6 py-14 flex flex-wrap gap-8 items-center">
    <div class="flex-[2_1_460px] flex flex-col gap-5">
      <span class="self-start -rotate-3 bg-ink text-lime px-3.5 py-1.5 rounded-lg font-display">ประกาศรับสมัครฮีโร่ · ไม่ต้องมีพลังพิเศษ</span>
      <h2 class="font-display font-semibold text-[clamp(36px,5.4vw,66px)] leading-[1.08]">ไม่ต้องมีผ้าคลุม<br>ก็เป็น <span class="inline-block bg-white px-3.5 rounded-2xl -rotate-2 shadow-[5px_5px_0_#10302F]">ฮีโร่โลก</span> ได้</h2>
      <p class="text-lg max-w-xl text-[#203F3E]">พลังของเราแค่ปิดไฟ พกแก้ว แยกขยะ — แต่ถ้าทั้งคณะทำพร้อมกัน นั่นคือพลังระดับจักรวาล</p>
      <div class="flex flex-wrap gap-3">
        <a href="<?= e(url('operations.php')) ?>" class="btn-dark text-lg font-display font-normal">รับภารกิจเลย</a>
        <button type="button" class="btn bg-white border-[3px] border-ink font-display font-normal" data-tip-next>สุ่มภารกิจวันนี้</button>
      </div>
    </div>
    <div class="flex-[1_1_320px] relative min-h-[360px] flex items-end justify-center" data-tips='<?= e(json_encode($tips, JSON_UNESCAPED_UNICODE)) ?>'>
      <div role="status" class="absolute left-0 top-0 max-w-[250px] bg-white border-[3px] border-ink rounded-3xl px-4 py-3 font-display text-lg leading-snug shadow-[6px_6px_0_#10302F] -rotate-2">
        <span class="block text-sm text-ku">ภารกิจวันนี้ของคุณ</span><span data-tip-text><?= e($tips[array_rand($tips)]) ?></span>
      </div>
      <span aria-hidden="true" class="anim-float absolute right-1.5 top-9 bg-sun border-[3px] border-ink rounded-full px-3 py-1 font-display rotate-6">+1 ใบไม้</span>
      <span aria-hidden="true" class="anim-float absolute left-2.5 bottom-8 bg-white border-[3px] border-ink rounded-full px-3 py-1 font-display" style="animation-delay:-3s">ปิดไฟแล้ว!</span>
      <button type="button" data-tip-next aria-label="กดน้องกล้าเพื่อสุ่มภารกิจ" class="bg-transparent border-0 p-0 cursor-pointer">
        <span class="block anim-bob"><?php mascot('hero', 'w-[230px] h-[250px]', 'น้องกล้าในชุดฮีโร่'); ?></span>
      </button>
    </div>
  </div>
</section>

<footer class="bg-ku-deep text-[#E6F3F1]">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 pt-12 pb-7 grid gap-8 [grid-template-columns:repeat(auto-fit,minmax(230px,1fr))]">
    <div class="flex flex-col gap-3 items-start">
      <img src="<?= e(url('assets/img/logo.png')) ?>" alt="AGR Green Office สำนักงานสีเขียว คณะเกษตร มก." class="w-28 h-28 object-contain bg-white rounded-full p-1.5">
      <div class="font-display text-lg text-white leading-snug">สำนักงานสีเขียว<br>คณะเกษตร มหาวิทยาลัยเกษตรศาสตร์</div>
    </div>
    <div class="flex flex-col gap-2.5">
      <h2 class="font-display text-xl text-lime mb-1">พูดคุยกับเรา</h2>
      <p class="flex gap-2.5 leading-relaxed"><svg aria-hidden="true" class="w-5 h-5 flex-none mt-0.5" viewBox="0 0 24 24" fill="none" stroke="#C5E063" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21s-7-6.2-7-11.5A7 7 0 0 1 19 9.5C19 14.8 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/></svg><span><?= nl2br(e(setting('contact_address'))) ?></span></p>
      <?php if ($web): ?>
        <a href="<?= e($web) ?>" target="_blank" rel="noopener" class="flex gap-2.5 items-center text-white underline-offset-4 hover:underline min-h-[32px]"><svg aria-hidden="true" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="#C5E063" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c3 3.5 3 14.5 0 18M12 3c-3 3.5-3 14.5 0 18"/></svg><?= e(preg_replace('#^https?://#', '', $web)) ?></a>
      <?php endif; ?>
      <?php if (setting('contact_phone')): ?>
        <a href="tel:<?= e(preg_replace('/[^0-9+]/', '', setting('contact_phone'))) ?>" class="flex gap-2.5 items-center min-h-[32px] text-white hover:underline"><svg aria-hidden="true" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="#C5E063" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2z"/></svg><?= e(setting('contact_phone')) ?></a>
      <?php endif; ?>
      <?php if (setting('contact_email')): ?>
        <a href="mailto:<?= e(setting('contact_email')) ?>" class="flex gap-2.5 items-center min-h-[32px] text-white hover:underline"><svg aria-hidden="true" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="#C5E063" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/></svg><?= e(setting('contact_email')) ?></a>
      <?php endif; ?>
    </div>
    <div class="flex flex-col gap-1 items-start">
      <h2 class="font-display text-xl text-lime mb-2">ข้อร้องเรียนด้านสิ่งแวดล้อม</h2>
      <a href="<?= e(url('feedback.php')) ?>" class="min-h-[44px] flex items-center text-white underline underline-offset-4">ข้อคิดเห็นและข้อร้องเรียนทางด้านสิ่งแวดล้อม</a>
      <a href="<?= e(url('complaints.php')) ?>" class="min-h-[44px] flex items-center text-white underline underline-offset-4">การดำเนินการเรื่องร้องเรียน</a>
      <a href="<?= e(url('complaint-notice.php')) ?>" class="min-h-[44px] flex items-center text-white underline underline-offset-4">ประกาศข้อร้องเรียน</a>
      <a href="<?= e(url('complaint-plan.php')) ?>" class="min-h-[44px] flex items-center text-white underline underline-offset-4">แผนการจัดการข้อร้องเรียน</a>
    </div>
    <div class="flex flex-col gap-1 items-start">
      <h2 class="font-display text-xl text-lime mb-2">เมนู</h2>
      <?php foreach ($nav as [$label, $href]): ?>
        <a href="<?= e(url($href)) ?>" class="min-h-[40px] flex items-center hover:text-white"><?= e($label) ?></a>
      <?php endforeach; ?>
    </div>
  </div>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 py-4 border-t border-white/10 text-sm text-[#A9C9C5] flex flex-wrap gap-3 justify-between">
    <span>© <?= current_year_be() ?> Green Office · คณะเกษตร มหาวิทยาลัยเกษตรศาสตร์</span>
    <a href="<?= e(url('admin/')) ?>" class="underline">สำหรับผู้ดูแลระบบ</a>
  </div>
</footer>
<script src="<?= e(url('assets/js/app.js')) ?>"></script>
</body>
</html>
