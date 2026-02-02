// feedback-messages.js - Sistema di messaggi di feedback per l'utente

document.addEventListener("DOMContentLoaded", () => {
    const urlParams = new URLSearchParams(window.location.search);
    const success = urlParams.get('success');
    const error = urlParams.get('error');
    const messages = {
        success: {
            'profilo_aggiornato': 'Profilo aggiornato con successo!',
            'account_eliminato': 'Account eliminato. Grazie per aver utilizzato FarmaFind24.',
            'registered': 'Registrazione completata! Accedi per continuare.',
            'booking_cancelled': 'Prenotazione cancellata con successo.'
        },
        error: {
            'campi_vuoti': 'Errore: tutti i campi sono obbligatori.',
            'email_non_valida': 'Errore: inserisci un indirizzo email valido.',
            'aggiornamento_fallito': 'Errore: impossibile aggiornare il profilo. Email già in uso?',
            'eliminazione_fallita': 'Errore: impossibile eliminare l\'account.',
            'db_error': 'Errore: problema di connessione al database.',
            'invalid_credentials': 'Errore: username o password non corretti.',
            'passwords_mismatch': 'Errore: le password non coincidono.',
            'username_taken': 'Errore: username già in uso.',
            'db_connection': 'Errore: impossibile connettersi al database.',
            'conferma_mancante': 'Errore: devi confermare l\'eliminazione.',
            'authentication_required': 'Devi effettuare l\'accesso per prenotare un appuntamento.',
            'session_invalid': 'Sessione non valida. Effettua nuovamente l\'accesso.',
            'cancellation_failed': 'Errore: impossibile cancellare la prenotazione.'
        }
    };
    if (success && messages.success[success]) {
        showMessage(messages.success[success], 'success');
    } else if (error && messages.error[error]) {
        showMessage(messages.error[error], 'error');
    }
    if (success || error) {
        const cleanUrl = window.location.pathname + window.location.hash;
        window.history.replaceState({}, document.title, cleanUrl);
    }
});
function showMessage(message, type) {
    let messageBox = document.getElementById('feedback-message');
    if (!messageBox) {
        messageBox = document.createElement('div');
        messageBox.id = 'feedback-message';
        messageBox.setAttribute('role', 'alert');
        messageBox.setAttribute('aria-live', 'polite');
        messageBox.setAttribute('tabindex', '-1');

        const main = document.querySelector('main');
        if (main) {
            main.insertBefore(messageBox, main.firstChild);
        } else {
            document.body.insertBefore(messageBox, document.body.firstChild);
        }
    }
    messageBox.className = type === 'success' ? 'success-message' : 'error-message';
    messageBox.style.display = 'block';
    messageBox.innerHTML = message;
    messageBox.focus();
    setTimeout(() => {
        hideMessage();
    }, 8000);
}

function hideMessage() {
    const messageBox = document.getElementById('feedback-message');
    if (messageBox) {
        messageBox.style.display = 'none';
        messageBox.innerHTML = '';
    }
}

