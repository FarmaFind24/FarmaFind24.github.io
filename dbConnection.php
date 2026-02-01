<?php
namespace DB;

date_default_timezone_set('Europe/Rome');

class DBAccess {

	private const HOST_DB = "localhost";
	private const DATABASE_NAME = "mrocco";
	private const USERNAME = "mrocco";
	private const PASSWORD = "Iegaemie1eiqueiz";

	private $connection;

	public function openDBConnection() {

		mysqli_report(MYSQLI_REPORT_ERROR);

		try {
			$this->connection = mysqli_connect(DBAccess::HOST_DB, DBAccess::USERNAME, DBAccess::PASSWORD, DBAccess::DATABASE_NAME);
			mysqli_set_charset($this->connection, "utf8mb4");
			return true;
		} catch (\mysqli_sql_exception $e) {
			return false;
		}
	}


	public function closeConnection() {
		mysqli_close($this->connection);
	}


    // 2. Funzione per CERCARE FARMACI (quella che mostra compresse/sciroppo separati)
    public function cercaFarmaci($testoRicerca) {
        $query = "SELECT * FROM farmaci 
                  WHERE nome_commerciale LIKE ? 
                  OR principio_attivo LIKE ? 
                  ORDER BY nome_commerciale, forma_farmaceutica";
        
        $stmt = mysqli_prepare($this->connection, $query);
        
        $searchTerm = "%" . $testoRicerca . "%"; // Aggiungiamo i % per la ricerca parziale
        mysqli_stmt_bind_param($stmt, "ss", $searchTerm, $searchTerm);
        
        mysqli_stmt_execute($stmt);
        $queryResult = mysqli_stmt_get_result($stmt);

        $result = array();
        while ($row = mysqli_fetch_assoc($queryResult)) {
            array_push($result, $row);
        }
        return $result;
    }

    // 3. Funzione per ottenere la lista delle FARMACIE
    public function getListaFarmacie() {
        $query = "SELECT * FROM farmacie ORDER BY nome ASC";
        $queryResult = mysqli_query($this->connection, $query);
        
        if (!$queryResult || mysqli_num_rows($queryResult) == 0) {
            return null;
        } else {
            $result = array();
            while ($row = mysqli_fetch_assoc($queryResult)) {
                array_push($result, $row);
            }
            return $result;
        }
    }
    
    // 4. Funzione per PRENOTARE un servizio (Esempio di INSERT)
    public function prenotaServizio($idUtente, $idFarmaciaServizio, $dataOra, $note) {
        $query = "INSERT INTO prenotazioni (utente_id, farmacia_servizio_id, data_ora_appuntamento, note_cliente) VALUES (?, ?, ?, ?)";
        
        $stmt = mysqli_prepare($this->connection, $query);
        // "iiss" sta per: intero, intero, stringa, stringa
        mysqli_stmt_bind_param($stmt, "iiss", $idUtente, $idFarmaciaServizio, $dataOra, $note);
        
        return mysqli_stmt_execute($stmt); // Ritorna true se andato a buon fine
    }

    // Aggiungi dentro class DBAccess in dbConnection.php
	
	public function cercaFarmacie($testoRicerca) {
		// Cerca per nome della farmacia O per città
		$query = "SELECT * FROM farmacie 
				  WHERE nome LIKE ? OR citta LIKE ? 
				  ORDER BY nome ASC";
		
		$stmt = mysqli_prepare($this->connection, $query);
		$searchTerm = "%" . $testoRicerca . "%";
		mysqli_stmt_bind_param($stmt, "ss", $searchTerm, $searchTerm);
		
		mysqli_stmt_execute($stmt);
		$queryResult = mysqli_stmt_get_result($stmt);
	
		$result = array();
		while ($row = mysqli_fetch_assoc($queryResult)) {
			array_push($result, $row);
		}
		return $result;
	}

    // 5. Funzione per cercare FARMACIE per nome o città (precedentemente duplicata)
    public function cercaFarmaciePerNomeOCitta($testoRicerca) {
        $query = "SELECT * FROM farmacie WHERE nome LIKE ? OR citta LIKE ? ORDER BY nome ASC";
        $stmt = mysqli_prepare($this->connection, $query);
        $searchTerm = "%" . $testoRicerca . "%";
        mysqli_stmt_bind_param($stmt, "ss", $searchTerm, $searchTerm);
        mysqli_stmt_execute($stmt);
        $queryResult = mysqli_stmt_get_result($stmt);
        return $queryResult->fetch_all(MYSQLI_ASSOC);
    }

