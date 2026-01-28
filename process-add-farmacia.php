<?php
session_start();
require_once "dbConnection.php";
use DB\DBAccess;

// 1. CONTROLLO SICUREZZA: Solo admin loggati possono accedere
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['ruolo'] !== 'admin') {
    header("Location: area-login.html");
    exit;
}

// 2. CONTROLLO METODO REQUEST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: area-admin.php");
    exit;
}

// 3. RECUPERO E SANITIZZAZIONE DATI
$nome = trim($_POST['nome'] ?? '');
$indirizzo = trim($_POST['indirizzo'] ?? '');
$citta = trim($_POST['citta'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$immagine = trim($_POST['immagine'] ?? '');
$zona = $_POST['zona'] ?? '';
$tipoOrario = $_POST['tipo_orario'] ?? '';

// 4. VALIDAZIONI LATO SERVER

// Controllo campi obbligatori
if (empty($nome) || empty($indirizzo) || empty($citta) || empty($telefono) || empty($zona) || empty($tipoOrario)) {
    header("Location: area-admin.php?error=empty_fields#gestione-farmacie");
    exit;
}

// Validazione formato telefono (numeri, spazi, trattini, parentesi, +)
if (!preg_match("/^[0-9 \-\+\(\)]+$/", $telefono)) {
    header("Location: area-admin.php?error=invalid_phone#gestione-farmacie");
    exit;
}

// Validazione lunghezza nome (min 3, max 100 caratteri)
if (strlen($nome) < 3 || strlen($nome) > 100) {
    header("Location: area-admin.php?error=invalid_name_length#gestione-farmacie");
    exit;
}

// Validazione zona
$zoneValide = ['alta_padovana', 'padova_centro', 'colli_euganei'];
if (!in_array($zona, $zoneValide)) {
    header("Location: area-admin.php?error=invalid_zone#gestione-farmacie");
    exit;
}

// Validazione tipo orario
$orariValidi = ['continuato', 'spezzato'];
if (!in_array($tipoOrario, $orariValidi)) {
    header("Location: area-admin.php?error=invalid_schedule#gestione-farmacie");
    exit;
}

// 5. CONNESSIONE AL DATABASE
$db = new DBAccess();
if (!$db->openDBConnection()) {
    header("Location: area-admin.php?error=db_error#gestione-farmacie");
    exit;
}

// 6. CONTROLLO DUPLICATI
if ($db->verificaDuplicatoFarmacia($nome, $citta)) {
    $db->closeConnection();
    header("Location: area-admin.php?error=duplicate_farmacia#gestione-farmacie");
    exit;
}

// 7. GENERAZIONE COORDINATE
$coordinate = $db->generaCoordinatePerZona($zona);
$latitudine = $coordinate['latitudine'];
$longitudine = $coordinate['longitudine'];

// 8. INSERIMENTO FARMACIA
$idFarmacia = $db->inserisciFarmacia($nome, $indirizzo, $citta, $telefono, $latitudine, $longitudine, $immagine);

if ($idFarmacia === false || $idFarmacia === 0) {
    $db->closeConnection();
    header("Location: area-admin.php?error=insert_failed#gestione-farmacie");
    exit;
}

// 9. SALVATAGGIO ORARI
$orariSalvati = $db->salvaOrariFarmacia($idFarmacia, $tipoOrario);

if (!$orariSalvati) {
    // Se il salvataggio degli orari fallisce, potremmo eliminare la farmacia appena inserita
    // Ma per semplicità manteniamo la farmacia e segnaliamo l'errore
    $db->closeConnection();
    header("Location: area-admin.php?error=schedule_save_failed#gestione-farmacie");
    exit;
}

// 10. SUCCESSO
$db->closeConnection();
header("Location: area-admin.php?success=farmacia_added#gestione-farmacie");
exit;
?>
