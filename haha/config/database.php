<?php
/**
 * Konfigurasi database terpusat.
 * Jika nama host, user, password, atau database berubah, cukup ubah file ini saja.
 */
const DB_HOST = 'localhost';
const DB_USER = 'root';
const DB_PASS = '';
const DB_NAME = 'mahasigma';

/**
 * Membuat koneksi MySQLi dengan mode error exception.
 * Mode exception membuat bug query lebih cepat terlihat saat development.
 */
function db(): mysqli
{
    static $connection = null;

    if ($connection instanceof mysqli) {
        return $connection;
    }

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    $connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $connection->set_charset('utf8mb4');

    return $connection;
}
