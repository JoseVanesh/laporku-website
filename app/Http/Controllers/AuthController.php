<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Mail\RegistrationOtpCode;
use App\Services\Firestore\UserStore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Kreait\Laravel\Firebase\Facades\Firebase;
use Throwable;

class AuthController extends Controller
{
    public function showLogin(Request $request)
    {
        if ($request->session()->has('firebase.uid')) {
            return redirect()->route('home');
        }

        return view('auth.login');
    }

    public function showRegister(Request $request)
    {
        if ($request->session()->has('firebase.uid')) {
            return redirect()->route('home');
        }

        return view('auth.register');
    }

    /**
     * Menerima ID token dari Firebase Auth di browser, memverifikasinya,
     * lalu membuat sesi Laravel.
     */
    public function login(Request $request, UserStore $users)
    {
        $validated = $request->validate([
            'idToken' => 'required|string',
        ]);

        $claims = $this->verifyToken($validated['idToken']);

        if ($claims === null) {
            return response()->json([
                'message' => 'Sesi login tidak valid. Silakan coba lagi.',
            ], 401);
        }

        $uid = (string) $claims->claims()->get('sub');
        $email = $this->normalizeEmail((string) ($claims->claims()->get('email') ?? ''));

        try {
            $user = $users->find($uid);

            if ($user === null) {
                $user = $users->create($uid, [
                    'nama' => (string) ($claims->claims()->get('name') ?: explode('@', $email)[0]),
                    'email' => $email,
                    'telepon' => '',
                    'role' => $this->defaultRole($email),
                    'blok' => '',
                    'rumah' => '',
                ]);
            } else {
                $user = $this->applySuperAdminRole($users, $user, $email);
            }

            $this->startSession($user);
        } catch (Throwable $e) {
            Log::error('Gagal memproses login: operasi Firestore gagal', [
                'uid' => $uid,
                'exception' => $e,
            ]);

            return response()->json([
                'message' => 'Server sedang bermasalah saat menghubungi data pengguna. Silakan coba lagi.',
            ], 500);
        }

        return response()->json(['redirect' => $this->homeFor((string) $user['role'])]);
    }

