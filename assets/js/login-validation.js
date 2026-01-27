// login-validation.js - Gestione validazione e errori per login

document.addEventListener("DOMContentLoaded", () => {
    const form = document.querySelector('form[action="process-login.php"]');
    
    if (!form) return;
    
    // Mappatura errori URL → campo + messaggio
    const errorMappings = {
        'empty_fields': {
            message: 'Compila tutti i campi per effettuare l\'accesso.'
        },
        'invalid_username_length': {
            field: 'username',
            message: 'Username deve essere tra 3 e 50 caratteri.'
        },
        'invalid_username_format': {
            field: 'username',
            message: 'Username può contenere solo lettere, numeri, _ e -'
        },
        'invalid_password_length': {
            field: 'password',
            message: 'La password deve essere di almeno 6 caratteri.'
        },
        'invalid_credentials': {
            message: 'Username o password non corretti. Riprova.'
        },
        'db_error': {
            message: 'Errore di connessione al database. Riprova più tardi.'
        }
    };
    
    // Gestisci errori da URL
    handleURLErrors(errorMappings);
    
    // Validazione client-side al submit
    form.addEventListener('submit', (e) => {
        clearAllFieldErrors(form);
        clearGeneralError(form);
        
        let isValid = true;
        let firstInvalidInput = null;
        
        const usernameInput = document.getElementById('username');
        const passwordInput = document.getElementById('password');
        
        // Validazione username
        if (usernameInput) {
            const username = usernameInput.value.trim();
            
            if (username === '') {
                showFieldError(usernameInput, 'Inserisci il tuo username.');
                isValid = false;
                if (!firstInvalidInput) firstInvalidInput = usernameInput;
            } else if (username.length < 3 || username.length > 50) {
                showFieldError(usernameInput, 'Username deve essere tra 3 e 50 caratteri.');
                isValid = false;
                if (!firstInvalidInput) firstInvalidInput = usernameInput;
            } else if (!/^[a-zA-Z0-9_-]+$/.test(username)) {
                showFieldError(usernameInput, 'Username può contenere solo lettere, numeri, _ e -');
                isValid = false;
                if (!firstInvalidInput) firstInvalidInput = usernameInput;
            }
        }
        
        // Validazione password
        if (passwordInput) {
            const password = passwordInput.value;
            const username = usernameInput ? usernameInput.value.trim() : '';
            
            // Utenti con eccezione per password più corta
            const exemptUsers = ['user', 'admin'];
            const minPasswordLength = exemptUsers.includes(username) ? 4 : 6;
            
            if (password === '') {
                showFieldError(passwordInput, 'Inserisci la tua password.');
                isValid = false;
                if (!firstInvalidInput) firstInvalidInput = passwordInput;
            } else if (password.length < minPasswordLength) {
                showFieldError(passwordInput, `La password deve essere di almeno ${minPasswordLength} caratteri.`);
                isValid = false;
                if (!firstInvalidInput) firstInvalidInput = passwordInput;
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
    const inputs = form.querySelectorAll('input[type="text"], input[type="password"]');
    inputs.forEach(input => {
        input.addEventListener('input', () => {
            clearFieldError(input);
            clearGeneralError(form);
        });
    });
});
