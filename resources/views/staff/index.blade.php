@extends('layouts.app')
@section('title','Data Staf')
@section('content')
<div class="heading"><div><p class="eyebrow">MASTER DATA</p><h1>Data Staf</h1><p>Identitas staf bersifat tetap. Status ketersediaan harian dikelola pada menu terpisah.</p></div><a href="{{ route('staff.create') }}" class="btn primary">＋ Tambah Staf</a></div>
<div class="panel"><form class="filters" method="get"><input name="q" value="{{ $q }}" placeholder="Cari nama atau kode staf"><button class="btn">Cari</button><a class="btn subtle" href="{{ route('staff.index') }}">Reset</a></form>
<div class="table-wrap"><table><thead><tr><th>Kode</th><th>Nama</th><th>Telepon</th><th>Keahlian</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
@forelse($staff as $person)<tr><td><b>{{ $person->code }}</b></td><td>{{ $person->name }}</td><td>{{ $person->phone ?? '—' }}</td><td>{{ $person->skill ?? '—' }}</td><td><span class="pill {{ $person->is_active ? 'published':'cancelled' }}">{{ $person->is_active ? 'Aktif':'Nonaktif' }}</span></td><td><div class="actions"><a class="btn small" href="{{ route('staff.edit',$person) }}">Edit</a><form method="post" action="{{ route('staff.toggle',$person) }}">@csrf @method('PATCH')<button class="btn small" onclick="return confirm('Ubah status aktif staf ini?')">{{ $person->is_active ? 'Nonaktifkan':'Aktifkan' }}</button></form></div></td></tr>
@empty<tr><td colspan="6" class="empty">Data staf tidak ditemukan.</td></tr>@endforelse</tbody></table></div>{{ $staff->links() }}</div>
@endsection
