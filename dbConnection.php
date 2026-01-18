<?php
namespace DB;

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
        $query = "SELECT DISTINCT citta FROM farmacie ORDER BY citta ASC";
        $queryResult = mysqli_query($this->connection, $query);
        if ($queryResult) {
            return $queryResult->fetch_all(MYSQLI_ASSOC);
        }
        return [];
    }

    // 8. Funzione per trovare le farmacie che offrono un dato servizio in una data città
    public function getFarmaciePerServizioECitta($idServizio, $citta) {
        $query = "SELECT f.id, f.nome, f.indirizzo 
                  FROM farmacie f 
                  INNER JOIN farmacia_servizi fs ON f.id = fs.farmacia_id 
                  WHERE fs.servizio_id = ? AND f.citta = ? 
                  ORDER BY f.nome ASC";
        $stmt = mysqli_prepare($this->connection, $query);
        mysqli_stmt_bind_param($stmt, "is", $idServizio, $citta);
        mysqli_stmt_execute($stmt);
        return mysqli_stmt_get_result($stmt)->fetch_all(MYSQLI_ASSOC);
    }

    // 9. Funzione per trovare farmacie "vicine" (in altre città) che offrono un servizio
    public function getFarmacieVicinePerServizio($idServizio, $cittaEsclusa, $limit = 5) {
        // Step 1: Ottiene le coordinate medie (lat, lon) per la città selezionata, usandole come centro per la ricerca
        $coordQuery = "SELECT AVG(latitudine) as lat, AVG(longitudine) as lon FROM farmacie WHERE citta = ?";
        $coordStmt = mysqli_prepare($this->connection, $coordQuery);
        mysqli_stmt_bind_param($coordStmt, "s", $cittaEsclusa);
        mysqli_stmt_execute($coordStmt);
        $coordResult = mysqli_stmt_get_result($coordStmt);
        $coords = mysqli_fetch_assoc($coordResult);

        // Se non è possibile trovare le coordinate per la città (es. nessuna farmacia presente), non si può calcolare la distanza.
        if (!$coords || is_null($coords['lat']) || is_null($coords['lon'])) {
            return [];
        }

        $lat = $coords['lat'];
        $lon = $coords['lon'];
        $distanzaMassima = 20; // Distanza massima in KM. Puoi modificare questo valore.

        // Step 2: Trova le farmacie vicine usando la formula di Haversine per calcolare la distanza in km.
        // 6371 è il raggio della Terra in km.
        $query = "SELECT f.id, f.nome, f.indirizzo, f.citta,
                    ( 6371 * acos( cos( radians(?) ) * cos( radians( f.latitudine ) ) * cos( radians( f.longitudine ) - radians(?) ) + sin( radians(?) ) * sin( radians( f.latitudine ) ) ) ) AS distanza
                  FROM farmacie f 
                  INNER JOIN farmacia_servizi fs ON f.id = fs.farmacia_id 
                  WHERE fs.servizio_id = ? AND f.citta != ? 
                  HAVING distanza < ?
                  ORDER BY distanza ASC 
                  LIMIT ?";
                  
        $stmt = mysqli_prepare($this->connection, $query);
        // I parametri sono: lat, lon, lat, idServizio, cittaEsclusa, distanzaMassima, limit
        mysqli_stmt_bind_param($stmt, "dddisii", $lat, $lon, $lat, $idServizio, $cittaEsclusa, $distanzaMassima, $limit);
        mysqli_stmt_execute($stmt);
        return mysqli_stmt_get_result($stmt)->fetch_all(MYSQLI_ASSOC);
    }

    public function getFarmacieDintorni($cittaRiferimento, $limit = 4) {
        // Step 1: Ottiene le coordinate medie (lat, lon) per la città selezionata, usandole come centro per la ricerca
        $coordQuery = "SELECT AVG(latitudine) as lat, AVG(longitudine) as lon FROM farmacie WHERE citta = ?";
        $coordStmt = mysqli_prepare($this->connection, $coordQuery);
        mysqli_stmt_bind_param($coordStmt, "s", $cittaRiferimento);
        mysqli_stmt_execute($coordStmt);
        $coordResult = mysqli_stmt_get_result($coordStmt);
        $coords = mysqli_fetch_assoc($coordResult);

        // Se non è possibile trovare le coordinate per la città (es. nessuna farmacia presente), non si può calcolare la distanza.
        if (!$coords || is_null($coords['lat']) || is_null($coords['lon'])) {
            return [];
        }

        $lat = $coords['lat'];
        $lon = $coords['lon'];
        $distanzaMassima = 20; // Distanza massima in KM. Puoi modificare questo valore.

        // Step 2: Trova le farmacie vicine usando la formula di Haversine per calcolare la distanza in km.
        // 6371 è il raggio della Terra in km.
        $query = "SELECT f.id, f.nome, f.indirizzo, f.citta, f.telefono, f.immagine,
                    ( 6371 * acos( cos( radians(?) ) * cos( radians( f.latitudine ) ) * cos( radians( f.longitudine ) - radians(?) ) + sin( radians(?) ) * sin( radians( f.latitudine ) ) ) ) AS distanza
                  FROM farmacie f 
                  HAVING distanza < ?
                  ORDER BY distanza ASC 
                  LIMIT ?";
                  
        $stmt = mysqli_prepare($this->connection, $query);
        // I parametri sono: lat, lon, lat, idServizio, cittaEsclusa, distanzaMassima, limit
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
                  AND TIME(?) BETWEEN TIME(ora_apertura) AND TIME(ora_chiusura)";
    
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
        $query = "SELECT p.data_appuntamento, p.ora_appuntamento, 
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
    public function verificaDisponibilitaSlot($idFarmacia, $data, $ora) {
        // Verifica se esiste già una prenotazione per quella farmacia, data e ora
        $query = "SELECT COUNT(*) as conteggio 
                  FROM prenotazioni 
                  WHERE farmacia_id = ? 
                  AND data_prenotazione = ? 
                  AND ora_prenotazione = ?";
        
        $stmt = mysqli_prepare($this->connection, $query);
        mysqli_stmt_bind_param($stmt, "iss", $idFarmacia, $data, $ora);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($result)) {
            // Se il conteggio è 0, lo slot è disponibile
            return $row['conteggio'] == 0;
        }
        return false;
    }

    // 8. Crea una nuova prenotazione
    public function creaPrenotazione($idUtente, $idFarmacia, $idServizio, $data, $ora) {
        $query = "INSERT INTO prenotazioni (utente_id, farmacia_id, servizio_id, data_prenotazione, ora_prenotazione, stato) 
                  VALUES (?, ?, ?, ?, ?, 'confermata')";
        
        $stmt = mysqli_prepare($this->connection, $query);
        mysqli_stmt_bind_param($stmt, "iiiss", $idUtente, $idFarmacia, $idServizio, $data, $ora);
        
        try {
            mysqli_stmt_execute($stmt);
            // Restituisce l'ID della prenotazione appena creata
            return mysqli_insert_id($this->connection);
        } catch (\mysqli_sql_exception $e) {
            return false;
        }
    }
}

?>