<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/helpers.php';

const UPLOAD_DIR = __DIR__ . '/../foto';
const MAX_UPLOAD_SIZE = 2000000;
const ALLOWED_IMAGE_TYPES = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
];

/**
 * Mengambil seluruh data mahasiswa dengan urutan stabil.
 */
function all_students(): array
{
    $result = db()->query('SELECT id, nama, nrp, jurusan, gambar FROM tabel_mahasiswa ORDER BY id ASC');

    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Mencari mahasiswa menggunakan LIKE yang tetap aman dari SQL Injection.
 */
function search_students(string $keyword): array
{
    $keyword = trim($keyword);

    if ($keyword === '') {
        return all_students();
    }

    $like = '%' . $keyword . '%';
    $stmt = db()->prepare(
        'SELECT id, nama, nrp, jurusan, gambar
         FROM tabel_mahasiswa
         WHERE nama LIKE ? OR nrp LIKE ? OR jurusan LIKE ?
         ORDER BY id ASC'
    );
    $stmt->bind_param('sss', $like, $like, $like);
    $stmt->execute();

    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

/**
 * Mengambil satu mahasiswa berdasarkan id.
 */
function find_student(int $id): ?array
{
    $stmt = db()->prepare('SELECT id, nama, nrp, jurusan, gambar FROM tabel_mahasiswa WHERE id = ? LIMIT 1');
    $stmt->bind_param('i', $id);
    $stmt->execute();

    $student = $stmt->get_result()->fetch_assoc();

    return $student ?: null;
}

/**
 * Membersihkan dan memvalidasi input form mahasiswa.
 */
function validate_student_input(array $input): array
{
    $nama = trim((string) ($input['nama'] ?? ''));
    $nrp = trim((string) ($input['nrp'] ?? ''));
    $jurusan = trim((string) ($input['jurusan'] ?? ''));
    $errors = [];

    if ($nama === '' || strlen($nama) > 100) {
        $errors[] = 'Nama wajib diisi dan maksimal 100 karakter.';
    }

    if ($nrp === '' || strlen($nrp) > 100) {
        $errors[] = 'NRP wajib diisi dan maksimal 100 karakter.';
    }

    if ($jurusan === '' || strlen($jurusan) > 100) {
        $errors[] = 'Jurusan wajib diisi dan maksimal 100 karakter.';
    }

    return [
        'data' => [
            'nama' => $nama,
            'nrp' => $nrp,
            'jurusan' => $jurusan,
        ],
        'errors' => $errors,
    ];
}

/**
 * Upload foto dengan validasi error, ukuran, MIME type, dan nama file acak.
 */
function upload_photo(array $file, bool $required = true): array
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return $required
            ? ['success' => false, 'message' => 'Foto wajib diisi.', 'filename' => null]
            : ['success' => true, 'message' => '', 'filename' => null];
    }

    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Upload foto gagal. Coba pilih file lain.', 'filename' => null];
    }

    if (($file['size'] ?? 0) > MAX_UPLOAD_SIZE) {
        return ['success' => false, 'message' => 'Ukuran foto maksimal 2MB.', 'filename' => null];
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);

    if (!isset(ALLOWED_IMAGE_TYPES[$mimeType])) {
        return ['success' => false, 'message' => 'Format foto harus JPG, JPEG, atau PNG.', 'filename' => null];
    }

    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0775, true);
    }

    $filename = bin2hex(random_bytes(16)) . '.' . ALLOWED_IMAGE_TYPES[$mimeType];
    $target = UPLOAD_DIR . DIRECTORY_SEPARATOR . $filename;

    if (!move_uploaded_file($file['tmp_name'], $target)) {
        return ['success' => false, 'message' => 'Foto gagal disimpan ke folder upload.', 'filename' => null];
    }

    return ['success' => true, 'message' => '', 'filename' => $filename];
}

/**
 * Menambah mahasiswa baru.
 */
function create_student(array $input, array $files): array
{
    $validated = validate_student_input($input);

    if ($validated['errors']) {
        return ['success' => false, 'message' => implode(' ', $validated['errors'])];
    }

    $upload = upload_photo($files['foto'] ?? [], true);

    if (!$upload['success']) {
        return ['success' => false, 'message' => $upload['message']];
    }

    $data = $validated['data'];
    $stmt = db()->prepare('INSERT INTO tabel_mahasiswa (nama, nrp, jurusan, gambar) VALUES (?, ?, ?, ?)');
    $stmt->bind_param('ssss', $data['nama'], $data['nrp'], $data['jurusan'], $upload['filename']);
    $stmt->execute();

    return ['success' => true, 'message' => 'Data mahasiswa berhasil ditambahkan.'];
}

/**
 * Mengubah mahasiswa. Foto lama dipertahankan jika user tidak upload foto baru.
 */
function update_student(int $id, array $input, array $files): array
{
    $student = find_student($id);

    if (!$student) {
        return ['success' => false, 'message' => 'Data mahasiswa tidak ditemukan.'];
    }

    $validated = validate_student_input($input);

    if ($validated['errors']) {
        return ['success' => false, 'message' => implode(' ', $validated['errors'])];
    }

    $upload = upload_photo($files['foto'] ?? [], false);

    if (!$upload['success']) {
        return ['success' => false, 'message' => $upload['message']];
    }

    $gambar = $upload['filename'] ?: $student['gambar'];
    $data = $validated['data'];

    $stmt = db()->prepare('UPDATE tabel_mahasiswa SET nama = ?, nrp = ?, jurusan = ?, gambar = ? WHERE id = ?');
    $stmt->bind_param('ssssi', $data['nama'], $data['nrp'], $data['jurusan'], $gambar, $id);
    $stmt->execute();

    return ['success' => true, 'message' => 'Data mahasiswa berhasil diperbarui.'];
}

/**
 * Menghapus mahasiswa berdasarkan id.
 */
function delete_student(int $id): bool
{
    $stmt = db()->prepare('DELETE FROM tabel_mahasiswa WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();

    return $stmt->affected_rows > 0;
}

/**
 * Menentukan URL foto dari halaman daftar_mahasiswa.
 */
function student_photo_url(?string $filename): ?string
{
    if (!$filename) {
        return null;
    }

    $path = UPLOAD_DIR . DIRECTORY_SEPARATOR . $filename;

    return is_file($path) ? '../foto/' . rawurlencode($filename) : null;
}
