<?php
/**
 * Debug upload dimatikan agar detail file upload tidak tampil ke user publik.
 */
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

require_login();
flash('warning', 'Halaman debug dinonaktifkan demi keamanan.');
redirect('index.php');
