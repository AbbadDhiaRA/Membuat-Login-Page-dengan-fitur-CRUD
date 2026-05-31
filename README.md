# Membuat Login Page dengan Fitur CRUD

## Deskripsi Project

Project ini merupakan implementasi sistem **Login Page** yang terintegrasi dengan fitur **CRUD (Create, Read, Update, Delete)** menggunakan PHP dan MySQL. Aplikasi ini dirancang untuk mensimulasikan proses autentikasi pengguna serta pengelolaan data secara aman dan terstruktur.

## Fitur Utama

### 1. Login System

Sistem login telah dilengkapi dengan beberapa mekanisme keamanan, antara lain:

* **Session Management**

  * Menggunakan session untuk menyimpan status login pengguna.
  * Membatasi akses ke halaman tertentu hanya untuk pengguna yang telah berhasil login.

* **Cookie (Remember Me)**

  * Menggunakan cookie untuk mempertahankan status autentikasi pengguna sesuai kebutuhan aplikasi.

* **Validasi Login**

  * Menampilkan pesan peringatan (warning message) apabila username atau password yang dimasukkan tidak sesuai.

* **Password Encryption**

  * Password disimpan dalam bentuk terenkripsi (hashed) sehingga tidak tersimpan sebagai plain text di database.
  * Verifikasi password dilakukan menggunakan mekanisme hash yang aman.

### 2. CRUD Data

Aplikasi menyediakan fitur CRUD lengkap untuk mengelola data, meliputi:

* Create (Menambah Data)
* Read (Menampilkan Data)
* Update (Mengubah Data)
* Delete (Menghapus Data)

### 3. Upload Gambar

Fitur upload gambar telah dilengkapi dengan sistem keamanan pada penamaan file.

* Setiap file gambar yang diunggah akan diberikan **nama unik berbasis hash**.
* Mencegah terjadinya konflik atau penimpaan (overwrite) ketika terdapat beberapa file dengan nama yang sama.
* Membantu menjaga integritas dan konsistensi data gambar yang tersimpan di server.

## Teknologi yang Digunakan

* PHP
* MySQL
* HTML
* CSS
* JavaScript
* XAMPP

## Tujuan Pembelajaran

Project ini dibuat untuk mempelajari dan mengimplementasikan:

* Konsep autentikasi pengguna.
* Penggunaan session dan cookie.
* Enkripsi password untuk keamanan data.
* Operasi CRUD menggunakan PHP dan MySQL.
* Upload file gambar yang aman.
* Pengelolaan data berbasis web.

## Catatan

Project ini dibuat sebagai media pembelajaran untuk memahami dasar pengembangan aplikasi web menggunakan PHP serta penerapan praktik keamanan dasar dalam sistem autentikasi dan manajemen data.
