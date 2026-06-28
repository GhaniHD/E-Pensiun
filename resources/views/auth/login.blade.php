<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk | Sistem Informasi Pengajuan Pensiun</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --primary: #1A5632;
            --accent:  #27AE60;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: #f0f4f8;
        }

        /* ── LEFT PANEL ─────────────────────────────────── */
        .login-left {
            width: 45%;
            background: linear-gradient(160deg, var(--primary) 0%, var(--accent) 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 3rem 2.5rem;
            position: relative;
            overflow: hidden;
        }

        .login-left::before {
            content: '';
            position: absolute;
            width: 400px; height: 400px;
            border-radius: 50%;
            background: rgba(255,255,255,0.05);
            top: -120px; right: -120px;
        }

        .login-left::after {
            content: '';
            position: absolute;
            width: 300px; height: 300px;
            border-radius: 50%;
            background: rgba(255,255,255,0.05);
            bottom: -80px; left: -80px;
        }

        .login-left .brand-logo {
            width: 80px; height: 80px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
            position: relative;
            z-index: 1;
        }

        .login-left .brand-logo i {
            font-size: 2.5rem;
            color: #fff;
        }

        .login-left h2 {
            color: #fff;
            font-weight: 700;
            text-align: center;
            margin-bottom: 0.5rem;
            position: relative;
            z-index: 1;
            font-size: 1.6rem;
        }

        .login-left .instansi {
            color: rgba(255,255,255,0.75);
            font-size: 0.9rem;
            text-align: center;
            position: relative;
            z-index: 1;
            margin-bottom: 2rem;
        }

        .login-left .feature-list {
            list-style: none;
            padding: 0;
            margin: 0;
            position: relative;
            z-index: 1;
            width: 100%;
        }

        .login-left .feature-list li {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: rgba(255,255,255,0.85);
            font-size: 0.88rem;
            padding: 0.5rem 0;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .login-left .feature-list li:last-child { border-bottom: none; }

        .login-left .feature-list li i {
            color: #F9E79F;
            font-size: 1rem;
            flex-shrink: 0;
        }

        /* ── RIGHT PANEL ─────────────────────────────────── */
        .login-right {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .login-card {
            width: 100%;
            max-width: 420px;
        }

        .login-card h3 {
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 0.25rem;
        }

        .login-card .subtitle {
            color: #6c757d;
            font-size: 0.9rem;
            margin-bottom: 2rem;
        }

        .form-label {
            font-weight: 600;
            font-size: 0.85rem;
            color: #444;
        }

        .form-control {
            border-radius: 8px;
            border: 1.5px solid #dee2e6;
            padding: 0.65rem 0.9rem;
            font-size: 0.9rem;
            transition: border-color 0.15s, box-shadow 0.15s;
        }

        .form-control:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(46,134,193,0.15);
        }

        .input-group-text {
            border-radius: 8px 0 0 8px;
            background: #f8f9fa;
            border: 1.5px solid #dee2e6;
            border-right: none;
            color: var(--primary);
        }

        .input-group .form-control {
            border-radius: 0 8px 8px 0;
        }

        .btn-masuk {
            width: 100%;
            background: var(--primary);
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 0.75rem;
            font-size: 0.95rem;
            font-weight: 600;
            letter-spacing: 0.3px;
            transition: background 0.15s;
            margin-top: 0.5rem;
        }

        .btn-masuk:hover {
            background: var(--accent);
        }

        .divider {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: #aaa;
            font-size: 0.8rem;
            margin: 1.5rem 0;
        }

        .divider::before, .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #dee2e6;
        }

        .footer-text {
            text-align: center;
            font-size: 0.78rem;
            color: #aaa;
            margin-top: 2rem;
        }

        @media (max-width: 767px) {
            .login-left { display: none; }
            body { background: var(--primary); }
            .login-right { padding: 1.5rem; background: #fff; border-radius: 16px 16px 0 0; margin-top: auto; align-items: flex-start; }
        }
    </style>
</head>
<body>

    {{-- Left Panel --}}
    <div class="login-left">
        <div class="brand-logo">
            <i class="bi bi-award-fill"></i>
        </div>
        <h2>Sistem Informasi<br>Pengajuan Pensiun</h2>
        <p class="instansi">Badan Kepegawaian Negara<br>Instansi Pemerintah</p>

        <ul class="feature-list">
            <li><i class="bi bi-check-circle-fill"></i> Pengajuan pensiun online terpadu</li>
            <li><i class="bi bi-check-circle-fill"></i> Tracking status pengajuan real-time</li>
            <li><i class="bi bi-check-circle-fill"></i> Upload berkas persyaratan digital</li>
            <li><i class="bi bi-check-circle-fill"></i> Verifikasi berkas terintegrasi</li>
            <li><i class="bi bi-check-circle-fill"></i> Informasi regulasi &amp; artikel MPP</li>
        </ul>
    </div>

    {{-- Right Panel --}}
    <div class="login-right">
        <div class="login-card">
            <h3>Selamat Datang</h3>
            <p class="subtitle">Masukkan kredensial Anda untuk mengakses sistem</p>

            {{-- Validation Errors --}}
            @if($errors->any())
                <div class="alert alert-danger d-flex align-items-start gap-2 py-2 mb-3" role="alert">
                    <i class="bi bi-exclamation-triangle-fill mt-1 flex-shrink-0"></i>
                    <div>
                        @foreach($errors->all() as $error)
                            <div style="font-size:0.875rem">{{ $error }}</div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger d-flex align-items-start gap-2 py-2 mb-3">
                    <i class="bi bi-exclamation-triangle-fill mt-1 flex-shrink-0"></i>
                    <div style="font-size:0.875rem">{{ session('error') }}</div>
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST">
                @csrf

                {{-- Email --}}
                <div class="mb-3">
                    <label for="email" class="form-label">Alamat Email</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope-fill"></i></span>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="form-control @error('email') is-invalid @enderror"
                            value="{{ old('email') }}"
                            placeholder="nama@instansi.go.id"
                            required
                            autofocus
                        >
                    </div>
                </div>

                {{-- Password --}}
                <div class="mb-3">
                    <label for="password" class="form-label">Kata Sandi</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-control @error('password') is-invalid @enderror"
                            placeholder="Masukkan kata sandi"
                            required
                        >
                        <button type="button" class="btn btn-outline-secondary" id="togglePassword" tabindex="-1"
                                style="border-radius:0 8px 8px 0;border-left:none">
                            <i class="bi bi-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>

                {{-- Remember Me --}}
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="remember" name="remember"
                           {{ old('remember') ? 'checked' : '' }}>
                    <label class="form-check-label" for="remember" style="font-size:0.875rem;color:#555">
                        Ingat saya di perangkat ini
                    </label>
                </div>

                <button type="submit" class="btn-masuk">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Masuk
                </button>
            </form>

            <div class="footer-text">
                &copy; {{ date('Y') }} Sistem Informasi Pengajuan Pensiun<br>
                Didukung oleh Badan Kepegawaian Negara
            </div>
        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Toggle password visibility
    const toggleBtn  = document.getElementById('togglePassword');
    const passwordEl = document.getElementById('password');
    const toggleIcon = document.getElementById('toggleIcon');

    if (toggleBtn) {
        toggleBtn.addEventListener('click', () => {
            const isPassword = passwordEl.type === 'password';
            passwordEl.type  = isPassword ? 'text' : 'password';
            toggleIcon.className = isPassword ? 'bi bi-eye-slash' : 'bi bi-eye';
        });
    }
</script>
</body>
</html>
