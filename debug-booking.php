<?php
// FILE DI DEBUG PER VEDERE I LOG - RIMUOVERE IN PRODUZIONE
// Accedi a: http://localhost/debug-booking.php

$logFile = ini_get('error_log');
if (!$logFile || !file_exists($logFile)) {
    // Prova percorsi comuni
    $possiblePaths = [
        'C:\\xampp\\apache\\logs\\error.log',
        'C:\\wamp\\logs\\apache_error.log',
        'C:\\wamp64\\logs\\apache_error.log',
        '/var/log/apache2/error.log',
        '/var/log/httpd/error_log'
    ];
    
    foreach ($possiblePaths as $path) {
        if (file_exists($path)) {
            $logFile = $path;
            break;
        }
    }
}

echo "<!DOCTYPE html><html><head><title>Debug Booking Log</title>";
echo "<style>body{font-family:monospace;padding:20px;background:#1e1e1e;color:#d4d4d4;}";
echo "pre{background:#252526;padding:15px;border-radius:5px;overflow:auto;}";
echo ".error{color:#f48771;} .success{color:#89d185;}</style></head><body>";

if ($logFile && file_exists($logFile)) {
    echo "<h1>Log File: $logFile</h1>";
    echo "<p>Ultimi 100 righe (filtrate per 'PROCESS-BOOKING'):</p>";
    
    $lines = file($logFile);
    $filteredLines = array_filter($lines, function($line) {
        return stripos($line, 'process-booking') !== false || 
               stripos($line, 'getFarmaciaServizio') !== false ||
               stripos($line, 'verificaDisponibilita') !== false ||
               stripos($line, 'creaPrenotazione') !== false;
    });
    
    $lastLines = array_slice($filteredLines, -100);
    
    echo "<pre>";
    foreach ($lastLines as $line) {
        $line = htmlspecialchars($line);
        if (stripos($line, 'ERRORE') !== false || stripos($line, 'ERROR') !== false) {
            echo "<span class='error'>$line</span>";
        } elseif (stripos($line, 'SUCCESS') !== false) {
            echo "<span class='success'>$line</span>";
        } else {
            echo $line;
        }
    }
    echo "</pre>";
    
    echo "<hr><p><a href='#' onclick='location.reload()'>Ricarica Log</a></p>";
} else {
    echo "<h1>Log file non trovato</h1>";
    echo "<p>error_log configurato: " . ini_get('error_log') . "</p>";
    echo "<p>Percorsi cercati:</p><ul>";
    foreach ($possiblePaths as $path) {
        $exists = file_exists($path) ? '✓' : '✗';
        echo "<li>$exists $path</li>";
    }
    echo "</ul>";
}

echo "</body></html>";
?>
