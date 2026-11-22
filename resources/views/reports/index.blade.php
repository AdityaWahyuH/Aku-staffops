@extends('layouts.app')
@section('title','Laporan Penjadwalan')
@section('content')
<div class="heading"><div><p class="eyebrow">MONITORING DAN EVALUASI</p><h1>Laporan Penjadwalan</h1><p>Rekap berdasarkan periode, lokasi, status, dan nama staf. Tabel menggunakan pagination agar tetap mudah dipakai untuk ratusan staf.</p></div><a href="{{ route('reports.csv',$filters) }}" class="btn primary">↓ Ekspor Laporan CSV</a><a href="{{ route('reports.print',$filters) }}" class="btn" target="_blank">▤ Cetak / Simpan PDF</a></div>
<div class="panel"><form method="get" class="filters"><label class="inline">Dari <input type="date" name="from" value="{{ $filters['from']??'' }}"></label><label class="inline">Sampai <input type="date" name="to" value="{{ $filters['to']??'' }}"></label>
<label class="inline">Lokasi <select name="location_id"><option value="">Semua lokasi</option>@foreach($locations as $location)<option value="{{ $location->id }}" @selected(($filters['location_id']??'')==$location->id)>{{ $location->name }}</option>@endforeach</select></label>
<label class="inline">Status <select name="status"><option value="">Semua</option><option value="draft" @selected(($filters['status']??'')==='draft')>Draft</option><option value="published" @selected(($filters['status']??'')==='published')>Dipublikasikan</option></select></label>
<input name="q" value="{{ $filters['q']??'' }}" placeholder="Cari nama/kode staf"><button class="btn">Tampilkan</button><a href="{{ route('reports.index') }}" class="btn subtle">Reset</a></form></div>
<div class="stat-grid top-gap"><div class="stat"><span>Total Penugasan</span><b>{{ $totals->total ?? 0 }}</b><small>Sesuai filter</small></div><div class="stat"><span>Sudah Dipublikasikan</span><b>{{ $totals->published ?? 0 }}</b><small>Jadwal resmi</small></div><div class="stat"><span>Masih Draft</span><b>{{ $totals->drafts ?? 0 }}</b><small>Menunggu pemeriksaan</small></div><div class="stat"><span>Staf pada Halaman Ini</span><b>{{ $distribution->count() }}</b><small>15 staf per halaman</small></div></div>
<div class="panel"><div class="panel-head"><h2>Distribusi Penugasan per Staf</h2><span class="muted small">Cari staf menggunakan filter, navigasikan halaman di bawah.</span></div><div class="table-wrap"><table><thead><tr><th>Kode Staf</th><th>Nama Staf</th><th>Total Shift</th><th>Dipublikasikan</th><th>Draft</th></tr></thead><tbody>
@forelse($distribution as $row)<tr><td>{{ $row->code }}</td><td><b>{{ $row->name }}</b></td><td>{{ $row->total }}</td><td>{{ $row->published }}</td><td>{{ $row->total-$row->published }}</td></tr>
@empty<tr><td colspan="5" class="empty">Belum ada data yang sesuai filter.</td></tr>@endforelse
</tbody></table></div><div class="pagination">{{ $distribution->links() }}</div></div>
<div class="panel top-gap"><div class="panel-head"><h2>Riwayat Peringatan Kekurangan Staf</h2><span class="muted small">Mencatat hasil generate sebelumnya, bukan estimasi shortage real-time.</span></div>
<div class="table-wrap"><table><thead><tr><th>Tanggal</th><th>Cakupan Lokasi</th><th>Kekurangan Saat Generate</th><th>Detail</th></tr></thead><tbody>
@forelse($shortageHistory as $batch)<tr><td>{{ $batch->schedule_date->format('d/m/Y') }}</td><td>{{ $batch->location?->name ?? 'Semua lokasi' }}</td><td><b>{{ $batch->shortage_count }}</b></td><td><span style="white-space:pre-line">{{ $batch->notes }}</span></td></tr>
@empty<tr><td colspan="4" class="empty">Belum ada riwayat kekurangan staf pada periode ini.</td></tr>@endforelse
</tbody></table></div></div>
<div class="note top-gap">Informasi ini bersumber dari jadwal tersimpan. Untuk mengukur efisiensi dalam skripsi, waktu penyusunan jadwal manual dan otomatis harus diuji pada skenario yang setara, tidak boleh diasumsikan dari tampilan dashboard.</div>
@endsection
