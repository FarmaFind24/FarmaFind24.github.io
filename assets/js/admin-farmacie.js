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

    if (form) {
        const inputs = {
            nameFarmacia: document.getElementById('nome'),
            indirizzoFarmacia: document.getElementById('indirizzo'),
            cittàFarmacia: document.getElementById('citta'),
            telefonoFarmacia: document.getElementById('telefono'),
            orarioFarmacia: document.querySelectorAll('input[name="tipo_orario"]')
        };

        const validateSingleField = (input) => {
            const isOrario = input instanceof NodeList || (input.length && input[0].name === "tipo_orario");

            if (isOrario) {
                const orarioChecked = Array.from(inputs.orarioFarmacia).some(i => i.checked);
                clearFieldError(inputs.orarioFarmacia[0]);
                if (!orarioChecked) {
                    showGeneralError(form, 'Seleziona un tipo di orario.');
                    return false;
                }
                return true;
            }
            if (!input) return true;
            clearFieldError(input);
            const valoreInput = input.value.trim();
            let isFieldValid = true;

            switch (input.id) {
                case 'nome':
                    if (valoreInput === '') {
                        showFieldError(input, 'Errore: inserire il nome della farmacia.');
                        isFieldValid = false;
                    } else if (valoreInput.length < 3 || valoreInput.length > 100) {
                        showFieldError(input, 'Errore: il nome deve essere tra 3 e 100 caratteri.');
                        isFieldValid = false;
                    }
                    break;
                case 'indirizzo':
                    const regexIndirizzo = /^(Via|Viale|Piazza|Piazzale)\s+.+$/i;
                    if (valoreInput === '') {
                        showFieldError(input, 'Errore: inserire l\'indirizzo.');
                        isFieldValid = false;
                    } else if (!regexIndirizzo.test(valoreInput)) {
                        showFieldError(input, 'Errore: formato indirizzo invalido');
                        isFieldValid = false;
                    }
                    break;
                case 'citta':
                    if (valoreInput === '') {
                        showFieldError(input, 'Errore: selezionare una città.');
                        isFieldValid = false;
                    }
                    break;
                case 'telefono':
                    const telefonoPulito = valoreInput.replace(/\D/g, '');
                    const phoneRegex = /^[0-9]{9,10}$/;
                    if (telefonoPulito === '') {
                        showFieldError(input, 'Errore: Inserire il numero di telefono.');
                        isFieldValid = false;
                    } else if (!phoneRegex.test(telefonoPulito)) {
                        showFieldError(input, 'Errore: il numero deve avere 9 o 10 cifre.');
                        isFieldValid = false;
                    }
                    break;
            }
            return isFieldValid;
        };

        Object.values(inputs).forEach(input => {
            if (input instanceof NodeList) {
                input.forEach(radio => {
                    radio.addEventListener('change', () => clearGeneralError(form));
                    radio.addEventListener('blur', (e) => {
                        const relatedTarget = e.relatedTarget;
                        const focusMovedOutsideGroup = !Array.from(input).includes(relatedTarget);
                        const isMovingUp = relatedTarget &&
                            (radio.compareDocumentPosition(relatedTarget) & Node.DOCUMENT_POSITION_PRECEDING);

                        if (focusMovedOutsideGroup && !isMovingUp) {
                            validateSingleField(input);
                        } else if (isMovingUp) {
                            clearGeneralError(form);
                        }
                    });
                });
            } else if (input) {
                input.addEventListener('blur', (e) => {
                    const relatedTarget = e.relatedTarget;
                    const isMovingUp = relatedTarget &&
                        (input.compareDocumentPosition(relatedTarget) & Node.DOCUMENT_POSITION_PRECEDING);

                    if (isMovingUp) {
                        clearFieldError(input);
                    } else {
                        validateSingleField(input);
                    }
                });
                input.addEventListener('input', () => clearFieldError(input));
            }
        });
        form.addEventListener('submit', (e) => {
            let formIsValid = true;
            let firstError = null;

            Object.values(inputs).forEach(input => {
                if (!validateSingleField(input)) {
                    formIsValid = false;
                    if (!firstError) firstError = (input instanceof NodeList) ? input[0] : input;
                }
            });
            if (!formIsValid) {
                e.preventDefault();
                if (firstError) firstError.focus();
            }
        });
    }
});