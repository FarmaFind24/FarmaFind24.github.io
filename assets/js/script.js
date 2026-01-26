// Imposta la scheda corrente alla prima (indice 0)
var currentTab = 0; 
showTab(currentTab); 

function showTab(n) {
  var x = document.getElementsByClassName("tab");
  x[n].style.display = "block";

    // Gestione pulsante "Indietro"
  if (n == 0) {
    document.getElementById("prevBtn").style.display = "none";
  } else {
    document.getElementById("prevBtn").style.display = "inline";
  }

  // Gestione pulsante "Avanti" o "Conferma"
  if (n == (x.length - 1)) {
    document.getElementById("nextBtn").innerHTML = "Conferma Prenotazione";
  } else {
    document.getElementById("nextBtn").innerHTML = "Prosegui";
  }

  // Aggiorna i cerchietti di progresso
  fixStepIndicator(n);

  // Seleziona il titolo della tab corrente
  var title = x[n].querySelector(".tab-title");
  if (title) {
    // Rendiamo il titolo focalizzabile solo via JS con -1
    title.setAttribute("tabindex", "-1"); 
    // Spostiamo il focus: lo screen reader leggerà il titolo immediatamente
    title.focus(); 
  }
}

function nextPrev(n) {
  var x = document.getElementsByClassName("tab");

  // Se provi ad andare avanti (n=1), controlla se il modulo è valido
  if (n == 1 && !validateForm()) return false;

  // Nascondi la scheda attuale prima di cambiare
  x[currentTab].style.display = "none";

  // Incrementa o decrementa l'indice della scheda
  currentTab = currentTab + n;

  // Se sei alla fine, invia il form
  if (currentTab >= x.length) {
    document.getElementById("regForm").submit();
    return false;
  }

  // Altrimenti mostra la nuova scheda
  showTab(currentTab);
}

function validateForm() {
  var x, y, i, valid = true;
  x = document.getElementsByClassName("tab");
  // Cerca tutti gli input (text, radio, select) nella scheda corrente
  y = x[currentTab].querySelectorAll("input, select");

  for (i = 0; i < y.length; i++) {
    // Se un campo "required" è vuoto o un radio non è selezionato
    if (y[i].hasAttribute('required')) {
        if (y[i].type === "radio") {
            // Controllo specifico per i radio button
            const radioName = y[i].name;
            if (!document.querySelector(`input[name="${radioName}"]:checked`)) {
                valid = false;
                y[i].closest('.service-box, .farm-box')?.classList.add("invalid");
            }
        } else if (y[i].value == "") {
            valid = false;
            y[i].classList.add("invalid");
        }
    }
  }

  // Se è valido, segna lo step come completato
  if (valid) {
    document.getElementsByClassName("step")[currentTab].className += " finish";
  }
  return valid;
}

function fixStepIndicator(n) {
  var i, x = document.getElementsByClassName("step");
  for (i = 0; i < x.length; i++) {
    x[i].className = x[i].className.replace(" active", "");
  }
  x[n].className += " active";
}