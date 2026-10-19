<?php
/**
 * Public page header. Before including, set:
 *   $pageTitle = 'ชื่อหน้า';
 *   $active    = 'home' | 'about' | 'ops' | 'stats' | 'news' | '';
 */
require_once __DIR__ . '/partials/mascot.php';
$pageTitle = $pageTitle ?? '';
$active = $active ?? '';
$nav = [
    'home'  => ['หน้าแรก', 'index.php'],
    'about' => ['เกี่ยวกับเรา', 'about.php'],
    'ops'   => ['การดำเนินงาน', 'operations.php'],
    'stats' => ['สถิติการใช้ทรัพยากร', 'stats.php'],
    'news'  => ['ข่าวสารและกิจกรรม', 'news.php'],
];
?>
<!doctype html>
<html lang="th">
<head>
<?php require __DIR__ . '/partials/head.php'; ?>
<title><?= e(($pageTitle ? $pageTitle . ' · ' : '') . setting('site_name', 'Green Office คณะเกษตร')) ?></title>
</head>
<body class="font-sans text-ink antialiased">
<a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:z-50 focus:bg-white focus:p-3">ข้ามไปเนื้อหา</a>
<header class="sticky top-0 z-30 bg-cream/90 backdrop-blur border-b border-line">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 py-2 flex items-center gap-3 flex-wrap">
    <a href="<?= e(url('index.php')) ?>" class="flex items-center gap-3 no-underline text-ink">
      <img src="<?= e(url('assets/img/logo.png')) ?>" alt="" class="w-14 h-14 object-contain">
      <span class="leading-tight">
        <span class="block font-display font-semibold text-ku text-lg">GREEN OFFICE</span>
        <span class="block text-xs text-muted">คณะเกษตร ม.เกษตรศาสตร์</span>
      </span>
    </a>
    <nav aria-label="เมนูหลัก" class="hidden lg:flex flex-1 justify-center gap-1">
      <?php foreach ($nav as $key => [$label, $href]): ?>
        <a href="<?= e(url($href)) ?>" class="min-h-[44px] inline-flex items-center px-4 rounded-full font-medium text-[15px] transition <?= $active === $key ? 'bg-ku text-white' : 'text-ink hover:bg-mint' ?>"<?= $active === $key ? ' aria-current="page"' : '' ?>><?= e($label) ?></a>
      <?php endforeach; ?>
    </nav>
    <a href="<?= e(url('admin/')) ?>" class="hidden lg:inline-flex btn-outline btn-sm">
      <svg aria-hidden="true" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg>ระบบหลังบ้าน
    </a>
    <details class="lg:hidden ml-auto relative">
      <summary class="list-none btn-outline btn-sm cursor-pointer" aria-label="เปิดเมนู">เมนู</summary>
      <div class="absolute right-0 mt-2 w-64 card p-2 shadow-xl flex flex-col">
        <?php foreach ($nav as $key => [$label, $href]): ?>
          <a href="<?= e(url($href)) ?>" class="min-h-[44px] flex items-center px-3 rounded-xl <?= $active === $key ? 'bg-ku text-white' : 'hover:bg-mint' ?>"><?= e($label) ?></a>
        <?php endforeach; ?>
        <a href="<?= e(url('admin/')) ?>" class="min-h-[44px] flex items-center px-3 rounded-xl text-ku font-semibold hover:bg-mint">ระบบหลังบ้าน</a>
      </div>
    </details>
  </div>
</header>
<main id="main">
