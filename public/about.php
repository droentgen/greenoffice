<?php
require __DIR__ . '/../app/bootstrap.php';

$pageTitle = 'เกี่ยวกับเรา';
$active = 'about';

$tabs = ['policy', 'goals', 'team', 'measures'];
$tabIntro = [
    'policy'   => 'นโยบายสิ่งแวดล้อมของคณะ ลงนามโดยผู้บริหาร และสื่อสารให้ทุกคนในคณะรับทราบ',
    'goals'    => 'เป้าหมายและตัวชี้วัดด้านทรัพยากร พลังงาน ของเสีย และก๊าซเรือนกระจกประจำปี',
    'team'     => 'คำสั่งแต่งตั้งและโครงสร้างคณะทำงานสำนักงานสีเขียว',
    'measures' => 'มาตรการที่ทุกคนในคณะใช้เป็นแนวทางในชีวิตประจำวัน',
];
$tab = in_array($_GET['tab'] ?? '', $tabs, true) ? $_GET['tab'] : 'policy';
$docs = rows('SELECT * FROM documents WHERE section = ? AND is_published = 1 ORDER BY sort, id', [$tab]);
$docId = (int)($_GET['doc'] ?? 0);
$doc = null;
foreach ($docs as $d) {
    if ($d['id'] == $docId) {
        $doc = $d;
    }
}
$doc = $doc ?? ($docs[0] ?? null);

$videoUrl = setting('video_url');
$embed = $videoUrl ? youtube_embed($videoUrl) : null;
$isVideoFile = $videoUrl && preg_match('/\.(mp4|webm)$/i', $videoUrl);

$joinWays = [
    ['1', 'สมัคร Green Ambassador', 'เป็นแกนนำสายกรีนประจำภาควิชาหรือชั้นปี ช่วยชวนเพื่อนทำภารกิจ', 'ดูประกาศรับสมัคร', 'news.php?cat=ประกาศ', '#FFF2C2'],
    ['2', 'ส่งไอเดียหรือข้อคิดเห็น', 'เห็นอะไรที่ทำให้คณะกรีนขึ้นได้ บอกเรา ทุกความเห็นถูกนำไปพิจารณา', 'ส่งความคิดเห็น', 'feedback.php', '#E6F3F1'],
    ['3', 'ร่วมภารกิจประจำเดือน', 'ภารกิจเล็ก ๆ ที่ทำได้ทุกวัน และเห็นผลเป็นตัวเลขจริงบนเว็บ', 'ดูภารกิจ', 'operations.php', '#EEF6E6'],
];
$faqs = [
    ['Green Office คืออะไร?', 'โครงการสำนักงานสีเขียวที่ประเมินตามเกณฑ์ 7 หมวด ตั้งแต่นโยบาย การใช้ทรัพยากร การจัดการของเสีย ไปจนถึงการดำเนินงานอย่างต่อเนื่อง'],
    ['นิสิตเกี่ยวข้องด้วยไหม?', 'เกี่ยวแน่นอน ทุกคนที่ใช้พื้นที่ของคณะช่วยได้ ตั้งแต่ปิดไฟ พกแก้ว ไปจนถึงแจ้งปัญหาสิ่งแวดล้อมที่พบ'],
    ['เจอปัญหาสิ่งแวดล้อมในคณะ ต้องแจ้งที่ไหน?', 'กรอกแบบสอบถาม “ข้อคิดเห็นและข้อร้องเรียนทางด้านสิ่งแวดล้อม” ได้ที่ส่วนท้ายของทุกหน้า และติดตามผลได้ที่การดำเนินการเรื่องร้องเรียน'],
];

