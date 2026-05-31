<?php
/**
 * Helper umum untuk keamanan output, redirect, pesan flash, dan CSRF.
 * File ini sengaja kecil agar bisa dipakai oleh halaman login maupun CRUD.
 */

/**
 * Menyalakan session dengan opsi cookie yang lebih aman.
 * httponly mencegah JavaScript membaca cookie session.
 */
function start_secure_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => !empty($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();
}

/**
 * Escape output HTML untuk mencegah XSS saat menampilkan data dari database/user.
 */
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Redirect standar agar tidak ada output setelah header Location.
 */
function redirect(string $path): never
{
    header('Location: ' . $path);
    exit;
}

/**
 * Menyimpan pesan satu kali tampil setelah redirect.
 */
function flash(string $type, string $message): void
{
    start_secure_session();
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message,
    ];
}

/**
 * Mengambil lalu menghapus flash message agar tidak muncul berulang.
 */
function consume_flash(): ?array
{
    start_secure_session();

    if (!isset($_SESSION['flash'])) {
        return null;
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);

    return $flash;
}

/**
 * Membuat token CSRF untuk melindungi form POST dari request palsu lintas situs.
 */
function csrf_token(): string
{
    start_secure_session();

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

/**
 * Validasi token CSRF. Semua form yang mengubah data wajib melewati fungsi ini.
 */
function verify_csrf_token(?string $token): bool
{
    start_secure_session();

    return is_string($token)
        && isset($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Field hidden praktis untuk disisipkan di form HTML.
 */
function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

/**
 * Validasi id dari URL/form agar selalu integer positif.
 */
function get_positive_int(mixed $value): ?int
{
    $id = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

    return $id === false ? null : $id;
}
