@extends('layouts.app')
@section('title','Ketersediaan Harian')
@section('content')
<div class="heading"><div><p class="eyebrow">MONITORING HARIAN</p><h1>Ketersediaan Staf</h1><p>Admin memperbarui status berdasarkan laporan staf harian. Sumber laporan dapat berasal dari komunikasi operasional.</p></div></div>
<div class="panel"><form method="get" class="filters"><label class="inline">Tanggal <input type="date" name="date" value="{{ $date }}" required></label><button class="btn">Tampilkan</button></form>
<div class="note">Status <b>Tersedia</b> adalah kesediaan pada tanggal terpilih. Jam yang sudah dipesan ditentukan dari jadwal aktif, bukan dari perubahan manual status harian.</div>
<form action="{{ route('availability.update') }}" method="post">@csrf @method('PUT')<input type="hidden" name="date" value="{{ $date }}">
<div class="table-wrap"><table><thead><tr><th>Staf</th><th>Status pada {{ $date }}</th><th>Jam yang terjadwal</th><th>Catatan</th></tr></thead><tbody>
@forelse($staff as $person)@php($row=$availability->get($person->id))
<tr><td><b>{{ $person->name }}</b><small class="sub">{{ $person->code }}</small></td><td><select name="records[{{ $person->id }}][status]">
@foreach(['unknown'=>'Belum Dilaporkan','available'=>'Tersedia','leave'=>'Izin/Cuti','unavailable'=>'Berhalangan'] as $value=>$label)<option value="{{ $value }}" @selected(old("records.{$person->id}.status",$row?->status ?? 'unknown')===$value)>{{ $label }}</option>@endforeach
</select></td><td>@forelse($booked->get($person->id,collect()) as $item)<div><span class="pill {{ $item->status }}">{{ $item->shift->name }}</span> {{ substr($item->shift->start_time,0,5) }}–{{ substr($item->shift->end_time,0,5) }} · {{ $item->location->name }}</div>@empty<span class="muted">Belum terjadwal pada tanggal ini</span>@endforelse</td><td><input name="records[{{ $person->id }}][notes]" value="{{ old("records.{$person->id}.notes",$row?->notes) }}" placeholder="Catatan harian"></td></tr>
@empty<tr><td colspan="4" class="empty">Belum ada staf aktif.</td></tr>@endforelse
</tbody></table></div><button class="btn primary top-gap" type="submit">Simpan Ketersediaan Harian</button></form></div>
<div class="note top-gap">Jika staf mendadak izin setelah dijadwalkan, ubah statusnya kemudian periksa ulang draft atau jadwal publikasi. Sistem tidak diam-diam menghapus jadwal lama.</div>
@endsection
