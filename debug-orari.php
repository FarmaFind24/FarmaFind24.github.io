<?php
// debug-orari.php - Script per debuggare gli orari delle farmacie
session_start();
require_once "dbConnection.php";
use DB\DBAccess;

$db = new DBAccess();
if ($db->openDBConnection()) {
    
    // Informazioni correnti
    echo "<h1>Debug Orari Farmacie</h1>";
    echo "<p><strong>Oggi è:</strong> " . date('l, d/m/Y H:i:s') . "</p>";
    echo "<p><strong>Giorno settimana (0=Dom, 6=Sab):</strong> " . date('w') . "</p>";
    echo "<p><strong>Ora corrente:</strong> " . date('H:i:s') . "</p>";
    
    echo "<hr>";
    
    // Prendi le prime 5 farmacie dalla funzione esistente
    $farmacie = $db->getListaFarmacie();
    
    if ($farmacie && count($farmacie) > 0) {
        $count = 0;
        foreach ($farmacie as $farmacia) {
            if ($count >= 5) break; // Limita a 5 farmacie
            
            $idFarmacia = $farmacia['id'];
            $nomeFarmacia = $farmacia['nome'];
            
            echo "<h2>Farmacia: {$nomeFarmacia} (ID: {$idFarmacia})</h2>";
            
            // Prendi gli orari per tutti i giorni usando la funzione esistente
            $giornoOggi = date('w');
            
            echo "<table border='1' style='margin-bottom: 20px; border-collapse: collapse;'>";
            echo "<tr style='background-color: #f0f0f0;'><th style='padding: 8px;'>Giorno</th><th style='padding: 8px;'>Apertura</th><th style='padding: 8px;'>Chiusura</th><th style='padding: 8px;'>Oggi?</th></tr>";
            
            $giorni = ['Domenica', 'Lunedì', 'Martedì', 'Mercoledì', 'Giovedì', 'Venerdì', 'Sabato'];
            $haOrariOggi = false;
            
            // Controlla tutti i giorni
            for ($g = 0; $g <= 6; $g++) {
                $orari = $db->getOrariFarmacia($idFarmacia, $g);
                
                if ($orari && count($orari) > 0) {
                    foreach ($orari as $orario) {
                        $isOggi = ($g == $giornoOggi) ? "✓ SÌ" : "";
                        if ($g == $giornoOggi) {
                            $haOrariOggi = true;
                        }
                        
                        $rowStyle = ($g == $giornoOggi) ? "background-color: #ffffcc;" : "";
                        
                        echo "<tr style='{$rowStyle}'>";
                        echo "<td style='padding: 8px;'>{$giorni[$g]} ({$g})</td>";
                        echo "<td style='padding: 8px;'>{$orario['ora_apertura']}</td>";
                        echo "<td style='padding: 8px;'>{$orario['ora_chiusura']}</td>";
                        echo "<td style='padding: 8px;'><strong>{$isOggi}</strong></td>";
                        echo "</tr>";
                    }
                }
            }
            echo "</table>";
            
            if (!$haOrariOggi) {
                echo "<p style='color: red;'><strong>⚠️ Nessun orario definito per oggi ({$giorni[$giornoOggi]})</strong></p>";
            }
            
            // Verifica stato
            $isAperta = $db->isFarmaciaAperta($idFarmacia);
            $stato = $isAperta ? "<span style='color: green; font-weight: bold;'>APERTA ✓</span>" : "<span style='color: red; font-weight: bold;'>CHIUSA ✗</span>";
            echo "<p style='font-size: 18px;'><strong>Stato calcolato:</strong> {$stato}</p>";
            
            echo "<hr>";
            $count++;
        }
    }
    
    $db->closeConnection();
} else {
    echo "<p>Errore connessione database</p>";
}
?>
