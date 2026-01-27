document.addEventListener('DOMContentLoaded', function () {
    const citySelect = document.getElementById('city');
    const pharmacyMessageContainer = document.getElementById('index-message-container');

    // Gestione submit del form di ricerca principale (supporta Enter)
    if (mainSearchForm) {
        mainSearchForm.addEventListener('submit', function(e) {
            e.preventDefault(); // Previene il submit tradizionale
            
            const formData = new FormData(mainSearchForm);
            const searchType = formData.get('type'); // 'farmacia' o 'medicinale'
            const searchQuery = formData.get('q');
            
            if (!searchQuery || searchQuery.trim() === '') {
                return; // Non fare nulla se la ricerca è vuota
            }
            
            // Redirect alla pagina appropriata
            if (searchType === 'farmacia') {
                window.location.href = 'farm-search.php?q=' + encodeURIComponent(searchQuery.trim());
            } else if (searchType === 'medicinale') {
                window.location.href = 'med-search.php?q=' + encodeURIComponent(searchQuery.trim());
            }
        });
    }

    function fetchPharmacies() {
        const pharmacyGrid = document.querySelector('.grid.four-columns');
        const selectedCity = citySelect.value;
        if (!pharmacyGrid) {
            console.error("Errore: Elemento .grid four-columns non trovato!");
            return;
        }
        // Pulisce la lista precedente e il messaggio
        pharmacyGrid.innerHTML = '';
        pharmacyMessageContainer.innerHTML = '';

        if (selectedCity) {
            // Mostra un loader/messaggio di caricamento
            pharmacyGrid.innerHTML = '<p>Caricamento farmacie...</p>';

            fetch(`get_dintorni.php?city=${selectedCity}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    pharmacyMessageContainer.innerHTML = data.message || '';
                    pharmacyGrid.innerHTML = data.html || '<p>Nessuna farmacia trovata.</p>';
                })
                .catch(error => {
                    console.error('Errore nel fetch delle farmacie:', error);
                    pharmacyGrid.innerHTML = '<p class="error">Impossibile caricare le farmacie. Riprova più tardi.</p>';
                });
        }
    }

    if (citySelect) {
        citySelect.addEventListener('change', fetchPharmacies);
    }
});