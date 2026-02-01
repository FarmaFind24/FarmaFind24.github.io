<?php
session_start();
// FILE: farm-search.php
require_once "dbConnection.php";
require_once "session-helper.php";
use DB\DBAccess;

// 1. Carica il template HTML puro come una stringa di testo
$paginaHTML = file_get_contents("med-search.html");

// 2. Inizializza variabili
$testoCercato = "";
$htmlRisultati = ""; // Qui costruiremo la lista o la tabella

// Gestione messaggi di errore da parametri URL
if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'missing_medicine_id':
            $htmlRisultati = '<p class="error" role="alert">ID medicinale mancante. Effettua una ricerca per trovare il medicinale desiderato.</p>';
            break;
        case 'invalid_medicine_id':
            $htmlRisultati = '<p class="error" role="alert">ID medicinale non valido.</p>';
            break;
        case 'medicine_not_found':
            $htmlRisultati = '<p class="error" role="alert">Il medicinale richiesto non è stato trovato. Potrebbe essere stato rimosso dal sistema.</p>';
            break;
        default:
            $htmlRisultati = '<p class="error" role="alert">Si è verificato un errore. Riprova.</p>';
            break;
    }
}



// 3. Esegui ricerca solo se non ci sono errori da parametri URL e c'è una ricerca in corso
if (empty($htmlRisultati) && isset($_GET['q']) && !empty(trim($_GET['q']))) {
    
    // Pulisci l'input per sicurezza (anche se usiamo prepared statements dopo)
    $testoCercato = trim($_GET['q']);
    
    // Apri connessione
    $db = new DBAccess();
    $connessioneOk = $db->openDBConnection();

    if ($connessioneOk) {
        // Esegui la ricerca (Assicurati che cercaFarmacie esista in dbConnection.php)
        $risultati = $db->cercaFarmaci($testoCercato);
        $db->closeConnection();
        
        // 4. Genera l'HTML dei risultati (Tabella o Lista)
        if ($risultati && count($risultati) > 0) {
            
            foreach ($risultati as $row) {
                // Costruisci la card o la riga della tabella. 
                // Esempio basato sul tuo HTML attuale:
                
                // Controlla se il campo 'immagine' nel DB è pieno, altrimenti usa un placeholder
                if (!empty($row['immagine'])) {
                    $srcImmagine = "assets/medImages/" . htmlspecialchars($row['immagine']);
                } else {
                    // Immagine di default se il farmaco non ha foto
                    $srcImmagine = "assets/immagine_farmaco.jpg"; 
                }

                if ($row['obbligo_ricetta'] == 1) {
                    $obbligoricetta = '<div class="badge badge-ricetta">Ricetta Richiesta</div>';
                } else {
                    $obbligoricetta = '<div class="badge badge-banco">Banco (OTC)</div>';
                }
                
                // Stampa HTML
                $htmlRisultati .= '<div class="drug-row">';
                $htmlRisultati .= '<dl>';
                $htmlRisultati .= '<dt></dt>';
                $htmlRisultati .= '<dd class="drug-image">';
                $htmlRisultati .= '<img src="' . htmlspecialchars($srcImmagine) . '" alt="Farmaco">';
                $htmlRisultati .= '</dd>'; 
                $htmlRisultati .= '</dl>';
                $htmlRisultati .= '<div>';
                $htmlRisultati .= '<dl class="drug-info">';
                $htmlRisultati .= '<dt>Nome farmaco:</dt>';
                $htmlRisultati .= '<dd class="drug-header">';
                $htmlRisultati .= '<h2>' . htmlspecialchars($row['nome_commerciale']) . '</h2>';
                $htmlRisultati .= '</dd>';
                $htmlRisultati .= '</dl>';
                $htmlRisultati .=  $obbligoricetta;
                $htmlRisultati .= '<div  class="active-ingredient">';
                $htmlRisultati .= '<p class="nohide">Principio Attivo:</p>';
                $htmlRisultati .= '<p><strong>' . htmlspecialchars($row['principio_attivo']) . '</strong></p>';
                $htmlRisultati .= '</div>';
                
                $htmlRisultati .= '<dl class="drug-meta">';
                $htmlRisultati .= '<dt>Formato:</dt>';
                $htmlRisultati .= '<dd>' . htmlspecialchars($row['forma_farmaceutica']) . '</dd>';
                $htmlRisultati .= '<dt>Dosaggio:</dt>';
                $htmlRisultati .= '<dd>' . $row['dosaggio'] . '</dd>';
                $htmlRisultati .= '</dl>';
                $htmlRisultati .= '<dl>';
                $htmlRisultati .= '<dt>Indicazioni:</dt>';
                $htmlRisultati .= '<dd class="drug-desc-short">' . htmlspecialchars($row['descrizione']) . '</dd>';
                $htmlRisultati .= '</dl>';
                $htmlRisultati .= '</div>';
                $htmlRisultati .= '<div class="drug-action">';
                $htmlRisultati .= '<a href="info-med.php?id=' . htmlspecialchars($row['id']) . '" class="btn-details">Dettagli/Disponibilità &rarr;</a>';
                $htmlRisultati .= '</div>';
                $htmlRisultati .= '</div>';
            }
            
        } else {
            $htmlRisultati = '<p class="no-results">Nessun risultato trovato per "<strong>' . htmlspecialchars($testoCercato) . '</strong>".</p>';
        }
        
    } else {
        $htmlRisultati = '<p class="error">Errore di connessione al database.</p>';
    }
} 

// 5. SOSTITUZIONE DEI SEGNAPOSTI (Il cuore della separazione HTML/PHP)

// Sostituisci [listaFarmacie] con l'HTML generato dal ciclo foreach qui sopra
$paginaHTML = str_replace('[listaFarmaci]', $htmlRisultati, $paginaHTML);

// Sostituisci [valore_ricerca] nel campo input per mostrare cosa l'utente ha cercato
// Se non ha cercato nulla, lo sostituiamo con stringa vuota
$paginaHTML = str_replace('[valore_ricerca]', htmlspecialchars($testoCercato), $paginaHTML);

// Gestione Area Personale nella navbar
$paginaHTML = str_replace('[area_personale_href]', getAreaPersonaleHref(), $paginaHTML);
$paginaHTML = str_replace('[area_personale_text]', getAreaPersonaleText(), $paginaHTML);

// 6. Stampa la pagina finale
echo $paginaHTML;
?>