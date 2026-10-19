<?php
/**
 * ข้อคิดเห็นและข้อร้องเรียนทางด้านสิ่งแวดล้อม — built-in questionnaire.
 * Submissions go to admin › ข้อร้องเรียน. If an external form (e.g. Google Form) is set in
 * admin › ตั้งค่า, a link to it is shown as well.
 */
require __DIR__ . '/../app/bootstrap.php';

$pageTitle = 'ข้อคิดเห็นและข้อร้องเรียนด้านสิ่งแวดล้อม';
$active = '';
$types = ['ข้อคิดเห็น', 'ข้อร้องเรียน', 'ข้อเสนอแนะ'];
$errors = [];
$old = ['type' => 'ข้อคิดเห็น', 'topic' => '', 'place' => '', 'detail' => '', 'rating' => '', 'name' => '', 'contact' => ''];
$ticket = null;

if (is_post()) {
    csrf_check();
    foreach ($old as $k => $_) {
        $old[$k] = trim((string)($_POST[$k] ?? ''));
    }
    if (!empty($_POST['website'])) {            // honeypot field: bots fill it, people never see it
        $ticket = 'GO-' . current_year_be() . '-000';
    } else {
        if (!in_array($old['type'], $types, true)) $errors[] = 'กรุณาเลือกเรื่องที่ต้องการแจ้ง';
        if (!in_array($old['topic'], COMPLAINT_TOPICS, true)) $errors[] = 'กรุณาเลือกด้านสิ่งแวดล้อม';
        if (mb_strlen($old['detail']) < 5) $errors[] = 'กรุณาเล่ารายละเอียดอย่างน้อยสั้น ๆ';
        if (mb_strlen($old['detail']) > 3000) $errors[] = 'รายละเอียดยาวเกินไป';
        $rating = $old['rating'] === '' ? null : max(1, min(5, (int)$old['rating']));
        if (!$errors) {
            $ticket = next_ticket_no();
            q('INSERT INTO complaints (ticket_no, type, topic, place, detail, rating, name, contact) VALUES (?,?,?,?,?,?,?,?)', [
                $ticket, $old['type'], $old['topic'], mb_substr($old['place'], 0, 255), $old['detail'], $rating,
                mb_substr($old['name'], 0, 150), mb_substr($old['contact'], 0, 150),
            ]);
        }
    }
}
$externalForm = clean_url(setting('feedback_form_url'));

