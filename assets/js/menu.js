document.addEventListener("DOMContentLoaded", () => {
  const toggleIcon = document.querySelector(".menu-toggle");
  const toggleText = document.querySelector(".menu-toggle-text");
  const menu = document.querySelector(".top-navbar ul");
  if (!menu || (!toggleIcon && !toggleText)) return;
  const handleToggle = () => {
    const isOpen = menu.classList.toggle("open");
    if (toggleIcon) toggleIcon.setAttribute("aria-expanded", isOpen);
    if (toggleText) toggleText.setAttribute("aria-expanded", isOpen);
  };
  if (toggleIcon) toggleIcon.addEventListener("click", handleToggle);
  if (toggleText) toggleText.addEventListener("click", handleToggle);
});