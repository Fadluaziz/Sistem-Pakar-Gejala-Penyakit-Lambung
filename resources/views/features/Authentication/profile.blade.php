@extends('layouts.app')

@section('title', 'Profil Saya')

@section('content')
    <div class="card">
        <div class="history-head">
            <div>
                <h2>Profil Saya</h2>
                <p class="muted" style="margin-top: .25rem;">
                    Data diri Anda dipakai sebagai identitas pada hasil diagnosa yang dapat dicetak.
                </p>
            </div>
            <a href="{{ route('patient.dashboard') }}" class="btn btn-secondary">Kembali ke Dashboard</a>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-error">
            <strong>{{ $errors->first() }}</strong>
        </div>
    @endif

    @php
        $rows = [
            'Nama Lengkap' => $user->name,
            'Email' => $user->email,
            'NIK' => $user->nik,
            'Tanggal Lahir' => $user->birth_date?->locale('id')->isoFormat('D MMMM YYYY'),
            'Umur' => $user->birth_date?->age !== null ? $user->birth_date->age . ' tahun' : null,
            'Jenis Kelamin' => $user->gender,
            'Golongan Darah' => $user->blood_type,
            'Nomor Telepon' => $user->phone,
            'Pekerjaan' => $user->occupation,
            'Alamat' => $user->address,
        ];
    @endphp

    {{-- Ringkasan profil lengkap --}}
    <div class="card">
        <div class="history-head">
            <div>
                <h2>Profil Lengkap</h2>
                <p class="muted" style="margin-top: .25rem;">Ringkasan data diri yang tersimpan di akun Anda.</p>
            </div>
            <div class="history-badges">
                <span class="pill {{ $completeness['filled'] === $completeness['total'] ? 'pill-success' : 'pill-danger' }}">
                    {{ $completeness['filled'] }}/{{ $completeness['total'] }} data terisi
                </span>
            </div>
        </div>

        <table class="print-meta">
            @foreach ($rows as $label => $value)
                <tr>
                    <th>{{ $label }}</th>
                    <td>
                        @if (filled($value))
                            {{ $value }}
                        @else
                            <span class="muted">Belum diisi</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </table>

        <div class="progress-track" style="margin-top: 1.25rem;">
            <div class="progress-fill" style="width: {{ $completeness['percent'] }}%"></div>
        </div>

        @if ($completeness['filled'] < $completeness['total'])
            <p class="muted" style="margin-top: .5rem;">
                Lengkapi data di bawah agar hasil diagnosa yang dicetak memuat identitas lengkap.
            </p>
        @else
            <p class="muted" style="margin-top: .5rem;">Profil Anda sudah lengkap dan siap dipakai pada cetakan hasil diagnosa.</p>
        @endif
    </div>

    {{-- Form ubah profil --}}
    <div class="card">
        <h2>Ubah Profil</h2>
        <form method="POST" action="{{ route('profile.update') }}" style="margin-top: .75rem;">
            @csrf
            @method('PUT')

            <div class="section-heading" style="font-size: 1rem; margin-bottom: .85rem;">Data Akun</div>

            <div class="form-group">
                <label for="name">Nama Lengkap</label>
                <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required>
            </div>

            <div class="section-heading" style="font-size: 1rem; margin: 1.5rem 0 .85rem;">Data Diri</div>

            <div class="form-row">
                <div class="form-group">
                    <label for="nik">NIK</label>
                    <input type="text" id="nik" name="nik" maxlength="16" inputmode="numeric"
                           value="{{ old('nik', $user->nik) }}" placeholder="16 digit angka">
                </div>
                <div class="form-group">
                    <label for="birth_date">Tanggal Lahir</label>
                    <input type="date" id="birth_date" name="birth_date"
                           value="{{ old('birth_date', $user->birth_date?->format('Y-m-d')) }}">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="gender">Jenis Kelamin</label>
                    <select id="gender" name="gender">
                        <option value="">Pilih</option>
                        <option value="Laki-laki" @selected(old('gender', $user->gender) === 'Laki-laki')>Laki-laki</option>
                        <option value="Perempuan" @selected(old('gender', $user->gender) === 'Perempuan')>Perempuan</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="blood_type">Golongan Darah</label>
                    <select id="blood_type" name="blood_type">
                        <option value="">Pilih</option>
                        @foreach (['A', 'B', 'AB', 'O'] as $type)
                            <option value="{{ $type }}" @selected(old('blood_type', $user->blood_type) === $type)>{{ $type }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="phone">Nomor Telepon</label>
                    <input type="text" id="phone" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="08xxxxxxxxxx">
                </div>
                <div class="form-group">
                    <label for="occupation">Pekerjaan</label>
                    <input type="text" id="occupation" name="occupation" value="{{ old('occupation', $user->occupation) }}">
                </div>
            </div>

            <div class="form-group">
                <label for="address">Alamat</label>
                <textarea id="address" name="address" placeholder="Alamat lengkap">{{ old('address', $user->address) }}</textarea>
            </div>

            <div class="actions">
                <span class="muted"></span>
                <button type="submit" class="btn btn-primary">Simpan Profil</button>
            </div>
        </form>
    </div>
@endsection
