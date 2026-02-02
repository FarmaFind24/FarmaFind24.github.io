document.body.classList.add('js-active');
var regForm = document.getElementById("regForm");



if (regForm && !regForm.classList.contains('content-hidden')) {
    var currentTab = 0;
    showTab(currentTab);
} else {
    var currentTab = null;
}

function showTab(n) {
    if (currentTab === null) return;

    document.getElementById("general-error-msg").style.display = "none";
    var x = document.getElementsByClassName("tab");
    x[n].style.display = "block";
    var prevBtn = document.getElementById("prevBtn");
    if (n == 0) {
        prevBtn.disabled = true;
        prevBtn.setAttribute("aria-hidden", "true");
        prevBtn.style.visibility = "hidden";
    } else {
        prevBtn.disabled = false;
        prevBtn.removeAttribute("aria-hidden");
        prevBtn.style.visibility = "visible";
    }
    if (n == (x.length - 1)) {
        document.getElementById("nextBtn").innerHTML = "Conferma Prenotazione";
    } else {
        document.getElementById("nextBtn").innerHTML = "Prosegui";
    }
    fixStepIndicator(n);
}

function nextPrev(n) {
    if (currentTab === null) return;
    var x = document.getElementsByClassName("tab");
    if (n == 1 && !validateForm()) return false;
    if (currentTab >= x.length - 1 && n == 1) {
        submitBookingForm();
        return false;
    }
    x[currentTab].style.display = "none";
    currentTab = currentTab + n;
    showTab(currentTab);
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
    var radioCheckedGroups = [];
    if (typeof resetFormError === "function") { resetFormError(); }

    for (var i = 0; i < inputs.length; i++) {
        var input = inputs[i];
        if (input.type === "text" || input.type === "email") {

            if (input.id === "fnote") {
                if (!input.checkValidity()) {
                    isValid = false;
                    msg += "<p>Inserisci una nota valida</p>";
                    input.classList.add("invalid");
                } else { input.classList.remove("invalid"); }
            }
        }
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
            if (!input.checkValidity()) {
                isValid = false;
                input.classList.add("invalid");
            } else {
                input.classList.remove("invalid");
            }
        }
    }


    if (!isValid) {
        if (msg === "") msg = "<p>Compila tutti i campi obbligatori prima di proseguire!</p>";

        if (typeof addFormError === "function") { addFormError(msg); }
    } else {
        document.getElementsByClassName("step")[currentTab].className += " finish";
    }

    return isValid;
}
function submitBookingForm() {
    var form = document.getElementById("regForm");
    if (form) {
        form.action = "process-booking.php";
        form.submit();
    }
}

function showSuccessMessage() {
    var x = document.getElementsByClassName("tab");
    if (x[currentTab]) x[currentTab].style.display = "none";

    document.querySelector(".step-actions").style.display = "none";
    document.querySelector(".step-indicator-container").style.display = "none";

    var instruction = document.querySelector(".instruction");
    if (instruction) instruction.style.display = "none";

    var successDiv = document.getElementById("success-step");
    if (successDiv) {
        successDiv.style.display = "block";
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