<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - LambungCare</title>
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
        .error { color: #c62828; font-size: .85rem; margin-bottom: .75rem; }
        .success {
            background: #e8f5e9; color: #2e7d32; border: 1px solid #a5d6a7;
            border-radius: 8px; padding: .65rem .85rem;
            font-size: .87rem; margin-bottom: 1rem;
        }
        .btn {
            width: 100%; padding: .75rem; background: #1565c0; color: #fff;
            border: none; border-radius: 8px; font-size: 1rem; cursor: pointer;
            font-family: inherit; margin-top: .5rem;
        }
        .btn:hover { background: #0d47a1; }
        .auth-link { text-align: center; margin-top: 1.25rem; font-size: .9rem; }
        .auth-link a { color: #1565c0; text-decoration: none; font-weight: 600; }
        .auth-link a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="auth-card">
        <h2>Lupa Password</h2>
        <p class="subtitle">
            Masukkan email yang terdaftar. Kami akan mengirim tautan untuk membuat password baru.
        </p>

        @if (session('success'))
            <div class="success">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="error"><strong>{{ $errors->first() }}</strong></div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email"
                       value="{{ old('email') }}" required autofocus autocomplete="email">
            </div>
            <button type="submit" class="btn">Kirim Tautan Reset</button>
        </form>

        <div class="auth-link">
            Ingat password Anda? <a href="{{ route('login') }}">Kembali ke halaman masuk</a>
        </div>
    </div>
</body>
</html>
