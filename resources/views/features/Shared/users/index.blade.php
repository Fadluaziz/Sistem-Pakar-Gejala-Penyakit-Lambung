@extends('layouts.app')

@section('title', 'Manajemen Pengguna')

@section('content')
    <div class="card">
        <div class="history-head">
            <div>
                <h2>Manajemen Pengguna</h2>
                <p class="muted" style="margin-top: .25rem;">Kelola role dan hak akses (permission) untuk setiap pengguna.</p>
            </div>
            <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">Kembali ke Dashboard</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div style="overflow-x: auto;">
            <table class="print-meta" style="width: 100%;">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                @foreach($user->roles as $role)
                                    <span class="pill" style="background:#e3f2fd; color:#1565c0;">{{ $role->name }}</span>
                                @endforeach
                            </td>
                            <td>
                                <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary" style="padding: .4rem .8rem; font-size: .8rem;">Edit Akses</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="pagination" style="margin-top: 1rem;">
            {{ $users->links() }}
        </div>
    </div>
@endsection
