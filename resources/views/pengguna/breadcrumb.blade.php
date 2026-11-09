<li class="breadcrumb-item">Data Master</li>
@if (request()->routeIs('pengguna.daftar'))
    <li class="breadcrumb-item active">Pengguna</li>
@else
    <li class="breadcrumb-item"><a href="{{ route('pengguna.daftar') }}">Pengguna</a></li>
    <li class="breadcrumb-item active">@yield('judul')</li>
@endif
