<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountIsActive
{
    /**
     * Memastikan akun yang sedang login masih aktif.
     */
    public function handle(
        Request $request,
        Closure $next
    ): Response {
        $user = $request->user();

        if ($user === null) {
            return $next($request);
        }

        $isInactive = $user->status !== 'active';

        $isLocked = $user->locked_until !== null
            && $user->locked_until->isFuture();

        if (! $isInactive && ! $isLocked) {
            return $next($request);
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->withErrors([
                'login' => 'Sesi dihentikan karena akun tidak aktif atau sedang dikunci.',
            ]);
    }
}
