<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/student_repository.php';
require_once __DIR__ . '/../includes/helpers.php';

require_login();

$keyword = trim((string) ($_GET['search'] ?? ''));
$mahasiswa = $keyword === '' ? all_students() : search_students($keyword);
$flash = consume_flash();
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar Mahasiswa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css?v=3">
</head>
<body>
<nav class="navbar navbar-expand-lg app-navbar">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">CRUD Mahasiswa</a>
        <div class="d-flex align-items-center gap-2">
            <span class="navbar-text d-none d-sm-inline">Halo, <?= e($_SESSION['username'] ?? 'User') ?></span>
            <a class="btn btn-outline-secondary btn-sm" href="logout.php">Logout</a>
        </div>
    </div>
</nav>

<main class="container py-4">
    <div class="toolbar">
        <div>
            <h1>Daftar Mahasiswa</h1>
            <p class="text-muted mb-0">Kelola data mahasiswa, jurusan, NRP, dan foto.</p>
        </div>
        <a class="btn btn-primary" href="insert.php">Tambah Mahasiswa</a>
    </div>

    <?php if ($flash): ?>
        <div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
    <?php endif; ?>

    <form class="search-card" method="get">
        <label class="form-label" for="search">Cari data</label>
        <div class="input-group">
            <input class="form-control" type="search" name="search" id="search" value="<?= e($keyword) ?>" placeholder="Cari nama, NRP, atau jurusan">
            <button class="btn btn-outline-primary" type="submit">Cari</button>
            <?php if ($keyword !== ''): ?>
                <a class="btn btn-outline-secondary" href="index.php">Reset</a>
            <?php endif; ?>
        </div>
    </form>

    <div class="table-wrap">
        <table class="table align-middle mb-0">
            <thead>
            <tr>
                <th>No</th>
                <th>Foto</th>
                <th>Nama</th>
                <th>NRP</th>
                <th>Jurusan</th>
                <th class="text-end">Aksi</th>
            </tr>
            </thead>
            <tbody>
            <?php if (!$mahasiswa): ?>
                <tr>
                    <td colspan="6" class="text-center text-muted py-5">Data mahasiswa belum ada.</td>
                </tr>
            <?php endif; ?>

            <?php foreach ($mahasiswa as $index => $mhs): ?>
                <?php $photoUrl = student_photo_url($mhs['gambar'] ?? null); ?>
                <tr>
                    <td><?= $index + 1 ?></td>
                    <td class="photo-cell">
                        <?php if ($photoUrl): ?>
                            <img class="avatar" src="<?= e($photoUrl) ?>" alt="Foto <?= e($mhs['nama']) ?>">
                        <?php else: ?>
                            <span class="avatar avatar-empty">-</span>
                        <?php endif; ?>
                    </td>
                    <td class="fw-semibold"><?= e($mhs['nama']) ?></td>
                    <td><?= e($mhs['nrp']) ?></td>
                    <td><?= e($mhs['jurusan']) ?></td>
                    <td class="text-end">
                        <a class="btn btn-sm btn-outline-primary" href="update.php?id=<?= e((string) $mhs['id']) ?>">Edit</a>
                        <form class="d-inline" action="delete.php" method="post" onsubmit="return confirm('Yakin ingin menghapus data ini?');">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= e((string) $mhs['id']) ?>">
                            <button class="btn btn-sm btn-outline-danger" type="submit">Hapus</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</main>
</body>
</html>
