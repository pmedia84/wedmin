//nav button active states
const navBtn = document.querySelector('.nav-btn');
const navLinks = document.querySelector('.nav-bar');
const navClose = document.querySelector('#nav-close');
navBtn.addEventListener('click', () => {
    const isOpened = navBtn.getAttribute('aria-expanded') === "true";

    if (isOpened ? closenavLinks() : opennavLinks());
})
navClose.addEventListener('click', () => {
    const isOpened = navBtn.getAttribute('aria-expanded') === "true";

    if (isOpened ? closenavLinks() : opennavLinks());
})

function opennavLinks() {
    navBtn.setAttribute('aria-expanded', "true");
    navLinks.setAttribute('data-state', "opened");
}
function closenavLinks() {
    navBtn.setAttribute('aria-expanded', "false");
    navLinks.setAttribute('data-state', "closing");
    navLinks.addEventListener('animationend', () => {
        navLinks.setAttribute('data-state', "closed");

    }, { once: true })
}

//js for showing user actions menu
$(".user .btn-expand").on("click", function () {
    $(this).toggleClass("open");
    $(this).parents().siblings(".user__actions").slideToggle(400);
})

