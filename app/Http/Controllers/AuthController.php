<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\Firestore\UserStore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
        $email = (string) ($claims->claims()->get('email') ?? '');

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

    public function register(Request $request, UserStore $users)
    {
        $validated = $request->validate([
            'idToken' => 'required|string',
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
        $email = (string) ($claims->claims()->get('email') ?? '');

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
        $normalized = mb_strtolower(trim($email));

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
        } catch (Throwable) {
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
        $request->session()->put('firebase.name', (string) ($user['nama'] ?? ''));
        $request->session()->put('firebase.email', (string) ($user['email'] ?? ''));
        $request->session()->regenerate();
    }

    private function homeFor(string $role): string
    {
        return route('dashboard');
    }
}
