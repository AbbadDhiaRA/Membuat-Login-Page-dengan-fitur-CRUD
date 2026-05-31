<?php
/**
 * Debug publik dimatikan.
 * File debug yang mencetak cookie/session/input dapat membocorkan data sensitif.
 */
require_once __DIR__ . '/includes/helpers.php';

flash('warning', 'Halaman debug dinonaktifkan demi keamanan.');
redirect('login/login.php');
