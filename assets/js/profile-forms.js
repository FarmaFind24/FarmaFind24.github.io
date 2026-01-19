document.addEventListener("DOMContentLoaded", () => {
    // ... (codice della navigazione precedente) ...

    // Selettori per il Profilo
    const editBtn = document.getElementById("edit-profile-btn");
    const saveBtn = document.getElementById("save-profile-btn");
    const cancelBtn = document.getElementById("cancel-edit-btn");
    const inputs = document.querySelectorAll(".details-grid input");

    if (editBtn) {
        editBtn.addEventListener("click", () => {
            // 1. Rimuovi readonly da tutti gli input (tranne username che è disabled)
            inputs.forEach(input => {
                if (!input.disabled) {
                    input.removeAttribute("readonly");
                    input.classList.add("editable"); // Opzionale: per dare stile CSS
                }
            });

            // 2. Mostra Applica e Annulla
            saveBtn.removeAttribute("hidden");
            cancelBtn.removeAttribute("hidden");

            // 3. Nascondi il tasto "Modifica Profilo"
            editBtn.setAttribute("hidden", "true");
            
            // 4. Metti il focus sul primo input editabile per comodità
            const firstEditableInput = Array.from(inputs).find(input => !input.disabled);
            if (firstEditableInput) firstEditableInput.focus();
        });
    }

    if (cancelBtn) {
        cancelBtn.addEventListener("click", (e) => {
            e.preventDefault(); // Previeni comportamenti indesiderati
            
            // 1. Ripristina readonly (tranne username che rimane disabled)
            inputs.forEach(input => {
                if (!input.disabled) {
                    input.setAttribute("readonly", "true");
                    input.classList.remove("editable");
                }
            });

            // 2. Nascondi Applica e Annulla
            saveBtn.setAttribute("hidden", "true");
            cancelBtn.setAttribute("hidden", "true");

            // 3. Mostra di nuovo "Modifica Profilo"
            editBtn.removeAttribute("hidden");
        });
    }
});