@extends('layouts.app')
@section('title','Generate & Jadwal')
@section('content')
<div class="heading"><div><p class="eyebrow">OTOMATISASI OPERASIONAL</p><h1>Generate & Penjadwalan Shift</h1><p>Susun draft dari staf tersedia, tampilkan kekurangan personel, lalu publikasikan secara massal setelah pemeriksaan.</p></div></div>
<div class="two-col">
<div class="panel"><h2>✣ Generate Rekomendasi Jadwal</h2><p class="muted small">Draft tidak langsung dipublikasikan. Menjalankan ulang akan mengisi posisi yang masih kosong, tanpa mengganti penugasan aktif sebelumnya.</p>
<form method="post" action="{{ route('schedules.generate') }}">@csrf
<div class="form-grid"><label>Tanggal Penjadwalan<input required type="date" name="date" value="{{ old('date',$date) }}"></label><label>Lokasi<select name="location_id"><option value="">Semua Lokasi</option>@foreach($locations as $location)<option value="{{ $location->id }}" @selected(old('location_id')==$location->id)>{{ $location->name }}</option>@endforeach</select></label></div>
<button type="submit" class="btn primary top-gap" onclick="return confirm('Generate rekomendasi untuk tanggal dan lokasi terpilih?')">✣ Generate Jadwal</button></form>
</div>
<div class="panel"><h2>Hasil Generate Terakhir</h2>
@if($lastBatch)<div class="mini-stats"><div><span>Kebutuhan</span><b>{{ $lastBatch->required_count }}</b></div><div><span>Draft Baru</span><b>{{ $lastBatch->created_count }}</b></div><div><span>Posisi Kurang</span><b class="{{ $lastBatch->shortage_count ? 'red':'' }}">{{ $lastBatch->shortage_count }}</b></div></div>
@if($lastBatch->shortage_count)<div class="alert warn"><b>Masih ada kekurangan personel:</b><pre>{{ $lastBatch->notes }}</pre>Penugasan yang berhasil tetap dibuat. Diskusikan penanganan kekurangan dengan koordinator.</div>@else<div class="note">{{ $lastBatch->notes }}</div>@endif
@else<p class="muted">Belum ada proses generate untuk tanggal terpilih.</p>@endif
</div>
</div>
<div class="panel top-gap"><div class="panel-head"><div><h2>Daftar Penugasan</h2><p class="muted small">Pilih sejumlah draft lalu tekan Publikasikan Terpilih. Tersedia publikasi individual melalui pilihan satu checkbox.</p></div><div class="actions"><a class="btn" href="{{ route('schedules.print',['date'=>$date]) }}" target="_blank">▤ Cetak / Simpan PDF</a><a class="btn" href="{{ route('schedules.csv',['date'=>$date]) }}">↓ Ekspor CSV</a></div></div>
<form class="filters" method="get"><label class="inline">Tanggal <input type="date" name="date" value="{{ $date }}"></label>
<label class="inline">Lokasi <select name="location_id"><option value="">Semua</option>@foreach($locations as $location)<option value="{{ $location->id }}" @selected(($filters['location_id']??'')==$location->id)>{{ $location->name }}</option>@endforeach</select></label>
<label class="inline">Status <select name="status"><option value="">Semua</option>@foreach(['draft'=>'Draft','published'=>'Dipublikasikan','cancelled'=>'Dibatalkan'] as $value=>$label)<option value="{{ $value }}" @selected(($filters['status']??'')===$value)>{{ $label }}</option>@endforeach</select></label>
<input name="q" placeholder="Cari nama/kode staf" value="{{ $filters['q'] ?? '' }}"><button class="btn">Filter</button><a class="btn subtle" href="{{ route('schedules.index',['date'=>$date]) }}">Reset</a></form>
<form id="bulkPublish" method="post" action="{{ route('schedules.publish') }}">@csrf</form>
<div class="table-wrap"><table><thead><tr><th><input type="checkbox" id="selectAll" aria-label="Pilih seluruh draft di halaman ini"></th><th>Staf</th><th>Lokasi</th><th>Tanggal</th><th>Shift & Jam</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
@forelse($assignments as $assignment)<tr><td>@if($assignment->status==='draft')<input type="checkbox" form="bulkPublish" name="assignment_ids[]" value="{{ $assignment->id }}" class="draft-choice">@endif</td>
<td><b>{{ $assignment->staff->name }}</b><small class="sub">{{ $assignment->staff->code }}</small></td><td>{{ $assignment->location->name }}</td><td>{{ $assignment->schedule_date->format('d/m/Y') }}</td><td><b>{{ $assignment->shift->name }}</b><small class="sub">{{ substr($assignment->shift->start_time,0,5) }}–{{ substr($assignment->shift->end_time,0,5) }}</small></td><td><span class="pill {{ $assignment->status }}">{{ ['draft'=>'Draft','published'=>'Dipublikasikan','cancelled'=>'Dibatalkan'][$assignment->status] }}</span></td>
<td><div class="actions">@if($assignment->status==='draft')<button type="button" class="btn small" data-open-edit="{{ $assignment->id }}">Edit Staf</button>@endif
@if($assignment->status!=='cancelled')<form method="post" action="{{ route('schedules.cancel',$assignment) }}">@csrf @method('PATCH')<button class="btn small danger-outline" onclick="return confirm('Batalkan penugasan ini?')">Batalkan</button></form>@endif</div></td></tr>
@empty<tr><td colspan="7" class="empty">Belum ada penugasan untuk filter ini.</td></tr>@endforelse
</tbody></table></div>
<div class="actions top-gap"><button form="bulkPublish" class="btn primary" onclick="return confirm('Publikasikan seluruh draft terpilih?')">✓ Publikasikan Terpilih</button>
<form method="post" action="{{ route('schedules.publish-day') }}">@csrf<input type="hidden" name="date" value="{{ $date }}"><button class="btn" onclick="return confirm('Publikasikan SEMUA draft tanggal {{ $date }} di seluruh halaman dan lokasi? Seluruh data akan divalidasi ulang.')">✓ Publikasikan Semua Draft Tanggal Ini</button></form>
<span class="small muted">Pilih semua hanya memilih halaman saat ini; tombol publikasi per tanggal mencakup seluruh halaman.</span></div>
<div class="pagination">{{ $assignments->links() }}</div>
</div>
@foreach($assignments as $assignment)
@if($assignment->status==='draft')
<dialog id="edit-{{ $assignment->id }}" class="dialog"><div class="dialog-head"><h3>Ganti Staf · {{ $assignment->shift->name }}</h3><button type="button" class="btn close-dialog" data-close-dialog="edit-{{ $assignment->id }}">Tutup</button></div>
<form method="post" action="{{ route('schedules.update',$assignment) }}">@csrf @method('PATCH')<p class="muted">{{ $assignment->location->name }} · {{ $assignment->schedule_date->format('d/m/Y') }} · {{ substr($assignment->shift->start_time,0,5) }}–{{ substr($assignment->shift->end_time,0,5) }}</p>
<label>Staf Pengganti <select name="staff_id" required>@foreach($staff as $person)<option value="{{ $person->id }}" @selected($person->id===$assignment->staff_id)>{{ $person->name }} ({{ $person->code }})</option>@endforeach</select></label>
<p class="small muted">Saat disimpan, server akan memeriksa ketersediaan dan konflik waktu staf terpilih.</p><button class="btn primary top-gap">Simpan Perubahan</button></form></dialog>
@endif
@endforeach
<div class="note top-gap"><b>Kendala yang berlaku:</b> staf harus aktif dan tersedia, tidak memiliki penugasan yang waktunya bertabrakan (termasuk lintas tanggal). Shift lain tanpa benturan tetap memungkinkan. Batas jam kerja dan istirahat minimum memerlukan validasi aturan perusahaan.</div>
@endsection
@push('scripts')<script>
document.getElementById('selectAll')?.addEventListener('change', function(){document.querySelectorAll('.draft-choice').forEach(el=>el.checked=this.checked)});
document.querySelectorAll('[data-open-edit]').forEach(button => button.addEventListener('click',()=>document.getElementById('edit-'+button.dataset.openEdit).showModal()));
document.querySelectorAll('[data-close-dialog]').forEach(button => button.addEventListener('click',()=>document.getElementById(button.dataset.closeDialog).close()));
</script>@endpush