    // 6. Funzione per ottenere la lista dei SERVIZI disponibili per la prenotazione
    public function getServiziDisponibili() {
        $query = "SELECT id, nome_servizio, descrizione, durata_media_minuti FROM servizi ORDER BY nome_servizio ASC";
        $queryResult = mysqli_query($this->connection, $query);
        if ($queryResult) {
            return $queryResult->fetch_all(MYSQLI_ASSOC);
        }
        return [];
    }

    // Funzione per ottenere la lista dei SERVIZI disponibili per la farmacia
    public function getServiziFarmacia($idFarm) {
        $query = "SELECT servizi.id, servizi.nome_servizio, servizi.descrizione, servizi.durata_media_minuti 
                FROM servizi 
                INNER JOIN farmacia_servizi ON servizi.id = farmacia_servizi.servizio_id 
                WHERE farmacia_servizi.farmacia_id = ? 
                ORDER BY nome_servizio ASC";
        $stmt = mysqli_prepare($this->connection, $query);
        mysqli_stmt_bind_param($stmt, "i", $idFarm);
        mysqli_stmt_execute($stmt);
        return mysqli_stmt_get_result($stmt)->fetch_all(MYSQLI_ASSOC);
    }

    // 7. Funzione per ottenere la lista di tutte le CITTÀ uniche
    public function getListaCitta() {
        $query = "SELECT DISTINCT citta FROM comuni ORDER BY citta ASC";
        $queryResult = mysqli_query($this->connection, $query);
        if ($queryResult) {
            return $queryResult->fetch_all(MYSQLI_ASSOC);
        }
        return [];
    }

    // 8. Funzione per trovare le farmacie che offrono un dato servizio in una data città
    public function getFarmaciePerServizioECitta($idServizio, $citta) {
    $query = "
        SELECT f.id, f.nome, f.indirizzo
        FROM farmacie f
        INNER JOIN farmacia_servizi fs ON f.id = fs.farmacia_id
        INNER JOIN comuni c ON f.citta = c.citta
        WHERE fs.servizio_id = ? AND f.citta = ?
        ORDER BY f.nome ASC
    ";

    $stmt = mysqli_prepare($this->connection, $query);
    mysqli_stmt_bind_param($stmt, "is", $idServizio, $citta);
    mysqli_stmt_execute($stmt);

    return mysqli_stmt_get_result($stmt)->fetch_all(MYSQLI_ASSOC);
}

    // 9. Funzione per trovare farmacie "vicine" (in altre città) che offrono un servizio
    public function getFarmacieVicinePerServizio($idServizio, $cittaEsclusa, $limit = 5) {
    // Step 1: Ottiene le coordinate della citt� di riferimento
    $coordQuery = "SELECT latitudine, longitudine FROM comuni WHERE citta = ? LIMIT 1";
    $coordStmt = mysqli_prepare($this->connection, $coordQuery);
    mysqli_stmt_bind_param($coordStmt, "s", $cittaEsclusa);
    mysqli_stmt_execute($coordStmt);
    $coordResult = mysqli_stmt_get_result($coordStmt);
    $coords = mysqli_fetch_assoc($coordResult);

    if (!$coords) {
        return [];
    }

    $lat = $coords['latitudine'];
    $lon = $coords['longitudine'];
    $distanzaMassima = 20; // Distanza massima in KM

    // Step 2: Trova le farmacie vicine usando le coordinate delle citt� delle farmacie
    $query = "
        SELECT f.id, f.nome, f.indirizzo, f.citta,
               ( 6371 * acos(
                    cos(radians(?)) 
                    * cos(radians(c.latitudine)) 
                    * cos(radians(c.longitudine) - radians(?)) 
                    + sin(radians(?)) 
                    * sin(radians(c.latitudine))
               )) AS distanza
        FROM farmacie f
        INNER JOIN farmacia_servizi fs ON f.id = fs.farmacia_id
        INNER JOIN comuni c ON f.citta = c.citta
        WHERE fs.servizio_id = ? AND f.citta != ?
        HAVING distanza < ?
        ORDER BY distanza ASC
        LIMIT ?
    ";

    $stmt = mysqli_prepare($this->connection, $query);
    mysqli_stmt_bind_param($stmt, "ddddsii", $lat, $lon, $lat, $idServizio, $cittaEsclusa, $distanzaMassima, $limit);
    mysqli_stmt_execute($stmt);

    return mysqli_stmt_get_result($stmt)->fetch_all(MYSQLI_ASSOC);
}


