<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Daftar peran bawaan aplikasi.
     */
    private const PERMISSIONS = [
        'access-education',
        'take-consultation',
        'update-profile',
        'review-consultations',
        'validate-consultation',
        'create-manual-diagnosis',
        'manage-rules',
        'manage-symptoms',
        'manage-diseases',
        'manage-users',
        'schedule-appointment',
        'manage-appointments',
    ];

    public function run(): void
    {
        // 1. Buat Permission
        foreach (self::PERMISSIONS as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // 2. Buat Role
        $patient = Role::firstOrCreate(['name' => 'patient', 'guard_name' => 'web']);
        $doctor = Role::firstOrCreate(['name' => 'doctor', 'guard_name' => 'web']);
        $editor = Role::firstOrCreate(['name' => 'editor', 'guard_name' => 'web']);
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        // 3. Assign Permission ke Role
        $patient->syncPermissions(['access-education', 'take-consultation', 'update-profile', 'schedule-appointment']);

        $doctor->syncPermissions([
            'access-education',
            'review-consultations',
            'validate-consultation',
            'create-manual-diagnosis',
            'manage-appointments',
        ]);

        $editor->syncPermissions([
            'access-education',
            'manage-rules',
            'manage-symptoms',
            'manage-diseases',
        ]);

        // Admin mendapat semua permission
        $admin->syncPermissions(Permission::all());
    }
}
