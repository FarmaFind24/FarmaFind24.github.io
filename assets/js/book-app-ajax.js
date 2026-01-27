document.addEventListener('DOMContentLoaded', function () {
    // Riferimenti esistenti
    const servicesContainer = document.querySelector('.services-grid');
    const citySelect = document.getElementById('city');
    const pharmacyGrid = document.querySelector('.farm-grid');
    const pharmacyMessageContainer = document.getElementById('farm-message-container');

    // NUOVI Riferimenti per data e orari
    const dateInput = document.getElementById('date-pick');
    const timeGrid = document.querySelector('.time-grid');
    
    // --- 1. FUNZIONE PER LE FARMACIE (Esistente) ---
    function fetchPharmacies() {
        const selectedService = document.querySelector('input[name="service"]:checked');
        const selectedCity = citySelect.value;
        const nextBtn = document.getElementById('nextBtn');

        pharmacyGrid.innerHTML = '';
        pharmacyMessageContainer.innerHTML = '';
        
        // Pulisce anche gli orari se cambio i criteri farmacia
        if(timeGrid) timeGrid.innerHTML = '<p>Seleziona una farmacia e una data.</p>';

        if (selectedService && selectedCity) {
            pharmacyGrid.innerHTML = '<p>Caricamento farmacie...</p>';
            const serviceId = selectedService.value;
            
            // Assicurati che get_pharmacies.php restituisca JSON {message: "...", html: "...", noPharmacies: bool}
            fetch(`get_pharmacies.php?service_id=${serviceId}&city=${selectedCity}`)
                .then(response => response.json())
                .then(data => {
                    pharmacyMessageContainer.innerHTML = data.message;
                    pharmacyGrid.innerHTML = data.html || '';
                    
                    // Gestione del bottone Prosegui: disabilita se non ci sono farmacie
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

    // --- 2. NUOVA FUNZIONE PER GLI ORARI ---
    function fetchTimeSlots() {
        // Cerco la farmacia selezionata (delegation necessaria perché caricata via AJAX/PHP)
        const selectedPharmacy = document.querySelector('input[name="pharmacy-selection"]:checked');
        const selectedDate = dateInput.value;

        if (selectedPharmacy && selectedDate) {
            timeGrid.innerHTML = '<p>Caricamento orari...</p>';
            
            // Chiamata al nuovo file PHP creato
            fetch(`get_slots.php?id=${selectedPharmacy.value}&date=${selectedDate}`)
                .then(response => response.text()) // get_slots.php restituisce HTML puro
                .then(html => {
                    timeGrid.innerHTML = html;
                })
                .catch(err => {
                    console.error('Errore orari:', err);
                    timeGrid.innerHTML = '<p class="error">Errore nel caricamento orari.</p>';
                });
        }
    }

    // --- 3. EVENT LISTENERS ---

    // Listener Farmacie (Esistenti)
    if (citySelect) citySelect.addEventListener('change', fetchPharmacies);
    if (servicesContainer) {
        servicesContainer.addEventListener('change', function(event) {
            if (event.target.name === 'service') fetchPharmacies();
        });
    }

    // NUOVO: Listener Data
    if (dateInput) {
        dateInput.addEventListener('change', fetchTimeSlots);
    }

    // NUOVO: Listener Farmacia (Event Delegation)
    // Poiché i radio button delle farmacie vengono iniettati dinamicamente,
    // dobbiamo ascoltare il contenitore genitore (.farm-grid)
    if (pharmacyGrid) {
        pharmacyGrid.addEventListener('change', function(event) {
            if (event.target.name === 'pharmacy-selection') {
                fetchTimeSlots();
            }
        });
    }
    
    // Controllo iniziale: se la pagina è stata ricaricata dal PHP e ci sono già valori,
    // non serve fare fetch, ma se l'utente cambia qualcosa, siamo pronti.
});