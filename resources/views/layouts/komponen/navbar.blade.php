    <nav class="main-header navbar navbar-expand-md navbar-light navbar-samudera shadow-sm" aria-label="Navigasi utama">
        <div class="container-fluid wadah-utama">
            <a href="{{ route('beranda') }}" class="navbar-brand d-flex align-items-center">
                <img src="{{ asset('image/pt-samudera-indonesia.jpg') }}" alt="Logo PT. Samudera Indonesia" class="logo-perusahaan mr-3">
                <span><span class="nama-perusahaan">SAMUDERA INDONESIA</span><small>SISTEM HUMAN RESOURCES</small></span>
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#menuUtama" aria-controls="menuUtama" aria-expanded="false" aria-label="Buka menu navigasi">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="menuUtama">
                <ul class="navbar-nav ml-md-4">
                    <li class="nav-item"><a href="{{ route('beranda') }}" class="nav-link {{ request()->routeIs('beranda') ? 'active' : '' }}">Beranda</a></li>
                    @if (auth()->user()?->dapatMengelola())
                    <li class="nav-item dropdown">
                        <a href="#" id="menuDataMaster" class="nav-link dropdown-toggle {{ request()->routeIs('role.*', 'pengguna.*', 'kota.*') ? 'active' : '' }}" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-layer-group mr-1" aria-hidden="true"></i> Data Master
                        </a>
                        <div class="dropdown-menu shadow-sm" aria-labelledby="menuDataMaster">
                            <a href="{{ route('role.daftar') }}" class="dropdown-item {{ request()->routeIs('role.*') ? 'active' : '' }}" @if(request()->routeIs('role.*')) aria-current="page" @endif>
                                <i class="fas fa-user-shield mr-2" aria-hidden="true"></i> Role
                            </a>
                            <a href="{{ route('pengguna.daftar') }}" class="dropdown-item {{ request()->routeIs('pengguna.*') ? 'active' : '' }}"><i class="fas fa-users mr-2" aria-hidden="true"></i>Pengguna</a>
                            <a href="{{ route('kota.daftar') }}" class="dropdown-item {{ request()->routeIs('kota.*') ? 'active' : '' }}"><i class="fas fa-city mr-2" aria-hidden="true"></i>Kota</a>
                        </div>
                    </li>
                    <li class="nav-item dropdown">
                        <a href="#" id="menuMasterOffice" class="nav-link dropdown-toggle {{ request()->routeIs('kantor.*', 'unit-organisasi.*', 'jabatan.*', 'status-pegawai.*', 'shift-pegawai.*') ? 'active' : '' }}" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-building mr-1" aria-hidden="true"></i> Master Office
                        </a>
                        <div class="dropdown-menu shadow-sm" aria-labelledby="menuMasterOffice">
                            <a href="{{ route('kantor.daftar') }}" class="dropdown-item {{ request()->routeIs('kantor.*') ? 'active' : '' }}"><i class="fas fa-building mr-2" aria-hidden="true"></i>Kantor</a>
                            <a href="{{ route('unit-organisasi.daftar') }}" class="dropdown-item {{ request()->routeIs('unit-organisasi.*') ? 'active' : '' }}"><i class="fas fa-sitemap mr-2" aria-hidden="true"></i>Unit Organisasi</a>
                            <a href="{{ route('jabatan.daftar') }}" class="dropdown-item {{ request()->routeIs('jabatan.*') ? 'active' : '' }}"><i class="fas fa-user-tie mr-2" aria-hidden="true"></i>Jabatan</a>
                            <a href="{{ route('status-pegawai.daftar') }}" class="dropdown-item {{ request()->routeIs('status-pegawai.*') ? 'active' : '' }}"><i class="fas fa-id-badge mr-2" aria-hidden="true"></i>Status Pegawai</a>
                            <a href="{{ route('shift-pegawai.daftar') }}" class="dropdown-item {{ request()->routeIs('shift-pegawai.*') ? 'active' : '' }}"><i class="fas fa-clock mr-2" aria-hidden="true"></i>Manajemen Shift</a>
                        </div>
                    </li>
                    <li class="nav-item dropdown">
                        <a href="#" id="menuPegawai" class="nav-link dropdown-toggle {{ request()->routeIs('pegawai.*', 'penempatan-pegawai.*', 'jadwal-kerja.*', 'absensi.*', 'ketidakhadiran.*', 'lembur.*', 'laporan-hr.*') ? 'active' : '' }}" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fas fa-user-friends mr-1" aria-hidden="true"></i>Pegawai</a>
                        <div class="dropdown-menu shadow-sm" aria-labelledby="menuPegawai">
                            <a href="{{ route('pegawai.daftar') }}" class="dropdown-item {{ request()->routeIs('pegawai.*') ? 'active' : '' }}">Data Pegawai</a>
                            <a href="{{ route('penempatan-pegawai.daftar') }}" class="dropdown-item {{ request()->routeIs('penempatan-pegawai.*') ? 'active' : '' }}">Penempatan &amp; Riwayat</a>
                            <div class="dropdown-divider"></div>
                            <a href="{{ route('jadwal-kerja.daftar') }}" class="dropdown-item {{ request()->routeIs('jadwal-kerja.*') ? 'active' : '' }}">Jadwal Kerja</a>
                            <a href="{{ route('absensi.daftar') }}" class="dropdown-item {{ request()->routeIs('absensi.*') ? 'active' : '' }}">Absensi</a>
                            <a href="{{ route('ketidakhadiran.daftar') }}" class="dropdown-item {{ request()->routeIs('ketidakhadiran.*') ? 'active' : '' }}">Cuti, Izin &amp; Sakit</a>
                            <a href="{{ route('lembur.daftar') }}" class="dropdown-item {{ request()->routeIs('lembur.*') ? 'active' : '' }}">Lembur</a>
                            <a href="{{ route('laporan-hr.daftar') }}" class="dropdown-item {{ request()->routeIs('laporan-hr.*') ? 'active' : '' }}">Laporan HR</a>
                        </div>
                    </li>
                    @endif
                </ul>
                @auth
                    <div class="nav-item dropdown ml-md-auto">
                        <a href="#" class="nav-link dropdown-toggle profil-navbar" id="menuPengguna" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" aria-label="Buka menu akun {{ auth()->user()->name }}" title="{{ auth()->user()->name }}">
                            @if (auth()->user()->foto_profil)
                                <img src="{{ asset('storage/'.auth()->user()->foto_profil) }}" alt="Foto profil" class="avatar-profil-navbar" width="36" height="36">
                            @else
                                <span class="avatar-profil-navbar avatar-profil-kosong" aria-hidden="true"><i class="fas fa-user"></i></span>
                            @endif
                            <span class="nama-profil-navbar">{{ auth()->user()->name }}</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="menuPengguna">
                            <span class="dropdown-header">{{ auth()->user()->role?->nama }}</span>
                            <div class="dropdown-divider"></div>
                            <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="dropdown-item text-danger"><i class="fas fa-sign-out-alt mr-2" aria-hidden="true"></i>Keluar</button></form>
                        </div>
                    </div>
                @endauth
            </div>
        </div>
    </nav>






