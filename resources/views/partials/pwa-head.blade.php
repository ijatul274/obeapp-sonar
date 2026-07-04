{{-- PWA: manifest, theme color, ikon iOS, dan registrasi service worker.
     Path root-relative agar selalu se-origin dengan halaman (hindari gagal
     registrasi SW saat APP_URL ≠ origin yang diakses). --}}
<link rel="manifest" href="/manifest.json">
<meta name="theme-color" content="#D04747">
<link rel="apple-touch-icon" href="/icons/apple-touch-icon.png">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="apple-mobile-web-app-title" content="OBE">
<script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function () {
            navigator.serviceWorker.register('/sw.js').catch(function () {});
        });
    }
</script>
