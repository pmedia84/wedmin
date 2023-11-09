
$("#delete-profile").click(function () {
    $(".modal").addClass("modal-active");
})
$("#upload_image").click(function () {
    $(".modal").addClass("modal-active");
})
$("#new_request").click(function () {
    $(".modal").addClass("modal-active");
})
//close modal when close button is clicked
$("#modal-btn-close").click(function () {
    $(".modal").removeClass("modal-active");

})
//close modal when close button is clicked
$("#delete-cancel").click(function () {
    $(".modal").removeClass("modal-active");

})

//close modal when close button is clicked
$("#cancel").click(function () {
    $(".modal").removeClass("modal-active");

})

//show dropdown menu when button clicked
$(".dropdown-btn").on("click", function () {
    $(this).siblings("ul").fadeToggle(400);
})
//show passwords on login forms
$("#show_pw").on("click", function () {
    if ($(".pw").prop("type") == "password") {
        $(".show_pw_on").removeClass("hidden");
        $(".show_pw_off").addClass("hidden");
        $(".pw").prop("type", "text");
    } else {
        $(".show_pw_on").addClass("hidden");
        $(".show_pw_off").removeClass("hidden");
        $(".pw").prop("type", "password");
    }

})

//off canvas JS
//canvas button active states
const canvas_data = { guest_id: null, canvas_type: null };
const open_canvas = $(".canvas_open");
const canvas = document.querySelector('.offcanvas_canvas');
const canvasBG = document.querySelector('.offcanvas_bg');
const canvasClose = document.querySelector('#close-canvas');
//body
const body = document.querySelector("body");
open_canvas.each(function () {
    $(this).on("click", function (e) {
        e.preventDefault();
        //find what form needs to be loaded
        canvas_data.guest_id = $(this).data("guest_id");
        canvas_data.canvas_type = $(this).data("data");
        const isOpened = canvas.getAttribute('data-state') === "closed";
        //if (isOpened ? openCanvas() : closeCanvas());
        const data = $(this).data("data");
    })
})
//open canvas to add a guest when add guest button is clicked
$("#add_guest_btn").on("click", function (e) {
    e.preventDefault();
    //find what form needs to be loaded
    canvas_data.guest_id = $(this).data("guest_id");
    canvas_data.canvas_type = $(this).data("data");

    //load the data into canvas from the data attribute on button


    const isOpened = canvas.getAttribute('data-state') === "closed";
    if (isOpened ? openCanvas() : closeCanvas());
    const data = $(this).data("data");

})
//open new canvas if a group leader or member is clicked on
$("#canvas").on("click", ".canvas_open", function (e) {
    e.preventDefault();
    //find what form needs to be loaded
    canvas_data.guest_id = $(this).data("guest_id");
    canvas_data.canvas_type = $(this).data("data");
    //load the data into canvas from the data attribute on button
    //update URL params
    var guest_id = $(this).data("guest_id");
    var url = new URL(document.URL);
    var params = url.searchParams;
    params.set("guest_id", guest_id);
    if (guest_id !== "") {
    }
    window.history.pushState("state", "Guest List", url);
    $.ajax({
        type: "GET",
        url: "scripts/canvas.php?canvas=" + canvas_data.canvas_type + "&guest_id=" + canvas_data.guest_id,

        contentType: false,
        processData: false,
        success: function (data, responseText) {
            $("#canvas").html(data);

        }
    });
})
$("#canvas").on("click", "#close-canvas", function () {
    closeCanvas();



})


//hide canvas if bg is clicked
canvasBG.addEventListener('click', () => {
    const isOpened = canvas.getAttribute('data-state') === "opened";

    if (isOpened ? closeCanvas() : openCanvas());
})
function openCanvas() {
    //load the data into canvas from the data attribute on button
    $.ajax({
        type: "GET",
        url: "scripts/canvas.php?canvas=" + canvas_data.canvas_type + "&guest_id=" + canvas_data.guest_id,

        contentType: false,
        processData: false,
        success: function (data, responseText) {
            $("#canvas").html(data);

        }
    });
    //find scrollbar width
    var scrollbarWidth = window.innerWidth - document.documentElement.clientWidth;
    open_canvas.data('aria-expanded', "true");
    canvas.setAttribute('data-state', "opened");
    canvasBG.setAttribute('data-state', "opened");
    //body.setAttribute('data-state', "opened");
    const scrollY = document.documentElement.style.getPropertyValue('--scroll-y');
    body.style.padding = "0px " + scrollbarWidth + "px 0px 0px";
    body.style.overflow = "hidden";


}
function closeCanvas() {
    open_canvas.data('aria-expanded', "false");
    canvas.setAttribute('data-state', "closing");
    canvasBG.setAttribute('data-state', "closing");
    canvas.addEventListener('animationend', () => {
        body.style.padding = "0px";
        body.style.overflow = "initial";

        canvas.setAttribute('data-state', "closed");
        canvasBG.setAttribute('data-state', "closed");

    }, { once: true })
    //remove guest_id params from url to prevent reloading if canvas has been closed
    var url = new URL(document.URL);
    var params = url.searchParams;
    params.delete("guest_id");
    window.history.pushState("state", "Guest List", url);
}
window.addEventListener('scroll', () => {
    document.documentElement.style.setProperty('--scroll-y', `${window.scrollY}px`);
});


//!! Tabs
function openTab(evt, TabName) {
    // Declare all variables
    var i, tabcontent, tablinks;
  
    // Get all elements with class="tabcontent" and hide them
    tabcontent = document.getElementsByClassName("tabcontent");
    for (i = 0; i < tabcontent.length; i++) {
      tabcontent[i].style.display = "none";
    }
  
    // Get all elements with class="tablinks" and remove the class "active"
    tablinks = document.getElementsByClassName("tablinks");
    for (i = 0; i < tablinks.length; i++) {
      tablinks[i].className = tablinks[i].className.replace(" active", "");
    }
  
    // Show the current tab, and add an "active" class to the button that opened the tab
    document.getElementById(TabName).style.display = "block";
    evt.currentTarget.className += " active";
}
  
