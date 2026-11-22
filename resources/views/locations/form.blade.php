@extends('layouts.app')
@section('title',$location->exists?'Edit Lokasi':'Tambah Lokasi')
@section('content')
<div class="heading"><div><h1>{{ $location->exists?'Edit Lokasi':'Tambah Lokasi' }}</h1><p>Nama lokasi dan informasi tambahan.</p></div></div>
<div class="panel narrow"><form method="post" action="{{ $location->exists?route('locations.update',$location):route('locations.store') }}">@csrf @if($location->exists)@method('PUT')@endif
<label>Nama Lokasi<input name="name" required value="{{ old('name',$location->name) }}"></label><label>Alamat/Keterangan<textarea rows="3" name="address">{{ old('address',$location->address) }}</textarea></label>
<label>Status Lokasi<select name="is_active"><option value="1" @selected(old('is_active',$location->is_active ?? true))>Aktif</option><option value="0" @selected(!old('is_active',$location->is_active ?? true))>Nonaktif</option></select></label>
<div class="actions top-gap"><button class="btn primary">Simpan</button><a class="btn" href="{{ route('locations.index') }}">Batal</a></div></form></div>
@endsection
