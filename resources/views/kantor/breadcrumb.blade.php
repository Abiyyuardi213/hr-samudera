<li class="breadcrumb-item">Data Master</li>
@if (request()->routeIs('kantor.daftar'))
    <li class="breadcrumb-item active">Kantor</li>
@else
    <li class="breadcrumb-item"><a href="{{ route('kantor.daftar') }}">Kantor</a></li>
    <li class="breadcrumb-item active">@yield('judul')</li>
@endif
