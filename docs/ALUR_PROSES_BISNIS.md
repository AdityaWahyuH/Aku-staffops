# Keterkaitan notulensi bimbingan dengan kode

| Hasil bimbingan | Implementasi yang disiapkan | Lokasi source |
|---|---|---|
| Laravel sejak awal | Routing MVC, migration, model, controller dan Blade | `routes`, `app`, `database`, `resources/views` |
| Data staf beda dengan ketersediaan harian | Tabel `staff` vs `availabilities` menurut tanggal | `StaffController`, `AvailabilityController` |
| Staf kurang tidak membatalkan generate | Partial assignment + `shortage_count` + catatan shortage | `SchedulingService::generate()` |
| Validasi staf tak bisa double-booking pada jam sama | Perbandingan interval tanggal+jam, termasuk shift lintas tengah malam | `SchedulingService::bounds()/overlaps()/hasConflict()` |
| Publikasi massal | Pilih checkbox draft atau seluruh draft pada tanggal terpilih, satu POST atomik yang memvalidasi ulang | `ScheduleController::publishSelected()` |
| Ekspor nyaman dibaca staf | Tampilan grouped per lokasi → shift → nama, cetak A4 landscape/PDF browser | `schedules/print.blade.php` |
| Laporan untuk ratusan staf | Filter, pencarian nama/kode, pagination 15 orang, ekspor CSV | `ReportController`, `reports/index.blade.php` |
| Dua peran pengguna | Middleware `role` dan portal jadwal staf | `EnsureRole`, `StaffPortalController` |

## Kode yang paling relevan untuk dipahami saat bimbingan

**Pertanyaan: Mengapa staf tidak bisa dipakai ulang jika jamnya sama?**

- Pada `SchedulingService::bounds()`, waktu mulai/selesai dibuat sebagai interval tanggal dan jam.
- Pada `overlaps()`, dua interval dianggap bertabrakan jika awal A lebih kecil dari akhir B **dan** awal B lebih kecil dari akhir A.
- Pada `hasConflict()`, sistem memeriksa seluruh jadwal draft atau published staf. Jam selesai sama dengan jam mulai shift berikutnya tidak dianggap benturan.

**Pertanyaan: Apa yang dilakukan saat kekurangan staf?**

- `generate()` memeriksa setiap kebutuhan lokasi dan shift.
- Jika kandidat tidak ditemukan, sistem mencatat selisihnya ke `shortage_count`, lalu berlanjut ke kebutuhan berikutnya.
- Setiap staf yang sudah berhasil ditempatkan disimpan sebagai draft melalui transaksi database.

**Pertanyaan: Mengapa publikasi dilakukan massal?**

- Admin memilih sejumlah draft dalam satu halaman.
- Server memuat penugasan, mengunci record terkait, memeriksa ulang status serta konflik, lalu mengubah seluruh draft terpilih menjadi published.
- Jika ada satu draft yang tidak valid, transaksi dibatalkan agar tidak terjadi publikasi setengah-setengah. Admin harus memperbaiki draft lalu mengulanginya.

## Catatan desain dan konsultasi

Rancangan ini merupakan implementasi awal dari notulensi, bukan ketetapan akhir perusahaan. Kebutuhan spesifik owner untuk laporan, kriteria pembagian shift, durasi istirahat, serta prosedur saat shortage belum dipastikan dalam notulensi dan harus ditanyakan. Jangan mengubah keterbatasan tersebut menjadi klaim hasil penelitian tanpa pengujian.