require __DIR__ . '/../app/views/public_top.php';
?>
<div class="max-w-5xl mx-auto px-4 sm:px-6 pt-12 pb-16 flex flex-col gap-6">
  <div>
    <span class="eyebrow">ข้อคิดเห็นและข้อร้องเรียนทางด้านสิ่งแวดล้อม</span>
    <h1 class="h-page mt-1.5 mb-2">เจออะไรไม่กรีน บอกเราได้เลย</h1>
    <p class="text-[17px] text-[#3E5553]">แบบสอบถามใช้เวลาไม่เกิน 2 นาที เลือกไม่ระบุตัวตนได้ ทุกเรื่องจะได้เลขรับเรื่องและติดตามผลได้ที่ “ประกาศข้อร้องเรียน”</p>
    <?php if ($externalForm): ?>
      <a href="<?= e($externalForm) ?>" target="_blank" rel="noopener" class="btn-outline btn-sm mt-3">หรือกรอกผ่านแบบสอบถามออนไลน์ ↗</a>
    <?php endif; ?>
  </div>

  <?php if ($ticket): ?>
    <div class="anim-pop bg-ku text-white rounded-[30px] p-9 flex flex-col gap-3.5 items-start">
      <span class="font-display text-lime">ได้รับเรื่องแล้ว ขอบคุณที่ช่วยกันดูแลคณะ</span>
      <div class="font-display text-[40px] leading-tight">เลขรับเรื่อง <?= e($ticket) ?></div>
      <p class="text-[#CFE8E4]">ทีม Green Office จะดำเนินการตามกรอบเวลาใน “แผนการจัดการข้อร้องเรียน”</p>
      <div class="flex gap-2.5 flex-wrap">
        <a href="<?= e(url('complaints.php')) ?>" class="btn-sun">ดูประกาศข้อร้องเรียน</a>
        <a href="<?= e(url('feedback.php')) ?>" class="btn border-2 border-white/50 text-white">ส่งเรื่องใหม่</a>
      </div>
    </div>
  <?php else: ?>
    <?php if ($errors): ?>
      <div role="alert" class="rounded-2xl bg-[#FDE2DD] text-[#7A2A1C] p-4"><?php foreach ($errors as $er): ?><div>• <?= e($er) ?></div><?php endforeach; ?></div>
    <?php endif; ?>
    <form method="post" class="card p-7 flex flex-col gap-6">
      <?= csrf_field() ?>
      <div class="hidden" aria-hidden="true"><label>เว็บไซต์ <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
      <fieldset class="flex flex-col gap-2.5">
        <legend class="font-semibold mb-2.5">1. เรื่องที่ต้องการแจ้ง</legend>
        <div class="flex gap-2 flex-wrap">
          <?php foreach ($types as $t): ?>
            <label class="cursor-pointer">
              <input type="radio" name="type" value="<?= e($t) ?>" class="peer sr-only" <?= $old['type'] === $t ? 'checked' : '' ?>>
              <span class="btn border-2 border-ku bg-white text-ku peer-checked:bg-ku peer-checked:text-white peer-focus-visible:ring-4 peer-focus-visible:ring-sun"><?= e($t) ?></span>
            </label>
          <?php endforeach; ?>
        </div>
      </fieldset>
      <div class="grid gap-4 [grid-template-columns:repeat(auto-fit,minmax(240px,1fr))]">
        <label class="label text-base">2. ด้านสิ่งแวดล้อม
          <select name="topic" required class="input">
            <option value="">เลือกหัวข้อ</option>
            <?php foreach (COMPLAINT_TOPICS as $t): ?><option <?= $old['topic'] === $t ? 'selected' : '' ?>><?= e($t) ?></option><?php endforeach; ?>
          </select>
        </label>
        <label class="label text-base">3. สถานที่
          <input type="text" name="place" value="<?= e($old['place']) ?>" maxlength="255" placeholder="เช่น อาคาร [x] ชั้น 2 หน้าห้องน้ำ" class="input">
        </label>
      </div>
      <label class="label text-base">4. รายละเอียด
        <textarea name="detail" rows="5" required maxlength="3000" placeholder="เล่าสั้น ๆ ว่าเจออะไร เกิดขึ้นเมื่อไร และอยากให้ปรับอย่างไร" class="input leading-relaxed"><?= e($old['detail']) ?></textarea>
      </label>
      <fieldset>
        <legend class="font-semibold mb-2.5">5. ความพึงพอใจต่อการดำเนินงาน Green Office ของคณะ</legend>
        <div class="flex gap-2 flex-wrap items-center">
          <?php foreach (['ควรปรับปรุงมาก', 'ควรปรับปรุง', 'พอใช้', 'ดี', 'ดีมาก'] as $i => $label): $v = $i + 1; ?>
            <label class="cursor-pointer" title="<?= e($label) ?>">
              <input type="radio" name="rating" value="<?= $v ?>" class="peer sr-only" <?= $old['rating'] == $v ? 'checked' : '' ?>>
              <span class="w-14 h-14 rounded-2xl border-2 border-[#DDE3D9] bg-white text-[#B5C0BD] flex flex-col items-center justify-center transition peer-checked:bg-lime peer-checked:border-[#2F6B1F] peer-checked:text-[#2F6B1F] peer-focus-visible:ring-4 peer-focus-visible:ring-sun hover:-translate-y-0.5">
                <?= leaf_icon('w-6 h-6') ?><span class="text-[11px] text-ink"><?= $v ?></span>
              </span>
              <span class="sr-only"><?= $v ?> คะแนน <?= e($label) ?></span>
            </label>
          <?php endforeach; ?>
          <span class="text-sm text-muted pl-2">1 = ควรปรับปรุงมาก · 5 = ดีมาก</span>
        </div>
      </fieldset>
      <div class="grid gap-4 [grid-template-columns:repeat(auto-fit,minmax(240px,1fr))]">
        <label class="label text-base">ชื่อ (ไม่บังคับ)<input type="text" name="name" value="<?= e($old['name']) ?>" maxlength="150" class="input"></label>
        <label class="label text-base">อีเมล / เบอร์โทร สำหรับแจ้งผล (ไม่บังคับ)<input type="text" name="contact" value="<?= e($old['contact']) ?>" maxlength="150" class="input"></label>
      </div>
      <div class="flex justify-between items-center gap-3 flex-wrap border-t border-[#EDF0EA] pt-5">
        <span class="text-sm text-muted">ข้อมูลผู้แจ้งจะไม่ถูกเปิดเผยในประกาศข้อร้องเรียน</span>
        <button type="submit" class="btn-primary text-lg font-display font-normal px-8 min-h-[54px]">ส่งเรื่อง</button>
      </div>
    </form>
  <?php endif; ?>
</div>
<?php require __DIR__ . '/../app/views/public_bottom.php'; ?>
