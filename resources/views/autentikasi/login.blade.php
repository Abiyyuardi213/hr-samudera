<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login | PT. Samudera Indonesia</title>
    <link rel="icon" type="image/png" href="{{ asset('image/Logo_Samudera_Indonesia_PT.png') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2.0/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@400;600;700&display=swap">
    <style>
        body { font-family: 'Source Sans Pro', sans-serif; background: url('{{ asset('image/wp1.jpg') }}') no-repeat center center fixed; background-size: cover; padding: 24px 16px; }
        .login-page { min-height: 100vh; height: auto; }
        .login-box { width: 400px; max-width: 100%; }
        .login-logo-image { width: 170px; max-width: 100%; height: auto; }
    </style>
</head>
<body class="hold-transition login-page">
    <main class="login-box">
        <div class="card card-outline card-primary shadow-lg mb-0">
            <div class="card-header text-center py-4">
                <img src="{{ asset('image/Logo_Samudera_Indonesia_PT.png') }}" class="login-logo-image" alt="Logo PT. Samudera Indonesia">
            </div>
            <div class="card-body login-card-body">
                <h1 class="h4 text-center font-weight-bold">Login Sistem HR</h1>
                <p class="login-box-msg">Masuk dengan akun perusahaan Anda.</p>
                @if (session('pesan'))<div class="alert alert-success" role="status">{{ session('pesan') }}</div>@endif
                @if ($errors->any())<div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>@endif
                <form method="POST" action="{{ route('login.masuk') }}">
                    @csrf
                    <label for="email">Email</label>
                    <div class="input-group mb-3">
                        <input id="email" name="email" type="email" class="form-control" value="{{ old('email') }}" placeholder="Email" autocomplete="username" required autofocus>
                        <div class="input-group-append"><span class="input-group-text"><i class="fas fa-user" aria-hidden="true"></i></span></div>
                    </div>
                    <label for="password">Password</label>
                    <div class="input-group mb-4">
                        <input id="password" name="password" type="password" class="form-control" placeholder="Password" autocomplete="current-password" required>
                        <div class="input-group-append"><span class="input-group-text"><i class="fas fa-lock" aria-hidden="true"></i></span></div>
                    </div>
                    <button class="btn btn-primary btn-block" type="submit"><i class="fas fa-sign-in-alt mr-2" aria-hidden="true"></i>Masuk ke Sistem HR</button>
                </form>
            </div>
            <div class="card-footer text-center text-muted small">Sistem HR PT. Samudera Indonesia</div>
        </div>
    </main>
</body>
</html>
