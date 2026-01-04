document.addEventListener('DOMContentLoaded', function () {
    const servicesContainer = document.querySelector('.services-grid');
    const citySelect = document.getElementById('city');
    const pharmacyGrid = document.querySelector('.farm-grid');
    const pharmacyMessageContainer = document.getElementById('farm-message-container');

    function fetchPharmacies() {
        const selectedService = document.querySelector('input[name="service"]:checked');
        const selectedCity = citySelect.value;

        // Pulisce la lista precedente e il messaggio
        pharmacyGrid.innerHTML = '';
        pharmacyMessageContainer.innerHTML = '';

        if (selectedService && selectedCity) {
            // Mostra un loader/messaggio di caricamento
            pharmacyGrid.innerHTML = '<p>Caricamento farmacie...</p>';

            const serviceId = selectedService.value;
            
            fetch(`get_pharmacies.php?service_id=${serviceId}&city=${selectedCity}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    pharmacyMessageContainer.innerHTML = data.message;
                    pharmacyGrid.innerHTML = data.html || '<p>Nessuna farmacia corrisponde ai criteri di ricerca.</p>';
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

    if (servicesContainer) {
        servicesContainer.addEventListener('change', function(event) {
            if (event.target.name === 'service') {
                fetchPharmacies();
            }
        });
    }
});