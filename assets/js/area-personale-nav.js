document.addEventListener("DOMContentLoaded", () => {
    const navLinks = document.querySelectorAll('a[href^="#"]');
    const sections = document.querySelectorAll(".content-section");

    // --- FUNZIONE PER CAMBIARE SEZIONE ---
    const activateSection = (targetId) => {
        // 1. Nascondi tutte le sezioni
        sections.forEach(s => s.classList.add("content-hidden"));

        // 2. Mostra la sezione target
        const targetSection = document.querySelector(targetId);
        if (targetSection) {
            targetSection.classList.remove("content-hidden");
        }

        // 3. Aggiorna lo stato dei link (CSS e Accessibilità)
        navLinks.forEach(link => {
            link.classList.remove("current");
            link.removeAttribute("aria-current");

            if (link.getAttribute("href") === targetId) {
                link.classList.add("current");
                link.ariaCurrent = "page"; // Corretto: assegna la stringa
            }
        });
    };

    // --- LOGICA DI AVVIO (Sempre Dashboard all'inizio) ---
    activateSection("#dashboard");

    // --- GESTIONE DEI CLICK ---
    navLinks.forEach(link => {
        link.addEventListener("click", (e) => {
            const targetId = link.getAttribute("href");

            if (targetId && targetId.startsWith("#") && targetId.length > 1) {
                e.preventDefault();
                activateSection(targetId);
            }
        });
    });
});