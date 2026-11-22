@extends('layouts.app')
@section('title','Kebutuhan Staf')
@section('content')
<div class="heading"><div><p class="eyebrow">ATURAN PENJADWALAN</p><h1>Kebutuhan Staf per Lokasi & Shift</h1><p>Isi kebutuhan minimum personel. Nilai 0 berarti shift di lokasi tersebut tidak memerlukan penempatan.</p></div></div>
<div class="panel"><form method="post" action="{{ route('requirements.update') }}">@csrf @method('PUT')
<div class="table-wrap"><table><thead><tr><th>Lokasi</th>@foreach($shifts as $shift)<th>{{ $shift->name }}<small class="sub">{{ substr($shift->start_time,0,5) }}–{{ substr($shift->end_time,0,5) }}</small></th>@endforeach</tr></thead><tbody>
@forelse($locations as $location)<tr><td><b>{{ $location->name }}</b></td>@foreach($shifts as $shift)@php($key="{$location->id}-{$shift->id}")
<td><input class="numeric" type="number" min="0" max="1000" name="needs[{{ $key }}]" value="{{ old('needs.'.$key,$values[$key] ?? 0) }}" required></td>@endforeach</tr>
@empty<tr><td colspan="6" class="empty">Tambahkan lokasi dan shift terlebih dahulu.</td></tr>@endforelse
</tbody></table></div><button class="btn primary top-gap">Simpan Kebutuhan</button></form></div>
<div class="note top-gap">Jumlah ini adalah konfigurasi dasar, bukan jumlah staf yang otomatis tersedia. Ketika staf kurang, generator tetap menyimpan penugasan yang berhasil dan memberikan peringatan kekurangan.</div>
@endsection
