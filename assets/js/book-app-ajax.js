document.addEventListener('DOMContentLoaded', function () {
    const servicesContainer = document.getElementById('servizi-grid');
    const pharmacyGrid = document.getElementById('farma-grid');
    const citySelect = document.getElementById('city');
    const pharmacyMessageContainer = document.getElementById('farm-message-container');
    const dateInput = document.getElementById('date-pick');
    const timeGrid = document.querySelector('.time-grid');
    function fetchPharmacies() {
        const selectedService = document.querySelector('input[name="service"]:checked');
        const selectedCity = citySelect.value;
        const nextBtn = document.getElementById('nextBtn');

        pharmacyGrid.innerHTML = '';
        pharmacyMessageContainer.innerHTML = '';
        if (timeGrid) timeGrid.innerHTML = '<p>Seleziona una farmacia e una data.</p>';

        if (selectedService && selectedCity) {
            pharmacyGrid.innerHTML = '<p>Caricamento farmacie...</p>';
            const serviceId = selectedService.value;
            fetch(`get_pharmacies.php?service_id=${serviceId}&city=${selectedCity}`)
                .then(response => response.json())
                .then(data => {
                    pharmacyMessageContainer.innerHTML = data.message;
                    pharmacyGrid.innerHTML = data.html || '';
                    if (nextBtn) {
                        if (data.noPharmacies === true) {
                            nextBtn.disabled = true;
                        } else {
                            nextBtn.disabled = false;
                        }
                    }
                })
                .catch(error => {
                    console.error('Errore:', error);
                    pharmacyGrid.innerHTML = '<p class="error">Impossibile caricare le farmacie.</p>';
                    if (nextBtn) nextBtn.disabled = true;
                });
        }
    }
    function fetchTimeSlots() {
        const selectedPharmacy = document.querySelector('input[name="pharmacy-selection"]:checked');
        const selectedDate = dateInput.value;

        if (selectedPharmacy && selectedDate) {
            timeGrid.innerHTML = '<p>Caricamento orari...</p>';
            fetch(`get_slots.php?id=${selectedPharmacy.value}&date=${selectedDate}`)
                .then(response => response.text())
                .then(html => {
                    timeGrid.innerHTML = html;
                })
                .catch(err => {
                    console.error('Errore orari:', err);
                    timeGrid.innerHTML = '<p class="error">Errore nel caricamento orari.</p>';
                });
        }
    }

    if (citySelect) citySelect.addEventListener('change', fetchPharmacies);
    if (servicesContainer) {
        servicesContainer.addEventListener('change', function (event) {
            if (event.target.name === 'service') fetchPharmacies();
        });
    }
    if (dateInput) {
        dateInput.addEventListener('change', fetchTimeSlots);
    }
    if (pharmacyGrid) {
        pharmacyGrid.addEventListener('change', function (event) {
            if (event.target.name === 'pharmacy-selection') {
                fetchTimeSlots();
            }
        });
    }
});