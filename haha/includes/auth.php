<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/helpers.php';

const REMEMBER_COOKIE = 'haha_remember';

/**
 * Mencari user berdasarkan username dengan prepared statement.
 * Prepared statement menutup celah SQL Injection.
 */
function find_user_by_username(string $username): ?array
{
    $stmt = db()->prepare('SELECT id, username, password, cookie FROM user WHERE username = ? LIMIT 1');
    $stmt->bind_param('s', $username);
    $stmt->execute();

    $user = $stmt->get_result()->fetch_assoc();

    return $user ?: null;
}

/**
 * Mencari user berdasarkan id untuk fitur remember me.
 */
function find_user_by_id(int $id): ?array
{
    $stmt = db()->prepare('SELECT id, username, password, cookie FROM user WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $id);
    $stmt->execute();

    $user = $stmt->get_result()->fetch_assoc();

    return $user ?: null;
}

/**
 * Menyimpan hash token remember me, bukan token asli.
 * Jika database bocor, token cookie user tetap tidak langsung bisa dipakai.
 */
function store_remember_token(int $userId, string $token): void
{
    $tokenHash = hash('sha256', $token);
    $stmt = db()->prepare('UPDATE user SET cookie = ? WHERE id = ?');
    $stmt->bind_param('si', $tokenHash, $userId);
    $stmt->execute();
}

/**
 * Menghapus token remember me dari database saat logout.
 */
function clear_remember_token(int $userId): void
{
    $empty = '';
    $stmt = db()->prepare('UPDATE user SET cookie = ? WHERE id = ?');
    $stmt->bind_param('si', $empty, $userId);
    $stmt->execute();
}

/**
 * Membuat session login setelah password benar.
 * session_regenerate_id mencegah session fixation.
 */
function login_user(array $user, bool $remember): void
{
    start_secure_session();
    session_regenerate_id(true);

    $_SESSION['login'] = true;
    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['username'] = $user['username'];

    if ($remember) {
        $token = bin2hex(random_bytes(32));
        store_remember_token((int) $user['id'], $token);

        setcookie(REMEMBER_COOKIE, $user['id'] . ':' . $token, [
            'expires' => time() + 60 * 60 * 24 * 30,
            'path' => '/',
            'secure' => !empty($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }
}

/**
 * Login memakai username dan password.
 * Password dicek memakai password_verify karena register sudah memakai password_hash.
 */
function attempt_login(string $username, string $password, bool $remember): bool
{
    $user = find_user_by_username($username);

    if (!$user || !password_verify($password, $user['password'])) {
        return false;
    }

    login_user($user, $remember);

    return true;
}

/**
 * Membaca cookie remember me dan mengubahnya menjadi session login yang valid.
 */
function login_from_remember_cookie(): void
{
    start_secure_session();

    if (!empty($_SESSION['login']) || empty($_COOKIE[REMEMBER_COOKIE])) {
        return;
    }

    $parts = explode(':', $_COOKIE[REMEMBER_COOKIE], 2);
    $userId = get_positive_int($parts[0] ?? null);
    $token = $parts[1] ?? '';

    if (!$userId || $token === '') {
        return;
    }

    $user = find_user_by_id($userId);
    $tokenHash = hash('sha256', $token);

    if ($user && !empty($user['cookie']) && hash_equals($user['cookie'], $tokenHash)) {
        login_user($user, true);
    }
}

/**
 * Proteksi halaman yang wajib login.
 */
function require_login(): void
{
    login_from_remember_cookie();

    if (empty($_SESSION['login'])) {
        flash('warning', 'Silakan login terlebih dahulu.');
        redirect('../login/login.php');
    }
}

/**
 * Logout bersih: hapus token remember me, cookie, dan session.
 */
function logout_user(): void
{
    start_secure_session();

    if (!empty($_SESSION['user_id'])) {
        clear_remember_token((int) $_SESSION['user_id']);
    }

    setcookie(REMEMBER_COOKIE, '', [
        'expires' => time() - 3600,
        'path' => '/',
        'secure' => !empty($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'] ?? '', $params['secure'], $params['httponly']);
    }

    session_destroy();
}

/**
 * Registrasi user baru dengan validasi dasar dan pesan error yang jelas.
 */
function register_user(array $input): array
{
    $username = strtolower(trim((string) ($input['username'] ?? '')));
    $password = (string) ($input['password'] ?? '');
    $confirmPassword = (string) ($input['confirmPassword'] ?? '');

    if (!preg_match('/^[a-z0-9_]{3,50}$/', $username)) {
        return ['success' => false, 'message' => 'Username harus 3-50 karakter dan hanya boleh huruf, angka, atau underscore.'];
    }

    if (strlen($password) < 6) {
        return ['success' => false, 'message' => 'Password minimal 6 karakter.'];
    }

    if ($password !== $confirmPassword) {
        return ['success' => false, 'message' => 'Konfirmasi password tidak sama.'];
    }

    if (find_user_by_username($username)) {
        return ['success' => false, 'message' => 'Username sudah digunakan.'];
    }

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $emptyCookie = '';
    $stmt = db()->prepare('INSERT INTO user (username, password, cookie) VALUES (?, ?, ?)');
    $stmt->bind_param('sss', $username, $passwordHash, $emptyCookie);
    $stmt->execute();

    return ['success' => true, 'message' => 'Akun berhasil dibuat. Silakan login.'];
}
