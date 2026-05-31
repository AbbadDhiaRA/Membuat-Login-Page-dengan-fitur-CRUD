<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/student_repository.php';
require_once __DIR__ . '/../includes/helpers.php';

require_login();

$id = get_positive_int($_GET['id'] ?? null);

if (!$id) {
    flash('danger', 'ID mahasiswa tidak valid.');
    redirect('index.php');
}

$student = find_student($id);

if (!$student) {
    flash('danger', 'Data mahasiswa tidak ditemukan.');
    redirect('index.php');
}

$error = '';
$old = [
    'nama' => $student['nama'],
    'nrp' => $student['nrp'],
    'jurusan' => $student['jurusan'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old = [
        'nama' => (string) ($_POST['nama'] ?? ''),
        'nrp' => (string) ($_POST['nrp'] ?? ''),
        'jurusan' => (string) ($_POST['jurusan'] ?? ''),
    ];

    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        $error = 'Token keamanan tidak valid. Silakan coba lagi.';
    } else {
        $result = update_student($id, $_POST, $_FILES);

        if ($result['success']) {
            flash('success', $result['message']);
            redirect('index.php');
        }

        $error = $result['message'];
    }
}

$photoUrl = student_photo_url($student['gambar'] ?? null);
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Mahasiswa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<nav class="navbar app-navbar">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">CRUD Mahasiswa</a>
        <a class="btn btn-outline-secondary btn-sm" href="index.php">Kembali</a>
    </div>
</nav>

<main class="container py-4">
    <div class="form-shell">
        <h1>Edit Mahasiswa</h1>
        <p class="text-muted">Kosongkan foto jika ingin memakai foto lama.</p>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= e($error) ?></div>
        <?php endif; ?>

        <?php if ($photoUrl): ?>
            <div class="mb-3">
                <img class="preview-photo" src="<?= e($photoUrl) ?>" alt="Foto <?= e($student['nama']) ?>">
            </div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label" for="nama">Nama</label>
                <input class="form-control" type="text" name="nama" id="nama" value="<?= e($old['nama']) ?>" required maxlength="100">
            </div>
            <div class="mb-3">
                <label class="form-label" for="nrp">NRP</label>
                <input class="form-control" type="text" name="nrp" id="nrp" value="<?= e($old['nrp']) ?>" required maxlength="100">
            </div>
            <div class="mb-3">
                <label class="form-label" for="jurusan">Jurusan</label>
                <input class="form-control" type="text" name="jurusan" id="jurusan" value="<?= e($old['jurusan']) ?>" required maxlength="100">
            </div>
            <div class="mb-4">
                <label class="form-label" for="foto">Ganti foto</label>
                <input class="form-control" type="file" name="foto" id="foto" accept=".jpg,.jpeg,.png">
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-primary" type="submit">Simpan Perubahan</button>
                <a class="btn btn-outline-secondary" href="index.php">Batal</a>
            </div>
        </form>
    </div>
</main>
</body>
</html>
