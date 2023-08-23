//nav
$(".nav-btn").click(function() {
    $(".nav-bar").slideDown(500);
});

$("#nav-btn-close").click(function() {
    $(".nav-bar").fadeOut(500);
})
$("#delete-profile").click(function() {
    $(".modal").addClass("modal-active");
})
$("#upload_image").click(function() {
    $(".modal").addClass("modal-active");
})
$("#new_request").click(function() {
    $(".modal").addClass("modal-active");
})
//close modal when close button is clicked
$("#modal-btn-close").click(function() {
    $(".modal").removeClass("modal-active");

})
//close modal when close button is clicked
$("#delete-cancel").click(function() {
    $(".modal").removeClass("modal-active");

})

//close modal when close button is clicked
$("#cancel").click(function() {
    $(".modal").removeClass("modal-active");

})

//show dropdown menu when button clicked
$(".dropdown-btn").on("click", function(){
    $(this).siblings("ul").fadeToggle(400);
})