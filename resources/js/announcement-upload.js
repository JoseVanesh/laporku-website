const MAX_BYTES = 5 * 1024 * 1024;
const ALLOWED = ['image/jpeg', 'image/png', 'image/webp'];

function setError(box, message) {
    if (!box) return;
    box.textContent = message || '';
    box.classList.toggle('hidden', !message);
}

function clearHiddenFields(form) {
    form.querySelectorAll('[data-cover-hidden]').forEach((input) => input.remove());
}

function addHidden(form, name, value) {
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = name;
    input.value = value;
    input.dataset.coverHidden = '1';
    form.appendChild(input);
}

function initForm(form) {
    const input = form.querySelector('[data-cover-input]');
    if (!input) return;

    const preview = form.querySelector('[data-cover-preview]');
    const errorBox = form.querySelector('[data-cover-error]');
    const progress = form.querySelector('[data-cover-progress]');
    const removeBox = form.querySelector('[data-cover-remove]');
    const submit = form.querySelector('[type="submit"]');
    const originalSrc = preview?.getAttribute('src') || '';
    let objectUrl = null;

    function showPreview(file) {
        if (!preview) return;

        if (objectUrl) {
            URL.revokeObjectURL(objectUrl);
            objectUrl = null;
        }

        if (!file) return;

        objectUrl = URL.createObjectURL(file);
        preview.src = objectUrl;
        preview.classList.remove('hidden');
    }

    input.addEventListener('change', () => {
        setError(errorBox, '');
        const file = input.files?.[0] ?? null;

        if (removeBox) removeBox.checked = false;

        if (!file) {
            showPreview(null);
            return;
        }

        if (!ALLOWED.includes(file.type)) {
            setError(errorBox, 'Gambar harus berformat JPG, PNG, atau WEBP.');
            input.value = '';
            showPreview(null);
            return;
        }

        if (file.size > MAX_BYTES) {
            setError(errorBox, 'Ukuran gambar maksimal 5 MB.');
            input.value = '';
            showPreview(null);
            return;
        }

        showPreview(file);
    });

    removeBox?.addEventListener('change', () => {
        if (!preview) return;

        if (removeBox.checked) {
            input.value = '';
            preview.classList.add('hidden');
        } else if (originalSrc) {
            preview.src = originalSrc;
            preview.classList.remove('hidden');
        }
    });

    form.addEventListener('submit', async (event) => {
        clearHiddenFields(form);

        const file = input.files?.[0] ?? null;

        if (!file) {
            if (removeBox?.checked) addHidden(form, 'hapus_gambar', '1');

            return;
        }

        event.preventDefault();
        setError(errorBox, '');

        const cloud = form.dataset.cloudName;
        const preset = form.dataset.uploadPreset;

        if (!cloud || !preset) {
            setError(errorBox, 'Konfigurasi unggah gambar belum lengkap. Hubungi pengurus (CLOUDINARY_CLOUD_NAME / CLOUDINARY_UPLOAD_PRESET).');
            return;
        }

        if (!ALLOWED.includes(file.type) || file.size > MAX_BYTES) {
            setError(errorBox, 'Gambar harus JPG/PNG/WEBP dan maksimal 5 MB.');
            return;
        }

        if (submit) {
            submit.disabled = true;
            submit.classList.add('opacity-60', 'cursor-not-allowed');
        }

        try {
            if (progress) {
                progress.classList.remove('hidden');
                progress.textContent = 'Mengunggah gambar pengumuman...';
            }

            const body = new FormData();
            body.append('file', file);
            body.append('upload_preset', preset);
            body.append('folder', 'pengumuman');

            const response = await fetch(`https://api.cloudinary.com/v1_1/${cloud}/image/upload`, {
                method: 'POST',
                body,
            });
            const data = await response.json();

            if (!response.ok || !data.secure_url) {
                throw new Error((data && data.error && data.error.message) || 'Gagal mengunggah gambar.');
            }

            addHidden(form, 'gambar_url', data.secure_url);
            addHidden(form, 'gambar_path', data.public_id || '');
            addHidden(form, 'gambar_mime', file.type || 'image/jpeg');
            addHidden(form, 'gambar_size', String(data.bytes || file.size || 0));

            input.disabled = true;
            form.submit();
        } catch (error) {
            setError(errorBox, error && error.message ? error.message : 'Gagal mengunggah gambar. Coba lagi.');

            if (submit) {
                submit.disabled = false;
                submit.classList.remove('opacity-60', 'cursor-not-allowed');
            }

            if (progress) {
                progress.classList.add('hidden');
                progress.textContent = '';
            }
        }
    });
}

export function initAnnouncementCover() {
    document.querySelectorAll('[data-announcement-form]').forEach(initForm);
}
