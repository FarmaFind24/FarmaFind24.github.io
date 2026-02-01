document.addEventListener("DOMContentLoaded", () => {
  const toggleIcon = document.querySelector(".menu-toggle");
  const toggleText = document.querySelector(".menu-toggle-text");
  const menu = document.querySelector(".top-navbar ul");

  // Se mancano gli elementi fondamentali, esci
  if (!menu || (!toggleIcon && !toggleText)) return;

  // Funzione unica per gestire l'apertura/chiusura
  const handleToggle = () => {
    const isOpen = menu.classList.toggle("open");

    // Aggiorna lo stato di entrambi i pulsanti (se esistono)
    if (toggleIcon) toggleIcon.setAttribute("aria-expanded", isOpen);
    if (toggleText) toggleText.setAttribute("aria-expanded", isOpen);
  };

  // Assegna l'evento a entrambi i pulsanti
  if (toggleIcon) toggleIcon.addEventListener("click", handleToggle);
  if (toggleText) toggleText.addEventListener("click", handleToggle);
});