// feedback-messages.js - Sistema di notifiche per l'utente

document.addEventListener("DOMContentLoaded", () => {
    // Leggi i parametri URL
    const urlParams = new URLSearchParams(window.location.search);
    const success = urlParams.get('success');
    const error = urlParams.get('error');
    
    // Definizione messaggi
    const messages = {
        success: {
            'profilo_aggiornato': 'Profilo aggiornato con successo!',
            'account_eliminato': 'Account eliminato. Grazie per aver utilizzato FarmaFind24.',
            'registered': 'Registrazione completata! Accedi per continuare.'
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
            'authentication_required': 'Devi effettuare l\'accesso per prenotare un appuntamento.'
        }
    };
    
    // Mostra il messaggio se presente
    if (success && messages.success[success]) {
        showNotification(messages.success[success], 'success');
    } else if (error && messages.error[error]) {
        showNotification(messages.error[error], 'error');
    }
});

function showNotification(message, type) {
    // Crea l'elemento notifica
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.setAttribute('role', 'alert');
    notification.setAttribute('aria-live', 'polite');
    
    // Icona
    const icon = document.createElement('span');
    icon.className = 'material-symbols-outlined';
    icon.setAttribute('aria-hidden', 'true');
    icon.textContent = type === 'success' ? 'check_circle' : 'error';
    
    // Testo
    const text = document.createElement('span');
    text.textContent = message;
    
    // Bottone chiudi
    const closeBtn = document.createElement('button');
    closeBtn.className = 'notification-close';
    closeBtn.setAttribute('aria-label', 'Chiudi notifica');
    closeBtn.innerHTML = '&times;';
    closeBtn.onclick = () => notification.remove();
    
    // Assembla
    notification.appendChild(icon);
    notification.appendChild(text);
    notification.appendChild(closeBtn);
    
    // Aggiungi al body
    document.body.appendChild(notification);
    
    // Rimuovi dopo 5 secondi
    setTimeout(() => {
        notification.style.opacity = '0';
        setTimeout(() => notification.remove(), 300);
    }, 5000);
    
    // Pulisci URL (rimuovi parametri)
    const cleanUrl = window.location.pathname;
    window.history.replaceState({}, document.title, cleanUrl);
}
