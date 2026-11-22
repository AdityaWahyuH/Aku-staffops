@extends('layouts.app')
@section('title','Dashboard')
@section('content')
<div class="heading"><div><p class="eyebrow">RINGKASAN OPERASIONAL</p><h1>Dashboard</h1><p>Pantau data ketersediaan dan penugasan staf pada {{ \Carbon\Carbon::parse($date)->translatedFormat('d F Y') }}.</p></div><a class="btn primary" href="{{ route('schedules.index') }}">✣ Generate Jadwal</a></div>
<div class="stat-grid">
<div class="stat"><span>Staf Aktif</span><b>{{ $staffCount }}</b><small>Master data staf</small></div>
<div class="stat"><span>Tersedia Hari Ini</span><b>{{ $availableCount }}</b><small>Status laporan harian</small></div>
<div class="stat"><span>Penugasan Hari Ini</span><b>{{ $assignmentCount }}</b><small>Draft + terpublikasi</small></div>
<div class="stat"><span>Draft Menunggu Publikasi</span><b>{{ $draftCount }}</b><small>Perlu ditinjau</small></div>
</div>
<div class="panel"><h2>Pemenuhan Kebutuhan Staf</h2>
<p class="muted">Total posisi terjadwal: <b>{{ $assignmentCount }}</b> dari <b>{{ $requiredCount }}</b> kebutuhan yang terkonfigurasi.</p>
<div class="progress"><div style="width:{{ $requiredCount ? min(100, round($assignmentCount / $requiredCount * 100)) : 0 }}%"></div></div>
<p class="small muted">Indikator ini berdasarkan seluruh kebutuhan aktif, bukan angka hasil evaluasi perusahaan.</p></div>
<div class="panel"><div class="panel-head"><h2>Penugasan Hari Ini</h2><a href="{{ route('schedules.index',['date'=>$date]) }}">Lihat Semua →</a></div>
<div class="table-wrap"><table><thead><tr><th>Staf</th><th>Lokasi</th><th>Shift</th><th>Status</th></tr></thead><tbody>
@forelse($assignments as $assignment)<tr><td>{{ $assignment->staff->name }}</td><td>{{ $assignment->location->name }}</td><td>{{ $assignment->shift->name }} ({{ substr($assignment->shift->start_time,0,5) }}–{{ substr($assignment->shift->end_time,0,5) }})</td><td><span class="pill {{ $assignment->status }}">{{ $assignment->status === 'draft' ? 'Draft' : 'Dipublikasikan' }}</span></td></tr>
@empty<tr><td colspan="4" class="empty">Belum ada penugasan hari ini. Buka menu Generate & Jadwal.</td></tr>@endforelse
</tbody></table></div></div>
@endsection
