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
 * Restituisce l'HTML per il link dell'area personale nella navbar
 * Se l'utente è loggato, mostra lo username, altrimenti "Area Personale"
 */
function getAreaPersonaleLink() {
    if (isLoggedIn()) {
        $username = htmlspecialchars($_SESSION['username']);
        $href = (isset($_SESSION['ruolo']) && $_SESSION['ruolo'] === 'admin') ? 'area-admin.php' : 'area-personale.php';
        return '<a href="' . $href . '"><span class="material-symbols-outlined" aria-hidden="true">person</span>' . $username . '</a>';
    } else {
        return '<a href="area-login.html">Area Personale</a>';
    }
}

/**
 * Restituisce solo il testo per il link (per template semplici)
 */
function getAreaPersonaleText() {
    if (isLoggedIn()) {
        return htmlspecialchars($_SESSION['username']);
    } else {
        return 'Area Personale';
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
        return 'area-login.html';
    }
}
?>
