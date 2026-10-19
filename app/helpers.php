<?php
/**
 * Small helper functions used across the site.
 */

/** Escape text for HTML output. Use it on everything that comes from the database or the user. */
function e($value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

/** URL path of the public/ folder, without a trailing slash. */
function base_url(): string
{
    global $CONFIG;
    if (array_key_exists('base_url', $CONFIG) && $CONFIG['base_url'] !== null) {
        return rtrim($CONFIG['base_url'], '/');
    }
    $dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
    $dir = preg_replace('#/(admin|api)$#', '', $dir);
    return rtrim($dir, '/');
}

/** Build a link to a page or file inside public/, e.g. url('about.php?tab=policy'). */
function url(string $path = ''): string
{
    return base_url() . '/' . ltrim($path, '/');
}

function redirect(string $path): never
{
    header('Location: ' . (preg_match('#^https?://#', $path) ? $path : url($path)));
    exit;
}

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function input(string $key, $default = '')
{
    $v = $_POST[$key] ?? $_GET[$key] ?? $default;
    return is_string($v) ? trim($v) : $v;
}

/* ---------- CSRF protection for every form that changes data ---------- */

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function csrf_check(): void
{
    $sent = $_POST['_csrf'] ?? '';
    if (!is_string($sent) || !hash_equals(csrf_token(), $sent)) {
        http_response_code(419);
        exit('แบบฟอร์มหมดอายุ กรุณากลับไปหน้าเดิมแล้วลองใหม่อีกครั้ง');
    }
}

/* ---------- Flash messages (shown once after a redirect) ---------- */

function flash(string $message, string $type = 'success'): void
{
    $_SESSION['flash'][] = ['m' => $message, 't' => $type];
}

function take_flashes(): array
{
    $f = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $f;
}

/* ---------- Site settings (editable in admin › ตั้งค่า) ---------- */

function settings(): array
{
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        foreach (rows('SELECT k, v FROM settings') as $r) {
            $cache[$r['k']] = $r['v'];
        }
    }
    return $cache;
}

function setting(string $key, string $default = ''): string
{
    $s = settings();
    return isset($s[$key]) && $s[$key] !== '' ? $s[$key] : $default;
}

function save_setting(string $key, string $value): void
{
    q('INSERT INTO settings (k, v) VALUES (?, ?) ON CONFLICT (k) DO UPDATE SET v = EXCLUDED.v', [$key, $value]);
}

/* ---------- Formatting ---------- */

function num($n, int $decimals = 0): string
{
    return number_format((float)$n, $decimals);
}

const THAI_MONTHS = ['', 'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];
const THAI_MONTHS_SHORT = ['', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];

/** 2026-10-19 → 19 ต.ค. 2569 */
function thai_date(?string $date): string
{
    if (!$date) {
        return '';
    }
    $t = strtotime($date);
    return date('j', $t) . ' ' . THAI_MONTHS_SHORT[(int)date('n', $t)] . ' ' . ((int)date('Y', $t) + 543);
}

/** Current Thai Buddhist-era year, e.g. 2569. */
function current_year_be(): int
{
    return (int)date('Y') + 543;
}

/** Years shown in the year filters (from settings, newest first). */
function report_years(): array
{
    $raw = setting('report_years', (current_year_be() - 2) . ',' . (current_year_be() - 1) . ',' . current_year_be());
    $years = array_values(array_filter(array_map('intval', explode(',', $raw))));
    rsort($years);
    return $years;
}

/* ---------- File uploads ---------- */

const UPLOAD_TYPES = [
    'pdf'  => ['application/pdf'],
    'jpg'  => ['image/jpeg'],
    'jpeg' => ['image/jpeg'],
    'png'  => ['image/png'],
    'webp' => ['image/webp'],
];

/**
 * Save an uploaded file into public/uploads/<folder>/ and return its path
 * relative to public/ (e.g. "uploads/docs/ab12cd.pdf"), or null if no file was sent.
 * Throws RuntimeException with a Thai message when the file is not allowed.
 */
function save_upload(string $field, string $folder, array $allowedExt = ['pdf', 'jpg', 'jpeg', 'png', 'webp']): ?string
{
    global $CONFIG;
    if (empty($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    $f = $_FILES[$field];
    if ($f['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('อัปโหลดไฟล์ไม่สำเร็จ (รหัส ' . $f['error'] . ') — ไฟล์อาจใหญ่เกินที่เซิร์ฟเวอร์อนุญาต');
    }
    $maxBytes = (int)($CONFIG['upload_max_mb'] ?? 20) * 1024 * 1024;
    if ($f['size'] > $maxBytes) {
        throw new RuntimeException('ไฟล์ใหญ่เกิน ' . ($CONFIG['upload_max_mb'] ?? 20) . ' MB');
    }
    $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExt, true) || !isset(UPLOAD_TYPES[$ext])) {
        throw new RuntimeException('อนุญาตเฉพาะไฟล์ ' . strtoupper(implode(', ', $allowedExt)));
    }
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
    if (!in_array($mime, UPLOAD_TYPES[$ext], true)) {
        throw new RuntimeException('ชนิดไฟล์ไม่ตรงกับนามสกุล');
    }
    $dir = PUBLIC_DIR . '/uploads/' . $folder;
    if (!is_dir($dir) && !mkdir($dir, 0775, true)) {
        throw new RuntimeException('สร้างโฟลเดอร์อัปโหลดไม่ได้');
    }
    $name = date('Ymd') . '-' . bin2hex(random_bytes(8)) . '.' . $ext;
    if (!move_uploaded_file($f['tmp_name'], $dir . '/' . $name)) {
        throw new RuntimeException('บันทึกไฟล์ไม่สำเร็จ ตรวจสิทธิ์การเขียนโฟลเดอร์ public/uploads');
    }
    return 'uploads/' . $folder . '/' . $name;
}

/** Delete a previously uploaded file (only inside public/uploads). */
function delete_upload(?string $relPath): void
{
    if (!$relPath || !str_starts_with($relPath, 'uploads/')) {
        return;
    }
    $full = realpath(PUBLIC_DIR . '/' . $relPath);
    $uploads = realpath(PUBLIC_DIR . '/uploads');
    if ($full && $uploads && str_starts_with($full, $uploads) && is_file($full)) {
        unlink($full);
    }
}

/** Accept only http(s) links for evidence and documents. */
function clean_url(string $u): ?string
{
    $u = trim($u);
    if ($u === '' || !filter_var($u, FILTER_VALIDATE_URL)) {
        return null;
    }
    $scheme = strtolower((string)parse_url($u, PHP_URL_SCHEME));
    return in_array($scheme, ['http', 'https'], true) ? $u : null;
}

/** Turn a YouTube link into an embeddable URL, or return null. */
function youtube_embed(string $url): ?string
{
    if (preg_match('~(?:youtu\.be/|youtube\.com/(?:watch\?v=|embed/|shorts/))([A-Za-z0-9_-]{11})~', $url, $m)) {
        return 'https://www.youtube.com/embed/' . $m[1];
    }
    return null;
}
