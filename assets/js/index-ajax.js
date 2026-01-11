document.addEventListener('DOMContentLoaded', function () {
    const citySelect = document.getElementById('city');
    const pharmacyMessageContainer = document.getElementById('index-message-container');

    function fetchPharmacies() {
        const pharmacyGrid = document.querySelector('.grid-4c');
        const selectedCity = citySelect.value;
        if (!pharmacyGrid) {
            console.error("Errore: Elemento .grid-4c non trovato!");
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