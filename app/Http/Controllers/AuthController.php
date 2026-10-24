<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\Firestore\UserStore;
use Illuminate\Http\Request;
use Kreait\Laravel\Firebase\Facades\Firebase;
use Throwable;

class AuthController extends Controller
{
    public function __construct(private readonly UserStore $users) {}

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
    public function login(Request $request)
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

        $user = $this->users->find($uid);

        if ($user === null) {
            $user = $this->users->create($uid, [
                'nama' => (string) ($claims->claims()->get('name') ?: explode('@', $email)[0]),
                'email' => $email,
                'telepon' => '',
                'role' => 'warga',
                'blok' => '',
                'rumah' => '',
            ]);
        }

        $this->startSession($user);

        return response()->json(['redirect' => $this->homeFor((string) $user['role'])]);
    }

    public function register(Request $request)
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

        $existing = $this->users->find($uid);

        $user = $existing ?? $this->users->create($uid, []);

        $this->users->update($uid, [
            'nama' => $validated['nama'],
            'email' => $email,
            'telepon' => $validated['telepon'],
            'blok' => $validated['blok'],
            'rumah' => $validated['rumah'] ?? '',
            'role' => $existing['role'] ?? 'warga',
        ]);

        $this->startSession($this->users->find($uid) ?? $user);

        return response()->json(['redirect' => $this->homeFor((string) ($user['role'] ?? 'warga'))]);
    }

    public function logout(Request $request)
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah keluar dari akun.');
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
