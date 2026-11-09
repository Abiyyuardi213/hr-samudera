<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('judul', 'Sistem HR') | PT. Samudera Indonesia</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('image/pt-samudera-indonesia.jpg') }}">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@300;400;600&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2.0/dist/css/adminlte.min.css">
    <style>
        body { font-family: 'Source Sans Pro', sans-serif; }
        .wadah-utama { width: 100%; max-width: 1140px; margin-right: auto; margin-left: auto; padding-right: 15px; padding-left: 15px; }
        .navbar-brand { font-weight: 700; }
        .navbar-samudera { background-color: #f9b314; }
        .navbar-samudera .navbar-brand, .navbar-samudera .nama-perusahaan, .navbar-samudera .nav-link, .navbar-samudera .navbar-toggler { color: #001f3f; }
        .navbar-samudera .navbar-brand:hover, .navbar-samudera .navbar-brand:focus, .navbar-samudera .nav-link:hover, .navbar-samudera .nav-link:focus, .navbar-samudera .nav-link.active, .navbar-samudera .show > .nav-link { color: #001f3f; }
        .navbar-samudera .nav-link:hover, .navbar-samudera .nav-link:focus, .navbar-samudera .nav-link.active, .navbar-samudera .show > .nav-link { background-color: rgba(0, 31, 63, .09); border-radius: .25rem; }
        .navbar-samudera .navbar-toggler { border-color: rgba(0, 31, 63, .35); }
        .navbar-samudera .profil-navbar { display: flex; align-items: center; gap: .65rem; height: auto; padding: .35rem .65rem; font-weight: 600; }
        .avatar-profil-navbar { width: 36px; height: 36px; border-radius: 50%; border: 2px solid rgba(0, 31, 63, .25); object-fit: cover; flex-shrink: 0; background-color: #f4f6f9; }
        .avatar-profil-kosong { display: inline-flex; align-items: center; justify-content: center; font-size: 1rem; }
        .nama-profil-navbar { display: inline-block; max-width: 180px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .navbar-samudera .navbar-toggler-icon { background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='30' height='30' viewBox='0 0 30 30'%3e%3cpath stroke='%23001f3f' stroke-width='2' stroke-linecap='round' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e"); }
        .logo-perusahaan { width: auto; height: 40px; max-width: 140px; object-fit: contain; }
        .navbar-brand small { display: block; font-size: .7rem; font-weight: 400; letter-spacing: .04em; }
        .content-wrapper > .content { padding-bottom: 32px; }
        .aksi { display: flex; flex-wrap: wrap; align-items: center; gap: 6px; }
        .aksi form { margin: 0; }
        .table td, .table th { vertical-align: middle; }
        .deskripsi-role { white-space: pre-wrap; overflow-wrap: anywhere; }
        .navbar-samudera .dropdown-item { color: #001f3f; }
        .navbar-samudera .dropdown-item.active, .navbar-samudera .dropdown-item:active { background-color: #f9b314; color: #001f3f; }
        @media (max-width: 575.98px) { .navbar-brand { font-size: 1rem; } }
    </style>
    @stack('gaya')
</head>
<body class="hold-transition layout-top-nav">
<div class="wrapper">
    @include('layouts.komponen.navbar')
    <main class="content-wrapper" id="kontenUtama">
        <div class="content-header">
            <div class="container-fluid wadah-utama">
                <div class="row mb-2 align-items-center">
                    <div class="col-md-7">
                        <h1 class="m-0 text-dark @yield('kelas-judul')">@yield('judul', 'Sistem HR')</h1>
                        @hasSection('keterangan')<p class="text-muted mb-0 mt-1">@yield('keterangan')</p>@endif
                    </div>
                    <div class="col-md-5">
                        <ol class="breadcrumb float-md-right mb-0 mt-2 mt-md-0">
                            @yield('breadcrumb')
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <section class="content" aria-label="Konten halaman">
            <div class="container-fluid wadah-utama">
                @if (session('pesan'))
                    <div class="alert alert-success" role="status"><i class="fas fa-check-circle mr-2" aria-hidden="true"></i>{{ session('pesan') }}</div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger" role="alert">
                        <p class="font-weight-bold mb-1">Periksa kembali data yang diisi:</p>
                        <ul class="mb-0 pl-3">@foreach ($errors->all() as $kesalahan)<li>{{ $kesalahan }}</li>@endforeach</ul>
                    </div>
                @endif
                @yield('isi')
            </div>
        </section>
    </main>
    @include('layouts.komponen.footer')
</div>
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2.0/dist/js/adminlte.min.js"></script>
@stack('skrip')
</body>
</html>