    public function getFarmacieDintorni($cittaRiferimento, $limit = 4) {

    // Prendo le coordinate della citt� di riferimento
    $coordQuery = "SELECT latitudine, longitudine 
                   FROM comuni 
                   WHERE citta = ? 
                   LIMIT 1";

    $coordStmt = mysqli_prepare($this->connection, $coordQuery);
    mysqli_stmt_bind_param($coordStmt, "s", $cittaRiferimento);
    mysqli_stmt_execute($coordStmt);
    $coordResult = mysqli_stmt_get_result($coordStmt);
    $coords = mysqli_fetch_assoc($coordResult);

    if (!$coords) {
        return [];
    }

    $lat = $coords['latitudine'];
    $lon = $coords['longitudine'];
    $distanzaMassima = 20; // km

    // Ora join con comuni per prendere lat/lon della citt� di ciascuna farmacia
    $query = "
        SELECT f.id, f.nome, f.indirizzo, f.citta, f.telefono, f.immagine,
        (
            6371 * acos(
                cos(radians(?)) 
                * cos(radians(c.latitudine)) 
                * cos(radians(c.longitudine) - radians(?)) 
                + sin(radians(?)) 
                * sin(radians(c.latitudine))
            )
        ) AS distanza
        FROM farmacie f
        JOIN comuni c ON f.citta = c.citta
        HAVING distanza < ?
        ORDER BY distanza ASC
        LIMIT ?
    ";

    $stmt = mysqli_prepare($this->connection, $query);
    mysqli_stmt_bind_param($stmt, "dddii", $lat, $lon, $lat, $distanzaMassima, $limit);
    mysqli_stmt_execute($stmt);

    return mysqli_stmt_get_result($stmt)->fetch_all(MYSQLI_ASSOC);
}


    // Inserire in dbConnection.php dentro la classe DBAccess
    
    public function isFarmaciaAperta($idFarmacia) {
        // 1. Dati attuali
        $giornoOggi = date('w'); // 0 (Dom) - 6 (Sab)
        $oraAdesso = date('H:i:s');
    
        // 2. Query - Verifica se esiste almeno una fascia oraria attiva
        // La farmacia è aperta se l'ora corrente cade in almeno una fascia oraria del giorno corrente
        $query = "SELECT COUNT(*) as total 
                  FROM orari_farmacie 
                  WHERE farmacia_id = ? 
                  AND giorno_settimana = ? 
                  AND ? BETWEEN ora_apertura AND ora_chiusura";
    
        $stmt = mysqli_prepare($this->connection, $query);
        
        if (!$stmt) {
            // In caso di errore nella preparazione, considera chiusa
            return false;
        }
        
        // Bind dei parametri: i (intero), i (intero), s (stringa)
        mysqli_stmt_bind_param($stmt, "iis", $idFarmacia, $giornoOggi, $oraAdesso);
        
        $executed = mysqli_stmt_execute($stmt);
        
        if (!$executed) {
            // In caso di errore nell'esecuzione, considera chiusa
            return false;
        }
        
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
    
        // 3. Ritorna TRUE se trova almeno 1 riga, altrimenti FALSE
        // Se non ci sono orari per questo giorno, la farmacia è considerata chiusa
        return $row && $row['total'] > 0;
    }

    // In dbConnection.php

    // Inserire in DBAccess dentro dbConnection.php
    
