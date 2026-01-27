// register-validation.js - Gestione validazione e errori per registrazione

document.addEventListener("DOMContentLoaded", () => {
    const form = document.querySelector('form[action="process-register.php"]');
    
    if (!form) return;
    
    // Mappatura errori URL → campo + messaggio
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
    
    // Gestisci errori/successi da URL
    handleURLErrors(errorMappings, successMappings);
    
    // Validazione client-side al submit
    form.addEventListener('submit', (e) => {
        clearAllFieldErrors(form);
        clearGeneralError(form);
        
        let isValid = true;
        let firstInvalidInput = null;
        
        const nameInput = document.getElementById('name');
        const surnameInput = document.getElementById('surname');
        const usernameInput = document.getElementById('username');
        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('confirm-password');
        
        // Validazione nome
        if (nameInput) {
            const nome = nameInput.value.trim();
            if (nome === '') {
                showFieldError(nameInput, 'Inserisci il tuo nome.');
                isValid = false;
                if (!firstInvalidInput) firstInvalidInput = nameInput;
            } else if (!validateNome(nome)) {
                showFieldError(nameInput, 'Nome non valido. Usa solo lettere (2-50 caratteri).');
                isValid = false;
                if (!firstInvalidInput) firstInvalidInput = nameInput;
            }
        }
        
        // Validazione cognome
        if (surnameInput) {
            const cognome = surnameInput.value.trim();
            if (cognome === '') {
                showFieldError(surnameInput, 'Inserisci il tuo cognome.');
                isValid = false;
                if (!firstInvalidInput) firstInvalidInput = surnameInput;
            } else if (!validateCognome(cognome)) {
                showFieldError(surnameInput, 'Cognome non valido. Usa solo lettere (2-50 caratteri).');
                isValid = false;
                if (!firstInvalidInput) firstInvalidInput = surnameInput;
            }
        }
        
        // Validazione username
        if (usernameInput) {
            const username = usernameInput.value.trim();
            if (username === '') {
                showFieldError(usernameInput, 'Scegli un username.');
                isValid = false;
                if (!firstInvalidInput) firstInvalidInput = usernameInput;
            } else if (!validateUsername(username)) {
                showFieldError(usernameInput, 'Username non valido. Usa lettere, numeri, _ o - (3-50 caratteri).');
                isValid = false;
                if (!firstInvalidInput) firstInvalidInput = usernameInput;
            }
        }
        
        // Validazione email
        if (emailInput) {
            const email = emailInput.value.trim();
            if (email === '') {
                showFieldError(emailInput, 'Inserisci la tua email.');
                isValid = false;
                if (!firstInvalidInput) firstInvalidInput = emailInput;
            } else if (!validateEmail(email)) {
                showFieldError(emailInput, 'Inserisci un indirizzo email valido.');
                isValid = false;
                if (!firstInvalidInput) firstInvalidInput = emailInput;
            }
        }
        
        // Validazione password
        if (passwordInput) {
            const password = passwordInput.value;
            if (password === '') {
                showFieldError(passwordInput, 'Scegli una password.');
                isValid = false;
                if (!firstInvalidInput) firstInvalidInput = passwordInput;
            } else if (!validatePassword(password)) {
                showFieldError(passwordInput, 'La password deve essere di almeno 8 caratteri e contenere una lettera e un numero.');
                isValid = false;
                if (!firstInvalidInput) firstInvalidInput = passwordInput;
            }
        }
        
        // Validazione conferma password
        if (confirmPasswordInput && passwordInput) {
            const password = passwordInput.value;
            const confirm = confirmPasswordInput.value;
            if (confirm === '') {
                showFieldError(confirmPasswordInput, 'Conferma la tua password.');
                isValid = false;
                if (!firstInvalidInput) firstInvalidInput = confirmPasswordInput;
            } else if (password !== confirm) {
                showFieldError(confirmPasswordInput, 'Le password non coincidono.');
                isValid = false;
                if (!firstInvalidInput) firstInvalidInput = confirmPasswordInput;
            }
        }
        
        if (!isValid) {
            e.preventDefault();
            if (firstInvalidInput) {
                firstInvalidInput.focus();
            }
        }
    });
    
    // Rimuovi errore quando utente inizia a digitare
    const inputs = form.querySelectorAll('input');
    inputs.forEach(input => {
        input.addEventListener('input', () => {
            clearFieldError(input);
        });
    });
});
