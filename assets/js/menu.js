document.addEventListener("DOMContentLoaded", () => {
  const toggle = document.querySelector(".menu-toggle");
  const toggle2 = document.querySelector(".menu-toggle-text");
  const menu = document.querySelector(".Top-NavBar ul");

  if (!toggle || !toggle || !menu) return;

  toggle.addEventListener("click", () => {
    const isOpen = menu.classList.toggle("open"); 
    toggle.setAttribute("aria-expanded", isOpen);
    toggle2.setAttribute("aria-expanded", isOpen);
  });
});
