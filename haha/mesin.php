<?php
/**
 * File kompatibilitas untuk kode lama.
 * Logic utama sudah dipisah ke config/includes agar lebih rapi dan mudah dirawat.
 */
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/student_repository.php';

$conn = db();

function select(string $sqlquery): array
{
    return db()->query($sqlquery)->fetch_all(MYSQLI_ASSOC);
}

function insert(array $inputPost, array $inputFiles): int
{
    $result = create_student($inputPost, $inputFiles);

    return $result['success'] ? 1 : 0;
}

function delete(int $inputGet): int
{
    return delete_student($inputGet) ? 1 : 0;
}

function search(array $inputPost): array
{
    return search_students((string) ($inputPost['search'] ?? ''));
}

function register(array $inputPost): int
{
    $result = register_user($inputPost);

    return $result['success'] ? 1 : 0;
}
