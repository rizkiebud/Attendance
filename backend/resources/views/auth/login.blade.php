<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Absensi KPPN</title>
    <link rel="icon" type="image/png" href="{{ asset('logo.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #1e293b 0%, #1a56db 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-card {
            background: #fff;
            border-radius: 16px;
            padding: 2.5rem;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.3);
        }
        .brand-icon {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, #1a56db, #1e40af);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
        }
        .form-control:focus {
            border-color: #1a56db;
            box-shadow: 0 0 0 0.2rem rgba(26, 86, 219, 0.15);
        }
        .btn-login {
            background: linear-gradient(135deg, #1a56db, #1e40af);
            border: none;
            color: white;
            padding: 0.75rem;
            border-radius: 8px;
            font-weight: 600;
            transition: opacity 0.2s;
        }
        .btn-login:hover {
            opacity: 0.9;
            color: white;
        }
        .input-group-text {
            background: #f8fafc;
            border-right: none;
        }
        .form-control {
            border-left: none;
        }
        .form-control:focus {
            border-left: 1px solid #1a56db;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="text-center mb-4">
            <div class="brand-icon">
                <img src="{{ asset('logo.png') }}" alt="Logo" style="width: 52px; height: 52px; border-radius: 10px;">
            </div>
            <h4 class="fw-bold text-dark mb-1">Sistem Absensi KPPN</h4>
            <p class="text-muted small">Monitoring Kehadiran Karyawan</p>
        </div>

        @if($errors->any())
            <div class="alert alert-danger py-2">
                <i class="bi bi-exclamation-circle me-2"></i>
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('web.login.post') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label fw-semibold text-dark small">Email</label>
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="bi bi-envelope text-muted"></i>
                    </span>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                        placeholder="admin@kppn.go.id" value="{{ old('email') }}" required autofocus>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold text-dark small">Password</label>
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="bi bi-lock text-muted"></i>
                    </span>
                    <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                </div>
            </div>

            <div class="d-flex align-items-center justify-content-between mb-4">
                <div class="form-check">
                    <input type="checkbox" name="remember" class="form-check-input" id="remember">
                    <label class="form-check-label small" for="remember">Ingat saya</label>
                </div>
            </div>

            <button type="submit" class="btn btn-login w-100">
                <i class="bi bi-box-arrow-in-right me-2"></i>Masuk
            </button>
        </form>

        <hr class="my-4">
        <p class="text-center text-muted small mb-0">
            <i class="bi bi-shield-lock me-1"></i>
            Hanya admin yang dapat mengakses panel ini
        </p>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
