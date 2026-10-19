<?php
require __DIR__ . '/../../app/bootstrap.php';

if (current_user()) {
    redirect('admin/');
}
$error = '';
$username = '';
if (is_post()) {
    csrf_check();
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    if (login_locked_seconds() > 0) {
        $error = 'ลองผิดหลายครั้งเกินไป กรุณารอ ' . login_locked_seconds() . ' วินาที';
    } elseif (attempt_login($username, $password)) {
        redirect('admin/');
    } else {
        $error = login_locked_seconds() > 0 ? 'ลองผิดหลายครั้งเกินไป กรุณารอ 60 วินาที' : 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง';
    }
}
?>
<!doctype html>
<html lang="th">
<head>
<?php require __DIR__ . '/../../app/views/partials/head.php'; ?>
<meta name="robots" content="noindex">
<title>เข้าสู่ระบบ · Green Office</title>
</head>
<body class="font-sans text-ink antialiased">
<main class="min-h-screen bg-ku flex items-center justify-center px-4 py-8 relative overflow-hidden">
  <div aria-hidden="true" class="anim-blob absolute w-[520px] h-[520px] -left-40 -top-40 bg-ku-mid"></div>
  <div aria-hidden="true" class="anim-blob absolute w-[380px] h-[380px] -right-28 -bottom-28 bg-[#004F4D]" style="animation-delay:-4s"></div>
  <form method="post" class="anim-pop relative w-full max-w-[420px] bg-white rounded-[32px] p-8 flex flex-col gap-4">
    <?= csrf_field() ?>
    <img src="<?= e(url('assets/img/logo.png')) ?>" alt="AGR Green Office" class="w-28 h-28 object-contain self-center">
    <h1 class="text-center font-display font-medium text-[26px]">เข้าสู่ระบบผู้ดูแล</h1>
    <label class="label">ชื่อผู้ใช้<input type="text" name="username" value="<?= e($username) ?>" autocomplete="username" required class="input"></label>
    <label class="label">รหัสผ่าน<input type="password" name="password" autocomplete="current-password" required class="input"></label>
    <?php if ($error): ?><div role="alert" class="bg-[#FDE2DD] text-[#7A2A1C] rounded-xl px-3.5 py-2.5 text-sm"><?= e($error) ?></div><?php endif; ?>
    <button class="btn-primary min-h-[52px] font-display font-normal text-lg">เข้าสู่ระบบ</button>
    <a href="<?= e(url('index.php')) ?>" class="text-center text-ku font-semibold min-h-[44px] inline-flex items-center justify-center">← กลับหน้าเว็บไซต์</a>
  </form>
</main>
</body>
</html>
