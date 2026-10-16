<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        if (! $user->hasRole(['manager_gudang', 'manager_accounting', 'direktur_sdm'])) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengelola akun pengguna.');
        }

        $users = User::with('roleModel')->latest()->paginate(15);
        $roles = Role::all();

        return Inertia::render('Users/Index', [
            'users' => $users,
            'roles' => $roles,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $currentUser = $request->user();
        if (! $currentUser->hasRole(['manager_gudang', 'manager_accounting', 'direktur_sdm'])) {
            abort(403, 'Anda tidak memiliki hak akses untuk menambahkan pengguna.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', Password::defaults()],
            'role' => 'required|exists:roles,name',
            'nip' => 'nullable|string|max:50',
            'jabatan' => 'required|string|max:100',
        ]);

        if ($request->role === 'direktur_sdm' && ! $currentUser->hasRole('direktur_sdm')) {
            abort(403, 'Hanya Direktur SDM yang berhak membuat akun dengan role Direktur SDM.');
        }

        $roleObj = Role::where('name', $request->role)->first();

        $newUser = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'role_id' => $roleObj?->id,
            'nip' => $request->nip,
            'jabatan' => $request->jabatan,
        ]);

        AuditLogService::log(
            $currentUser,
            'CREATE_USER',
            User::class,
            $newUser->id,
            null,
            $newUser->toArray()
        );

        return redirect()->route('users.index')
            ->with('success', "Akun user {$newUser->name} berhasil dibuat.");
    }

    public function updateRole(Request $request, int $id): RedirectResponse
    {
        $currentUser = $request->user();
        if (! $currentUser->hasRole(['manager_gudang', 'manager_accounting', 'direktur_sdm'])) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengubah role pengguna.');
        }

        $request->validate([
            'role' => 'required|exists:roles,name',
        ]);

        $targetUser = User::findOrFail($id);

        if ($currentUser->id === $targetUser->id) {
            abort(403, 'Anda tidak diperbolehkan mengubah role akun Anda sendiri.');
        }

        if ($request->role === 'direktur_sdm' && ! $currentUser->hasRole('direktur_sdm')) {
            abort(403, 'Hanya Direktur SDM yang berhak menetapkan role Direktur SDM.');
        }

        if ($targetUser->hasRole('direktur_sdm') && ! $currentUser->hasRole('direktur_sdm')) {
            abort(403, 'Hanya Direktur SDM yang berhak mengubah role akun Direktur SDM.');
        }

        $roleObj = Role::where('name', $request->role)->first();

        $oldValues = $targetUser->toArray();
        $targetUser->update([
            'role' => $request->role,
            'role_id' => $roleObj?->id,
        ]);

        AuditLogService::log(
            $currentUser,
            'ASSIGN_ROLE',
            User::class,
            $targetUser->id ?? $id,
            $oldValues,
            $targetUser->fresh()->toArray()
        );

        return redirect()->route('users.index')
            ->with('success', "Role user {$targetUser->name} berhasil diubah menjadi {$roleObj->display_name}.");
    }
}