    public function sendRegisterOtp(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email:rfc|max:255',
        ]);

        $email = $this->normalizeEmail($validated['email']);

        if ($this->firebaseEmailExists($email)) {
            return response()->json([
                'message' => 'Email sudah terdaftar. Silakan gunakan login.',
            ], 422);
        }

        $cooldownKey = $this->otpCooldownKey($email);

        if (Cache::has($cooldownKey)) {
            $cooldownRemaining = $this->cacheSecondsRemaining($cooldownKey);
            $message = $cooldownRemaining !== null
                ? "Tunggu {$cooldownRemaining} detik sebelum mengirim ulang kode."
                : 'Tunggu sebentar sebelum mengirim ulang kode.';

            return response()->json([
                'message' => $message,
            ], 429);
        }

        $code = (string) random_int(100000, 999999);
        $expiresMinutes = $this->otpExpiresMinutes();

        Cache::put($this->otpKey($email), [
            'hash' => hash('sha256', $code),
            'attempts' => 0,
        ], now()->addMinutes($expiresMinutes));

        Cache::put($cooldownKey, true, now()->addSeconds($this->otpResendCooldownSeconds()));

        try {
            Mail::to($email)->send(new RegistrationOtpCode($code, $expiresMinutes));
        } catch (Throwable $e) {
            Log::error('Gagal mengirim email OTP pendaftaran', [
                'email_hash' => sha1($email),
                'exception' => $e,
            ]);

            Cache::forget($this->otpKey($email));
            Cache::forget($cooldownKey);

            return response()->json([
                'message' => 'Gagal mengirim kode verifikasi. Silakan coba lagi.',
            ], 500);
        }

        return response()->json([
            'message' => 'Kode verifikasi telah dikirim ke email Anda.',
            'expires_in' => $expiresMinutes * 60,
            'resend_in' => $this->otpResendCooldownSeconds(),
        ]);
    }

    public function verifyRegisterOtp(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email:rfc|max:255',
            'code' => 'required|digits:6',
        ]);

        $email = $this->normalizeEmail($validated['email']);
        $otpKey = $this->otpKey($email);
        $record = Cache::get($otpKey);

        if (! is_array($record) || empty($record['hash'])) {
            return response()->json([
                'message' => 'Kode verifikasi tidak ditemukan atau sudah kedaluwarsa. Silakan minta kode baru.',
            ], 422);
        }

        $attempts = (int) ($record['attempts'] ?? 0);

        if ($attempts >= $this->otpMaxAttempts()) {
            Cache::forget($otpKey);

            return response()->json([
                'message' => 'Terlalu banyak percobaan salah. Silakan minta kode baru.',
            ], 429);
        }

        $inputHash = hash('sha256', (string) $validated['code']);

        if (! hash_equals((string) $record['hash'], $inputHash)) {
            $attempts++;
            $record['attempts'] = $attempts;

            if ($attempts >= $this->otpMaxAttempts()) {
                Cache::forget($otpKey);

                return response()->json([
                    'message' => 'Terlalu banyak percobaan salah. Silakan minta kode baru.',
                ], 429);
            }

            $ttl = $this->cacheSecondsRemaining($otpKey) ?? ($this->otpExpiresMinutes() * 60);
            Cache::put($otpKey, $record, now()->addSeconds(max($ttl, 1)));

            return response()->json([
                'message' => 'Kode verifikasi salah. Silakan periksa kembali.',
                'remaining_attempts' => $this->otpMaxAttempts() - $attempts,
            ], 422);
        }

        Cache::forget($otpKey);

        $token = Str::random(64);
        Cache::put($this->verifiedKey($email), [
            'hash' => hash('sha256', $token),
        ], now()->addMinutes($this->verifiedTokenMinutes()));

        return response()->json([
            'message' => 'Email berhasil diverifikasi.',
            'verification_token' => $token,
            'expires_in' => $this->verifiedTokenMinutes() * 60,
        ]);
    }

    public function register(Request $request, UserStore $users)
    {
        $validated = $request->validate([
            'idToken' => 'required|string',
            'verification_token' => 'required|string|size:64',
            'nama' => 'required|string|min:3|max:100',
            'telepon' => 'required|string|min:9|max:20',
            'blok' => 'required|string|max:20',
            'rumah' => 'nullable|string|max:20',
        ]);

        $claims = $this->verifyToken($validated['idToken']);

        if ($claims === null) {
            return response()->json([
                'message' => 'Sesi pendaftaran tidak valid. Silakan coba lagi.',
            ], 401);
        }

        $uid = (string) $claims->claims()->get('sub');
        $email = $this->normalizeEmail((string) ($claims->claims()->get('email') ?? ''));

        if ($email === '') {
            return response()->json([
                'message' => 'Email akun tidak ditemukan. Silakan coba lagi.',
            ], 422);
        }

        $verifiedKey = $this->verifiedKey($email);
        $verifiedRecord = Cache::get($verifiedKey);

        if (! is_array($verifiedRecord) || empty($verifiedRecord['hash'])) {
            return response()->json([
                'message' => 'Verifikasi email belum selesai atau sudah kedaluwarsa. Silakan verifikasi ulang.',
            ], 422);
        }

        if (! hash_equals((string) $verifiedRecord['hash'], hash('sha256', (string) $validated['verification_token']))) {
            return response()->json([
                'message' => 'Token verifikasi tidak valid. Silakan verifikasi ulang.',
            ], 422);
        }

        try {
            $existing = $users->find($uid);

            $user = $existing ?? $users->create($uid, []);

            $users->update($uid, [
                'nama' => $validated['nama'],
                'email' => $email,
                'telepon' => $validated['telepon'],
                'blok' => $validated['blok'],
                'rumah' => $validated['rumah'] ?? '',
                'role' => $this->isSuperAdminEmail($email) ? 'super_admin' : ($existing['role'] ?? 'warga'),
            ]);

            $user = $users->find($uid) ?? $user;

            $this->startSession($user);
            Cache::forget($verifiedKey);
        } catch (Throwable $e) {
            Log::error('Gagal memproses pendaftaran: operasi Firestore gagal', [
                'uid' => $uid,
                'exception' => $e,
            ]);

            return response()->json([
                'message' => 'Server sedang bermasalah saat menyimpan data pendaftaran. Silakan coba lagi.',
            ], 500);
        }

        return response()->json(['redirect' => $this->homeFor((string) ($user['role'] ?? 'warga'))]);
    }

    public function logout(Request $request)
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah keluar dari akun.');
    }

    private function defaultRole(string $email): string
    {
        return $this->isSuperAdminEmail($email) ? 'super_admin' : 'warga';
    }

    private function isSuperAdminEmail(string $email): bool
    {
        $normalized = $this->normalizeEmail($email);

        if ($normalized === '') {
            return false;
        }

        return in_array($normalized, (array) config('laporku.super_admins', []), true);
    }

    /**
     * @param  array<string, mixed>  $user
     * @return array<string, mixed>
     */
    private function applySuperAdminRole(UserStore $users, array $user, string $email): array
    {
        if (! $this->isSuperAdminEmail($email) || ($user['role'] ?? '') === 'super_admin') {
            return $user;
        }

        $users->update((string) $user['uid'], ['role' => 'super_admin']);

        $user['role'] = 'super_admin';

        return $user;
    }

    private function verifyToken(string $idToken): mixed
    {
        try {
            return Firebase::auth()->verifyIdToken($idToken);
        } catch (Throwable $e) {
            Log::warning('Gagal verifikasi token Firebase', [
                'exception' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * @param  array<string, mixed>  $user
     */
    private function startSession(array $user): void
    {
        $request = request();

        $request->session()->put('firebase.uid', (string) $user['uid']);
        $request->session()->put('firebase.role', (string) ($user['role'] ?? 'warga'));
        $request->session()->put('firebase.blok', (string) ($user['blok'] ?? ''));
        $request->session()->put('firebase.blocks', array_values(array_filter(array_map(
            static fn (mixed $block): string => trim((string) $block),
            (array) ($user['bloks'] ?? []),
        ))));
        $request->session()->put('firebase.name', (string) ($user['nama'] ?? ''));
        $request->session()->put('firebase.email', (string) ($user['email'] ?? ''));
        $request->session()->regenerate();
    }

    private function homeFor(string $role): string
    {
        return route('dashboard');
    }

    private function normalizeEmail(string $email): string
    {
        return mb_strtolower(trim($email));
    }

    private function otpKey(string $email): string
    {
        return 'reg_otp:'.hash('sha256', $email);
    }

    private function otpCooldownKey(string $email): string
    {
        return 'reg_otp_cooldown:'.hash('sha256', $email);
    }

    private function verifiedKey(string $email): string
    {
        return 'reg_verified:'.hash('sha256', $email);
    }

    private function otpExpiresMinutes(): int
    {
        return 10;
    }

    private function otpMaxAttempts(): int
    {
        return 5;
    }

    private function otpResendCooldownSeconds(): int
    {
        return 60;
    }

    private function verifiedTokenMinutes(): int
    {
        return 15;
    }

    private function cacheSecondsRemaining(string $key): ?int
    {
        try {
            $store = Cache::getStore();

            if (! method_exists($store, 'getRawExpiration')) {
                return null;
            }

            $expires = $store->getRawExpiration(Cache::getPrefix().$key);

            if ($expires <= 0) {
                return null;
            }

            $remaining = $expires - time();

            return $remaining > 0 ? $remaining : null;
        } catch (Throwable) {
            return null;
        }
    }

    private function firebaseEmailExists(string $email): bool
    {
        if ($email === '') {
            return false;
        }

        try {
            Firebase::auth()->getUserByEmail($email);

            return true;
        } catch (Throwable $e) {
            $message = mb_strtolower($e->getMessage());

            if (str_contains($message, 'no user') || str_contains($message, 'user-not-found') || str_contains($message, 'not found')) {
                return false;
            }

            Log::warning('Gagal memeriksa email di Firebase saat OTP', [
                'email_hash' => sha1($email),
                'exception' => $e,
            ]);

            return false;
        }
    }
}
