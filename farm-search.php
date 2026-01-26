<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "dbConnection.php";
require_once "session-helper.php";
use DB\DBAccess;

$paginaHTML = file_get_contents("farm-search.html");

$testoCercato = "";
$htmlRisultati = ""; 

if (isset($_GET['q']) && !empty(trim($_GET['q']))) {
    
    $testoCercato = trim($_GET['q']);

    // VALIDAZIONE INPUT RICERCA
    // Validazione lunghezza (min 2, max 100 caratteri)
    if (strlen($testoCercato) < 2 || strlen($testoCercato) > 100) {
        $htmlRisultati = '<p class="no-results">La ricerca deve contenere tra 2 e 100 caratteri.</p>';
    } else {
    
    $db = new DBAccess();
    $connessioneOk = $db->openDBConnection();

    if ($connessioneOk) {
        try {
            $risultati = $db->cercaFarmacie($testoCercato);
            
            if ($risultati && count($risultati) > 0) {
                
foreach ($risultati as $row) {
    
    // GESTIONE IMMAGINE (Invariata)
    if (!empty($row['immagine'])) {
        $srcImmagine = "assets/farmCovers/" . htmlspecialchars($row['immagine']);
    } else {
        $srcImmagine = "assets/immagine_farmacia.jpg"; 
    }
    
    $idFarm = $row['id']; // Salviamo l'ID subito
    
    // --- INIZIO COSTRUZIONE CARD ---
    $htmlRisultati .= '<div class="farm-card">';
    
    // Immagine Header
    $htmlRisultati .= '<div class="card-image-header">';
    $htmlRisultati .= '<img src="' . $srcImmagine . '" alt="Foto ' . htmlspecialchars($row['nome']) . '">';
    $htmlRisultati .= '</div>'; // chiusura card-image-header

    // Contenuto Card
    $htmlRisultati .= '<div class="farm-card-content">';
    
    // Header: Titolo e Indirizzo
    $htmlRisultati .= '<div class="card-header">';
    $htmlRisultati .= '<h3 class="title-card">' . htmlspecialchars($row['nome']) . '</h3>';
    $htmlRisultati .= '<p class="address"><i class="fa-solid fa-location-dot"></i> ' . htmlspecialchars($row['indirizzo']) . ', ' . htmlspecialchars($row['citta']) . '</p>';
    $htmlRisultati .= '</div>';

    // Contatti
    $htmlRisultati .= '<div class="card-meta">';
    $htmlRisultati .= '<span class="phone-link"><i class="fa-solid fa-phone"></i> ' . htmlspecialchars($row['telefono']) . '</span>';
    $htmlRisultati .= '</div>';

    // --- LOGICA SERVIZI (AUTOMATIZZATA) ---
    $servizi = $db->getServiziFarmacia($idFarm);
    
    if ($servizi && count($servizi) > 0) {
        $maxVisibili = 3; // DECIDI QUI QUANTI MOSTRARNE
        $totaleServizi = count($servizi);
        $rimanenti = $totaleServizi - $maxVisibili;

        // Prende solo i primi 3
        $serviziDaMostrare = array_slice($servizi, 0, $maxVisibili);

        $htmlRisultati .= '<div class="services-preview">';
        // ID univoco per accessibilità
        $lblId = 'lbl-serv-' . $idFarm; 
        $htmlRisultati .= '<span class="service-label" id="' . $lblId . '">Servizi principali:</span>';
        $htmlRisultati .= '<ul class="service-tags" aria-labelledby="' . $lblId . '">';
        
        // Ciclo sui servizi visibili
        foreach ($serviziDaMostrare as $servizio) {
            $htmlRisultati .= '<li>' . htmlspecialchars($servizio['nome_servizio']) . '</li>';
        }

        // Badge "+ altri" se ce ne sono di più
        if ($rimanenti > 0) {
            $htmlRisultati .= '<li class="more-badge">';
            $htmlRisultati .= '<span aria-hidden="true">+' . $rimanenti . ' altri</span>';
            $htmlRisultati .= '<span class="sr-only">e altri ' . $rimanenti . ' servizi disponibili</span>';
            $htmlRisultati .= '</li>';
        }

        $htmlRisultati .= '</ul>';
        $htmlRisultati .= '</div>'; // chiusura services-preview
    }

    // Bottone (spinto in fondo dal CSS margin-top: auto)
    $htmlRisultati .= '<div class="row-btn">
                        <a href="info-farm.php?id=' . $idFarm . '" class="btn-like primary">Dettagli</a>
                       </div>';

    $htmlRisultati .= '</div>'; // chiusura farm-card-content
    $htmlRisultati .= '</div>'; // chiusura farm-card
}
            } else {
                $htmlRisultati = '<p class="no-results">Nessuna farmacia o città trovata per "<strong>' . htmlspecialchars($testoCercato) . '</strong>".</p>';
            }
        } catch (\mysqli_sql_exception $e) {
            // Per il debug, puoi registrare l'errore: error_log($e->getMessage());
            $htmlRisultati = '<p class="error">Si è verificato un errore durante la ricerca. Riprova più tardi.</p>';
        } finally {
            $db->closeConnection();
        }
    } else {
        $htmlRisultati = '<p class="error">Errore di connessione al database.</p>';
    }
    } // Chiusura else validazione lunghezza
}
$paginaHTML = str_replace('[listaFarmacie]', $htmlRisultati, $paginaHTML);
$paginaHTML = str_replace('[valore_ricerca]', htmlspecialchars($testoCercato), $paginaHTML);

// Gestione Area Personale nella navbar
$paginaHTML = str_replace('[area_personale_href]', getAreaPersonaleHref(), $paginaHTML);
$paginaHTML = str_replace('[area_personale_text]', getAreaPersonaleText(), $paginaHTML);

echo $paginaHTML;
?>