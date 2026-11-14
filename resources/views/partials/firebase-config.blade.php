<script>
    window.__LAPORKU_FIREBASE__ = <?= json_encode([
        'apiKey' => config('firebase.projects.app.web.api_key'),
        'authDomain' => config('firebase.projects.app.web.auth_domain'),
        'projectId' => config('firebase.projects.app.web.project_id'),
        'storageBucket' => config('firebase.projects.app.web.storage_bucket'),
        'messagingSenderId' => config('firebase.projects.app.web.messaging_sender_id'),
        'appId' => config('firebase.projects.app.web.app_id'),
    ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
</script>
