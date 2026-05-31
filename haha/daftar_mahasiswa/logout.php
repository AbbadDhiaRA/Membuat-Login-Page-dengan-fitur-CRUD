<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

logout_user();
flash('success', 'Logout berhasil.');
redirect('../login/login.php');
