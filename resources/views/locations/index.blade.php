@extends('layouts.app')
@section('title','Lokasi Kerja')
@section('content')
<div class="heading"><div><p class="eyebrow">KONFIGURASI OPERASIONAL</p><h1>Lokasi Kerja</h1><p>Lokasi penempatan cleaning service.</p></div><a class="btn primary" href="{{ route('locations.create') }}">＋ Tambah Lokasi</a></div>
<div class="panel"><div class="table-wrap"><table><thead><tr><th>Nama Lokasi</th><th>Keterangan</th><th>Status</th><th>Aksi</th></tr></thead><tbody>@forelse($locations as $location)
<tr><td><b>{{ $location->name }}</b></td><td>{{ $location->address ?? '—' }}</td><td><span class="pill {{ $location->is_active ? 'published':'cancelled' }}">{{ $location->is_active?'Aktif':'Nonaktif' }}</span></td><td><a class="btn small" href="{{ route('locations.edit',$location) }}">Edit</a></td></tr>
@empty<tr><td colspan="4" class="empty">Belum ada lokasi.</td></tr>@endforelse</tbody></table></div></div>
@endsection
