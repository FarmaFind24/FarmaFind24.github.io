// book-form-validation.js - Validazione form prenotazioni

// MAPPATURA ERRORI URL FORM PRENOTAZIONE
document.addEventListener("DOMContentLoaded", () => {
    const form = document.querySelector('form#regForm');

    if (!form) return;

    const errorMappings = {
        'missing_fields': {
            message: 'Compila tutti i campi obbligatori per completare la prenotazione.'
        },
        'invalid_name': {
            field: 'fname',
            message: 'Nome non valido. Usa solo lettere.'
        },
        'invalid_fiscal_code': {
            field: 'fcode',
            message: 'Codice fiscale non valido (16 caratteri, formato corretto).'
        },
        'invalid_date': {
            field: 'date',
            message: 'Seleziona una data valida.'
        },
        'invalid_time': {
            field: 'time',
            message: 'Seleziona un orario valido.'
        },
        'past_date': {
            field: 'date',
            message: 'Non puoi prenotare appuntamenti nel passato.'
        },
        'db_connection': {
            message: 'Errore di connessione al database. Riprova più tardi.'
        },
        'session_invalid': {
            message: 'Sessione non valida. Effettua nuovamente l\'accesso.'
        },
        'service_not_available': {
            message: 'Il servizio selezionato non è disponibile.'
        },
        'slot_unavailable': {
            message: 'L\'orario selezionato non è più disponibile. Scegline un altro.'
        },
        'booking_failed': {
            message: 'Impossibile completare la prenotazione. Riprova.'
        },
        'authentication_required': {
            message: 'Devi effettuare l\'accesso per prenotare un appuntamento.'
        }
    };

    handleURLErrors(errorMappings);
});

function validateNome() {
    var nome = document.getElementById("fname").value;
    const validChars = /^[A-Za-zÀ-ù\s']+$/;

    if (nome.trim() === "") return true;
    if (!validChars.test(nome)) return false;

    return true;
}

function resetFormError() {
    var errorBox = document.getElementById("general-error-msg");
    if (errorBox) {
        errorBox.style.display = "none";
        errorBox.innerHTML = "";
    }
}

function addFormError(msg) {
    var errorBox = document.getElementById("general-error-msg");
    if (errorBox) {
        errorBox.style.display = "block";
        errorBox.innerHTML = msg;
        errorBox.focus();
    }
}