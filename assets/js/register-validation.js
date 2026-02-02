// register-validation.js - Gestione validazione e errori per registrazione

document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById('register-form');
    if (!form) return;
    const errorMappings = {
        'empty_fields': {
            message: 'Compila tutti i campi per completare la registrazione.'
        },
        'invalid_name': {
            field: 'name',
            message: 'Nome non valido. Usa solo lettere (2-50 caratteri).'
        },
        'invalid_surname': {
            field: 'surname',
            message: 'Cognome non valido. Usa solo lettere (2-50 caratteri).'
        },
        'invalid_username': {
            field: 'username',
            message: 'Username non valido. Usa lettere, numeri, _ o - (3-50 caratteri).'
        },
        'invalid_email': {
            field: 'email',
            message: 'Inserisci un indirizzo email valido.'
        },
        'email_too_long': {
            field: 'email',
            message: 'Email troppo lunga (massimo 100 caratteri).'
        },
        'password_too_short': {
            field: 'password',
            message: 'La password deve essere di almeno 8 caratteri.'
        },
        'password_weak': {
            field: 'password',
            message: 'La password deve contenere almeno una lettera e un numero.'
        },
        'passwords_mismatch': {
            field: 'confirm-password',
            message: 'Le password non coincidono.'
        },
        'username_taken': {
            field: 'username',
            message: 'Questo username è già in uso. Scegline un altro.'
        },
        'db_connection': {
            message: 'Errore di connessione al database. Riprova più tardi.'
        }
    };
    const successMappings = {
        'registered': 'Registrazione completata! Accedi per continuare.'
    };
    handleURLErrors(errorMappings, successMappings);
    if (form) {
        const inputs = {
            name: document.getElementById('name'),
            surname: document.getElementById('surname'),
            username: document.getElementById('username'),
            email: document.getElementById('email'),
            password: document.getElementById('password'),
            confirm: document.getElementById('confirm-password')
        };
        const validateSingleField = (input) => {
            if (!input) return true;

            clearFieldError(input);
            const valoreInput = input.value.trim();
            switch (input.id) {
                case 'name':
                    if (valoreInput === '') { showFieldError(input, 'Errore: inserire un nome.'); return false; }
                    if (!validateNome(valoreInput)) { showFieldError(input, 'Errore: nome non valido, inserire da 2 a 50 lettere.') }
                    break;
                case 'surname':
                    if (valoreInput === '') { showFieldError(input, 'Errore: inserire un cognome.'); return false; }
                    if (!validateCognome(valoreInput)) { showFieldError(input, 'Errore: cognome non valido (2-50 lettere).'); return false; }
                    break;
                case 'username':
                    if (valoreInput === '') { showFieldError(input, 'Errore: inserire uno username'); return false; }
                    if (!validateUsername(valoreInput)) { showFieldError(input, 'Errore: username non valido (3-50 caratteri: lettere, numeri, _, -).'); return false; }
                    break;
                case 'email':
                    if (valoreInput === '') { showFieldError(input, 'Errore: inserire una email.'); return false; }
                    if (!validateEmail(valoreInput)) { showFieldError(input, 'Errore: inserisci un indirizzo email valido.'); return false; }
                    break;
                case 'password':
                    if (valoreInput === '') { showFieldError(input, 'Errore: inserire una password.'); return false; }
                    if (!validatePassword(valoreInput)) { showFieldError(input, 'Errore: la password ha un formato non valido.'); return false; }
                    break;
                case 'confirm-password':
                    if (valoreInput === '') { showFieldError(input, 'Errore: confermare la password.'); return false; }
                    if (valoreInput !== inputs.password.value) { showFieldError(input, 'Errore: le password non coincidono.'); return false; }
                    break;

                default:
                    return true;

            }
            return true;
        };
        Object.values(inputs).forEach(input => {
            if (input) {
                input.addEventListener('blur', (e) => {
                    const relatedTarget = e.relatedTarget;
                    const isMovingUp = relatedTarget &&
                        (input.compareDocumentPosition(relatedTarget) & Node.DOCUMENT_POSITION_PRECEDING);

                    if (!isMovingUp) {
                        validateSingleField(input);
                    } else {
                        clearFieldError(input);
                    }
                });
                input.addEventListener('input', () => {
                    clearFieldError(input);
                });
            }
        });
        form.addEventListener('submit', (e) => {
            let formIsValid = true;
            let firstError = null;
            Object.values(inputs).forEach(input => {
                if (!validateSingleField(input)) {
                    formIsValid = false;
                    if (!firstError) firstError = input;
                }
            });
            if (!formIsValid) {
                e.preventDefault();
                if (firstError) firstError.focus();
            }
        });
    }
});

