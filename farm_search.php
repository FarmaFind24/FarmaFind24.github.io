<?php
// FILE: farm_search.php
require_once "dbConnection.php";
use DB\DBAccess;

// 1. Carica il template HTML puro come una stringa di testo
$paginaHTML = file_get_contents("farm_search.html");

// 2. Inizializza variabili
$testoCercato = "";
$htmlRisultati = ""; // Qui costruiremo la lista o la tabella

// 3. Verifica se c'è una ricerca in corso
if (isset($_GET['q']) && !empty(trim($_GET['q']))) {
    
    // Pulisci l'input per sicurezza (anche se usiamo prepared statements dopo)
    $testoCercato = trim($_GET['q']);
    
    // Apri connessione
    $db = new DBAccess();
    $connessioneOk = $db->openDBConnection();

    if ($connessioneOk) {
        // Esegui la ricerca (Assicurati che cercaFarmacie esista in dbConnection.php)
        $risultati = $db->cercaFarmacie($testoCercato);
        $db->closeConnection();
        
        // 4. Genera l'HTML dei risultati (Tabella o Lista)
        if ($risultati && count($risultati) > 0) {
            
            foreach ($risultati as $row) {
                // Costruisci la card o la riga della tabella. 
                // Esempio basato sul tuo HTML attuale:
                
                // Controlla se il campo 'immagine' nel DB è pieno, altrimenti usa un placeholder
                if (!empty($row['immagine'])) {
                    $srcImmagine = "assets/" . htmlspecialchars($row['immagine']);
                } else {
                    // Immagine di default se il farmaco non ha foto
                    $srcImmagine = "assets/immagine_farmacia.jpg"; 
                }
                
                // Stampa HTML
                $htmlRisultati .= '<div class="farm-card">';
                $htmlRisultati .= '<img src="' . $srcImmagine . '" alt="Foto ' . htmlspecialchars($row['nome']) . '">';
                // ... resto del codice ...
                
                $htmlRisultati .= '<div class="farm-card-content">';
                $htmlRisultati .= '<h3>' . htmlspecialchars($row['nome']) . '</h3>';
                $htmlRisultati .= '<p>' . htmlspecialchars($row['indirizzo']) . ', ' . htmlspecialchars($row['citta']) . '</p>';
                $htmlRisultati .= '<span class="farm-orario">Tel: ' . htmlspecialchars($row['telefono']) . '</span>';
                // Aggiungi altri campi se necessario
                $htmlRisultati .= '</div></div>';
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
$paginaHTML = str_replace('[listaFarmacie]', $htmlRisultati, $paginaHTML);

// Sostituisci [valore_ricerca] nel campo input per mostrare cosa l'utente ha cercato
// Se non ha cercato nulla, lo sostituiamo con stringa vuota
$paginaHTML = str_replace('[valore_ricerca]', htmlspecialchars($testoCercato), $paginaHTML);

// 6. Stampa la pagina finale
echo $paginaHTML;
?>