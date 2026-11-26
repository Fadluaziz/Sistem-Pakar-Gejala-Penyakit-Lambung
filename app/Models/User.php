<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Exceptions\RoleDoesNotExist;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'username',
        'role',
        'nik',
        'birth_date',
        'gender',
        'phone',
        'address',
        'occupation',
        'blood_type',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'birth_date' => 'date',
        ];
    }

    /**
     * Sinkronisasi role Spatie setiap kali kolom `role` disimpan,
     * agar backward compatibility dengan kode lama tetap terjaga.
     */
    protected static function booted(): void
    {
        static::saved(function (User $user): void {
            if ($user->wasChanged('role') || $user->roles()->doesntExist()) {
                if ($user->role) {
                    try {
                        $user->syncRoles([$user->role]);
                    } catch (RoleDoesNotExist) {
                        // Abaikan jika role belum di-seed (misal saat testing migrasi awal).
                    }
                }
            }
        });
    }
}
