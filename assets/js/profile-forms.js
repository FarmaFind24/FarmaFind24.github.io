document.addEventListener("DOMContentLoaded", () => {
    const editBtn = document.getElementById("edit-profile-btn");
    const saveBtn = document.getElementById("save-profile-btn");
    const cancelBtn = document.getElementById("cancel-edit-btn");
    const inputs = document.querySelectorAll(".details-grid input");

    if (editBtn) {
        editBtn.addEventListener("click", () => {
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