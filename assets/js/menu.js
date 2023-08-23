$("#edit-courses").on("click", function () {
    $("#course-modal").addClass("modal-active");
})
$("#course-modal").on("click", ".close", function () {
    $("#course-modal").removeClass("modal-active");
})
$("#course-editor").on("click", "#courses-save", function (e) {
    e.preventDefault();
    var formData = new FormData($("#courses-editor").get(0));

    let action = $(this).data("action");
    formData.append("action", action);
    $.ajax({ //start ajax post
        type: "POST",
        url: "scripts/menu.script.php",
        data: formData,
        contentType: false,
        processData: false,
        success: function (data, responseText) {
            ///need script to catch errors
            $("#course-modal").removeClass("modal-active");
        }
    });

});
$("#course-editor").on("click", ".btn-delete", function (e) {
    e.preventDefault();
    var formData = new FormData();
    let course_id = $(this).data("course_id");
    let action = $(this).data("action");
    formData.append("action", action);
    formData.append("course_id", course_id);
    $.ajax({ //start ajax post
        type: "POST",
        url: "scripts/menu.script.php",
        data: formData,
        contentType: false,
        processData: false,
        success: function (data, responseText) {
            ///need script to catch errors
            $("#course-editor").load("scripts/menu.script.php?action=load");
        }
    });
});

$("#add-dish").on("click", function () {
    $("#dish-modal").addClass("modal-active");
})

$("#dish-modal").on("click", ".close", function () {
    $("#dish-modal").removeClass("modal-active");
})

$("#save-dish").on("click", function (e) {
    e.preventDefault();
    let formData = new FormData($("#create-dish").get(0));
    let action = $(this).data("action");
    let menu_id = $(this).data("menu_id");
    formData.append("action", action);
    formData.append("menu_id", menu_id);
    $.ajax({ //start ajax post
        type: "POST",
        url: "scripts/menu.script.php",
        data: formData,
        contentType: false,
        processData: false,
        success: function (data, responseText) {
            if (data === '1') {
                $("#create-dish")[0].reset();
                $("#menu").load("scripts/menu.script.php?action=load_menu&menu_id=" + menu_id);
            } else {
                $("#response").html(data);
                $("#response").slideDown(400);
            }


        }
    });
})

$("#menu").on("click", ".edit-dish", function () {
    let dish_id = $(this).data("dish_id");
    let menu_id = $(this).data("menu_id");
    $("#edit-dish-modal").load("scripts/menu.script.php?action=edit_dish&dish_id=" + dish_id + "&menu_id=" + menu_id);
    $("#edit-dish-modal").addClass("modal-active");
})
$("#menu").on("click", ".delete-dish", function () {
    let dish_id = $(this).data("dish_id");
    let menu_id = $(this).data("menu_id");
    let formData = new FormData();
    let action = $(this).data("action");
    formData.append("action", action);
    formData.append("menu_item_id", dish_id);
    $.ajax({ //start ajax post
        type: "POST",
        url: "scripts/menu.script.php",
        data: formData,
        contentType: false,
        processData: false,
        success: function (data, responseText) {
            if (data === '1') {
                $("#menu").load("scripts/menu.script.php?action=load_menu&menu_id=" + menu_id);
            } else {
                $("#response").html(data);
                $("#response").slideDown(400);
            }


        }
    });
})

$("#edit-dish-modal").on("submit", "#edit-dish", function (e) {
    e.preventDefault();
    let dish_id = $("#edit-save-dish").data("dish_id");
    let menu_id = $("#edit-save-dish").data("menu_id");
    let formData = new FormData($("#edit-dish").get(0));
    let action = "edit_dish";
    formData.append("action", action);
    formData.append("menu_item_id", dish_id);
    formData.append("menu_id", menu_id);
    $.ajax({ //start ajax post
        type: "POST",
        url: "scripts/menu.script.php",
        data: formData,
        contentType: false,
        processData: false,
        success: function (data, responseText) {
            if (data === '1') {
                $("#edit-dish")[0].reset();
                $("#menu").load("scripts/menu.script.php?action=load_menu&menu_id=" + menu_id);
            } else {
                $("#response").html(data);
                $("#response").slideDown(400);
            }
        }
    });
    $("#edit-dish-modal").removeClass("modal-active");
})
$("#edit-dish-modal").on("click", ".close", function () {
    $("#edit-dish-modal").removeClass("modal-active");
})

$("#add-menu").on("click", function () {
    $("#menu-modal").addClass("modal-active");
})
$("#close-menu-modal").on("click", function () {
    $("#menu-modal").removeClass("modal-active");
})
$("#create-menu").on("submit", function(e){
    e.preventDefault();
    let formData = new FormData($("#create-menu").get(0));
    let action = $(this).data("action");
    formData.append("action", action);
    $.ajax({ //start ajax post
        type: "POST",
        url: "scripts/menu.script.php",
        data: formData,
        contentType: false,
        processData: false,
        success: function (data, responseText) {
            if (data === '1') {
                $("#create-menu")[0].reset();
                $("#menu-body").load("scripts/menu.script.php?action=load_menus");
                $("#menu-modal").removeClass("modal-active");
            } else {
                $("#response").html(data);
                $("#response").slideDown(400);
            }


        }
    });
})

$(".menu_name_edit").on("focusout", function(){
    let menu_id = $(this).data("menu_id");
    let action = $(this).data("action");
    let menu_name = $(this).text();
    const data = new FormData();
    data.append("menu_id", menu_id);
    data.append("action", action);
    data.append("menu_name", menu_name);
    $.ajax({ //start ajax post
        type: "POST",
        url: "scripts/menu.script.php",
        data: data,
        contentType: false,
        processData: false,
        success: function (data, responseText) {
        }
    });
})
