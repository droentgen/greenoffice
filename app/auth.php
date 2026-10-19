<?php
/**
 * Admin login. Passwords are stored with password_hash() (bcrypt).
 * Roles: admin (everything), editor (content, documents, news), staff (statistics + reports), auditor (reports + audits).
 */

const ROLES = [
    'admin'   => 'ผู้ดูแลระบบ',
    'editor'  => 'ผู้จัดการเนื้อหา',
    'staff'   => 'ผู้กรอกข้อมูล',
    'auditor' => 'ผู้ตรวจประเมิน',
];

function current_user(): ?array
{
    static $user = false;
    if ($user === false) {
        $user = null;
        if (!empty($_SESSION['uid'])) {
            $user = row('SELECT id, username, display_name, role FROM users WHERE id = ? AND is_active = 1', [$_SESSION['uid']]);
        }
    }
    return $user;
}

function attempt_login(string $username, string $password): bool
{
    // Slow down guessing: after 5 wrong tries, wait 60 seconds.
    $fails = $_SESSION['login_fails'] ?? 0;
    $lockedUntil = $_SESSION['login_locked_until'] ?? 0;
    if ($lockedUntil > time()) {
        return false;
    }
    $u = row('SELECT id, password_hash FROM users WHERE username = ? AND is_active = 1', [$username]);
    if ($u && password_verify($password, $u['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['uid'] = (int)$u['id'];
        unset($_SESSION['login_fails'], $_SESSION['login_locked_until']);
        q('UPDATE users SET last_login = NOW() WHERE id = ?', [$u['id']]);
        if (password_needs_rehash($u['password_hash'], PASSWORD_DEFAULT)) {
            q('UPDATE users SET password_hash = ? WHERE id = ?', [password_hash($password, PASSWORD_DEFAULT), $u['id']]);
        }
        return true;
    }
    $_SESSION['login_fails'] = $fails + 1;
    if ($_SESSION['login_fails'] >= 5) {
        $_SESSION['login_locked_until'] = time() + 60;
        $_SESSION['login_fails'] = 0;
    }
    return false;
}

function login_locked_seconds(): int
{
    return max(0, ($_SESSION['login_locked_until'] ?? 0) - time());
}

function logout(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 3600, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

/** Put at the top of every admin page. Pass roles to restrict a page, e.g. require_login(['admin']). */
function require_login(array $roles = []): array
{
    $u = current_user();
    if (!$u) {
        redirect('admin/login.php');
    }
    if ($roles && $u['role'] !== 'admin' && !in_array($u['role'], $roles, true)) {
        http_response_code(403);
        exit('บัญชีนี้ไม่มีสิทธิ์เข้าหน้านี้');
    }
    return $u;
}
