document.addEventListener("DOMContentLoaded", () => {
    const editBtn = document.getElementById("edit-profile-btn");
    const saveBtn = document.getElementById("save-profile-btn");
    const cancelBtn = document.getElementById("cancel-edit-btn");
    const inputs = document.querySelectorAll(".details-grid input");
    const profileForm = document.querySelector("form[action='process-update-profile.php']");

    // FUNZIONI DI VALIDAZIONE
    
    function validateNome(nome) {
        const validChars = /^[A-Za-zÀ-ù\s']{2,50}$/;
        return nome.trim() !== "" && validChars.test(nome);
    }

    function validateCognome(cognome) {
        const validChars = /^[A-Za-zÀ-ù\s']{2,50}$/;
        return cognome.trim() !== "" && validChars.test(cognome);
    }

    function validateEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return email.trim() !== "" && emailRegex.test(email) && email.length <= 100;
    }

    function showError(input, message) {
        input.classList.add("invalid");
        let errorMsg = input.parentElement.querySelector(".error-message");
        if (!errorMsg) {
            errorMsg = document.createElement("p");
            errorMsg.className = "error-message";
            errorMsg.setAttribute("role", "alert");
            input.parentElement.appendChild(errorMsg);
        }
        errorMsg.textContent = message;
        errorMsg.style.display = "block";
    }

    function clearError(input) {
        input.classList.remove("invalid");
        const errorMsg = input.parentElement.querySelector(".error-message");
        if (errorMsg) {
            errorMsg.style.display = "none";
        }
    }

    function clearAllErrors() {
        inputs.forEach(input => clearError(input));
    }

    // VALIDAZIONE FORM AL SUBMIT
    if (profileForm) {
        profileForm.addEventListener("submit", (e) => {
            let isValid = true;
            clearAllErrors();

            const nomeInput = document.querySelector("input[name='name']");
            const cognomeInput = document.querySelector("input[name='surname']");
            const emailInput = document.querySelector("input[name='email']");

            if (nomeInput && !nomeInput.disabled && !nomeInput.readOnly) {
                if (!validateNome(nomeInput.value)) {
                    showError(nomeInput, "Nome non valido. Usa solo lettere (2-50 caratteri).");
                    isValid = false;
                }
            }

            if (cognomeInput && !cognomeInput.disabled && !cognomeInput.readOnly) {
                if (!validateCognome(cognomeInput.value)) {
                    showError(cognomeInput, "Cognome non valido. Usa solo lettere (2-50 caratteri).");
                    isValid = false;
                }
            }

            if (emailInput && !emailInput.disabled && !emailInput.readOnly) {
                if (!validateEmail(emailInput.value)) {
                    showError(emailInput, "Email non valida.");
                    isValid = false;
                }
            }

            if (!isValid) {
                e.preventDefault();
                // Focus sul primo campo con errore
                const firstInvalid = profileForm.querySelector(".invalid");
                if (firstInvalid) firstInvalid.focus();
            }
        });
    }

    if (editBtn) {
        editBtn.addEventListener("click", () => {
            clearAllErrors();
            inputs.forEach(input => {
                if (!input.disabled) {
                    input.removeAttribute("readonly");
                }
            });

            saveBtn.removeAttribute("hidden");
            cancelBtn.removeAttribute("hidden");

            editBtn.setAttribute("hidden", "true");

            const firstEditableInput = Array.from(inputs).find(input => !input.disabled);
            if (firstEditableInput) firstEditableInput.focus();
        });
    }

    if (cancelBtn) {
        cancelBtn.addEventListener("click", (e) => {
            e.preventDefault();
            clearAllErrors();

            inputs.forEach(input => {
                if (!input.disabled) {
                    input.setAttribute("readonly", "true");
                    input.classList.remove("editable");
                }
            });

            saveBtn.setAttribute("hidden", "true");
            cancelBtn.setAttribute("hidden", "true");

            editBtn.removeAttribute("hidden");
        });
    }
});