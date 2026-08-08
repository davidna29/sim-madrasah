<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    /**
     * Memperbarui password pengguna yang sedang login.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => [
                'required',
                'current_password',
            ],
            'password' => [
                'required',
                Password::defaults(),
                'confirmed',
            ],
        ]);

        $request->user()->forceFill([
            'password' => Hash::make($validated['password']),
            'password_changed_at' => now(),
            'failed_login_count' => 0,
            'locked_until' => null,
        ])->save();

        return back()->with('status', 'password-updated');
    }
}
