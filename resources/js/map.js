import L from 'leaflet';

const STATUS_COLORS = {
    baru: '#f59e0b',
    diproses: '#0ea5e9',
    selesai: '#248a55',
    ditolak: '#f43f5e',
};

const STATUS_LABELS = {
    baru: 'Baru',
    diproses: 'Diproses',
    selesai: 'Selesai',
    ditolak: 'Ditolak',
};

const TILE_URL = 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
const TILE_ATTRIBUTION = '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>';

function pinIcon(color) {
    return L.divIcon({
        className: 'laporku-pin',
        html: `<span style="display:block;width:22px;height:22px;border-radius:9999px 9999px 9999px 0;transform:rotate(-45deg);background:${color};border:2.5px solid #fff;box-shadow:0 4px 10px -2px rgba(15,35,25,.5)"></span>`,
        iconSize: [22, 22],
        iconAnchor: [11, 22],
        popupAnchor: [0, -20],
    });
}

function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>"']/g, (char) => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;',
    }[char]));
}

function formatDate(value) {
    if (!value) return '';
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return '';

    return new Intl.DateTimeFormat('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    }).format(date);
}

function popupHtml(point) {
    const color = STATUS_COLORS[point.status] ?? '#64748b';
    const label = STATUS_LABELS[point.status] ?? point.status;

    const link = point.url
        ? `<a href="${escapeHtml(point.url)}" style="display:inline-block;margin-top:8px;font-weight:600;color:#1f6d46">Lihat detail &rarr;</a>`
        : `<p style="margin-top:8px;font-size:11px;color:#94a3b8">Detail hanya untuk pemilik/pengurus.</p>`;

    return `
        <div style="min-width:190px">
            <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap">
                <span style="font-size:11px;font-weight:600;color:#1f6d46;background:#ddf5e6;border-radius:6px;padding:2px 6px">${escapeHtml(point.kategori)}</span>
                <span style="font-size:11px;font-weight:600;color:#fff;background:${color};border-radius:9999px;padding:2px 8px">${escapeHtml(label)}</span>
            </div>
            <p style="margin:8px 0 0;font-weight:600;color:#0f172a;line-height:1.3">${escapeHtml(point.judul)}</p>
            <p style="margin:4px 0 0;font-size:12px;color:#64748b">
                ${point.blok ? 'Blok ' + escapeHtml(point.blok) + ' &middot; ' : ''}${escapeHtml(formatDate(point.created_at))}
            </p>
            ${link}
        </div>
    `;
}

export function initDashboardMap(el) {
    if (!el || typeof L === 'undefined') return null;

    const pointsUrl = el.dataset.pointsUrl;
    const centerLat = parseFloat(el.dataset.centerLat) || -6.2;
    const centerLng = parseFloat(el.dataset.centerLng) || 106.8166;
    const zoom = parseInt(el.dataset.zoom, 10) || 12;

    const map = L.map(el, { scrollWheelZoom: true }).setView([centerLat, centerLng], zoom);

    L.tileLayer(TILE_URL, { attribution: TILE_ATTRIBUTION, maxZoom: 19 }).addTo(map);

    const layer = L.layerGroup().addTo(map);
    let filter = '';
    let signature = '';
    let timer = null;

    const statusEl = document.querySelector('[data-map-status]');
    const countEl = document.querySelector('[data-map-count]');

    function setStatus(text, ok = true) {
        if (!statusEl) return;
        statusEl.textContent = text;
        statusEl.className = ok
            ? 'text-xs font-medium text-brand-600'
            : 'text-xs font-medium text-rose-500';
    }

    async function refresh() {
        if (!pointsUrl) return;

        try {
            const url = filter ? `${pointsUrl}?status=${encodeURIComponent(filter)}` : pointsUrl;
            const response = await fetch(url, { headers: { Accept: 'application/json' } });

            if (!response.ok) throw new Error('gagal');

            const data = await response.json();
            const points = Array.isArray(data.points) ? data.points : [];
            const next = points.map((p) => `${p.id}:${p.status}:${p.lat}:${p.lng}`).join('|');

            if (next !== signature) {
                signature = next;
                layer.clearLayers();

                points.forEach((point) => {
                    L.marker([point.lat, point.lng], { icon: pinIcon(STATUS_COLORS[point.status] ?? '#64748b') })
                        .bindPopup(popupHtml(point))
                        .addTo(layer);
                });
            }

            if (countEl) countEl.textContent = points.length;

            const time = new Date(data.server_time ?? Date.now());
            setStatus(`Live · ${points.length} titik · ${time.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' })}`);
        } catch (error) {
            setStatus('Gagal memperbarui peta. Menunggu koneksi...', false);
            console.error('Peta laporan gagal diperbarui:', error);
        }
    }

    function start() {
        refresh();
        timer = window.setInterval(refresh, 12000);
    }

    const api = {
        setFilter(status) {
            filter = status ?? '';
            signature = '';
            refresh();
        },
        refresh,
        focus(lat, lng) {
            map.setView([lat, lng], Math.max(map.getZoom(), 16));
        },
    };

    start();

    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            if (timer) window.clearInterval(timer);
            timer = null;
        } else if (!timer) {
            start();
        }
    });

    return api;
}

