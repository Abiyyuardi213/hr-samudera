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
        .nama-perusahaan { color: #f9b314; }
        .logo-perusahaan { width: auto; height: 40px; max-width: 140px; object-fit: contain; }
        .navbar-brand small { display: block; font-size: .7rem; font-weight: 400; letter-spacing: .04em; }
        .content-wrapper > .content { padding-bottom: 32px; }
        .aksi { display: flex; flex-wrap: wrap; align-items: center; gap: 6px; }
        .aksi form { margin: 0; }
        .table td, .table th { vertical-align: middle; }
        .deskripsi-role { white-space: pre-wrap; overflow-wrap: anywhere; }
        .navbar .dropdown-item.active { background-color: #001f3f; }
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


