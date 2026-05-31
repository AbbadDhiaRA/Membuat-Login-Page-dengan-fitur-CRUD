-- Rekomendasi perbaikan schema untuk database `mahasigma`.
-- Jalankan setelah memastikan tidak ada data duplikat yang melanggar constraint.

ALTER TABLE `user`
    MODIFY `username` varchar(50) NOT NULL,
    MODIFY `password` varchar(255) NOT NULL,
    MODIFY `cookie` varchar(255) NOT NULL DEFAULT '',
    ADD UNIQUE KEY `uniq_user_username` (`username`);

ALTER TABLE `tabel_mahasiswa`
    MODIFY `nama` varchar(100) NOT NULL,
    MODIFY `nrp` varchar(100) NOT NULL,
    MODIFY `jurusan` varchar(100) NOT NULL,
    MODIFY `gambar` varchar(100) NULL DEFAULT NULL;

-- Catatan:
-- NRP pada database saat audit masih memiliki beberapa duplikasi.
-- Jika NRP harus unik, bersihkan data duplikat dulu lalu aktifkan constraint berikut:
-- ALTER TABLE `tabel_mahasiswa` ADD UNIQUE KEY `uniq_mahasiswa_nrp` (`nrp`);