    public function getOrariFarmacia($idFarmacia, $giornoSettimana) {
        // $giornoSettimana va da 0 (Domenica) a 6 (Sabato)
        $query = "SELECT ora_apertura, ora_chiusura 
                  FROM orari_farmacie 
                  WHERE farmacia_id = ? AND giorno_settimana = ? 
                  ORDER BY ora_apertura ASC";
        
        $stmt = mysqli_prepare($this->connection, $query);
        mysqli_stmt_bind_param($stmt, "ii", $idFarmacia, $giornoSettimana);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $orari = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $orari[] = $row;
        }
        return $orari;
    }

    public function getFarmaciaById($idFarmacia) {
    	$query = "SELECT * FROM farmacie WHERE id = ?";
    	$stmt = mysqli_prepare($this->connection, $query);
    	mysqli_stmt_bind_param($stmt, "i", $idFarmacia);
    	mysqli_stmt_execute($stmt);
    	$result = mysqli_stmt_get_result($stmt);
    
    	if ($row = mysqli_fetch_assoc($result)) {
        	return $row;
    	}
    	return null;
    }

    // INFO-MED 

    // Metodo per ottenere dettagli di un farmaco specifico
        public function getFarmacoById($idFarmaco) {
        $query = "SELECT * FROM farmaci WHERE id = ?";
        $stmt = mysqli_prepare($this->connection, $query);
        mysqli_stmt_bind_param($stmt, "i", $idFarmaco);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
    
        if ($row = mysqli_fetch_assoc($result)) {
            return $row;
        }
        return null;
    }

// Metodo per ottenere le farmacie che hanno un determinato farmaco
public function getFarmacieConFarmaco($idFarmaco) {
    $query = "SELECT f.*, d.prezzo, d.quantita, d.data_aggiornamento
              FROM farmacie f 
              INNER JOIN disponibilita d ON f.id = d.farmacia_id 
              WHERE d.farmaco_id = ? 
              ORDER BY f.nome ASC";
    
    $stmt = mysqli_prepare($this->connection, $query);
    mysqli_stmt_bind_param($stmt, "i", $idFarmaco);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $farmacie = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $farmacie[] = $row;
    }
    return $farmacie;
}

    // --- AUTENTICAZIONE ---

    // 1. Registrazione nuovo utente
    public function registraUtente($nome, $cognome, $username, $email, $password) {
        // Crittografia della password
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $ruolo = 'user'; // Di default tutti sono user

        $query = "INSERT INTO utenti (nome, cognome, username, email, password, ruolo) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($this->connection, $query);
        mysqli_stmt_bind_param($stmt, "ssssss", $nome, $cognome, $username, $email, $passwordHash, $ruolo);
        
        try {
            return mysqli_stmt_execute($stmt);
        } catch (\Exception $e) {
            // Probabile errore di duplicato username/email
            return false;
        }
    }

    // 2. Login Utente
    public function eseguiLogin($username, $password) {
        $query = "SELECT * FROM utenti WHERE username = ?";
        $stmt = mysqli_prepare($this->connection, $query);
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            // Verifica la password hashata
            if (password_verify($password, $row['password'])) {
                return $row; // Ritorna tutti i dati dell'utente
            }
        }
        return null; // Login fallito
    }

    // In dbConnection.php -> class DBAccess

public function getPrenotazioniUtente($idUtente) {
    // MODIFICA: Selezioniamo data_appuntamento e ora_appuntamento separatamente
    $query = "SELECT p.id, p.data_appuntamento, p.ora_appuntamento, 
                     f.nome AS nome_farmacia, f.indirizzo, s.nome_servizio 
              FROM prenotazioni p
              JOIN farmacia_servizi fs ON p.farmacia_servizio_id = fs.id
              JOIN farmacie f ON fs.farmacia_id = f.id
              JOIN servizi s ON fs.servizio_id = s.id
              WHERE p.utente_id = ?
              ORDER BY p.data_appuntamento ASC, p.ora_appuntamento ASC";
              
    $stmt = mysqli_prepare($this->connection, $query);
    mysqli_stmt_bind_param($stmt, "i", $idUtente);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $prenotazioni = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $prenotazioni[] = $row;
    }
    return $prenotazioni;
}

