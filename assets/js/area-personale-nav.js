document.addEventListener("DOMContentLoaded", () => {
    const navLinks = document.querySelectorAll('a[href^="#"]');
    const sections = document.querySelectorAll(".content-section");
    const activateSection = (targetId) => {
        sections.forEach(s => s.classList.add("content-hidden"));
        const targetSection = document.querySelector(targetId);
        if (targetSection) {
            targetSection.classList.remove("content-hidden");
        }
        navLinks.forEach(link => {
            link.classList.remove("current");
            link.removeAttribute("aria-current");

            if (link.getAttribute("href") === targetId) {
                link.classList.add("current");
                link.ariaCurrent = "page";
            }
        });
    };
    activateSection("#dashboard");
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