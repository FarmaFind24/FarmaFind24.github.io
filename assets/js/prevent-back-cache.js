/**
 * Previene l'accesso tramite back button a pagine che richiedono autenticazione
 * dopo logout o eliminazione account
 */

(function() {
    // Previene la navigazione tramite back button forzando il reload
    window.addEventListener('pageshow', function(event) {
        // Se la pagina viene caricata dalla cache del browser (bfcache)
        if (event.persisted) {
            // Forza il reload della pagina per verificare nuovamente la sessione
            window.location.reload();
        }
    });

    // Controllo aggiuntivo con Navigation Timing API Level 2
    if (window.performance && window.performance.getEntriesByType) {
        var navEntries = window.performance.getEntriesByType('navigation');
        if (navEntries.length > 0 && navEntries[0].type === 'back_forward') {
            window.location.reload();
        }
    }

    // Previene il caching con History API
    if (window.history && window.history.pushState) {
        window.history.pushState(null, null, window.location.href);
        window.addEventListener('popstate', function() {
            window.history.pushState(null, null, window.location.href);
        });
    }
})();
