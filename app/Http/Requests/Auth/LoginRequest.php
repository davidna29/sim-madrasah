<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Jumlah maksimal kegagalan login sebelum akun dikunci.
     */
    private const MAX_ACCOUNT_ATTEMPTS = 5;

    /**
     * Lama penguncian akun dalam menit.
     */
    private const ACCOUNT_LOCK_MINUTES = 15;

    /**
     * Menentukan apakah request diizinkan.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Membersihkan input sebelum divalidasi.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'login' => trim((string) $this->input('login')),
        ]);
    }

    /**
     * Aturan validasi formulir login.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'login' => [
                'required',
                'string',
                'max:191',
            ],
            'password' => [
                'required',
                'string',
            ],
        ];
    }

    /**
     * Menjalankan proses autentikasi.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $identifier = (string) $this->input('login');

        /*
         * Jika input memiliki format email, cari melalui kolom email.
         * Selain itu, anggap input sebagai username.
         */
        $loginColumn = filter_var($identifier, FILTER_VALIDATE_EMAIL)
            ? 'email'
            : 'username';

        $user = User::query()
            ->where($loginColumn, $identifier)
            ->first();

        /*
         * Bersihkan status penguncian jika waktunya sudah berakhir.
         */
        if ($user !== null) {
            $this->clearExpiredAccountLock($user);

            if ($this->isAccountLocked($user)) {
                $remainingMinutes = max(
                    1,
                    (int) ceil(now()->diffInSeconds($user->locked_until) / 60)
                );

                throw ValidationException::withMessages([
                    'login' => "Akun sedang dikunci. Coba kembali dalam {$remainingMinutes} menit.",
                ]);
            }
        }

        /*
         * Rate limiting tetap digunakan untuk melindungi endpoint
         * dari percobaan login berulang berdasarkan login dan IP.
         */
        $this->ensureIsNotRateLimited();

        if (
            $user === null ||
            ! Hash::check((string) $this->input('password'), $user->password)
        ) {
            $this->recordFailedLogin($user);

            throw ValidationException::withMessages([
                'login' => trans('auth.failed'),
            ]);
        }

        /*
         * Password benar, tetapi akun harus tetap berstatus aktif.
         */
        if ($user->status !== 'active') {
            throw ValidationException::withMessages([
                'login' => 'Akun tidak aktif. Hubungi administrator SIM Madrasah.',
            ]);
        }

        /*
         * Login menggunakan instance User yang sudah diperiksa.
         */
        Auth::login(
            $user,
            $this->boolean('remember')
        );

        /*
         * Reset kegagalan dan simpan informasi login terakhir.
         */
        $user->forceFill([
            'failed_login_count' => 0,
            'locked_until' => null,
            'last_login_at' => now(),
            'last_login_ip' => $this->ip(),
        ])->save();

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Mencatat kegagalan login.
     */
    private function recordFailedLogin(?User $user): void
    {
        RateLimiter::hit($this->throttleKey());

        /*
         * Pengguna yang tidak ditemukan tetap dibatasi oleh
         * RateLimiter, tetapi tidak memiliki record untuk diperbarui.
         */
        if ($user === null) {
            return;
        }

        $user->increment('failed_login_count');
        $user->refresh();

        if ($user->failed_login_count >= self::MAX_ACCOUNT_ATTEMPTS) {
            $user->forceFill([
                'locked_until' => now()->addMinutes(
                    self::ACCOUNT_LOCK_MINUTES
                ),
            ])->save();
        }
    }

    /**
     * Memeriksa apakah akun masih dikunci.
     */
    private function isAccountLocked(User $user): bool
    {
        return $user->locked_until !== null
            && $user->locked_until->isFuture();
    }

    /**
     * Membuka kembali akun yang masa pengunciannya sudah berakhir.
     */
    private function clearExpiredAccountLock(User $user): void
    {
        if (
            $user->locked_until === null ||
            ! $user->locked_until->isPast()
        ) {
            return;
        }

        $user->forceFill([
            'failed_login_count' => 0,
            'locked_until' => null,
        ])->save();
    }

    /**
     * Memastikan endpoint login tidak melewati batas percobaan.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'login' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Membuat identitas unik untuk rate limiting.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(
            Str::lower((string) $this->input('login'))
            .'|'
            .$this->ip()
        );
    }
}
