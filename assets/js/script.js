// Imposta la scheda corrente alla prima (indice 0)
var currentTab = 0;
showTab(currentTab);

function showTab(n) {
  var x = document.getElementsByClassName("tab");
  x[n].style.display = "block";
  if (n == 0) {
    document.getElementById("prevBtn").style.display = "none";
  } else {
    document.getElementById("prevBtn").style.display = "inline";
  }
  if (n == (x.length - 1)) {
    document.getElementById("nextBtn").innerHTML = "Conferma Prenotazione";
  } else {
    document.getElementById("nextBtn").innerHTML = "Prosegui";
  }
  fixStepIndicator(n);
  var title = x[n].querySelector(".tab-title");
  if (title) {
    title.setAttribute("tabindex", "-1");
    title.focus();
  }
}

function nextPrev(n) {
  var x = document.getElementsByClassName("tab");
  if (n == 1 && !validateForm()) return false;
  x[currentTab].style.display = "none";
  currentTab = currentTab + n;
  if (currentTab >= x.length) {
    document.getElementById("regForm").submit();
    return false;
  }
  showTab(currentTab);
}

function validateForm() {
  var x, y, i, valid = true;
  x = document.getElementsByClassName("tab");
  y = x[currentTab].querySelectorAll("input, select");

  for (i = 0; i < y.length; i++) {
    if (y[i].hasAttribute('required')) {
      if (y[i].type === "radio") {
        const radioName = y[i].name;
        if (!document.querySelector(`input[name="${radioName}"]:checked`)) {
          valid = false;
          y[i].closest('.service-box, .farm-box')?.classList.add("invalid");
        }
      } else if (y[i].value == "") {
        valid = false;
        y[i].classList.add("invalid");
      }
    }
  }
  if (valid) {
    document.getElementsByClassName("step")[currentTab].className += " finish";
  }
  return valid;
}

function fixStepIndicator(n) {
  var i, x = document.getElementsByClassName("step");
  for (i = 0; i < x.length; i++) {
    x[i].className = x[i].className.replace(" active", "");
  }
  x[n].className += " active";
}