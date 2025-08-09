/*!
    * Start Bootstrap - Creative v6.0.3 (https://startbootstrap.com/themes/creative)
    * Copyright 2013-2020 Start Bootstrap
    * Licensed under MIT (https://github.com/StartBootstrap/startbootstrap-creative/blob/master/LICENSE)
    */
document.addEventListener('DOMContentLoaded', function () {
    // Menu & profile toggle
    let menu = document.querySelector('#menu-btn');
    let navbar = document.querySelector('.navbar');
    let profile = document.querySelector('.Navigation .profile');
    let user = document.querySelector('#user-btn');


    menu.onclick = () => {
    menu.classList.toggle('fa-times');
    navbar.classList.toggle('active'); // ✅ toggles navbar visibility
    profile.classList.remove('active');
};

    user.onclick = () => {
        profile.classList.toggle('active');
        navbar.classList.remove('active');
    };

    window.onscroll = () => {
        navbar.classList.remove('active');
        profile.classList.remove('active');
        menu.classList.remove('fa-times');
    };

    document.addEventListener('click', function(event) {
        const clickedInsideMenu = menu.contains(event.target) || navbar.contains(event.target);
        const clickedInsideProfile = user.contains(event.target) || profile.contains(event.target);

        if (!clickedInsideMenu && !clickedInsideProfile) {
            navbar.classList.remove('active');
            profile.classList.remove('active');
            menu.classList.remove('fa-times');
        }
    });

    // Swiper slider
    var swiper = new Swiper(".home-slider", {
        spaceBetween: 20,
        effect: "fade",
        grabCursor: true,
        loop: true,
        centeredSlides: true,
        autoplay: {
            delay: 3000,
        },
        speed: 1500,
        pagination: {
            el: ".swiper-pagination",
            clickable: true,
        },
    });


});

    // Login/Register panel animation
    const signUpButton = document.getElementById('signUp');
    const signInButton = document.getElementById('signIn');
    const container = document.getElementById('container');

    if (signUpButton && signInButton && container) {
        signUpButton.addEventListener('click', () => {
            container.classList.add("right-panel-active");
        });

        signInButton.addEventListener('click', () => {
            container.classList.remove("right-panel-active");
        });
    }

    //setting the reservation form to clear after submiting or refreshing the website
window.addEventListener("DOMContentLoaded", () => {
  const msg = document.getElementById("availability_msg");
  if (msg && msg.textContent.includes("Reservation successful") || msg.textContent.includes("tentative")) {
    setTimeout(() => {
      document.getElementById("filter").reset();
    }, 3000);
  }
});

const feedbackForm = document.getElementById("feedbackForm");
if (feedbackForm) feedbackForm.reset();



//Discoutn part
const numRoomsInput = document.getElementById('num_rooms');
const discountMsg = document.querySelector('.discount_msg');

function updateDiscountMessage() {
  const rooms = parseInt(numRoomsInput.value) || 0;

  discountMsg.classList.remove('normal', 'warning', 'success');

  if (discountPercent === 0) {
    // If discount is zero for any user type
    discountMsg.textContent = 'No discount available at the moment!.';
    discountMsg.classList.add('normal');  // red
  } else {
    // Discount percent > 0
    if (rooms >= 3) {
      discountMsg.textContent = `Discount applied! You get ${discountPercent}% off for booking ${rooms} rooms.`;
      discountMsg.classList.add('success');
    } else if (rooms === 2) {
      discountMsg.textContent = 'Book one more room to get a discount!';
      discountMsg.classList.add('warning');
    } else {
      discountMsg.textContent = 'No discount available for less than 3 rooms.';
      discountMsg.classList.add('normal');
    }
  }
}

numRoomsInput.addEventListener('input', updateDiscountMessage);
updateDiscountMessage();  // run once on page load



