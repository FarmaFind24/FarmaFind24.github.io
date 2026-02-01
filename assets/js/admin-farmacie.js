document.addEventListener("DOMContentLoaded", () => {
    const navLinks = document.querySelectorAll('a[href^="#"]');
    const sections = document.querySelectorAll(".content-section");
    const activateSection = (targetId) => {
        const targetElement = document.querySelector(targetId);
        if (targetElement && targetElement.classList.contains("content-section")) {
            sections.forEach(s => s.classList.add("content-hidden"));
            targetElement.classList.remove("content-hidden");
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

    const hash = window.location.hash;
    const urlParams = new URLSearchParams(window.location.search);
    const hasError = urlParams.get('error');
    const hasSuccess = urlParams.get('success');

    if (hash && hash.length > 1) {
        activateSection(hash);
        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                const targetElement = document.querySelector(hash);
                if (targetElement) {
                    const message = targetElement.querySelector('.message.error, .message.success');
                    if (message) {
                        message.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        message.focus();
                    } else {
                        targetElement.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                }
            });
        });
    } else if (hasError || hasSuccess) {
        activateSection("#gestione-farmacie");
        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                const section = document.querySelector('#gestione-farmacie');
                if (section) {
                    const message = section.querySelector('.success-message, .error-message');
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
        activateSection("#dashboard");
    }

    navLinks.forEach(link => {
        link.addEventListener("click", (e) => {
            const targetId = link.getAttribute("href");

            if (targetId && targetId.startsWith("#") && targetId.length > 1) {
                const targetElement = document.querySelector(targetId);

                if (targetElement && targetElement.classList.contains("content-section")) {
                    e.preventDefault();
                    activateSection(targetId);
                }

            }
        });
    });

    // === TOGGLE FORM INSERIMENTO FARMACIA ===
    const addBtn = document.getElementById('addFarmaciaBtn');
    const formContainer = document.getElementById('add-farmacia-form-container');
    const form = document.getElementById('add-farmacia-form');

    if (addBtn && formContainer) {
        addBtn.addEventListener('click', function (e) {
            e.preventDefault();
            requestAnimationFrame(() => {
                formContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
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

            if (indirizzoInput) {
                const indirizzo = indirizzoInput.value.trim();
                if (indirizzo === '') {
                    showFieldError(indirizzoInput, 'Inserisci l\'indirizzo.');
                    isValid = false;
                    if (!firstInvalidInput) firstInvalidInput = indirizzoInput;
                }
            }

            if (cittaInput) {
                const citta = cittaInput.value.trim();
                if (citta === '') {
                    showFieldError(cittaInput, 'Seleziona una città.');
                    isValid = false;
                    if (!firstInvalidInput) firstInvalidInput = cittaInput;
                }
            }

            if (telefonoInput) {
                let telefonoPulito = telefonoInput.value.replace(/\s+/g, '');

                const phoneRegex = /^[0-9 \-\+\(\)]+$/;

                if (telefonoPulito === '') {
                    showFieldError(telefonoInput, 'Inserisci il numero di telefono.');
                    isValid = false;
                    if (!firstInvalidInput) firstInvalidInput = telefonoInput;
                }
                else if (!phoneRegex.test(telefonoPulito)) {
                    showFieldError(telefonoInput, 'Formato non valido.');
                    isValid = false;
                    if (!firstInvalidInput) firstInvalidInput = telefonoInput;
                }
                else if (telefonoPulito.length < 9 || telefonoPulito.length > 10) {
                    showFieldError(telefonoInput, 'Il numero deve avere tra 9 e 10 cifre.');
                    isValid = false;
                    if (!firstInvalidInput) firstInvalidInput = telefonoInput;
                }
            }

            const orarioChecked = Array.from(orarioInputs).some(input => input.checked);
            if (!orarioChecked) {
                showGeneralError(form, 'Seleziona un tipo di orario.');
                isValid = false;
                if (!firstInvalidInput) firstInvalidInput = orarioInputs[0];
            }
            if (!isValid) {
                e.preventDefault();
            }
        });
        const inputs = form.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.addEventListener('input', () => clearFieldError(input));
            input.addEventListener('change', () => clearFieldError(input));
        });
    }
});