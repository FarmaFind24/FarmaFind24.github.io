// booking-details-validation.js - Gestione errori per booking-details

document.addEventListener("DOMContentLoaded", () => {
    const main = document.querySelector('main');
    
    if (!main) return;
    
    // Mappatura errori URL
    const errorMappings = {
        'authentication_required': {
            message: 'Devi effettuare l\'accesso per visualizzare le prenotazioni.'
        },
        'booking_not_found': {
            message: 'Prenotazione non trovata.'
        },
        'invalid_booking_id': {
            message: 'ID prenotazione non valido.'
        },
        'cancellation_failed': {
            message: 'Impossibile cancellare la prenotazione. Riprova.'
        },
        'db_error': {
            message: 'Errore di connessione al database. Riprova più tardi.'
        }
    };
    
    const successMappings = {
        'booking_cancelled': 'Prenotazione cancellata con successo.'
    };
    
    // Gestisci errori/successi da URL
    handleURLErrors(errorMappings, successMappings);
});
