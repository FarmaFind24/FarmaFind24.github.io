let scrollbtn = document.getElementById("scroll-btn");

window.onscroll = function() {scrollFunction()};

function scrollFunction() {
  if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
    scrollbtn.style.display = "block";
  } else {
    scrollbtn.style.display = "none";
  }
}

function topFunction() {
    window.scrollTo({ 
        top: 0, 
        behavior: 'smooth' 
    });

}