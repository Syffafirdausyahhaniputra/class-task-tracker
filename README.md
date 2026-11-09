# Class Task Tracker

Class Task Tracker adalah aplikasi sederhana untuk guru mengelola tugas kelas dan siswa melihat serta menandai tugasnya sebagai selesai. Aplikasi menyediakan REST API dan antarmuka web responsif.

## Teknologi

- PHP 8.3 atau lebih baru (sesuai kebutuhan Laravel 13 pada proyek ini)
- Laravel 13
- MySQL
- Laravel Sanctum untuk token API
- Blade, JavaScript biasa, dan CSS biasa
- Composer untuk mengelola dependency PHP

## Menjalankan dari awal

### 1. Pasang kebutuhan

Pasang PHP 8.3+, Composer, dan MySQL Server. Di Windows, Laragon dapat menyediakan PHP, Composer, dan MySQL dalam satu lingkungan. Pastikan ekstensi PHP `pdo_mysql` aktif.

Periksa instalasi:

```powershell
php -v
composer -V
```

### 2. Buat database MySQL

Jalankan MySQL, lalu buat database. Contoh melalui MySQL CLI:

```sql
CREATE DATABASE class_task_tracker
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
```

### 3. Pasang dependency dan siapkan konfigurasi

Dari direktori proyek:

```powershell
Copy-Item .env.example .env
composer install
php artisan key:generate
```

Jika menggunakan Git Bash atau shell yang mendukung `cp`, perintah penyalinan dapat ditulis `cp .env.example .env`.

