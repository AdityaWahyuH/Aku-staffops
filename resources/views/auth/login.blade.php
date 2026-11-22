<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Login · AKU StaffOps</title><link rel="stylesheet" href="{{ asset('css/app.css') }}"></head><body class="login-screen">
<div class="login-card"><div class="brand" style="margin-bottom:22px"><span class="brand-mark">AK</span><span><b>AKU StaffOps</b><small>PT Artha Kembar Utama</small></span></div>
<h1>Masuk ke Sistem</h1><p class="muted">Gunakan akun admin atau staf untuk mengakses penjadwalan.</p>
@if($errors->any())<div class="alert danger">{{ $errors->first() }}</div>@endif
<form method="post" action="{{ route('login.submit') }}">@csrf
<label>Email<input type="email" name="email" required autofocus autocomplete="username" value="{{ old('email') }}"></label>
<label>Kata sandi<input type="password" name="password" required autocomplete="current-password"></label>
<button class="btn primary full" type="submit">Masuk</button>
</form><p class="small muted">Akun pengujian terdapat di file README. Jangan gunakan akun demo untuk data nyata.</p></div></body></html>
