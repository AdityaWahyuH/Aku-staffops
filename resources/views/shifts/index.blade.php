@extends('layouts.app')
@section('title','Definisi Shift')
@section('content')
<div class="heading"><div><p class="eyebrow">KONFIGURASI OPERASIONAL</p><h1>Definisi Shift</h1><p>Atur jam shift. Shift yang melewati tengah malam didukung oleh validasi konflik.</p></div><a class="btn primary" href="{{ route('shifts.create') }}">＋ Tambah Shift</a></div>
<div class="panel"><div class="table-wrap"><table><thead><tr><th>Shift</th><th>Mulai</th><th>Selesai</th><th>Status</th><th>Aksi</th></tr></thead><tbody>@foreach($shifts as $shift)
<tr><td><b>{{ $shift->name }}</b></td><td>{{ substr($shift->start_time,0,5) }}</td><td>{{ substr($shift->end_time,0,5) }}</td><td><span class="pill {{ $shift->is_active?'published':'cancelled' }}">{{ $shift->is_active?'Aktif':'Nonaktif' }}</span></td><td><a class="btn small" href="{{ route('shifts.edit',$shift) }}">Edit</a></td></tr>@endforeach</tbody></table></div></div>
@endsection
