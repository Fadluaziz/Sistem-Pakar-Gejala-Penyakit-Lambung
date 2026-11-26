@extends('layouts.app')

@section('title', 'Edit Hak Akses')

@section('content')
    <div class="card">
        <div class="history-head">
            <div>
                <h2>Edit Hak Akses: {{ $user->name }}</h2>
                <p class="muted" style="margin-top: .25rem;">{{ $user->email }}</p>
            </div>
            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Kembali</a>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-error">
            <strong>{{ $errors->first() }}</strong>
        </div>
    @endif

    @php
        $roleLabels = [
            'patient' => 'Pasien',
            'doctor' => 'Dokter',
            'editor' => 'Editor Basis Aturan',
            'admin' => 'Administrator Sistem',
        ];
        $permissionLabels = [
            'access-education' => 'Mengakses Halaman Edukasi',
            'take-consultation' => 'Mengikuti Konsultasi',
            'update-profile' => 'Mengubah Profil',
            'review-consultations' => 'Meninjau Riwayat Konsultasi',
            'validate-consultation' => 'Memvalidasi Hasil Konsultasi',
            'create-manual-diagnosis' => 'Membuat Diagnosa Manual',
            'manage-rules' => 'Mengelola Aturan Penalaran',
            'manage-symptoms' => 'Mengelola Data Gejala',
            'manage-diseases' => 'Mengelola Data Penyakit',
            'manage-users' => 'Mengelola Pengguna & Hak Akses',
        ];
    @endphp

    <form method="POST" action="{{ route('admin.users.update', $user) }}">
        @csrf
        @method('PUT')

        <div class="card">
            <h3 style="margin-bottom: 1rem; color: #1565c0; border-bottom: 1px solid #eee; padding-bottom: .5rem;">Role</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: .5rem;">
                @foreach($roles as $role)
                    <label style="display: flex; align-items: center; gap: .5rem; cursor: pointer;">
                        <input type="checkbox" name="roles[]" value="{{ $role->name }}"
                            {{ $user->hasRole($role->name) ? 'checked' : '' }}
                            style="width: 18px; height: 18px; accent-color: #1565c0;">
                        {{ $roleLabels[$role->name] ?? ucfirst($role->name) }}
                    </label>
                @endforeach
            </div>
        </div>

        <div class="card">
            <h3 style="margin-bottom: 1rem; color: #1565c0; border-bottom: 1px solid #eee; padding-bottom: .5rem;">Permission Tambahan</h3>
            <p class="muted" style="margin-bottom: 1rem; font-size: .85rem;">Centang permission jika ingin memberikan akses khusus di luar role-nya.</p>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: .5rem;">
                @foreach($permissions as $permission)
                    <label style="display: flex; align-items: center; gap: .5rem; cursor: pointer;">
                        <input type="checkbox" name="permissions[]" value="{{ $permission->name }}"
                            {{ $user->hasDirectPermission($permission->name) ? 'checked' : '' }}
                            style="width: 18px; height: 18px; accent-color: #1565c0;">
                        {{ $permissionLabels[$permission->name] ?? ucfirst(str_replace('-', ' ', $permission->name)) }}
                    </label>
                @endforeach
            </div>
        </div>

        <div style="display: flex; justify-content: flex-end; margin-bottom: 3rem;">
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        </div>
    </form>
@endsection
