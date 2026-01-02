<!DOCTYPE html>
<html lang="it">
  <head>
    <title>FarmaFind24</title>
    <meta charset="utf-8" />
    <meta
      name="description"
      content="Pagina dedicata alla ricerca di farmacie di turno aperte e di farmaci disponibili nelle farmacie ricercate"
    />
    <meta name="keywords" content="salute" />
    <link rel="stylesheet" href="assets/css/desktop.css" media="screen" />
    <link
      rel="stylesheet"
      href="assets/css/mini.css"
      media="screen and (max-width:950px)"
    />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Lexend:wght@100..900&display=swap"
      rel="stylesheet"
    />
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
    />
  </head>

  <body>
    <header class="main-header">
      <h1>FarmaFind24</h1>
      <img src="assets/logo_FarmaFind24.png" alt="logo farmafind24" />
      <nav class="Top-NavBar">
        <ul aria-label="menu di navigazione">
          <li>
            <a lang="en-GB" href="index.html">Home</a>
          </li>
          <li>
            <a href="#" aria-current="page" tabindex="-1" class="current-page"
              ><span class="sr-only">Pagina Corrente</span>Farmacie</a
            >
          </li>
          <li><a href="search-med.html">Medicine</a></li>
          <li><a href="contatti.html">Prenota Servizio</a></li>
          <li>
            <a href="about.html"><span lang="en-GB">About</span></a>
          </li>
        </ul>
      </nav>
    </header>

    <main>
      <section id="farmxcity-hero-section">
        <h1>Trova la Farmacia perfetta vicino a te, in ogni momento</h1>
        <p>
          Cerca farmacie nella tua città, visualizza orari, servizi e molto
          altro.
        </p>
        <div id="farmxcity-hero-section-div">
          <form action="search.php" method="get"  >
            <div class="search-div">
              <label for="search-input">Cerca Farmacia</label>
              <input
                type="id"
                id="search-input"
                placeholder="Cerca farmacia..."
                value="<?php echo htmlspecialchars($testoCercato); ?>" required>
              />
            </div>

            <div class="form-group">
              <label for="tipo-select">Filtra per:</label>
                <select id="tipo-select" name="tipo">
                <option value="farmaci" <?php if($tipoRicerca == 'farmaci') echo 'selected'; ?>>Farmaci</option>
                <option value="farmacie" <?php if($tipoRicerca == 'farmacie') echo 'selected'; ?>>Farmacie</option>
                </select>
            </div>

            <button id="search-drug-button" type="submit">
              Cerca farmacia
            </button>
          </form>
        </div>
      </section>

      <?php if ($errore): ?>
        <div class="error" role="alert">
          <p><?php echo $errore; ?></p>
        </div>
      <?php endif; ?>

      <?php if ($haCercato && !$errore): ?>
        <section aria-live="polite">
          <?php if ($risultati && count($risultati) > 0): ?>
            <?php if ($tipoRicerca === 'farmaci'): ?>
              
              <?php foreach ($risultati as $row): ?>
              <div class="grid-farm-card">
                <div class="farm-card">
                  <img src="https://farmaciaalvigini.it/wp-content/uploads/2021/10/Farmacia-a-Genova-Farmacia-Alvigini-8.jpg" alt=""/>
                <div>
                  <h3><?php echo htmlspecialchars($row['nome']); ?></h3>
                  <p><?php echo htmlspecialchars($row['indirizzo']); ?></p>
                  <span class="farm-orario">08:30 - 20:00</span>
                  <span>Servizi</span>
                    <ul>
                      <li>Misurazione Pressione</li>
                      <li>Screening Prevenzione</li>
                      <li>Test Rapidi Covid</li>
                    </ul>
                </div>
                <button type="button">Vedi Dettagli</button>
              </div>
              
              <?php endforeach; ?>
              
              
              
              
              
              
              
              <table>
                <caption>Risultati ricerca farmaci per "<?php echo htmlspecialchars($testoCercato); ?>"</caption>
                  <thead>
                    <tr>
                    <th scope="col">Nome Commerciale</th>
                    <th scope="col">Formato</th>
                    <th scope="col">Dosaggio</th>
                    <th scope="col">Principio Attivo</th>
                    <th scope="col">Azione</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($risultati as $row): ?>
                      <tr>
                      <td><strong><?php echo htmlspecialchars($row['nome_commerciale']); ?></strong></td>
                      <td><?php echo htmlspecialchars($row['forma_farmaceutica']); ?></td>
                      <td><?php echo htmlspecialchars($row['dosaggio']); ?></td>
                      <td><?php echo htmlspecialchars($row['principio_attivo']); ?></td>
                      <td><a href="dettaglio.php?id=<?php echo $row['id']; ?>">Vedi dettagli</a></td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
              </table>
            <?php elseif ($tipoRicerca === 'farmacie'): ?>
              <table>
                <caption>Risultati ricerca farmacie per "<?php echo htmlspecialchars($testoCercato); ?>"</caption>
                  <thead>
                    <tr>
                    <th scope="col">Nome Farmacia</th>
                    <th scope="col">Città</th>
                    <th scope="col">Indirizzo</th>
                    <th scope="col">Telefono</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($risultati as $row): ?>
                      <tr>
                      <td><strong><?php echo htmlspecialchars($row['nome']); ?></strong></td>
                      <td><?php echo htmlspecialchars($row['citta']); ?></td>
                      <td><?php echo htmlspecialchars($row['indirizzo']); ?></td>
                      <td><?php echo htmlspecialchars($row['telefono']); ?></td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
              </table>
            <?php endif; ?>
            <?php else: ?>
              <p class="no-results">Nessun risultato trovato per "<strong><?php echo htmlspecialchars($testoCercato); ?></strong>".</p>
            <?php endif; ?>
        </section>
      <?php endif; ?>


      <?php if ($tipoRicerca === 'farmaci'): ?>
                    <table>
                        <caption>Risultati ricerca farmaci per "<?php echo htmlspecialchars($testoCercato); ?>"</caption>
                        <thead>
                            <tr>
                                <th scope="col">Nome Commerciale</th>
                                <th scope="col">Formato</th>
                                <th scope="col">Dosaggio</th>
                                <th scope="col">Principio Attivo</th>
                                <th scope="col">Azione</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($risultati as $row): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($row['nome_commerciale']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($row['forma_farmaceutica']); ?></td>
                                    <td><?php echo htmlspecialchars($row['dosaggio']); ?></td>
                                    <td><?php echo htmlspecialchars($row['principio_attivo']); ?></td>
                                    <td><a href="dettaglio.php?id=<?php echo $row['id']; ?>">Vedi dettagli</a></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
      <?php endif; ?>

      <div class="grid-farm-card">
        <div class="farm-card">
          <img
            src="https://farmaciaalvigini.it/wp-content/uploads/2021/10/Farmacia-a-Genova-Farmacia-Alvigini-8.jpg"
            alt=""
          />
          <div>
            <h3>Farmacia Centrale</h3>
            <p>Via Francesco Petrarca, 14r, 16121 Genova GE</p>
            <span class="farm-orario">08:30 - 20:00</span>
            <span>Servizi</span>
            <ul>
              <li>Misurazione Pressione</li>
              <li>Screening Prevenzione</li>
              <li>Test Rapidi Covid</li>
            </ul>
          </div>
          <button type="button">Vedi Dettagli</button>
        </div>
        <div class="farm-card">
          <img
            src="https://farmaciaalvigini.it/wp-content/uploads/2021/10/Farmacia-a-Genova-Farmacia-Alvigini-8.jpg"
            alt=""
          />
          <div>
            <h3>Farmacia Centrale</h3>
            <p>Via Francesco Petrarca, 14r, 16121 Genova GE</p>
            <span class="farm-orario">08:30 - 20:00</span>
            <span>Servizi</span>
            <ul aria-label="Servizi disponibili">
              <li>Misurazione Pressione</li>
              <li>Screening Prevenzione</li>
              <li>Test Rapidi Covid</li>
            </ul>
          </div>
          <button type="button">Vedi Dettagli</button>
        </div>
        <div class="farm-card">
          <img
            src="https://farmaciaalvigini.it/wp-content/uploads/2021/10/Farmacia-a-Genova-Farmacia-Alvigini-8.jpg"
            alt=""
          />
          <div>
            <h3>Farmacia Centrale</h3>
            <p>Via Francesco Petrarca, 14r, 16121 Genova GE</p>
            <span class="farm-orario">08:30 - 20:00</span>
            <span>Servizi</span>
            <ul>
              <li>Misurazione Pressione</li>
              <li>Screening Prevenzione</li>
              <li>Test Rapidi Covid</li>
            </ul>
          </div>
          <button type="button">Vedi Dettagli</button>
        </div>
        <div class="farm-card">
          <img
            src="https://farmaciaalvigini.it/wp-content/uploads/2021/10/Farmacia-a-Genova-Farmacia-Alvigini-8.jpg"
            alt=""
          />
          <div>
            <h3>Farmacia Centrale</h3>
            <p>Via Francesco Petrarca, 14r, 16121 Genova GE</p>
            <span class="farm-orario">08:30 - 20:00</span>
            <span>Servizi</span>
            <ul>
              <li>Misurazione Pressione</li>
              <li>Screening Prevenzione</li>
              <li>Test Rapidi Covid</li>
            </ul>
          </div>
          <button type="button">Vedi Dettagli</button>
        </div>
      </div>
    </main>
    <footer>
      <p>Copyright &copy; 2025</p>
    </footer>
  </body>
</html>
