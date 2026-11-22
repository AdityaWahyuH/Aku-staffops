@extends('layouts.app')
@section('title',$staff->exists ? 'Edit Staf':'Tambah Staf')
@section('content')
<div class="heading"><div><p class="eyebrow">MASTER DATA</p><h1>{{ $staff->exists ? 'Edit Staf':'Tambah Staf' }}</h1><p>Masukkan identitas staf sesuai data yang telah diperiksa.</p></div></div>
<div class="panel narrow"><form method="post" action="{{ $staff->exists ? route('staff.update',$staff) : route('staff.store') }}">@csrf @if($staff->exists)@method('PUT')@endif
<div class="form-grid"><label>Kode Staf<input name="code" required maxlength="25" value="{{ old('code',$staff->code) }}" placeholder="STF-001"></label><label>Nama Staf<input name="name" required maxlength="150" value="{{ old('name',$staff->name) }}"></label><label>Nomor Telepon<input name="phone" value="{{ old('phone',$staff->phone) }}"></label><label>Keahlian<input name="skill" value="{{ old('skill',$staff->skill) }}" placeholder="Cleaning Service"></label></div>
<div class="actions top-gap"><button class="btn primary">Simpan</button><a class="btn" href="{{ route('staff.index') }}">Batal</a></div></form></div>
@endsection
