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

// VALIDAZIONE EMAIL
function validateEmail() {
    var email = document.getElementById("femail").value;
    // Regex: lettere e numeri - @ - lettere e numeri . lettere
    const validEmail = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    
    if(email.trim() === "") return false;
    if(!validEmail.test(email)) return false;
    
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