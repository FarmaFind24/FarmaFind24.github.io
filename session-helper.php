<?php
// session-helper.php - Funzioni helper per la gestione della sessione

/**
 * Inizializza la sessione se non è già attiva
 */
function initSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

/**
 * Verifica se l'utente è loggato
 */
function isLoggedIn() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}


/**
 * Restituisce solo il testo per il link (per template semplici)
 */
function getAreaPersonaleText() {
    if (isLoggedIn()) {
        if ($_SESSION['ruolo'] === 'admin') {
            return 'Area Amministrativa';
        } else {
            return 'Area Personale';
        }
    } else {
        return '<span class="login">Accedi</span>';
    }
}

/**
 * Restituisce l'href corretto per il link area personale
 */
function getAreaPersonaleHref() {
    if (isLoggedIn()) {
        // Se è admin, rimanda a area-admin.php
        if (isset($_SESSION['ruolo']) && $_SESSION['ruolo'] === 'admin') {
            return 'area-admin.php';
        }
        return 'area-personale.php';
    } else {
        return 'area-login.php';
    }
}
?>
