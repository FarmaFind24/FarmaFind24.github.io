<?php
// FILE: test_senza_modifiche.php

// 1. IMPOSTAZIONI DEL TEST (Modifica qui per viaggiare nel tempo)
$idFarmacia = 42;      // ID Farmacia Guizza (quella con orario spezzato)
$giornoSimulato = 1;  // 1 = Lunedì
$oraSimulata = '15:31:00'; // Proviamo l'ora del pranzo (dovrebbe essere CHIUSO)

// 2. CONNESSIONE MANUALE AL DB (Solo per questo test)
// Usa le stesse credenziali del tuo file originale
$mysqli = new mysqli("localhost", "mrocco", "Iegaemie1eiqueiz", "mrocco"); // Sostituisci con le tue credenziali reali se diverse

if ($mysqli->connect_error) {
    die("Errore connessione test: " . $mysqli->connect_error);
}

echo "<h1>Test Simulazione Orario</h1>";
echo "Sto controllando la Farmacia ID: <strong>$idFarmacia</strong><br>";
echo "Giorno: <strong>$giornoSimulato</strong> (Lunedì)<br>";
echo "Ora simulata: <strong>$oraSimulata</strong><br><hr>";

// 3. LA QUERY "COPIA-INCOLLATA"
// Questa è l'esatta logica che userai nella classe, ma qui possiamo iniettare le variabili
$sql = "SELECT COUNT(*) as total 
        FROM orari_farmacie 
        WHERE farmacia_id = ? 
        AND giorno_settimana = ? 
        AND ? BETWEEN ora_apertura AND ora_chiusura";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param("iis", $idFarmacia, $giornoSimulato, $oraSimulata);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

// 4. RISULTATO
if ($row['total'] > 0) {
    echo "<h2 style='color:green'>RISULTATO: APERTO</h2>";
    echo "La query ha trovato una corrispondenza nel database.";
} else {
    echo "<h2 style='color:red'>RISULTATO: CHIUSO</h2>";
    echo "Nessuna fascia oraria copre le $oraSimulata (Corretto se è pausa pranzo).";
}

$mysqli->close();
?>