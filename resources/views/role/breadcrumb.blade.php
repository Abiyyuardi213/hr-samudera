<li class="breadcrumb-item">Data Master</li>
@if (request()->routeIs('role.daftar'))
    <li class="breadcrumb-item active" aria-current="page">Role</li>
@else
    <li class="breadcrumb-item"><a href="{{ route('role.daftar') }}">Role</a></li>
    <li class="breadcrumb-item active" aria-current="page">@yield('judul')</li>
@endif
