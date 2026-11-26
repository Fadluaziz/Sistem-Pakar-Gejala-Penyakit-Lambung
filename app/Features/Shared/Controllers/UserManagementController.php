<?php

namespace App\Features\Shared\Controllers;

use App\Features\Shared\Services\AuthService;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserManagementController extends BaseController
{
    public function __construct()
    {
        parent::__construct(app(AuthService::class));
    }

    public function index(): View
    {
        $users = User::with('roles')->orderBy('name')->paginate(15);

        return view('features.Shared.users.index', compact('users'));
    }

    public function edit(User $user): View
    {
        $roles = Role::all();
        $permissions = Permission::all();

        return view('features.Shared.users.edit', compact('user', 'roles', 'permissions'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'roles' => ['nullable', 'array'],
            'roles.*' => ['exists:roles,name'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,name'],
        ]);

        // Sinkronisasi Role Spatie
        $user->syncRoles($request->input('roles', []));

        // Sinkronisasi Permission Langsung ke User
        $user->syncPermissions($request->input('permissions', []));

        // Update kolom legacy `role` untuk fallback (ambil role pertama)
        $primaryRole = $request->input('roles.0', 'patient');
        $user->update(['role' => $primaryRole]);

        return redirect()->route('admin.users.index')
            ->with('success', "Hak akses untuk {$user->name} berhasil diperbarui.");
    }
}
