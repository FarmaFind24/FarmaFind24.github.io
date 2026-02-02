document.addEventListener('DOMContentLoaded', function () {
    const citySelect = document.getElementById('city');
    const pharmacyMessageContainer = document.getElementById('index-message-container');
    const mainSearchForm = document.getElementById('mainSearchForm');
    if (mainSearchForm) {
        mainSearchForm.addEventListener('submit', function (e) {
            e.preventDefault();

            const formData = new FormData(mainSearchForm);
            const searchType = formData.get('type');
            const searchQuery = formData.get('q');

            if (!searchQuery || searchQuery.trim() === '') {
                return;
            }
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
            console.error("Errore: Elemento .grid.four-columns non trovato!");
            return;
        }
        pharmacyGrid.innerHTML = '';
        pharmacyMessageContainer.innerHTML = '';

        if (selectedCity) {
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