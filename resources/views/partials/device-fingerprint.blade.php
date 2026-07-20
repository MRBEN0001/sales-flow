<script>
(function () {
    function buildFingerprint() {
        // Avoid canvas — Brave/Firefox farble it per-origin, so shop.subdomain
        // and the central domain would not match after login vs register.
        var parts = [
            navigator.userAgent || '',
            navigator.language || '',
            (navigator.languages || []).join(','),
            screen.width + 'x' + screen.height + 'x' + (screen.colorDepth || ''),
            new Date().getTimezoneOffset(),
            !!window.sessionStorage,
            !!window.localStorage,
            navigator.hardwareConcurrency || '',
            navigator.platform || '',
            navigator.maxTouchPoints || 0,
            (screen.availWidth || '') + 'x' + (screen.availHeight || '')
        ];

        return btoa(unescape(encodeURIComponent(parts.join('|')))).slice(0, 400);
    }

    window.SalesFlowDevice = window.SalesFlowDevice || {};
    window.SalesFlowDevice.buildFingerprint = buildFingerprint;
})();
</script>
