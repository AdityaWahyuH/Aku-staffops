# AKU Shift
### Sistem Monitoring Ketersediaan dan Penjadwalan Shift Cleaning Service

**PT Artha Kembar Utama (AKU)**

AKU Shift merupakan aplikasi berbasis web yang dikembangkan untuk membantu proses monitoring ketersediaan staf cleaning service dan penyusunan jadwal kerja berdasarkan kebutuhan personel pada setiap lokasi dan shift.

Sistem ini dikembangkan sebagai proyek Capstone/Skripsi dengan menggunakan metode pengembangan perangkat lunak **Prototyping**.

## 1. Tujuan Pengembangan

Pengembangan AKU Shift bertujuan untuk:

- Mempermudah pengelolaan data staf cleaning service.
- Memantau ketersediaan staf berdasarkan tanggal.
- Membantu penyusunan jadwal shift melalui rekomendasi otomatis.
- Mengurangi risiko benturan jadwal penugasan.
- Menampilkan informasi kekurangan personel.
- Mempermudah publikasi dan distribusi jadwal kerja.
- Menyediakan laporan sebagai pendukung monitoring operasional.

## 2. Teknologi yang Digunakan

| Teknologi | Fungsi |
|---|---|
| Laravel 12 | Framework backend |
| PHP | Bahasa pemrograman backend |
| MySQL | Database |
| Blade | Template antarmuka Laravel |
| HTML, CSS, JavaScript | Pengembangan antarmuka |
| XAMPP | Server database lokal |
| Visual Studio Code | Code editor |
| Git & GitHub | Version control dan kolaborasi |

## 3. Fitur Sistem

Modul yang telah disiapkan dalam pengembangan awal:

1. Login dan autentikasi pengguna.
2. Dashboard admin operasional.
3. Manajemen data staf.
4. Monitoring ketersediaan harian.
5. Manajemen lokasi kerja.
6. Manajemen definisi shift.
7. Pengaturan kebutuhan personel.
8. Generate rekomendasi jadwal otomatis.
9. Pemeriksaan konflik jadwal dan kekurangan staf.
10. Publikasi jadwal.
11. Ekspor jadwal kerja.
12. Laporan penjadwalan.
13. Portal jadwal pribadi staf.

**Catatan:** Implementasi awal telah dibuat, tetapi setiap modul masih memerlukan pengujian dan penyempurnaan.

## 4. Cara Instalasi

### Persyaratan

- PHP 8.2 atau lebih baru
- Composer
- MySQL
- XAMPP atau lingkungan server lokal sejenis
- Git

### Langkah Instalasi

**1. Clone repository**

```bash
git clone https://github.com/AdityaWahyuH/Aku-staffops.git
```

**2. Masuk ke folder proyek**

```bash
cd Aku-staffops
```

**3. Instal dependensi Laravel**

```bash
composer install
```

**4. Buat file konfigurasi environment**

Pada terminal PowerShell:

```powershell
Copy-Item .env.example .env
```

**5. Generate application key**

```bash
php artisan key:generate
```

**6. Konfigurasi database**

Buat database MySQL melalui phpMyAdmin dengan nama:

```text
aku_shift
```

Kemudian atur file `.env`:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=aku_shift
DB_USERNAME=root
DB_PASSWORD=
```

Sesuaikan username, password, dan port dengan konfigurasi MySQL masing-masing.

**7. Jalankan migration dan seeder**

```bash
php artisan migrate --seed
```

**8. Jalankan aplikasi**

```bash
php artisan serve
```

Akses aplikasi melalui:

http://127.0.0.1:8000

Jika menggunakan aset Vite, jalankan pula `npm install` dan `npm run dev` pada terminal terpisah.

## 5. Hak Akses Pengguna

**Admin Operasional**

Mengelola data staf, ketersediaan, lokasi kerja, shift, kebutuhan personel, proses penjadwalan, publikasi jadwal, dan laporan.

**Staf Cleaning Service**

Melihat jadwal pribadi yang telah dipublikasikan melalui akun masing-masing.

Fitur pembuatan dan pengelolaan akun untuk seluruh staf masih direncanakan untuk pengembangan berikutnya.

## 6. Metode Pengembangan

Penelitian menggunakan metode **Prototyping**, dengan tahapan:

1. Communication
2. Quick Plan
3. Modeling Quick Design
4. Construction of Prototype
5. Deployment, Delivery & Feedback

Pengembangan dilakukan secara bertahap berdasarkan hasil evaluasi dosen pembimbing dan kebutuhan stakeholder PT Artha Kembar Utama.

## 7. Tim Pengembang

Proyek ini dikembangkan secara berkelompok

## 8. Kolaborasi Pengembangan

Proyek menggunakan GitHub sebagai media kolaborasi.

Setiap anggota disarankan mengembangkan fitur melalui branch masing-masing dan menggunakan Pull Request sebelum perubahan digabungkan ke branch `main`.

## 9. Status Pengembangan

**Status: Dalam Pengembangan (Development)**

Aplikasi saat ini telah memiliki struktur Laravel, database MySQL, autentikasi awal, dan beberapa modul operasional.

Pengembangan berikutnya mencakup penyempurnaan manajemen akun staf, validasi penjadwalan, publikasi massal, ekspor jadwal, laporan, serta pengujian sistem.

Data yang digunakan pada tahap awal merupakan data demonstrasi dan bukan data operasional sebenarnya.
---

**AKU Shift — Monitoring Ketersediaan dan Penjadwalan Shift Cleaning Service**

PT Artha Kembar Utama
