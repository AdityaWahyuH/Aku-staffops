# Skenario uji yang disarankan dari hasil bimbingan

| ID | Skenario | Hasil yang diharapkan |
|---|---|---|
| BB-01 | Login admin benar/salah | Admin masuk; data salah ditolak |
| BB-02 | Login staf akses URL admin | Ditolak 403 |
| BB-03 | Tambah dan edit staf | Perubahan tersimpan MySQL |
| BB-04 | Ubah status staf tiap tanggal | Hanya status tanggal terpilih yang berubah |
| BB-05 | Staf belum dilaporkan pada tanggal shift | Tidak dipilih generator |
| BB-06 | Jumlah tersedia 2, kebutuhan 3 | Dua draft dibuat, peringatan kekurangan 1 |
| BB-07 | 2 lokasi, hanya 1 lokasi kekurangan | Generator tetap membuat draft untuk lokasi lain |
| BB-08 | Generate dua kali tanggal sama | Tidak menambah penugasan untuk posisi yang sudah terisi |
| BB-09 | Staf terjadwal 07.00–15.00 lalu ditugaskan 12.00–20.00 | Penugasan kedua ditolak |
| BB-10 | Staf terjadwal 07.00–15.00 lalu shift 15.00–23.00 | Tidak ditolak semata karena benturan waktu; periksa aturan istirahat perusahaan |
| BB-11 | Shift 23.00–07.00, penugasan tanggal berikutnya 06.00–14.00 | Ditolak karena benturan lintas tanggal |
| BB-12 | Edit draft ganti ke staf berhalangan | Ditolak |
| BB-13 | Pilih 10 draft lalu publikasi massal | Seluruh 10 draft valid menjadi published; status dapat terlihat oleh staf |
| BB-13A | Publikasi semua draft tanggal terpilih untuk 300 staf di beberapa halaman | Semua draft yang valid dipublikasikan dalam satu aksi; pagination tidak membatasi cakupan |
| BB-14 | Pilih draft dengan status ketersediaan sudah berubah menjadi izin | Publikasi batch ditolak; admin harus meninjau ulang |
| BB-15 | Ekspor jadwal publikasi | Hanya published, diurutkan per lokasi/shift/nama |
| BB-16 | Staf mencari nama di file PDF | Nama, shift, lokasi, jam mudah ditemukan |
| BB-17 | Laporan 300 staf | Pagination, pencarian nama, filter dan CSV tetap mudah dipakai |
| BB-18 | Cetak laporan rentang tanggal | Data rekap sesuai periode terfilter |

## Data uji dan pembahasan stakeholder

Dosen mengarahkan agar kasus kekurangan staf tidak menghentikan keseluruhan generate. Langkah penanganan kekurangan personel tetap memerlukan kesepakatan bersama owner/koordinator. Pertanyaan yang perlu dibawa:

1. Apakah satu orang boleh bekerja dua shift dalam satu hari jika jam tidak bertabrakan? Bagaimana jeda minimum?
2. Apakah ada batas jam kerja mingguan, ketentuan lembur, atau larangan shift malam beruntun?
3. Bagaimana staf bisa berpindah dari lokasi A ke B? Apakah ada durasi perjalanan minimum?
4. Jika kebutuhan kurang satu orang, siapa yang memutuskan penanganannya, dan apa langkah resmi?
5. Siapa yang berwenang menekan publikasi massal; apakah perlu persetujuan owner dahulu?
6. Apa bentuk jadwal yang paling mudah dibaca staf di WhatsApp: PDF per tanggal, per lokasi, atau personal?
7. Metrik apa yang ingin owner pantau: shortage, ketidakhadiran, distribusi shift, lembur, atau durasi pembuatan jadwal?
8. Apakah WhatsApp akan tetap digunakan secara manual atau perlu sistem notifikasi otomatis?

## Metrik evaluasi skripsi

- Waktu penyusunan jadwal (menit) untuk skenario sebelum/sesudah yang sebanding.
- Jumlah benturan jadwal yang lolos validasi (target: nol dalam skenario uji).
- Tingkat pemenuhan kebutuhan: `posisi terisi / posisi dibutuhkan × 100%`.
- Jumlah posisi yang kekurangan staf per lokasi/shift.
- Distribusi shift antarstaf (misalnya rentang jumlah shift atau simpangan baku jika diukur).
- Skor penerimaan pengguna (UAT) berdasarkan tugas yang dikerjakan.