Buka `.env` dan sesuaikan koneksi database dengan MySQL lokal:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=class_task_tracker
DB_USERNAME=root
DB_PASSWORD=
```

Ubah nama pengguna atau password bila konfigurasi MySQL Anda berbeda. Jangan commit `.env`; gunakan `.env.example` sebagai template dan isi `APP_DEBUG=false` pada lingkungan production.

### 4. Buat tabel dan akun contoh

```powershell
php artisan migrate --seed
```

### 5. Jalankan aplikasi

```powershell
php artisan serve
```

Buka [http://127.0.0.1:8000](http://127.0.0.1:8000).

## Akun contoh

Semua akun contoh menggunakan password `Password123`.

| Peran | Email |
|---|---|
| Guru | `guru@sekolah.test` |
| Siswa | `siswa1@sekolah.test`, `siswa2@sekolah.test`, `siswa3@sekolah.test` |

Pendaftaran publik hanya membuat akun siswa. Akun guru dibuat melalui seeder atau proses tepercaya, bukan dengan memilih peran saat mendaftar.

## Endpoint API

Semua endpoint berada di prefix `/api`. Endpoint yang memerlukan login memakai header `Authorization: Bearer <token>` dan `Accept: application/json`.

| Method | Endpoint | Akses | Keterangan |
|---|---|---|---|
| POST | `/api/auth/register` | Publik, dibatasi rate limit | Daftar akun siswa |
| POST | `/api/auth/login` | Publik, dibatasi rate limit | Login dan memperoleh token |
| POST | `/api/auth/logout` | Login | Cabut token yang sedang digunakan |
| GET | `/api/auth/me` | Login | Data pengguna yang login |
| GET | `/api/classes` | Publik | Daftar kelas untuk pendaftaran |
| GET | `/api/tasks` | Login | Daftar tugas; mendukung `search`, `status`, `page`, dan `per_page` |
| GET | `/api/tasks/upcoming-deadlines` | Login | Tugas yang tenggatnya dalam tiga hari |
| GET | `/api/tasks/{task}` | Login | Detail tugas sesuai batas akses pengguna |
| POST | `/api/tasks` | Guru | Buat tugas |
| PUT | `/api/tasks/{task}` | Guru pemilik tugas | Ubah tugas |
| DELETE | `/api/tasks/{task}` | Guru pemilik tugas | Hapus tugas |
| POST | `/api/tasks/{task}/complete` | Siswa di kelas tugas | Tandai tugas selesai |
| DELETE | `/api/tasks/{task}/complete` | Siswa di kelas tugas | Batalkan status selesai |

Siswa hanya melihat tugas kelasnya. Guru hanya melihat tugas yang dibuatnya. Pada daftar tugas siswa, `status=done` dan `status=pending` memfilter penyelesaian milik siswa tersebut. Jumlah tugas per halaman bawaan adalah 10 dan nilai `per_page` dibatasi maksimal 50.

Respons sukses menggunakan bentuk `{ "data": ... }`; daftar berpaginasi juga berisi `meta` dan `links`. Error API menggunakan `{ "message": "...", "errors": {...} }`, dengan `errors` disertakan jika relevan. Validasi mengembalikan 422, autentikasi 401, otorisasi 403, dan resource tidak ditemukan 404.

## Keputusan desain

- Status selesai disimpan di `task_completions`, satu baris per pasangan tugas dan siswa. Tabel `tasks` tidak menyimpan status selesai global karena setiap siswa dapat memiliki progres sendiri.
- Laravel Sanctum menerbitkan token bearer untuk autentikasi API. Logout mencabut token yang sedang dipakai.
- Validasi input dan sebagian otorisasi operasi menggunakan Form Request. API Resource menjaga bentuk data respons dan mencegah password masuk ke respons pengguna.
- Error API diformat terpusat di `bootstrap/app.php` agar tidak mengirim detail exception atau stack trace.
- Token frontend disimpan di `localStorage` sebagai pendekatan sederhana untuk aplikasi satu domain. JavaScript menambahkan token ke request API; keamanan sebenarnya tetap ditegakkan di backend. Jika terjadi XSS, token di `localStorage` dapat dibaca oleh script berbahaya; cookie `httpOnly`, `Secure`, dan `SameSite` merupakan alternatif yang lebih aman.
- Nilai tenggat dari `datetime-local` dikonversi dari zona waktu lokal browser ke UTC sebelum dikirim. Waktu API dikonversi kembali ke waktu lokal saat ditampilkan atau diedit.

## Penggunaan AI

### Ringkasan tahap pengerjaan

- Tahap 1: menyiapkan Sanctum, konfigurasi MySQL, `.env.example`, `.gitignore`, dan middleware role.
- Tahap 2: membuat model, migrasi, factory, seeder, relasi, serta index database.
- Tahap 3: membuat API autentikasi dan daftar kelas, Form Request, rate limit, serta format error API.
- Tahap 4: membuat detail dan operasi CRUD tugas dengan middleware role, Policy, Form Request, dan API Resource.
- Tahap 5: menambahkan pencarian, filter status, pagination, serta fitur siswa menandai dan membatalkan penyelesaian tugas.
- Tahap 6: membuat halaman Blade untuk login, pendaftaran, dan dashboard, beserta interaksi API melalui JavaScript.
- Tahap 7: merapikan tampilan responsif dan menyiapkan koleksi Postman.
- Tahap 8: mengaudit keamanan, membatasi pendaftaran publik hanya untuk siswa, dan melengkapi dokumentasi proyek.

### Bagian yang dibantu AI

AI membantu implementasi dan pemeriksaan kode pada tahap 1–8, termasuk backend Laravel, API, autentikasi, validasi, antarmuka, responsivitas, audit keamanan, dan dokumentasi. Menampilkan tugas yang jatuh tempo dalam tiga hari; daftar tugas dibatasi sesuai akses pengguna, dan siswa hanya diberi tahu tentang tugas yang belum selesai.

### Yang saya ubah/periksa sendiri

- Saya mencoba mengubah jumlah tugas per halaman menjadi 5 di controller dan JavaScript, lalu memeriksa perilaku pagination saat ada 6 tugas. Saya melihat bahwa JavaScript mengirim nilai `per_page` ke API sehingga perubahan di frontend memengaruhi hasil yang tampil.
- Saya memeriksa waktu pada formulir tenggat dan menemukan bahwa nilai `datetime-local` tampak berbeda dari waktu setempat yang saya harapkan. Saya meminta agar alur waktu tersebut diperiksa dan disesuaikan.
- Saya memeriksa kurang adanya pemberitahuan mengenai tenggat tugas yang mendekati datetime tenggat.

## Belum selesai / rencana perbaikan

- Belum ada feature test otomatis untuk endpoint API dan alur antarmuka.
- Belum ada Docker atau konfigurasi deployment.
- Token di `localStorage` lebih rentan terhadap pencurian melalui XSS dibanding cookie `httpOnly`; mempertimbangkan perubahan mekanisme autentikasi sebelum penggunaan production.
- Pendaftaran akun guru dinonaktifkan untuk publik; akun guru perlu dibuat melalui proses tepercaya.
