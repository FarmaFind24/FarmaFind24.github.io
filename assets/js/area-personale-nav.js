document.addEventListener("DOMContentLoaded", () => {
    const navLinks = document.querySelectorAll('a[href^="#"]');
    const sections = document.querySelectorAll(".content-section");

    const activateSection = (targetId) => {
        const targetElement = document.querySelector(targetId);
        if (!targetElement) return;

        // Trova la sezione (.content-section) che contiene l'elemento cliccato
        // Se l'elemento stesso è una sezione, prenderà se stesso
        const parentSection = targetElement.closest(".content-section");

        if (parentSection) {
            // 1. Nascondi tutte le sezioni
            sections.forEach(s => s.classList.add("content-hidden"));

            // 2. Mostra la sezione che contiene l'obiettivo
            parentSection.classList.remove("content-hidden");

            // 3. Aggiorna i link della navigazione laterale (sidenav)
            navLinks.forEach(link => {
                const href = link.getAttribute("href");
                link.classList.remove("current");
                link.removeAttribute("aria-current");

                // Se il link punta alla sezione ora attiva, evidenzialo
                if (href === `#${parentSection.id}`) {
                    link.classList.add("current");
                    link.ariaCurrent = "page";
                }
            });

            // 4. Se l'obiettivo era un elemento interno (es. breadcrumb), spostaci il focus
            // Questo permette all'accessibilità di funzionare correttamente
            if (targetElement !== parentSection) {
                targetElement.focus();
            }
        }
    };

    // Avvio: attiva la dashboard
    activateSection("#dashboard");

    navLinks.forEach(link => {
        link.addEventListener("click", (e) => {
            const targetId = link.getAttribute("href");

            if (targetId && targetId.startsWith("#") && targetId.length > 1) {
                e.preventDefault();
                activateSection(targetId);
                
                // Aggiorna l'URL nel browser (opzionale, mantiene la cronologia)
                history.pushState(null, null, targetId);
            }
        });
    });
});