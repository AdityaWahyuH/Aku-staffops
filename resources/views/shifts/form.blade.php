@extends('layouts.app')
@section('title',$shift->exists?'Edit Shift':'Tambah Shift')
@section('content')
<div class="heading"><div><h1>{{ $shift->exists?'Edit Shift':'Tambah Shift' }}</h1><p>Jam selesai yang lebih awal dari jam mulai dianggap berakhir pada hari berikutnya.</p></div></div>
<div class="panel narrow"><form method="post" action="{{ $shift->exists?route('shifts.update',$shift):route('shifts.store') }}">@csrf @if($shift->exists)@method('PUT')@endif
<label>Nama Shift<input name="name" required value="{{ old('name',$shift->name) }}"></label>
<div class="form-grid"><label>Mulai<input type="time" name="start_time" required value="{{ old('start_time',substr($shift->start_time ?? '07:00',0,5)) }}"></label><label>Selesai<input type="time" name="end_time" required value="{{ old('end_time',substr($shift->end_time ?? '15:00',0,5)) }}"></label></div>
<label>Status Shift<select name="is_active"><option value="1" @selected(old('is_active',$shift->is_active ?? true))>Aktif</option><option value="0" @selected(!old('is_active',$shift->is_active ?? true))>Nonaktif</option></select></label>
<div class="actions top-gap"><button class="btn primary">Simpan</button><a class="btn" href="{{ route('shifts.index') }}">Batal</a></div></form></div>
@endsection
