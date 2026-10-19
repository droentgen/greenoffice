<?php
require __DIR__ . '/../../app/bootstrap.php';
$u = require_login();
$pageTitle = 'Dashboard ผู้ดูแล';
$active = 'dash';

$year = report_years()[0];
$prog = category_progress($year);
$total = array_sum(array_column($prog, 'total'));
$done = array_sum(array_column($prog, 'done'));
$readiness = $total ? round($done / $total * 100) : 0;
$evidence = (int)val('SELECT COUNT(*) FROM criteria_evidence WHERE year_be = ?', [$year]);
$openFindings = (int)val("SELECT COUNT(*) FROM audit_findings WHERE status <> 'closed'");
$newComplaints = (int)val("SELECT COUNT(*) FROM complaints WHERE status <> 'done'");
$game = game_stats();
[$cur, $prev] = latest_two_months();
$recentComplaints = rows('SELECT * FROM complaints ORDER BY created_at DESC LIMIT 5');
$cats = categories();

require __DIR__ . '/../../app/views/admin_top.php';
?>
<div class="grid gap-3.5 [grid-template-columns:repeat(auto-fit,minmax(200px,1fr))]">
  <?php foreach ([
      ['ความพร้อมก่อนประเมิน ปี ' . $year, $readiness, '%', 'รายงานแล้ว ' . $done . '/' . $total . ' ตัวชี้วัด', '#006664'],
      ['ลิงก์หลักฐาน ปี ' . $year, $evidence, '', 'รายการ', '#FFC845'],
      ['ประเด็นตรวจประเมินรอปิด', $openFindings, '', 'ข้อ', '#E2725B'],
      ['ข้อร้องเรียนที่ยังไม่ปิด', $newComplaints, '', 'เรื่อง', '#7CC6E8'],
  ] as [$k, $v, $suf, $sub, $c]): ?>
    <div class="hover-lift bg-white rounded-[22px] p-5 border-t-[6px]" style="border-color:<?= e($c) ?>">
      <div class="text-sm text-muted"><?= e($k) ?></div>
      <div class="font-display text-4xl leading-snug"><span data-count="<?= (int)$v ?>">0</span><?= e($suf) ?></div>
      <div class="text-sm text-muted"><?= e($sub) ?></div>
    </div>
  <?php endforeach; ?>
</div>

<div class="flex flex-wrap gap-4">
  <div class="flex-[2_1_420px] bg-white rounded-3xl p-6">
    <div class="flex justify-between items-center mb-3"><h2 class="font-display font-medium text-xl">ทรัพยากรเดือน <?= e(month_label($cur)) ?></h2><a href="<?= e(url('admin/stats.php')) ?>" class="btn-sm btn-outline">กรอกข้อมูล</a></div>
    <?php if ($cur): $no = 0; foreach (resource_meta() as $key => $m): $no++; $v = resource_value($cur, $key); [$b, $bb, $bf] = $prev ? change_badge($v, resource_value($prev, $key)) : ['—', '#EDF0EA', '#4A5F5D']; ?>
      <div class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-[#F4F9F8]">
        <span class="w-3 h-3 rounded flex-none" style="background:<?= e($m['ac']) ?>"></span>
        <span class="flex-1">0<?= $no ?> <?= e($m['name']) ?></span>
        <span class="font-display text-lg tabular-nums"><?= num($v, $m['dec']) ?> <span class="text-sm text-muted"><?= e($m['unit']) ?></span></span>
        <span class="chip min-w-[70px] text-center" style="background:<?= e($bb) ?>;color:<?= e($bf) ?>"><?= e($b) ?></span>
      </div>
    <?php endforeach; else: ?><p class="text-muted">ยังไม่มีข้อมูล</p><?php endif; ?>
  </div>
  <div class="flex-[1_1_300px] bg-white rounded-3xl p-6">
    <h2 class="font-display font-medium text-xl mb-3">รายงานผลตามหมวด ปี <?= $year ?></h2>
    <div class="flex flex-col gap-2.5">
      <?php foreach ($cats as $c): $p = $prog[$c['id']] ?? ['total' => 0, 'done' => 0]; $pct = $p['total'] ? round($p['done'] / $p['total'] * 100) : 0; ?>
        <a href="<?= e(url('admin/reports.php?cat=' . $c['id'] . '&year=' . $year)) ?>" class="block hover:bg-[#F4F9F8] rounded-xl p-1.5">
          <div class="flex justify-between text-sm"><span>หมวด <?= (int)$c['id'] ?></span><span class="font-semibold"><?= $p['done'] ?>/<?= $p['total'] ?></span></div>
          <div class="h-2 rounded-full bg-[#EDF0EA] overflow-hidden mt-1"><div class="grow-bar h-full bg-ku" data-w="<?= $pct ?>"></div></div>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<div class="flex flex-wrap gap-4">
  <div class="flex-[2_1_420px] bg-white rounded-3xl p-6">
    <div class="flex justify-between items-center mb-3"><h2 class="font-display font-medium text-xl">ข้อร้องเรียนล่าสุด</h2><a href="<?= e(url('admin/complaints.php')) ?>" class="btn-sm btn-outline">ดูทั้งหมด</a></div>
    <?php if (!$recentComplaints): ?><p class="text-muted">ยังไม่มีเรื่อง</p><?php endif; ?>
    <?php foreach ($recentComplaints as $c): $st = COMPLAINT_STATUS[$c['status']]; ?>
      <a href="<?= e(url('admin/complaints.php?id=' . $c['id'])) ?>" class="flex gap-3 items-center px-3 py-2.5 rounded-xl hover:bg-[#F4F9F8] border-t border-[#EDF0EA]">
        <span class="text-sm text-muted w-28 flex-none"><?= e($c['ticket_no']) ?></span>
        <span class="flex-1 min-w-0 truncate"><?= e($c['public_title'] ?: mb_substr($c['detail'], 0, 60)) ?></span>
        <span class="chip" style="background:<?= e($st['bg']) ?>;color:<?= e($st['fg']) ?>"><?= e($st['label']) ?></span>
      </a>
    <?php endforeach; ?>
  </div>
  <div class="flex-[1_1_300px] bg-ink text-white rounded-3xl p-6">
    <h2 class="font-display text-xl text-lime mb-3">ผู้ร่วมเกม Green Check-in</h2>
    <div class="font-display text-5xl"><span data-count="<?= $game['players'] ?>">0</span> <span class="text-base text-[#CFE8E4]">คน</span></div>
    <div class="flex flex-col gap-2.5 mt-4">
      <?php foreach ($game['topics'] as $t): ?>
        <div><div class="flex justify-between text-sm"><span><?= e($t['name']) ?></span><span><?= $t['count'] ?> คน · <?= $t['pct'] ?>%</span></div><div class="h-2 rounded-full bg-white/15 mt-1 overflow-hidden"><div class="grow-bar h-full bg-lime" data-w="<?= $t['pct'] ?>"></div></div></div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php require __DIR__ . '/../../app/views/admin_bottom.php'; ?>
