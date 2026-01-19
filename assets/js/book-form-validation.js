// Contiene le funzioni specifiche per controllare il formato dei dati 

// VALIDAZIONE NOME
function validateNome() {
    var nome = document.getElementById("fname").value;
    // Regex: solo lettere
    const validChars = /^[A-Za-zÀ-ù\s']+$/; 
    
    if(nome.trim() === "") return false;
    if(!validChars.test(nome)) return false; 
    
    return true;
}

// VALIDAZIONE COGNOME
function validateCognome() {
    var cognome = document.getElementById("fsurname").value;
    //solo lettere
    const validChars = /^[A-Za-zÀ-ù\s']+$/;
    
    if(cognome.trim() === "") return false;
    if(!validChars.test(cognome)) return false;
    
    return true;
}

// VALIDAZIONE CODICE FISCALE
function validateCodiceFiscale() {
    var codiceFiscale = document.getElementById("fcode").value;
    // Regex: 6 lettere, 2 numeri, 1 lettera, 2 numeri, 1 lettera, 3 numeri, 1 lettera (16 caratteri totali)
    const validCF = /^[A-Z]{6}[0-9]{2}[A-Z][0-9]{2}[A-Z][0-9]{3}[A-Z]$/i;
    
    if(codiceFiscale.trim() === "") return false;
    if(codiceFiscale.length !== 16) return false;
    if(!validCF.test(codiceFiscale.toUpperCase())) return false;
    
    return true;
}

// resetta errori
function resetFormError() {
    var errorBox = document.getElementById("general-error-msg");
    errorBox.style.display = "none";
    errorBox.innerHTML = "";
    errorBox.className = "error-message";
}

// trovati errori
function addFormError(msg) {
    var errorBox = document.getElementById("general-error-msg");
    errorBox.style.display = "block";
    errorBox.innerHTML = msg; 
    errorBox.focus();
}