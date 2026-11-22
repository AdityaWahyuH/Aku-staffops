@extends('layouts.app')
@section('title','Jadwal Saya')
@section('content')
<div class="heading"><div><p class="eyebrow">AKSES STAF CLEANING SERVICE</p><h1>Jadwal Penugasan Saya</h1><p>Hanya jadwal yang telah disetujui dan dipublikasikan admin yang ditampilkan.</p></div></div>
@if(!auth()->user()->staff_id)<div class="alert warn">Akun Anda belum dihubungkan ke data staf. Hubungi admin.</div>@endif
<div class="panel"><div class="table-wrap"><table><thead><tr><th>Tanggal</th><th>Lokasi</th><th>Shift</th><th>Jam Kerja</th><th>Status</th></tr></thead><tbody>
@forelse($assignments as $assignment)<tr><td><b>{{ $assignment->schedule_date->format('d/m/Y') }}</b></td><td>{{ $assignment->location->name }}</td><td>{{ $assignment->shift->name }}</td><td>{{ substr($assignment->shift->start_time,0,5) }}–{{ substr($assignment->shift->end_time,0,5) }}</td><td><span class="pill published">Dipublikasikan</span></td></tr>
@empty<tr><td colspan="5" class="empty">Belum ada penugasan yang dipublikasikan untuk Anda.</td></tr>@endforelse
</tbody></table></div>{{ $assignments->links() }}</div>
@endsection
