import { initializeApp } from 'firebase/app';
import { getAuth } from 'firebase/auth';

// Prefer runtime config injected by Blade (works regardless of build-time env);
// fall back to VITE_FIREBASE_* inlined by Vite for local dev.
const runtime = window.__LAPORKU_FIREBASE__;

const config = {
    apiKey: runtime?.apiKey ?? import.meta.env.VITE_FIREBASE_API_KEY,
    authDomain: runtime?.authDomain ?? import.meta.env.VITE_FIREBASE_AUTH_DOMAIN,
    projectId: runtime?.projectId ?? import.meta.env.VITE_FIREBASE_PROJECT_ID,
    storageBucket: runtime?.storageBucket ?? import.meta.env.VITE_FIREBASE_STORAGE_BUCKET,
    messagingSenderId: runtime?.messagingSenderId ?? import.meta.env.VITE_FIREBASE_MESSAGING_SENDER_ID,
    appId: runtime?.appId ?? import.meta.env.VITE_FIREBASE_APP_ID,
};

let auth = null;

if (config.apiKey && config.projectId) {
    try {
        auth = getAuth(initializeApp(config));
    } catch (error) {
        console.error('Firebase gagal diinisialisasi:', error);
    }
}

export { auth };