require __DIR__ . '/../app/views/public_top.php';
?>
<div class="max-w-7xl mx-auto px-4 sm:px-6 pt-14 pb-16 flex flex-col gap-10">
  <div class="flex flex-wrap gap-8 items-center">
    <div class="flex-[2_1_460px]">
      <span class="eyebrow">เกี่ยวกับเรา</span>
      <h1 class="font-display font-semibold text-[clamp(36px,5vw,56px)] leading-[1.12] mt-1.5 mb-3.5">ทีมเล็ก ๆ ที่อยากให้<br><span class="text-ku">ทั้งคณะกรีนไปด้วยกัน</span></h1>
      <p class="text-lg text-[#3E5553] max-w-2xl">Green Office คณะเกษตร ทำงานร่วมกับบุคลากร นิสิต และหน่วยงานในคณะ เพื่อลดการใช้ทรัพยากร ลดก๊าซเรือนกระจก และสร้างสำนักงานที่น่าอยู่ ตามเกณฑ์สำนักงานสีเขียว 7 หมวด</p>
    </div>
    <div class="anim-float flex-[1_1_260px] flex justify-center">
      <img src="<?= e(url('assets/img/logo.png')) ?>" alt="AGR Green Office" class="w-60 h-60 object-contain">
    </div>
  </div>

  <!-- Document tabs: policy / goals / team / measures (image or PDF) -->
  <section aria-label="เอกสารเกี่ยวกับเรา" class="card p-6 flex flex-col gap-4">
    <nav aria-label="หมวดเอกสาร" class="flex gap-2 flex-wrap">
      <?php foreach ($tabs as $t): ?>
        <a href="<?= e(url('about.php?tab=' . $t)) ?>#docs" class="btn border-2 border-ku font-display font-normal text-base <?= $t === $tab ? 'bg-ku text-white' : 'bg-white text-ku' ?>"<?= $t === $tab ? ' aria-current="page"' : '' ?>><?= e(DOC_SECTIONS[$t]) ?></a>
      <?php endforeach; ?>
    </nav>
    <p id="docs" class="text-[#3E5553]"><?= e($tabIntro[$tab]) ?></p>
    <?php if (!$docs): ?>
      <div class="rounded-2xl bg-[#F4F9F8] p-8 text-center text-muted">ยังไม่มีเอกสารในหมวดนี้ — ผู้ดูแลเพิ่มได้ที่ ระบบหลังบ้าน › เอกสารเกี่ยวกับเรา</div>
    <?php else: ?>
    <div class="flex flex-wrap gap-5">
      <div class="flex-[1_1_260px] flex flex-col gap-2">
        <?php foreach ($docs as $d): $on = $doc && $d['id'] == $doc['id']; ?>
          <a href="<?= e(url('about.php?tab=' . $tab . '&doc=' . $d['id'])) ?>#docs" class="min-h-[64px] flex items-center gap-3 px-3.5 py-2.5 rounded-2xl border-2 no-underline text-ink <?= $on ? 'border-ku bg-mint' : 'border-line bg-white hover:bg-[#F4F9F8]' ?>">
            <span class="w-11 h-11 rounded-xl text-white text-xs font-bold flex items-center justify-center flex-none <?= $d['file_type'] === 'image' ? 'bg-[#155E80]' : ($d['file_type'] === 'pdf' ? 'bg-[#B23A48]' : 'bg-ku') ?>"><?= $d['file_type'] === 'image' ? 'IMG' : ($d['file_type'] === 'pdf' ? 'PDF' : 'LINK') ?></span>
            <span class="font-semibold leading-snug"><?= e($d['title']) ?></span>
          </a>
        <?php endforeach; ?>
      </div>
      <div class="flex-[3_1_480px] min-w-0 flex flex-col gap-2.5">
        <div class="flex justify-between items-center gap-2.5 flex-wrap">
          <h2 class="text-lg font-semibold"><?= e($doc['title']) ?></h2>
          <span class="flex gap-2">
            <a href="<?= e(doc_href($doc)) ?>" target="_blank" rel="noopener" class="btn-sm btn bg-mint text-ku">เปิดเต็มจอ</a>
            <?php if ($doc['file_path']): ?><a href="<?= e(doc_href($doc)) ?>" download class="btn-sm btn-outline">ดาวน์โหลด</a><?php endif; ?>
          </span>
        </div>
        <?php if ($doc['file_type'] === 'image'): ?>
          <a href="<?= e(doc_href($doc)) ?>" target="_blank" rel="noopener" class="block rounded-2xl overflow-hidden bg-[#F4F9F8]"><img src="<?= e(doc_href($doc)) ?>" alt="<?= e($doc['title']) ?>" class="w-full max-h-[720px] object-contain anim-pop"></a>
        <?php elseif ($doc['file_type'] === 'pdf'): ?>
          <iframe src="<?= e(doc_href($doc)) ?>" title="<?= e($doc['title']) ?>" class="w-full h-[720px] rounded-2xl border border-line bg-[#E9ECE6]"></iframe>
        <?php else: ?>
          <div class="rounded-2xl bg-[#F4F9F8] p-8 text-center"><a href="<?= e(doc_href($doc)) ?>" target="_blank" rel="noopener" class="btn-primary">เปิดเอกสาร</a></div>
        <?php endif; ?>
      </div>
    </div>
    <?php endif; ?>
  </section>

  <!-- Summary video -->
  <section aria-label="คลิปสรุปการดำเนินงาน" class="flex flex-wrap gap-5 items-stretch">
    <div class="flex-[3_1_520px] min-w-0">
      <div class="relative aspect-video rounded-[30px] overflow-hidden bg-ku-deep text-white">
        <?php if ($embed): ?>
          <iframe src="<?= e($embed) ?>" title="<?= e(setting('video_title')) ?>" class="absolute inset-0 w-full h-full" allow="accelerometer; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
        <?php elseif ($isVideoFile): ?>
          <video src="<?= e($videoUrl) ?>" controls class="absolute inset-0 w-full h-full"></video>
        <?php else: ?>
          <div aria-hidden="true" class="anim-blob absolute -left-20 -bottom-28 w-[380px] h-[380px] bg-ku"></div>
          <img src="<?= e(url('assets/img/logo.png')) ?>" alt="" class="absolute left-6 top-5 w-20 h-20 object-contain bg-white rounded-2xl p-1">
          <div class="absolute left-6 right-6 top-28 font-display text-[clamp(22px,3vw,34px)] leading-tight"><?= e(setting('video_title')) ?></div>
          <div class="absolute inset-0 flex items-center justify-center"><span class="anim-bob w-24 h-24 rounded-full bg-sun text-ink flex items-center justify-center shadow-[0_0_0_16px_rgba(255,200,69,.25)]"><svg aria-hidden="true" class="w-10 h-10" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5.5v13a1 1 0 0 0 1.5.86l10.5-6.5a1 1 0 0 0 0-1.72L9.5 4.64A1 1 0 0 0 8 5.5z"/></svg></span></div>
          <div class="absolute left-0 right-0 bottom-0 px-6 py-3 bg-black/30 text-sm">คลิปจะแสดงที่นี่เมื่อผู้ดูแลใส่ลิงก์ YouTube ในหน้า ตั้งค่า</div>
        <?php endif; ?>
      </div>
    </div>
    <div class="flex-[1_1_260px] card p-6 flex flex-col gap-2.5">
      <span class="eyebrow">คลิปสรุปการดำเนินงาน</span>
      <h2 class="font-display font-medium text-2xl leading-tight mb-1">ดูปีที่ผ่านมาของเรา ในไม่กี่นาที</h2>
      <?php foreach (['นโยบายและเป้าหมายปีนี้', 'ตัวเลขทรัพยากร 5 ด้าน', 'กิจกรรมเด่นของคณะ', 'ก้าวต่อไปของ Green Office'] as $i => $ch): ?>
        <div class="min-h-[52px] flex items-center gap-3 px-3 py-2 rounded-2xl border border-line"><span class="w-8 h-8 rounded-[10px] bg-mint text-ku font-display flex items-center justify-center flex-none"><?= $i + 1 ?></span><span><?= e($ch) ?></span></div>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- Ways to join -->
  <section class="flex flex-col gap-4">
    <h2 class="font-display font-medium text-[28px]">ร่วมเป็นส่วนหนึ่งได้ 3 ทาง</h2>
    <div class="grid gap-4 [grid-template-columns:repeat(auto-fit,minmax(240px,1fr))]">
      <?php foreach ($joinWays as [$n, $t, $d, $cta, $href, $bg]): ?>
        <div class="hover-lift rounded-[26px] p-6 flex flex-col gap-2.5" style="background:<?= e($bg) ?>">
          <span class="ico-bg w-14 h-14 rounded-[18px] bg-white text-ku flex items-center justify-center font-display text-[26px]"><?= e($n) ?></span>
          <h3 class="font-display font-medium text-[21px]"><?= e($t) ?></h3>
          <p class="text-[#3E5553]"><?= e($d) ?></p>
          <a href="<?= e(url($href)) ?>" class="mt-auto self-start btn-dark"><?= e($cta) ?> →</a>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- FAQ -->
  <section class="bg-[#EEF6E6] rounded-[30px] p-7 flex flex-col gap-2.5">
    <h2 class="font-display font-medium text-[28px] mb-1.5">คำถามที่พบบ่อย</h2>
    <?php foreach ($faqs as [$q, $a]): ?>
      <details class="group bg-white rounded-[18px]">
        <summary class="list-none cursor-pointer min-h-[56px] flex justify-between items-center gap-3 px-4.5 px-5 py-3 font-semibold"><span><?= e($q) ?></span><span aria-hidden="true" class="text-2xl text-ku group-open:rotate-45 transition">+</span></summary>
        <p class="px-5 pb-4 text-[#3E5553]"><?= e($a) ?></p>
      </details>
    <?php endforeach; ?>
  </section>
</div>
<?php require __DIR__ . '/../app/views/public_bottom.php'; ?>
