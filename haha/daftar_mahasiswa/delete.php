<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/student_repository.php';
require_once __DIR__ . '/../includes/helpers.php';

require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    flash('warning', 'Aksi hapus harus melalui tombol hapus di tabel.');
    redirect('index.php');
}

if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
    flash('danger', 'Token keamanan tidak valid. Data tidak dihapus.');
    redirect('index.php');
}

$id = get_positive_int($_POST['id'] ?? null);

if (!$id) {
    flash('danger', 'ID mahasiswa tidak valid.');
    redirect('index.php');
}

if (delete_student($id)) {
    flash('success', 'Data mahasiswa berhasil dihapus.');
} else {
    flash('warning', 'Data mahasiswa tidak ditemukan atau sudah dihapus.');
}

redirect('index.php');
