document.body.classList.add('js-active');
// tab di indice 0
var currentTab = 0; 
showTab(currentTab); 

function showTab(n) {
  document.getElementById("general-error-msg").style.display = "none";
  var x = document.getElementsByClassName("tab");
  
  // mostra tab corrente
  x[n].style.display = "block";

  //pulsante indietro
  if (n == 0) {
    document.getElementById("prevBtn").style.display = "none";
  } else {
    document.getElementById("prevBtn").style.display = "inline";
  }

  // pulsanti avanti conferma
  if (n == (x.length - 1)) {
    document.getElementById("nextBtn").innerHTML = "Conferma Prenotazione";
  } else {
    document.getElementById("nextBtn").innerHTML = "Prosegui";
  }

  // cerchietti progresso
  fixStepIndicator(n);
}

function nextPrev(n) {
  var x = document.getElementsByClassName("tab");

  // 1. Validazione: Se provi ad andare avanti (n=1) e il form NON è valido, fermati.
  if (n == 1 && !validateForm()) return false;

  // 2. Controllo Finale: Se siamo all'ultima tab e premiamo avanti
  if (currentTab >= x.length - 1 && n == 1) {
    showSuccessMessage(); 
    return false; 
  }

  // 3. Cabio Tab Standard
  x[currentTab].style.display = "none"; 
  currentTab = currentTab + n; 
  showTab(currentTab); 

  // sposta focus sul titolo del nuovo passaggio
  var title = x[currentTab].querySelector(".tab-title");
  if (title) {
      title.setAttribute("tabindex", "-1");
      title.focus();
      title.style.outline = "none"; 
  }
}
function validateForm() {
  var x = document.getElementsByClassName("tab");
  var inputs = x[currentTab].querySelectorAll("input, select");
  var isValid = true;
  var msg = ""; 
  
  // array per ricordare quali gruppi radio abbiamo già controllato
  var radioCheckedGroups = [];

  // pulizia errori
  if (typeof resetFormError === "function") { resetFormError(); }

  for (var i = 0; i < inputs.length; i++) {
    var input = inputs[i];

    //dati personali
    if (input.type === "text" || input.type === "email") {
        
        if (input.id === "fname") { 
            if (!validateNome()) {
                isValid = false;
                msg += "<p>Nome: Inserisci solo lettere.</p>";
                input.classList.add("invalid");
            } else { input.classList.remove("invalid"); }
        }
        else if (input.id === "fsurname") {
            if (!validateCognome()) {
                isValid = false;
                msg += "<p>Cognome: Inserisci solo lettere.</p>";
                input.classList.add("invalid");
            } else { input.classList.remove("invalid"); }
        }
        else if (input.id === "femail") {
            if (!validateEmail()) {
                isValid = false;
                msg += "<p>Email: nome@mail.com </p>";
                input.classList.add("invalid");
            } else { input.classList.remove("invalid"); }
        }
    }
    //radio buttons
    else if (input.type === "radio") {
        var groupName = input.name;
        
        
        if (radioCheckedGroups.indexOf(groupName) === -1) {
            radioCheckedGroups.push(groupName); 

            
            var isChecked = document.querySelector('input[name="' + groupName + '"]:checked');

            if (!isChecked) {
                isValid = false;
            }
        }
    }


    else {
        // type="date" 
        if (!input.checkValidity()) {
            isValid = false;
            input.classList.add("invalid"); 
        } else {
            input.classList.remove("invalid");
        }
    }
  }


  if (!isValid) {
      // errore generico
      if (msg === "") msg = "<p>Compila tutti i campi obbligatori prima di proseguire!</p>";
      
      if (typeof addFormError === "function") { addFormError(msg); }
  } else {
      document.getElementsByClassName("step")[currentTab].className += " finish";
  }

  return isValid;
}
function showSuccessMessage() {
    // 1. Nascondi l'ultima tab visibile
    var x = document.getElementsByClassName("tab");
    if(x[currentTab]) x[currentTab].style.display = "none"; 

    // 2. Nascondi pulsanti, pallini e istruzioni
    document.querySelector(".step-actions").style.display = "none";
    document.querySelector(".step-indicator-container").style.display = "none";
    
    var instruction = document.querySelector(".instruction");
    if(instruction) instruction.style.display = "none";

    // 3. Mostra il box di successo
    var successDiv = document.getElementById("success-step");
    if(successDiv) {
        successDiv.style.display = "block";
        window.scrollTo({ top: 0, behavior: 'smooth' });
    } else {
        console.error("Errore: Elemento 'success-step' non trovato nell'HTML");
    }
}

function fixStepIndicator(n) {
  var i, x = document.getElementsByClassName("step");
  for (i = 0; i < x.length; i++) {
    x[i].className = x[i].className.replace(" active", "");
  }
  x[n].className += " active";
}


document.addEventListener('input', function (e) {
    if (e.target.classList.contains('invalid')) {
        if (e.target.checkValidity()) {
            e.target.classList.remove('invalid');
            e.target.setAttribute("aria-invalid", "false");
        }
    }
}, true);