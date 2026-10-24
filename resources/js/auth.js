import {
    createUserWithEmailAndPassword,
    signInWithEmailAndPassword,
    signOut,
} from 'firebase/auth';
import { auth } from './firebase';

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
}

async function postJson(url, payload) {
    const response = await fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-CSRF-TOKEN': csrfToken(),
        },
        body: JSON.stringify(payload),
    });

    const data = await response.json().catch(() => ({}));

    if (!response.ok) {
        throw new Error(data.message || 'Terjadi kesalahan. Silakan coba lagi.');
    }

    return data;
}

function showAuthError(message) {
    const box = document.getElementById('auth-error');

    if (!box) return;

    box.textContent = message;
    box.classList.toggle('hidden', !message);
}

function setLoading(form, loading) {
    const button = form.querySelector('button[type="submit"]');

    if (!button) return;

    button.disabled = loading;
    button.classList.toggle('opacity-60', loading);

    if (loading) {
        button.dataset.label = button.textContent;
        button.textContent = 'Memproses...';
    } else if (button.dataset.label) {
        button.textContent = button.dataset.label;
    }
}

function friendlyError(error) {
    const code = error?.code ?? '';

    if (code.includes('invalid-credential') || code.includes('wrong-password') || code.includes('user-not-found')) {
        return 'Email atau kata sandi salah.';
    }
    if (code.includes('email-already-in-use')) return 'Email sudah terdaftar. Silakan gunakan login.';
    if (code.includes('weak-password')) return 'Kata sandi minimal 6 karakter.';
    if (code.includes('invalid-email')) return 'Format email tidak valid.';
    if (code.includes('too-many-requests')) return 'Terlalu banyak percobaan. Coba lagi beberapa saat lagi.';
    if (code.includes('network')) return 'Koneksi bermasalah. Periksa internet Anda.';
    if (code.includes('operation-not-allowed')) {
        return 'Login Email/Password belum diaktifkan di Firebase Console.';
    }

    return error?.message || 'Terjadi kesalahan. Silakan coba lagi.';
}

function configMissingMessage() {
    return 'Konfigurasi Firebase belum diisi. Isi variabel VITE_FIREBASE_* di file .env, lalu jalankan ulang npm run dev (atau npm run build).';
}

export function initAuthForms() {
    const loginForm = document.getElementById('login-form');
    const registerForm = document.getElementById('register-form');
    const logoutForms = document.querySelectorAll('#logout-form, #logout-form-mobile');

    if (!auth) {
        for (const form of [loginForm, registerForm]) {
            form?.addEventListener('submit', (event) => {
                event.preventDefault();
                showAuthError(configMissingMessage());
            });
        }

        return;
    }

    loginForm?.addEventListener('submit', async (event) => {
        event.preventDefault();
        showAuthError('');
        setLoading(loginForm, true);

        try {
            const credential = await signInWithEmailAndPassword(
                auth,
                loginForm.email.value.trim(),
                loginForm.password.value,
            );

            const idToken = await credential.user.getIdToken();
            const data = await postJson('/login', { idToken });

            window.location.href = data.redirect;
        } catch (error) {
            showAuthError(friendlyError(error));
            setLoading(loginForm, false);
        }
    });

    registerForm?.addEventListener('submit', async (event) => {
        event.preventDefault();
        showAuthError('');
        setLoading(registerForm, true);

        try {
            const credential = await createUserWithEmailAndPassword(
                auth,
                registerForm.email.value.trim(),
                registerForm.password.value,
            );

            const idToken = await credential.user.getIdToken();
            const data = await postJson('/register', {
                idToken,
                nama: registerForm.nama.value.trim(),
                telepon: registerForm.telepon.value.trim(),
                blok: registerForm.blok.value.trim(),
                rumah: registerForm.rumah.value.trim(),
            });

            window.location.href = data.redirect;
        } catch (error) {
            showAuthError(friendlyError(error));
            setLoading(registerForm, false);
        }
    });

    logoutForms.forEach((form) => {
        form?.addEventListener('submit', async (event) => {
            event.preventDefault();

            try {
                await signOut(auth);
            } catch {
                // diabaikan: sesi server tetap dihapus
            }

            HTMLFormElement.prototype.submit.call(form);
        });
    });
}
