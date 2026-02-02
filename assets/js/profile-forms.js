document.addEventListener("DOMContentLoaded", () => {
    const editBtn = document.getElementById("edit-profile-btn");
    const saveBtn = document.getElementById("save-profile-btn");
    const cancelBtn = document.getElementById("cancel-edit-btn");
    const inputs = document.querySelectorAll(".grid.two-columns input");
    const profileForm = document.querySelector("form[action='process-update-profile.php']");

    // MAPPATURA ERRORI URL PER AREA PERSONALE
    const errorMappings = {
        'campi_vuoti': {
            message: 'Tutti i campi sono obbligatori.'
        },
        'nome_non_valido': {
            field: 'name',
            message: 'Nome non valido. Usa solo lettere (2-50 caratteri).'
        },
        'cognome_non_valido': {
            field: 'surname',
            message: 'Cognome non valido. Usa solo lettere (2-50 caratteri).'
        },
        'email_non_valida': {
            field: 'email',
            message: 'Inserisci un indirizzo email valido.'
        },
        'email_troppo_lunga': {
            field: 'email',
            message: 'Email troppo lunga (massimo 100 caratteri).'
        },
        'aggiornamento_fallito': {
            message: 'Impossibile aggiornare il profilo. Email già in uso?'
        },
        'db_error': {
            message: 'Errore di connessione al database. Riprova più tardi.'
        }
    };

    const successMappings = {
        'profilo_aggiornato': 'Profilo aggiornato con successo!',
        'account_eliminato': 'Account eliminato. Grazie per aver utilizzato FarmaFind24.',
        'booking_cancelled': 'Prenotazione cancellata con successo.'
    };
    const main = document.querySelector('main');
    handleURLErrors(errorMappings, successMappings);

    // VALIDAZIONE FORM AL SUBMIT
    if (profileForm) {
        profileForm.addEventListener("submit", (e) => {
            let isValid = true;
            clearAllFieldErrors(profileForm);

            const nomeInput = document.querySelector("input[name='name']");
            const cognomeInput = document.querySelector("input[name='surname']");
            const emailInput = document.querySelector("input[name='email']");

            if (nomeInput && !nomeInput.disabled && !nomeInput.readOnly) {
                if (!validateNome(nomeInput.value)) {
                    showFieldError(nomeInput, "Nome non valido. Usa solo lettere (2-50 caratteri).");
                    isValid = false;
                }
            }

            if (cognomeInput && !cognomeInput.disabled && !cognomeInput.readOnly) {
                if (!validateCognome(cognomeInput.value)) {
                    showFieldError(cognomeInput, "Cognome non valido. Usa solo lettere (2-50 caratteri).");
                    isValid = false;
                }
            }

            if (emailInput && !emailInput.disabled && !emailInput.readOnly) {
                if (!validateEmail(emailInput.value)) {
                    showFieldError(emailInput, "Email non valida.");
                    isValid = false;
                }
            }

            if (!isValid) {
                e.preventDefault();
                const firstInvalid = profileForm.querySelector(".invalid");
                if (firstInvalid) firstInvalid.focus();
            }
        });
    }

    if (editBtn) {
        editBtn.addEventListener("click", () => {
            clearAllFieldErrors(profileForm);
            inputs.forEach(input => {
                if (!input.disabled) {
                    input.removeAttribute("readonly");
                }
            });

            saveBtn.removeAttribute("hidden");
            cancelBtn.removeAttribute("hidden");

            editBtn.setAttribute("hidden", "true");

            const firstEditableInput = Array.from(inputs).find(input => !input.disabled);
            if (firstEditableInput) firstEditableInput.focus();
        });
    }

    if (cancelBtn) {
        cancelBtn.addEventListener("click", (e) => {
            e.preventDefault();
            clearAllFieldErrors(profileForm);
            profileForm.reset();
            inputs.forEach(input => {
                if (!input.disabled) {
                    input.setAttribute("readonly", "true");
                    input.classList.remove("editable");
                }
            });

            saveBtn.setAttribute("hidden", "true");
            cancelBtn.setAttribute("hidden", "true");

            editBtn.removeAttribute("hidden");
        });
    }
    inputs.forEach(input => {
        input.addEventListener('input', () => {
            clearFieldError(input);
        });
    });
});