public function getAllPrenotazioni() {
    // MODIFICA: Selezioniamo data_appuntamento e ora_appuntamento separatamente
    $query = "SELECT p.id, p.data_appuntamento, p.ora_appuntamento, 
                     f.nome AS nome_farmacia, f.indirizzo, s.nome_servizio, u.username
              FROM prenotazioni p
              JOIN farmacia_servizi fs ON p.farmacia_servizio_id = fs.id
              JOIN farmacie f ON fs.farmacia_id = f.id
              JOIN servizi s ON fs.servizio_id = s.id
              JOIN utenti u ON p.utente_id = u.id
              ORDER BY p.data_appuntamento ASC, p.ora_appuntamento ASC";
              
    $stmt = mysqli_prepare($this->connection, $query);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $prenotazioni = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $prenotazioni[] = $row;
    }
    return $prenotazioni;
}

    // Metodo per eliminare una prenotazione
    public function eliminaPrenotazione($idPrenotazione, $idUtente) {
        // Verifica che la prenotazione appartenga all'utente prima di eliminarla
        $query = "DELETE FROM prenotazioni WHERE id = ? AND utente_id = ?";
        $stmt = mysqli_prepare($this->connection, $query);
        mysqli_stmt_bind_param($stmt, "ii", $idPrenotazione, $idUtente);
    
        try {
            return mysqli_stmt_execute($stmt);
        } catch (\Exception $e) {
            return false;
        }
    }
    public function getPrenotazioniUtentePreview($idUtente) {
        // MODIFICA: Selezioniamo data_appuntamento e ora_appuntamento separatamente
        $query = "SELECT p.id, p.data_appuntamento, p.ora_appuntamento, 
                         f.nome AS nome_farmacia, f.indirizzo, s.nome_servizio 
                  FROM prenotazioni p
                  JOIN farmacia_servizi fs ON p.farmacia_servizio_id = fs.id
                  JOIN farmacie f ON fs.farmacia_id = f.id
                  JOIN servizi s ON fs.servizio_id = s.id
                  WHERE p.utente_id = ?
                  ORDER BY p.data_appuntamento ASC, p.ora_appuntamento ASC
                  LIMIT 5";
                  
        $stmt = mysqli_prepare($this->connection, $query);
        mysqli_stmt_bind_param($stmt, "i", $idUtente);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $prenotazioni = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $prenotazioni[] = $row;
        }
        return $prenotazioni;
    }

    public function getAllPrenotazioniPreview() {
    // MODIFICA: Selezioniamo data_appuntamento e ora_appuntamento separatamente
    $query = "SELECT p.id, p.data_appuntamento, p.ora_appuntamento, 
                     f.nome AS nome_farmacia, f.indirizzo, s.nome_servizio, u.username
              FROM prenotazioni p
              JOIN farmacia_servizi fs ON p.farmacia_servizio_id = fs.id
              JOIN farmacie f ON fs.farmacia_id = f.id
              JOIN servizi s ON fs.servizio_id = s.id
              JOIN utenti u ON p.utente_id = u.id
              ORDER BY p.data_appuntamento ASC, p.ora_appuntamento ASC
              LIMIT 5";
              
    $stmt = mysqli_prepare($this->connection, $query);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $prenotazioni = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $prenotazioni[] = $row;
    }
    return $prenotazioni;
}

    // 3. Aggiornamento profilo utente
    public function aggiornaProfiloUtente($idUtente, $nome, $cognome, $email) {
        $query = "UPDATE utenti SET nome = ?, cognome = ?, email = ? WHERE id = ?";
        $stmt = mysqli_prepare($this->connection, $query);
        mysqli_stmt_bind_param($stmt, "sssi", $nome, $cognome, $email, $idUtente);
        
        try {
            return mysqli_stmt_execute($stmt);
        } catch (\Exception $e) {
            // Errore (es. email duplicata)
            return false;
        }
    }

    // 4. Eliminazione account utente
    public function eliminaUtente($idUtente) {
        // Prima eliminiamo le prenotazioni dell'utente (per integrità referenziale)
        $query1 = "DELETE FROM prenotazioni WHERE utente_id = ?";
        $stmt1 = mysqli_prepare($this->connection, $query1);
        mysqli_stmt_bind_param($stmt1, "i", $idUtente);
        mysqli_stmt_execute($stmt1);
        
        // Poi eliminiamo l'utente
        $query2 = "DELETE FROM utenti WHERE id = ?";
        $stmt2 = mysqli_prepare($this->connection, $query2);
        mysqli_stmt_bind_param($stmt2, "i", $idUtente);
        
        try {
            return mysqli_stmt_execute($stmt2);
        } catch (\Exception $e) {
            return false;
        }
    }

    // 5. Cambio password
    public function cambiaPassword($idUtente, $nuovaPassword) {
        $passwordHash = password_hash($nuovaPassword, PASSWORD_DEFAULT);
        $query = "UPDATE utenti SET password = ? WHERE id = ?";
        $stmt = mysqli_prepare($this->connection, $query);
        mysqli_stmt_bind_param($stmt, "si", $passwordHash, $idUtente);
        
        try {
            return mysqli_stmt_execute($stmt);
        } catch (\Exception $e) {
            return false;
        }
    }

    // 6. Verifica password corrente (utile per cambio password)
    public function verificaPasswordUtente($idUtente, $password) {
        $query = "SELECT password FROM utenti WHERE id = ?";
        $stmt = mysqli_prepare($this->connection, $query);
        mysqli_stmt_bind_param($stmt, "i", $idUtente);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            return password_verify($password, $row['password']);
        }
        return false;
    }

    // 7. Verifica disponibilità di uno slot orario
    public function verificaDisponibilitaSlot($idFarmaciaServizio, $data, $ora) {
        // Verifica se esiste già una prenotazione per quella combinazione farmacia-servizio, data e ora
        $query = "SELECT COUNT(*) as conteggio 
                  FROM prenotazioni 
                  WHERE farmacia_servizio_id = ? 
                  AND data_appuntamento = ? 
                  AND ora_appuntamento = ?";
        
        $stmt = mysqli_prepare($this->connection, $query);
        mysqli_stmt_bind_param($stmt, "iss", $idFarmaciaServizio, $data, $ora);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            // Se il conteggio è 0, lo slot è disponibile
            return $row['conteggio'] == 0;
        }
        return false;
    }

    // 8. Crea una nuova prenotazione
    public function creaPrenotazione($idUtente, $idFarmaciaServizio, $data, $ora, $noteAggiuntive) {
        error_log("creaPrenotazione chiamata con: utente=$idUtente, fs_id=$idFarmaciaServizio, data=$data, ora=$ora, noteAggiuntive=$noteAggiuntive");
        
        $query = "INSERT INTO prenotazioni (utente_id, farmacia_servizio_id, data_appuntamento, ora_appuntamento, note_aggiuntive) 
                  VALUES (?, ?, ?, ?, ?)";
        
        $stmt = mysqli_prepare($this->connection, $query);
        
        if (!$stmt) {
            error_log("creaPrenotazione: ERRORE PREPARE - " . mysqli_error($this->connection));
            return false;
        }
        
        mysqli_stmt_bind_param($stmt, "iisss", $idUtente, $idFarmaciaServizio, $data, $ora, $noteAggiuntive);
        
        try {
            $executeResult = mysqli_stmt_execute($stmt);
            
            if (!$executeResult) {
                error_log("creaPrenotazione: ERRORE EXECUTE - " . mysqli_stmt_error($stmt));
                return false;
            }
            
            $affectedRows = mysqli_affected_rows($this->connection);
            $insertId = mysqli_insert_id($this->connection);
            
            error_log("creaPrenotazione: affected_rows=$affectedRows, insert_id=$insertId");
            
            // Se almeno una riga è stata inserita, considera l'operazione riuscita
            if ($affectedRows > 0) {
                // Se l'ID è 0 o negativo, proviamo a recuperare l'ultimo ID inserito con una query
                if ($insertId <= 0) {
                    $lastIdQuery = "SELECT LAST_INSERT_ID() as last_id";
                    $result = mysqli_query($this->connection, $lastIdQuery);
                    if ($result && $row = mysqli_fetch_assoc($result)) {
                        $insertId = $row['last_id'];
                        error_log("creaPrenotazione: recuperato last_id dalla query = $insertId");
                    }
                    
                    // Se ancora 0, proviamo a trovare la prenotazione appena creata
                    if ($insertId <= 0) {
                        $findQuery = "SELECT id FROM prenotazioni 
                                     WHERE utente_id = ? AND farmacia_servizio_id = ? 
                                     AND data_appuntamento = ? AND ora_appuntamento = ? 
                                     ORDER BY id DESC LIMIT 1";
                        $findStmt = mysqli_prepare($this->connection, $findQuery);
                        mysqli_stmt_bind_param($findStmt, "iisss", $idUtente, $idFarmaciaServizio, $data, $ora);
                        mysqli_stmt_execute($findStmt);
                        $findResult = mysqli_stmt_get_result($findStmt);
                        if ($findRow = mysqli_fetch_assoc($findResult)) {
                            $insertId = $findRow['id'];
                            error_log("creaPrenotazione: recuperato ID dalla ricerca = $insertId");
                        }
                    }
                }
                
                error_log("creaPrenotazione: SUCCESS - ID finale = $insertId");
                // Restituisce l'ID (anche se è 0, l'inserimento è riuscito)
                return $insertId ?: 1; // Restituisce almeno 1 se l'ID è ancora 0
            }
            
            error_log("creaPrenotazione: ERRORE - Nessuna riga inserita");
            return false;
        } catch (\mysqli_sql_exception $e) {
            return false;
        }
    }

    // 9. Ottieni farmacia_servizio_id dato idFarmacia e idServizio
    public function getFarmaciaServizioId($idFarmacia, $idServizio) {
        $query = "SELECT id FROM farmacia_servizi 
                  WHERE farmacia_id = ? AND servizio_id = ?";
        
        $stmt = mysqli_prepare($this->connection, $query);
        mysqli_stmt_bind_param($stmt, "ii", $idFarmacia, $idServizio);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            return $row['id'];
        }
        return null;
    }

    // Verifica se un utente esiste nel database
    public function verificaUtenteEsiste($idUtente) {
        $query = "SELECT id FROM utenti WHERE id = ?";
        $stmt = mysqli_prepare($this->connection, $query);
        mysqli_stmt_bind_param($stmt, "i", $idUtente);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        $esiste = mysqli_fetch_assoc($result) !== null;
        error_log("verificaUtenteEsiste: utente_id=$idUtente - " . ($esiste ? 'ESISTE' : 'NON ESISTE'));
        return $esiste;
    }

    // 10. Ottieni dettagli completi di una prenotazione per il riepilogo
    public function getDettagliPrenotazione($idPrenotazione, $idUtente = null) {
        $query = "SELECT p.id, p.data_appuntamento, p.ora_appuntamento, p.note_aggiuntive,
                         s.nome_servizio as servizio_nome, s.durata_media_minuti as servizio_durata,
                         f.nome as farmacia_nome, f.indirizzo as farmacia_indirizzo, 
                         f.citta as farmacia_citta, f.telefono as farmacia_telefono,
                         u.email as utente_email
                  FROM prenotazioni p
                  JOIN farmacia_servizi fs ON p.farmacia_servizio_id = fs.id
                  JOIN servizi s ON fs.servizio_id = s.id
                  JOIN farmacie f ON fs.farmacia_id = f.id
                  JOIN utenti u ON p.utente_id = u.id
                  WHERE p.id = ?";
        
        // Se viene fornito l'ID utente, verifica che la prenotazione appartenga a quell'utente
        if ($idUtente !== null) {
            $query .= " AND p.utente_id = ?";
        }
        
        $stmt = mysqli_prepare($this->connection, $query);
        
        if ($idUtente !== null) {
            mysqli_stmt_bind_param($stmt, "ii", $idPrenotazione, $idUtente);
        } else {
            mysqli_stmt_bind_param($stmt, "i", $idPrenotazione);
        }
        
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            return $row;
        }
        return null;
    }

    // === METODI PER GESTIONE FARMACIE (ADMIN) ===
    
    // Restituisce tutte le farmacie per la tabella amministrativa
    public function getAllFarmacie() {
        $query = "SELECT id, nome, indirizzo, citta, telefono, immagine FROM farmacie ORDER BY nome ASC";
        $queryResult = mysqli_query($this->connection, $query);
        
        if (!$queryResult || mysqli_num_rows($queryResult) == 0) {
            return null;
        } else {
            $result = array();
            while ($row = mysqli_fetch_assoc($queryResult)) {
                array_push($result, $row);
            }
            return $result;
        }
    }

