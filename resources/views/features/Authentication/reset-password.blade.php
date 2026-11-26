<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Password Baru - LambungCare</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #e0f7fa, #bbdefb);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.25rem;
        }
        .auth-card {
            background: #fff;
            padding: 2.5rem;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 440px;
        }
        .auth-card h2 { text-align: center; margin-bottom: .5rem; color: #1565c0; }
        .subtitle {
            text-align: center; color: #666; font-size: .9rem;
            line-height: 1.6; margin-bottom: 1.5rem;
        }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; margin-bottom: .4rem; font-weight: 600; color: #333; }
        .form-group input {
            width: 100%; padding: .65rem .75rem; border: 1px solid #ccc;
            border-radius: 8px; font-size: 1rem; font-family: inherit;
        }
        .form-group input:focus { outline: none; border-color: #1565c0; box-shadow: 0 0 0 3px rgba(21,101,192,.12); }
        .form-group input[readonly] { background: #f5f5f5; color: #666; }
        .error { color: #c62828; font-size: .85rem; margin-bottom: .75rem; }
        .btn {
            width: 100%; padding: .75rem; background: #1565c0; color: #fff;
            border: none; border-radius: 8px; font-size: 1rem; cursor: pointer;
            font-family: inherit; margin-top: .5rem;
        }
        .btn:hover { background: #0d47a1; }
        .auth-link { text-align: center; margin-top: 1.25rem; font-size: .9rem; }
        .auth-link a { color: #1565c0; text-decoration: none; font-weight: 600; }
        .auth-link a:hover { text-decoration: underline; }
        .password-wrapper { position: relative; }
        .password-wrapper input { padding-right: 2.8rem; }
        .toggle-password {
            position: absolute; right: .6rem; top: 50%; transform: translateY(-50%);
            background: none; border: none; cursor: pointer;
            color: #757575; padding: .2rem; line-height: 1;
        }
        .toggle-password:hover { color: #1565c0; }
        .toggle-password svg { display: block; width: 20px; height: 20px; }
    </style>
</head>
<body>
    <div class="auth-card">
        <h2>Buat Password Baru</h2>
        <p class="subtitle">Masukkan password baru untuk akun Anda.</p>

        @if ($errors->any())
            <div class="error"><strong>{{ $errors->first() }}</strong></div>
        @endif

        <form method="POST" action="{{ route('password.store') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email"
                       value="{{ old('email', $email) }}" required autocomplete="email">
            </div>

            <div class="form-group">
                <label for="password">Password Baru</label>
                <div class="password-wrapper">
                    <input type="password" id="password" name="password" required autocomplete="new-password">
                    <button type="button" class="toggle-password" onclick="togglePassword('password', this)" title="Lihat password" aria-label="Lihat password">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </button>
                </div>
            </div>

            <div class="form-group">
                <label for="password_confirmation">Konfirmasi Password Baru</label>
                <div class="password-wrapper">
                    <input type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password">
                    <button type="button" class="toggle-password" onclick="togglePassword('password_confirmation', this)" title="Lihat password" aria-label="Lihat password">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn">Simpan Password Baru</button>
        </form>

        <div class="auth-link">
            <a href="{{ route('login') }}">Kembali ke halaman masuk</a>
        </div>
    </div>

    <script>
        function togglePassword(inputId, btn) {
            const input = document.getElementById(inputId);
            input.type = input.type === 'password' ? 'text' : 'password';
        }
    </script>
</body>
</html>
