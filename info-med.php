<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "dbConnection.php";
require_once "session-helper.php";
use DB\DBAccess;

$paginaHTML = file_get_contents("info-med.html");

// Validazione e recupero ID farmaco
$idFarmaco = $_GET['id'] ?? null;

if (!$idFarmaco) {
    header("Location: med-search.php");
    exit;
}

// Validazione ID (deve essere un intero positivo)
$idFarmaco = filter_var($idFarmaco, FILTER_VALIDATE_INT);
if ($idFarmaco === false || $idFarmaco <= 0) {
    header("Location: med-search.php");
    exit;
}

$db = new DBAccess();
$connessioneOk = $db->openDBConnection();

if (!$connessioneOk) {
    echo '<p class="error">Errore di connessione al database.</p>';
    exit;
}

// Recupera dettagli del farmaco
$farmaco = $db->getFarmacoById($idFarmaco);

if (!$farmaco) {
    $db->closeConnection();
    header("Location: med-search.php");
    exit;
}

// Recupera farmacie che hanno il farmaco
$farmacie = $db->getFarmacieConFarmaco($idFarmaco);

// Costruzione HTML dettagli farmaco
$nomeFarmaco = htmlspecialchars($farmaco['nome_commerciale']);
$immagineFarmaco = !empty($farmaco['immagine']) ? 
    "assets/medImages/" . htmlspecialchars($farmaco['immagine']) : 
    "assets/immagine_farmaco.jpg";
$altImmagine = "Confezione di " . $nomeFarmaco;

$descrizione = htmlspecialchars($farmaco['descrizione']);
$principioAttivo = htmlspecialchars($farmaco['principio_attivo']);
$formato = htmlspecialchars($farmaco['forma_farmaceutica']);
$dosaggio = $farmaco['dosaggio'];
$obbligoRicetta = $farmaco['obbligo_ricetta'] == '1' ? 'S&igrave' : 'No (<abbr title="Over The Counter" lang="en">OTC</abbr>)';
$produttore = htmlspecialchars($farmaco['produttore']);

// Costruzione HTML lista farmacie
$htmlFarmacie = '';
if ($farmacie && count($farmacie) > 0) {
    foreach ($farmacie as $farmacia) {
        $idFarm = $farmacia['id'];
        $immagineFarmacia = !empty($farmacia['immagine']) ? 
            "assets/farmCovers/" . htmlspecialchars($farmacia['immagine']) : 
            "assets/immagine_farmacia.jpg";
        
        $htmlFarmacie .= '<div class="farm-card">';
        $htmlFarmacie .= '<img src="' . $immagineFarmacia . '" alt="Facciata della ' . htmlspecialchars($farmacia['nome']) . '" />';
        $htmlFarmacie .= '<div class="farm-card-content">';
        $htmlFarmacie .= '<h3 class="title-card">' . htmlspecialchars($farmacia['nome']) . '</h3>';
        $htmlFarmacie .= '<p>' . htmlspecialchars($farmacia['indirizzo']) . ', ' . htmlspecialchars($farmacia['citta']) . '</p>';
        
        // Calcola orario (puoi migliorare questa parte se hai gli orari nel DB)
        $isAperta = $db->isFarmaciaAperta($idFarm);
        $statoOrario = $isAperta ? "Aperta ora" : "Chiusa";
        $htmlFarmacie .= '<span class="farm-orario">' . $statoOrario . '</span>';
        
        // Mostra prezzo e disponibilit�
        if (isset($farmacia['prezzo']) && $farmacia['prezzo'] > 0) {
            $htmlFarmacie .= '<p><strong>Prezzo Farmaco:</strong> &euro;' . number_format($farmacia['prezzo'], 2, ',', '.') . '</p>';
        }
        if (isset($farmacia['quantita'])) {
            $disponibilita = $farmacia['quantita'] > 3 ? 
                '<span style="color: green;">Disponibile (' . $farmacia['quantita'] . ' pezzi)</span>' : 
                '<span style="color: red;">Ridotta (' . $farmacia['quantita'] . ' pezzi/o)</span>';
            $htmlFarmacie .= '<p><strong>Disponibilit&agrave:</strong> ' . $disponibilita . '</p>';
        }
        
        $htmlFarmacie .= '<div class="row-btn">';
        $htmlFarmacie .= '<a href="info-farm.php?id=' . $idFarm . '" class="btn-like primary">Vedi Dettagli</a>';
        $htmlFarmacie .= '</div>';
        $htmlFarmacie .= '</div></div>';
    }
} else {
    $htmlFarmacie = '<p class="no-results">Nessuna farmacia disponibile per questo farmaco al momento.</p>';
}

$db->closeConnection();

// Sostituzioni nel template HTML
$paginaHTML = str_replace('[nome_farmaco]', $nomeFarmaco, $paginaHTML);
$paginaHTML = str_replace('[immagine_farmaco]', $immagineFarmaco, $paginaHTML);
$paginaHTML = str_replace('[alt_immagine]', $altImmagine, $paginaHTML);
$paginaHTML = str_replace('[descrizione]', $descrizione, $paginaHTML);
$paginaHTML = str_replace('[principio_attivo]', $principioAttivo, $paginaHTML);
$paginaHTML = str_replace('[formato]', $formato, $paginaHTML);
$paginaHTML = str_replace('[dosaggio]', $dosaggio, $paginaHTML);
$paginaHTML = str_replace('[obbligo_ricetta]', $obbligoRicetta, $paginaHTML);
$paginaHTML = str_replace('[produttore]', $produttore, $paginaHTML);
$paginaHTML = str_replace('[lista_farmacie]', $htmlFarmacie, $paginaHTML);

// Gestione Area Personale nella navbar
$paginaHTML = str_replace('[area_personale_href]', getAreaPersonaleHref(), $paginaHTML);
$paginaHTML = str_replace('[area_personale_text]', getAreaPersonaleText(), $paginaHTML);

echo $paginaHTML;
?>