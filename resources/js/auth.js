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
        const error = new Error(data.message || 'Terjadi kesalahan. Silakan coba lagi.');
        error.status = response.status;
        error.data = data;
        throw error;
    }

    return data;
}

function showAuthError(message) {
    const box = document.getElementById('auth-error');

    if (!box) return;

    box.textContent = message;
    box.classList.toggle('hidden', !message);
}

function showAuthSuccess(message) {
    const box = document.getElementById('auth-success');

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

function setButtonLoading(button, loading, loadingLabel = 'Memproses...') {
    if (!button) return;

    button.disabled = loading;
    button.classList.toggle('opacity-60', loading);

    if (loading) {
        button.dataset.label = button.textContent;
        button.textContent = loadingLabel;
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
    return 'Konfigurasi Firebase belum diisi. Isi variabel VITE_FIREBASE_* di environment (Vercel / file .env lalu npm run build).';
}

function initRegisterFlow(registerForm) {
    const stepForm = document.getElementById('register-step-form');
    const stepOtp = document.getElementById('register-step-otp');
    const verifyBtn = document.getElementById('verify-otp-btn');
    const resendBtn = document.getElementById('resend-otp-btn');
    const changeEmailBtn = document.getElementById('change-email-btn');
    const otpCodeInput = document.getElementById('otp-code');
    const otpEmailLabel = document.getElementById('otp-email-label');
    const devHint = document.getElementById('otp-dev-hint');

    if (!stepForm || !stepOtp || !verifyBtn || !resendBtn || !changeEmailBtn || !otpCodeInput) {
        return;
    }

    const sendUrl = registerForm.dataset.otpSendUrl || '/register/otp/send';
    const verifyUrl = registerForm.dataset.otpVerifyUrl || '/register/otp/verify';
    const registerUrl = registerForm.dataset.registerUrl || '/register';

    let resendTimer = null;

    function showOtpStep(email) {
        stepForm.classList.add('hidden');
        stepOtp.classList.remove('hidden');
        otpCodeInput.value = '';
        if (otpEmailLabel) otpEmailLabel.textContent = email;
        if (devHint && window.location.hostname === 'localhost') devHint.classList.remove('hidden');
        otpCodeInput.focus();
    }

    function showFormStep(clearOtp = true) {
        stepOtp.classList.add('hidden');
        stepForm.classList.remove('hidden');
        if (clearOtp) otpCodeInput.value = '';
        if (resendTimer) {
            clearInterval(resendTimer);
            resendTimer = null;
        }
        setResendCooldown(0);
        showAuthSuccess('');
    }

    function setResendCooldown(seconds) {
        if (resendTimer) {
            clearInterval(resendTimer);
            resendTimer = null;
        }

        if (seconds <= 0) {
            resendBtn.disabled = false;
            resendBtn.textContent = resendBtn.dataset.label || 'Kirim ulang kode';
            return;
        }

        resendBtn.disabled = true;
        let remaining = seconds;
        resendBtn.textContent = `Kirim ulang (${remaining}s)`;

        resendTimer = setInterval(() => {
            remaining -= 1;
            if (remaining <= 0) {
                clearInterval(resendTimer);
                resendTimer = null;
                resendBtn.disabled = false;
                resendBtn.textContent = resendBtn.dataset.label || 'Kirim ulang kode';
                return;
            }
            resendBtn.textContent = `Kirim ulang (${remaining}s)`;
        }, 1000);
    }

    function getFormData() {
        return {
            nama: registerForm.nama.value.trim(),
            email: registerForm.email.value.trim(),
            password: registerForm.password.value,
            telepon: registerForm.telepon.value.trim(),
            blok: registerForm.blok.value.trim(),
            rumah: registerForm.rumah.value.trim(),
        };
    }

    registerForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (!stepOtp.classList.contains('hidden')) return;

        showAuthError('');
        showAuthSuccess('');
        setLoading(registerForm, true);

        const data = getFormData();

        try {
            const response = await postJson(sendUrl, { email: data.email });
            showAuthSuccess(response.message || 'Kode verifikasi telah dikirim.');
            showOtpStep(data.email);
            setResendCooldown(response.resend_in || 60);
        } catch (error) {
            showAuthError(friendlyError(error));
        } finally {
            setLoading(registerForm, false);
        }
    });

    resendBtn.addEventListener('click', async () => {
        if (resendBtn.disabled) return;

        showAuthError('');
        showAuthSuccess('');
        resendBtn.dataset.label = 'Kirim ulang kode';
        setButtonLoading(resendBtn, true, 'Mengirim...');

        try {
            const response = await postJson(sendUrl, { email: registerForm.email.value.trim() });
            showAuthSuccess(response.message || 'Kode verifikasi baru telah dikirim.');
            setResendCooldown(response.resend_in || 60);
        } catch (error) {
            showAuthError(friendlyError(error));
            setResendCooldown(0);
        } finally {
            setButtonLoading(resendBtn, false);
        }
    });

    changeEmailBtn.addEventListener('click', () => {
        showFormStep(true);
    });

    verifyBtn.addEventListener('click', async () => {
        showAuthError('');
        showAuthSuccess('');

        const formData = getFormData();
        const code = otpCodeInput.value.replace(/\D/g, '').trim();

        if (!/^\d{6}$/.test(code)) {
            showAuthError('Masukkan kode verifikasi 6 digit.');
            otpCodeInput.focus();
            return;
        }

        setButtonLoading(verifyBtn, true, 'Memverifikasi...');

        try {
            const verified = await postJson(verifyUrl, { email: formData.email, code });

            if (!auth) {
                throw new Error(configMissingMessage());
            }

            setButtonLoading(verifyBtn, true, 'Membuat akun...');

            let credential;

            try {
                credential = await createUserWithEmailAndPassword(
                    auth,
                    formData.email,
                    formData.password,
                );
            } catch (error) {
                if ((error?.code ?? '').includes('email-already-in-use')) {
                    credential = await signInWithEmailAndPassword(
                        auth,
                        formData.email,
                        formData.password,
                    );
                } else {
                    throw error;
                }
            }

            const idToken = await credential.user.getIdToken();
            const data = await postJson(registerUrl, {
                idToken,
                verification_token: verified.verification_token,
                nama: formData.nama,
                telepon: formData.telepon,
                blok: formData.blok,
                rumah: formData.rumah,
            });

            window.location.href = data.redirect;
        } catch (error) {
            showAuthError(friendlyError(error));
            if (error?.status === 429 || (error?.message || '').toLowerCase().includes('kode baru')) {
                otpCodeInput.value = '';
            }
        } finally {
            setButtonLoading(verifyBtn, false);
        }
    });
}

export function initAuthForms() {
    const loginForm = document.getElementById('login-form');
    const registerForm = document.getElementById('register-form');
    const logoutForms = document.querySelectorAll('#logout-form, #logout-form-mobile');

    const isOtpFlow = Boolean(document.getElementById('register-step-otp'));

    if (!auth) {
        if (loginForm) {
            loginForm.addEventListener('submit', (event) => {
                event.preventDefault();
                showAuthError(configMissingMessage());
            });
        }
        if (registerForm && isOtpFlow) {
            registerForm.addEventListener('submit', (event) => {
                event.preventDefault();
                showAuthError(configMissingMessage());
            });
            document.getElementById('verify-otp-btn')?.addEventListener('click', () => {
                showAuthError(configMissingMessage());
            });
        } else if (registerForm) {
            registerForm.addEventListener('submit', (event) => {
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

    if (registerForm && isOtpFlow) {
        initRegisterFlow(registerForm);
    } else if (registerForm) {
        registerForm.addEventListener('submit', async (event) => {
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
    }

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
