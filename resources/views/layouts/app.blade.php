<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title','AKU StaffOps') — PT Artha Kembar Utama</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
<div class="shell">
    <aside class="sidebar">
        <div class="brand"><span class="brand-mark">AK</span><span><b>AKU StaffOps</b><small>PT Artha Kembar Utama</small></span></div>
        <div class="nav-cap">MENU</div>
        @if(auth()->user()->role === 'admin')
            <a class="nav-link {{ request()->routeIs('dashboard') ? 'on' : '' }}" href="{{ route('dashboard') }}">▦ &nbsp; Dashboard</a>
            <a class="nav-link {{ request()->routeIs('staff.*') ? 'on' : '' }}" href="{{ route('staff.index') }}">♙ &nbsp; Data Staf</a>
            <a class="nav-link {{ request()->routeIs('availability.*') ? 'on' : '' }}" href="{{ route('availability.index') }}">◷ &nbsp; Ketersediaan</a>
            <a class="nav-link {{ request()->routeIs('locations.*') ? 'on' : '' }}" href="{{ route('locations.index') }}">⌖ &nbsp; Lokasi Kerja</a>
            <a class="nav-link {{ request()->routeIs('shifts.*') ? 'on' : '' }}" href="{{ route('shifts.index') }}">◫ &nbsp; Definisi Shift</a>
            <a class="nav-link {{ request()->routeIs('requirements.*') ? 'on' : '' }}" href="{{ route('requirements.index') }}">▤ &nbsp; Kebutuhan Staf</a>
            <a class="nav-link {{ request()->routeIs('schedules.*') ? 'on' : '' }}" href="{{ route('schedules.index') }}">✣ &nbsp; Generate & Jadwal</a>
            <a class="nav-link {{ request()->routeIs('reports.*') ? 'on' : '' }}" href="{{ route('reports.index') }}">▥ &nbsp; Laporan</a>
        @else
            <a class="nav-link {{ request()->routeIs('staff-portal.*') ? 'on' : '' }}" href="{{ route('staff-portal.index') }}">▤ &nbsp; Jadwal Saya</a>
        @endif
        <div class="sidebar-bottom"><span class="mode-dot"></span> Proyek Skripsi · Laravel
            <form action="{{ route('logout') }}" method="post">@csrf<button class="logout" type="submit">↪ Keluar</button></form>
        </div>
    </aside>
    <main class="main">
        <header class="topbar"><span>PT Artha Kembar Utama / <b>@yield('title','Dashboard')</b></span><div class="current-user">{{ auth()->user()->name }} <span class="role-pill">{{ strtoupper(auth()->user()->role) }}</span></div></header>
        <div class="content">
            @if(session('success'))<div class="alert success">✓ {{ session('success') }}</div>@endif
            @if($errors->any())<div class="alert danger"><b>Periksa kembali data:</b><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
            @yield('content')
        </div>
        <footer class="footer">AKU StaffOps — Sistem Monitoring Ketersediaan dan Penjadwalan Shift · Proyek penelitian</footer>
    </main>
</div>
@stack('scripts')
</body>
</html>
