document.addEventListener("DOMContentLoaded", () => {
    const navLinks = document.querySelectorAll('a[href^="#"]');
    const sections = document.querySelectorAll(".content-section");

    // --- FUNZIONE PER CAMBIARE SEZIONE ---
    const activateSection = (targetId) => {
        const targetElement = document.querySelector(targetId);
        
        // Se l'elemento target è una section con classe content-section, gestisci il toggle
        if (targetElement && targetElement.classList.contains("content-section")) {
            // 1. Nascondi tutte le sezioni
            sections.forEach(s => s.classList.add("content-hidden"));

            // 2. Mostra la sezione target
            targetElement.classList.remove("content-hidden");

            // 3. Aggiorna lo stato dei link (CSS e Accessibilità)
            navLinks.forEach(link => {
                link.classList.remove("current");
                link.removeAttribute("aria-current");

                if (link.getAttribute("href") === targetId) {
                    link.classList.add("current");
                    link.ariaCurrent = "page";
                }
            });
        }
    };

    // --- LOGICA DI AVVIO ---
    // Controlla se c'è un'ancora nell'URL (es. #gestione-farmacie) 
    // OPPURE se ci sono parametri error/success che indicano la sezione
    const hash = window.location.hash;
    const urlParams = new URLSearchParams(window.location.search);
    const hasError = urlParams.get('error');
    const hasSuccess = urlParams.get('success');
    
    if (hash && hash.length > 1) {
        // Se c'è un'ancora, attiva quella sezione
        activateSection(hash);
        
        // IMPORTANTE: display:none -> display:block richiede un reflow completo
        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                const targetElement = document.querySelector(hash);
                if (targetElement) {
                    // Cerca un messaggio di errore/successo nella sezione
                    const message = targetElement.querySelector('.message.error, .message.success');
                    if (message) {
                        // Scrolla al messaggio
                        message.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        message.focus();
                    } else {
                        // Altrimenti scrolla alla sezione
                        targetElement.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                }
            });
        });
    } else if (hasError || hasSuccess) {
        // Se ci sono parametri error/success, attiva gestione-farmacie
        activateSection("#gestione-farmacie");
        
        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                const section = document.querySelector('#gestione-farmacie');
                if (section) {
                    // Cerca un messaggio di errore/successo
                    const message = section.querySelector('.message.error, .message.success');
                    if (message) {
                        message.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        message.focus();
                    } else {
                        section.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                }
            });
        });
    } else {
        // Altrimenti, sempre Dashboard all'inizio
        activateSection("#dashboard");
    }

    // --- GESTIONE DEI CLICK ---
    navLinks.forEach(link => {
        link.addEventListener("click", (e) => {
            const targetId = link.getAttribute("href");

            if (targetId && targetId.startsWith("#") && targetId.length > 1) {
                const targetElement = document.querySelector(targetId);
                
                // Previeni il comportamento di default solo se è una content-section
                if (targetElement && targetElement.classList.contains("content-section")) {
                    e.preventDefault();
                    activateSection(targetId);
                }
                // Per gli altri elementi (es. #add-farmacia-form-container), 
                // continua con la gestione specifica sotto
            }
        });
    });

    // === TOGGLE FORM INSERIMENTO FARMACIA ===
    const addBtn = document.getElementById('addFarmaciaBtn');
    const formContainer = document.getElementById('add-farmacia-form-container');
    const form = document.getElementById('add-farmacia-form');

    if (addBtn && formContainer) {
        addBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Aspetta che il browser renderizzi l'elemento prima di scrollare
            requestAnimationFrame(() => {
                formContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
                // Sposta il focus al primo campo del form per accessibilità
                const firstInput = formContainer.querySelector('input, select');
                if (firstInput) {
                    setTimeout(() => firstInput.focus(), 300);
                }
            });
        });
    }

    // === VALIDAZIONE FORM INSERIMENTO FARMACIA ===
    if (form) {
        // NOTA: I messaggi di errore/successo sono già gestiti da PHP nel placeholder [feedback_messages]
        // JavaScript si occupa solo della validazione client-side prima del submit
        
        // Validazione client-side al submit
        form.addEventListener('submit', (e) => {
            clearAllFieldErrors(form);
            clearGeneralError(form);
            
            let isValid = true;
            let firstInvalidInput = null;
            
            const nomeInput = document.getElementById('nome');
            const indirizzoInput = document.getElementById('indirizzo');
            const cittaInput = document.getElementById('citta');
            const telefonoInput = document.getElementById('telefono');
            const zonaInputs = document.querySelectorAll('input[name="zona"]');
            const orarioInputs = document.querySelectorAll('input[name="tipo_orario"]');
            
            // Validazione nome farmacia
            if (nomeInput) {
                const nome = nomeInput.value.trim();
                if (nome === '') {
                    showFieldError(nomeInput, 'Inserisci il nome della farmacia.');
                    isValid = false;
                    if (!firstInvalidInput) firstInvalidInput = nomeInput;
                } else if (nome.length < 3 || nome.length > 100) {
                    showFieldError(nomeInput, 'Il nome deve essere tra 3 e 100 caratteri.');
                    isValid = false;
                    if (!firstInvalidInput) firstInvalidInput = nomeInput;
                }
            }
            
            // Validazione indirizzo
            if (indirizzoInput) {
                const indirizzo = indirizzoInput.value.trim();
                if (indirizzo === '') {
                    showFieldError(indirizzoInput, 'Inserisci l\'indirizzo.');
                    isValid = false;
                    if (!firstInvalidInput) firstInvalidInput = indirizzoInput;
                }
            }
            
            // Validazione città
            if (cittaInput) {
                const citta = cittaInput.value.trim();
                if (citta === '') {
                    showFieldError(cittaInput, 'Seleziona una città.');
                    isValid = false;
                    if (!firstInvalidInput) firstInvalidInput = cittaInput;
                }
            }
            
            // Validazione telefono
            if (telefonoInput) {
                const telefono = telefonoInput.value.trim();
                const phoneRegex = /^[0-9 \-\+\(\)]+$/;
                if (telefono === '') {
                    showFieldError(telefonoInput, 'Inserisci il numero di telefono.');
                    isValid = false;
                    if (!firstInvalidInput) firstInvalidInput = telefonoInput;
                } else if (!phoneRegex.test(telefono)) {
                    showFieldError(telefonoInput, 'Inserisci un numero di telefono valido (solo numeri, spazi, +, -, parentesi).');
                    isValid = false;
                    if (!firstInvalidInput) firstInvalidInput = telefonoInput;
                }
            }
            
            // Validazione zona geografica (radio)
            const zonaChecked = Array.from(zonaInputs).some(input => input.checked);
            if (!zonaChecked) {
                showGeneralError(form, 'Seleziona una zona geografica.');
                isValid = false;
                if (!firstInvalidInput) firstInvalidInput = zonaInputs[0];
            }
            
            // Validazione tipo orario (radio)
            const orarioChecked = Array.from(orarioInputs).some(input => input.checked);
            if (!orarioChecked) {
                if (!form.querySelector('.general-error')) {
                    showGeneralError(form, 'Seleziona un tipo di orario.');
                }
                isValid = false;
                if (!firstInvalidInput) firstInvalidInput = orarioInputs[0];
            }
            
            // Se ci sono errori, previeni submit e porta focus al primo campo errato
            if (!isValid) {
                e.preventDefault();
                if (firstInvalidInput) {
                    firstInvalidInput.focus();
                }
            }
        });
        
        // Rimuovi errori in tempo reale quando l'utente corregge
        const inputs = form.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.addEventListener('input', () => clearFieldError(input));
            input.addEventListener('change', () => clearFieldError(input));
        });
    }
});