// Restituisce la lista dei comuni della provincia di Padova
public function getComuniProvinciaPadova() {
    $query = "SELECT citta FROM comuni ORDER BY citta ASC";
    $queryResult = mysqli_query($this->connection, $query);
    
    if (!$queryResult || mysqli_num_rows($queryResult) == 0) {
        return [];
    }
    
    $result = [];
    while ($row = mysqli_fetch_assoc($queryResult)) {
        $result[] = $row['citta'];
    }
    
    return $result;
}
     // Inserisce una nuova farmacia
public function inserisciFarmacia($nome, $indirizzo, $citta, $telefono, $immagine) {
    // La citt� deve esistere nella tabella comuni
    $query = "INSERT INTO farmacie (nome, indirizzo, citta, telefono, immagine) 
              VALUES (?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($this->connection, $query);
    mysqli_stmt_bind_param($stmt, "sssss", $nome, $indirizzo, $citta, $telefono, $immagine);
    
    try {
        $result = mysqli_stmt_execute($stmt);
        if ($result) {
            return mysqli_insert_id($this->connection);
        }
        return false;
    } catch (\mysqli_sql_exception $e) {
        // Se la citt� non esiste o c'� un errore di chiave esterna, ritorna false
        return false;
    }
}

    // Salva gli orari di una farmacia (Continuato o Spezzato)
    public function salvaOrariFarmacia($idFarmacia, $tipoOrario) {
        // La domenica (giorno 0) è sempre chiusa
        
        if ($tipoOrario === 'continuato') {
            // Lunedì-Venerdì: 08:00-20:00
            for ($giorno = 1; $giorno <= 5; $giorno++) {
                $query = "INSERT INTO orari_farmacie (farmacia_id, giorno_settimana, ora_apertura, ora_chiusura) 
                          VALUES (?, ?, '08:00:00', '20:00:00')";
                $stmt = mysqli_prepare($this->connection, $query);
                mysqli_stmt_bind_param($stmt, "ii", $idFarmacia, $giorno);
                mysqli_stmt_execute($stmt);
            }
            
            // Sabato: 08:00-13:00
            $query = "INSERT INTO orari_farmacie (farmacia_id, giorno_settimana, ora_apertura, ora_chiusura) 
                      VALUES (?, 6, '08:00:00', '13:00:00')";
            $stmt = mysqli_prepare($this->connection, $query);
            mysqli_stmt_bind_param($stmt, "i", $idFarmacia);
            mysqli_stmt_execute($stmt);
            
        } elseif ($tipoOrario === 'spezzato') {
            // Lunedì-Venerdì: 08:00-13:00 e 15:00-19:00 (due fasce)
            for ($giorno = 1; $giorno <= 5; $giorno++) {
                // Fascia mattina
                $query1 = "INSERT INTO orari_farmacie (farmacia_id, giorno_settimana, ora_apertura, ora_chiusura) 
                           VALUES (?, ?, '08:00:00', '13:00:00')";
                $stmt1 = mysqli_prepare($this->connection, $query1);
                mysqli_stmt_bind_param($stmt1, "ii", $idFarmacia, $giorno);
                mysqli_stmt_execute($stmt1);
                
                // Fascia pomeriggio
                $query2 = "INSERT INTO orari_farmacie (farmacia_id, giorno_settimana, ora_apertura, ora_chiusura) 
                           VALUES (?, ?, '15:00:00', '19:00:00')";
                $stmt2 = mysqli_prepare($this->connection, $query2);
                mysqli_stmt_bind_param($stmt2, "ii", $idFarmacia, $giorno);
                mysqli_stmt_execute($stmt2);
            }
            
            // Sabato: 08:00-13:00
            $query = "INSERT INTO orari_farmacie (farmacia_id, giorno_settimana, ora_apertura, ora_chiusura) 
                      VALUES (?, 6, '08:00:00', '13:00:00')";
            $stmt = mysqli_prepare($this->connection, $query);
            mysqli_stmt_bind_param($stmt, "i", $idFarmacia);
            mysqli_stmt_execute($stmt);
        }
        
        return true;
    }


    public function getFarmaciaByNomeCitta($nome, $citta) {
        $query = "SELECT * FROM farmacie WHERE nome = ? AND citta = ?";
        $stmt = mysqli_prepare($this->connection, $query);
        mysqli_stmt_bind_param($stmt, "ss", $nome, $citta);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            return $row;
        }
        return null;
    }

    // Elimina una farmacia e tutti i dati associati (integrità referenziale)
    public function eliminaFarmacia($nome, $citta) {
        try {
            
            // Elimina la farmacia
            $query = "DELETE FROM farmacie WHERE nome = ? AND citta = ?";
            $stmt = mysqli_prepare($this->connection, $query);
            mysqli_stmt_bind_param($stmt, "ss", $nome, $citta);
            $result = mysqli_stmt_execute($stmt);
            
            return $result;
        } catch (\mysqli_sql_exception $e) {
            return false;
        }
    }

    // Verifica se esiste già una farmacia con lo stesso nome nella stessa città
    public function verificaDuplicatoFarmacia($nome, $citta) {
        $query = "SELECT COUNT(*) as conteggio FROM farmacie WHERE nome = ? AND citta = ?";
        $stmt = mysqli_prepare($this->connection, $query);
        mysqli_stmt_bind_param($stmt, "ss", $nome, $citta);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            return $row['conteggio'] > 0;
        }
        return false;
    }
}

?>