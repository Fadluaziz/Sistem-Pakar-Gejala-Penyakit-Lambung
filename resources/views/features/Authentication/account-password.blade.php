@extends('layouts.app')

@section('title', 'Ubah Password')

@section('content')
    <div class="card">
        <div class="history-head">
            <div>
                <h2>Ubah Password</h2>
                <p class="muted" style="margin-top: .25rem;">
                    Masukkan password saat ini, lalu tentukan password baru Anda.
                </p>
            </div>
            <a href="{{ url('/' . auth()->user()->role . '/dashboard') }}" class="btn btn-secondary">Kembali ke Dashboard</a>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-error">
            <strong>{{ $errors->first() }}</strong>
        </div>
    @endif

    <div class="card">
        <form method="POST" action="{{ route('account.password.update') }}">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="current_password">Password Saat Ini</label>
                <input type="password" id="current_password" name="current_password" required autocomplete="current-password">
            </div>

            <div class="form-group">
                <label for="password">Password Baru</label>
                <input type="password" id="password" name="password" required autocomplete="new-password">
                <p class="muted" style="margin-top: .35rem; font-size: .82rem;">Minimal 6 karakter.</p>
            </div>

            <div class="form-group">
                <label for="password_confirmation">Konfirmasi Password Baru</label>
                <input type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password">
            </div>

            <div class="actions">
                <span class="muted"></span>
                <button type="submit" class="btn btn-primary">Simpan Password Baru</button>
            </div>
        </form>
    </div>
@endsection
