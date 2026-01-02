<?php
// FILE: index.php
require_once "dbConnection.php";
use DB\DBAccess;

// 1. Inizializzazione variabili per la vista
$titoloPagina = "Ricerca Farmaci e Farmacie";
$risultati = null;
$errore = null;
$haCercato = false;
$tipoRicerca = 'farmaci'; // Default
$testoCercato = '';

// 2. Logica di elaborazione
if (isset($_GET['q']) && !empty(trim($_GET['q']))) {
    $haCercato = true;
    $testoCercato = trim($_GET['q']);
    $tipoRicerca = $_GET['tipo'] ?? 'farmaci'; // Prende 'farmaci' o 'farmacie'

    $db = new DBAccess();
    $connessioneOk = $db->openDBConnection();

    if ($connessioneOk) {
        if ($tipoRicerca === 'farmaci') {
            $risultati = $db->cercaFarmaci($testoCercato);
        } elseif ($tipoRicerca === 'farmacie') {
            $risultati = $db->cercaFarmacie($testoCercato);
        }
        $db->closeConnection();
    } else {
        $errore = "Impossibile connettersi al database. Riprova più tardi.";
    }
}

// 3. Caricamento della Vista (HTML)
// Le variabili $risultati, $errore, ecc. saranno disponibili dentro questo file
require "view_search.php";
?>