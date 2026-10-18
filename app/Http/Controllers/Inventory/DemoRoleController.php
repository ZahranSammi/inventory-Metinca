<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DemoRoleController extends Controller
{
    /**
     * Switch demo user for quick testing of all 4 roles.
     */
    public function switchRole(Request $request)
    {
        $role = $request->input('role');

        $user = match ($role) {
            'accounting', 'staff_accounting' => User::where('role', 'staff_accounting')->first(),
            'manager' => User::where('role', 'manager')->first(),
            'direktur', 'direktur_sdm' => User::where('role', 'direktur_sdm')->first(),
            default => User::where('role', 'karyawan')->first(),
        };

        if ($user) {
            Auth::login($user);
            return redirect()->back()->with('success', "Beralih ke peran: {$user->role_label} ({$user->name})");
        }

        return redirect()->back()->with('error', 'User untuk role tersebut tidak ditemukan.');
    }
}