export function initPointMap(el) {
    if (!el || typeof L === 'undefined') return;

    const lat = parseFloat(el.dataset.lat);
    const lng = parseFloat(el.dataset.lng);

    if (Number.isNaN(lat) || Number.isNaN(lng)) return;

    const zoom = parseInt(el.dataset.zoom, 10) || 16;

    const map = L.map(el, { zoomControl: true, scrollWheelZoom: false }).setView([lat, lng], zoom);

    L.tileLayer(TILE_URL, { attribution: TILE_ATTRIBUTION, maxZoom: 19 }).addTo(map);

    L.marker([lat, lng], { icon: pinIcon('#248a55') }).addTo(map);
}

export function initLocationPicker(el) {
    if (!el || typeof L === 'undefined') return;

    const latInput = document.getElementById(el.dataset.latInput || 'lat');
    const lngInput = document.getElementById(el.dataset.lngInput || 'lng');
    const gpsButton = document.getElementById(el.dataset.gpsButton || 'use-gps');
    const clearButton = document.getElementById(el.dataset.clearButton || 'clear-location');
    const hintEl = document.querySelector('[data-location-hint]');

    const centerLat = parseFloat(el.dataset.centerLat) || -6.2;
    const centerLng = parseFloat(el.dataset.centerLng) || 106.8166;
    const zoom = parseInt(el.dataset.zoom, 10) || 13;

    const map = L.map(el).setView([centerLat, centerLng], zoom);

    L.tileLayer(TILE_URL, { attribution: TILE_ATTRIBUTION, maxZoom: 19 }).addTo(map);

    const icon = L.divIcon({
        className: 'laporku-pin',
        html: '<span style="display:block;width:26px;height:26px;border-radius:9999px 9999px 9999px 0;transform:rotate(-45deg);background:#248a55;border:3px solid #fff;box-shadow:0 6px 14px -2px rgba(15,35,25,.55)"></span>',
        iconSize: [26, 26],
        iconAnchor: [13, 26],
    });

    let marker = null;

    function setPoint(lat, lng, pan = true) {
        if (latInput) latInput.value = lat.toFixed(6);
        if (lngInput) lngInput.value = lng.toFixed(6);

        if (!marker) {
            marker = L.marker([lat, lng], { icon, draggable: true }).addTo(map);
            marker.on('dragend', () => {
                const pos = marker.getLatLng();
                setPoint(pos.lat, pos.lng, false);
            });
        } else {
            marker.setLatLng([lat, lng]);
        }

        if (hintEl) {
            hintEl.textContent = `Koordinat tersimpan: ${lat.toFixed(5)}, ${lng.toFixed(5)}`;
            hintEl.classList.remove('hidden');
        }

        if (pan) map.setView([lat, lng], Math.max(map.getZoom(), 16));
    }

    map.on('click', (event) => setPoint(event.latlng.lat, event.latlng.lng, false));

    if (latInput?.value && lngInput?.value) {
        setPoint(parseFloat(latInput.value), parseFloat(lngInput.value), false);
    }

    gpsButton?.addEventListener('click', () => {
        if (!navigator.geolocation) {
            alert('Perangkat ini tidak mendukung deteksi lokasi.');
            return;
        }

        gpsButton.disabled = true;
        const original = gpsButton.textContent;
        gpsButton.textContent = 'Mencari lokasi...';

        navigator.geolocation.getCurrentPosition(
            (position) => {
                setPoint(position.coords.latitude, position.coords.longitude);
                gpsButton.disabled = false;
                gpsButton.textContent = original;
            },
            () => {
                alert('Lokasi tidak dapat diambil. Pastikan izin lokasi diaktifkan.');
                gpsButton.disabled = false;
                gpsButton.textContent = original;
            },
            { enableHighAccuracy: true, timeout: 10000 },
        );
    });

    clearButton?.addEventListener('click', () => {
        if (marker) {
            map.removeLayer(marker);
            marker = null;
        }
        if (latInput) latInput.value = '';
        if (lngInput) lngInput.value = '';
        hintEl?.classList.add('hidden');
    });
}
