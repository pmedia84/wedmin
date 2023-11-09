$("#guest_list").on("click", ".group-btn", function (e) {
    e.preventDefault();
    $(this).children(".icon").toggleClass("icon-clicked");
    $(this).parents(".guest-card").find(".guest-group-card").slideToggle(300);

})

//script for searching for guests
$("#guest_search").on('keyup', function (event) {
    event.preventDefault();

    var formData = new FormData($("#guest_list_filter").get(0));
    formData.append("action", "search");
    //event filter variable
    var e = $("#event_filter").val();
    //guest search variable
    var search = $("#guest_search").val();
    //change URL to reflect post terms
    var url = new URL(document.URL);
    var params = url.searchParams;
    if (e !== "") {
        params.set("e", e);
    } else {
        params.delete("e");
    }
    if (search !== "") {
        params.set("s", search);
    } else {
        params.delete("s");
    }


    window.history.pushState("state", "Guest List", url);

    $.ajax({ //start ajax post
        type: "POST",
        url: "scripts/guest_list.script.php",
        data: formData,
        contentType: false,
        processData: false,
        success: function (data, responseText) {
            $("#guest_list").html(data);

        }
    });

});
//script for searching for guests
$("#guest_list_filter").on('change', function (event) {
    event.preventDefault();
    var formData = new FormData($("#guest_list_filter").get(0));
    formData.append("action", "guest_filter");
    //event filter variable
    var e = $("#event_filter").val();
    //guest search variable
    var search = $("#guest_search").val();
    //change URL to reflect post terms
    var url = new URL(document.URL);
    var params = url.searchParams;
    params.delete("guest_id");
    if (e !== "") {
        params.set("e", e);
    } else {
        params.delete("e");
    }
    if (search !== "") {
        params.set("s", search);
    } else {
        params.delete("s");
    }
    window.history.pushState("state", "Guest List", url);
    $.ajax({ //start ajax post
        type: "POST",
        url: "scripts/guest_list.script.php",
        data: formData,
        contentType: false,
        processData: false,
        success: function (data, responseText) {
            $("#guest_list").html(data);

        }
    });

});

//? Adding a guest
//show address field 
$("#canvas").on("click", "#show_address", function () {
    $(".form-hidden").slideToggle(500);
})

// adding additional guests
var arrcount = 0;
$("#canvas").on("click", "#add-member", function () {
    var guest_sname = $(this).data("guest_sname");
    var error = $("error");
    var inputs = $("<div class='guest-group-member my-2 d-none' ><div class='form-row'><div class='form-input-col'> <label for='guest_group[" + arrcount + "][guest_fname]'><strong>First Name</strong></label><input class='text-input input' type='text' name='guest_group[" + arrcount + "][guest_fname]' placeholder='First Name' required='' id='guest_group[" + arrcount + "][guest_fname]'></div><div class='form-input-col'><label for='guest_group[" + arrcount + "][guest_sname]'><strong>Surname</strong></label><input class='text-input input' type='text' name='guest_group[" + arrcount + "][guest_sname]' id='guest_group[" + arrcount + "][guest_sname]' placeholder='Surname' required=''value='"+guest_sname+"'></div><button class='btn-remove-guest btn-primary btn-delete' type='button'><svg class='icon x-mark'> <use href='assets/img/icons/solid.svg#xmark'/></svg> Remove </i></button></div><label class='checkbox-form-control my-2' for='guest_group[" + arrcount + "][plus_one]'><input type='checkbox' id='guest_group[" + arrcount + "][plus_one]' name='guest_group[" + arrcount + "][plus_one]' value='plus_one'><strong>Add as a plus one if unsure of name</strong></label></div>");
    $("#guest-group-row").append(inputs);
    $(".guest-group-member").slideDown(400);


    arrcount++;
});

$("#canvas").on("click", "#guest-group-row .btn-remove-guest", function () {
    $(this).parents('.guest-group-member').remove();
});
$("#canvas").on("change", "#guest-group-row input[type=checkbox]", function () {
    if ($(this).is(":checked")) {
        $(this).parents('.guest-group-member').find('input[type=text]').removeAttr('required').addClass('disabled').val('');

    } else {
        $(this).parents('.guest-group-member').find('input[type=text]').attr('required', '').removeClass('disabled');
    };
})

//!adding a new guest
$("#canvas ").on("click", "#save-and-new", function (event) {
    event.preventDefault();
    //find the current url params
    var url = new URL(document.URL);
    var params = url.searchParams;
    //pass in RSVP type
    var rsvp = params.get("rsvp");
    //pass in event filter
    var event_filter = params.get("e");
    console.log(event_filter);
    //empty the error card
    $(".response-card-text").empty();
    //check for empty inputs
    var err_num = 0;
    $("#canvas #add_guest input, textarea").each(function () {

        if ($(this).attr("required")) {
            if ($(this).val() == "") {
                $(this).addClass("input-error");
                var title = $(this).attr("title");

                $(".response-card-text").append("<p>" + title + " is required</p>");
                err_num++;
            } else {
                $(this).removeClass("input-error");
            }

        }
    })
    if (err_num > 0) {
        $("#error-icon").show();
        $("#guest-check").hide();
        $("#response-card-title").text("Please correct the following errors");
        $(".response-card").addClass("error-card");
        $(".response-card-wrapper").fadeIn(400);
        $(".response-card-wrapper").delay(1500).fadeOut(400);

        return;
    }
    var formData = new FormData($("#add_guest").get(0));
    formData.append("action", "new_guest");
    $.ajax({ //start ajax post
        type: "POST",
        url: "scripts/functions.php",
        data: formData,
        contentType: false,
        processData: false,
        success: function (data, responseText) {
            const response = JSON.parse(data);
            if (response.response_code === 200) {
                $("#error-icon").hide();
                $("#guest-removed").show();
                $("#guest-check").show();
                $("#response-card-title").text(response.response_message);
                $(".response-card-text").append("<p>" + response.guest_name + " added to your guest list</p>");
                $(".response-card").removeClass("error-card");
                $(".response-card-wrapper").fadeIn(400);
                $(".response-card-wrapper").delay(2000).fadeOut(400);
                $("#add_guest")[0].reset();
                var postData = new FormData();
                if (event_filter > 0) {
                    postData.append("event_filter", event_filter);
                }
                postData.append("rsvp", rsvp);
                $.ajax({ //start ajax post
                    type: "POST",
                    url: "scripts/guest_list.script",
                    data: postData,
                    contentType: false,
                    processData: false,
                    success: function (response, responseText) {
                        $("#guest_list").html(response);



                    }
                });


            }
            if (response.response_code === 500) {
                //handle error from server
                $("#error-icon").show();
                $("#guest-added").hide();
                $("#response-card-title").text("An error has occurred, error code: " + response.response_code);
                $(".response-card-text").append("<p>" + response.response_message + "</p>");
                $(".response-card").addClass("error-card");
                $(".response-card-wrapper").fadeIn(400);
                $(".response-card-wrapper").delay(1500).fadeOut(400);
            }

        }
    });

});

//!save and close add guest screen
$("#canvas").on("click", "#save-close", function (event) {
    event.preventDefault();
    //find the current url params
    var url = new URL(document.URL);
    var params = url.searchParams;
    //pass in RSVP type
    var rsvp = params.get("rsvp");
    //pass in event filter
    var event_filter = params.get("e");

    //empty the error card
    $(".response-card-text").empty();
    //check for empty inputs
    var err_num = 0;
    $("#canvas #add_guest input, textarea").each(function () {

        if ($(this).attr("required")) {
            if ($(this).val() == "") {
                $(this).addClass("input-error");
                var title = $(this).attr("title");

                $(".response-card-text").append("<p>" + title + " is required</p>");
                err_num++;
            } else {
                $(this).removeClass("input-error");
            }

        }
    })
    if (err_num > 0) {
        $("#error-icon").show();
        $("#guest-check").hide();
        $("#guest-removed").hide();
        $("#response-card-title").text("Please correct the following errors");
        $(".response-card").addClass("error-card");
        $(".response-card-wrapper").fadeIn(400);
        $(".response-card-wrapper").delay(1500).fadeOut(400);

        return;
    }
    var formData = new FormData($("#add_guest").get(0));
    formData.append("action", "new_guest");
    $.ajax({ //start ajax post
        type: "POST",
        url: "scripts/functions.php",
        data: formData,
        contentType: false,
        processData: false,
        success: function (data, responseText) {
            const response = JSON.parse(data);
            if (response.response_code === 200) {
                $("#error-icon").hide();
                $("#guest-removed").hide();
                $("#guest-check").show();
                $("#response-card-title").text(response.response_message);
                $(".response-card-text").append("<p>" + response.guest_name + " added to your guest list</p>");
                $(".response-card").removeClass("error-card");
                $(".response-card-wrapper").fadeIn(400);
                $(".response-card-wrapper").delay(2000).fadeOut(400);
                $("#add_guest")[0].reset();
                var postData = new FormData();
                if (event_filter > 0) {
                    postData.append("event_filter", event_filter);
                }
                postData.append("rsvp", rsvp);
                $.ajax({ //start ajax post
                    type: "POST",
                    url: "scripts/guest_list.script",
                    data: postData,
                    contentType: false,
                    processData: false,
                    success: function (response, responseText) {
                        $("#guest_list").html(response);



                    }
                });
                closeCanvas();
            }


        }
    });

});
//!update guest and close close canvas
$("#canvas ").on("click", "#edit-guest", function (event) {
    //check for empty inputs
    $(".response-card-text").empty();
    var err_num = 0;
    $("#canvas #edit_guest input, textarea").each(function () {

        if ($(this).attr("required")) {
            if ($(this).val() == "") {
                $(this).addClass("input-error");
                var title = $(this).attr("title");

                $(".response-card-text").append("<p>" + title + " is required</p>");
                err_num++;
            } else {
                $(this).removeClass("input-error");
            }

        }
    })
    if (err_num > 0) {
        $("#error-icon").show();
        $("#guest-check").hide();
        $("#guest-removed").hide();
        $("#response-card-title").text("Please correct the following errors");
        $(".response-card").addClass("error-card");
        $(".response-card-wrapper").fadeIn(400);
        $(".response-card-wrapper").delay(1500).fadeOut(400);

        return;
    }
    var guest_id = $(this).data("guest_id");
    //action post params
    var action = "update_guest";
    //find the current url params
    var url = new URL(document.URL);
    var params = url.searchParams;
    //pass in RSVP type
    var rsvp = params.get("rsvp");
    //pass in event filter
    var event_filter = params.get("e");
    var postData = new FormData($("#edit_guest").get(0));
    if (event_filter > 0) {
        postData.append("event_filter", event_filter);
    }
    postData.append("rsvp", rsvp);
    postData.append("action", action);
    postData.append("guest_id", guest_id);
    console.log(postData);
    $.ajax({ //start ajax post
        type: "POST",
        url: "scripts/functions.php",
        data: postData,
        contentType: false,
        processData: false,
        success: function (response_data, responseText) {
            //decode response from functions.php
            const response = JSON.parse(response_data);
            //!if successful then re load the guest list
            if (response.response_code == 200) {

                $("#error-icon").hide();
                $("#guest-check").show();
                $("#guest-removed").hide();
                $("#response-card-title").text(response.response_message);
                $(".response-card").removeClass("error-card");
                $(".response-card-wrapper").fadeIn(400);
                $(".response-card-wrapper").delay(2000).fadeOut(400);
                //! re load guest list
                var postData = new FormData();
                if (event_filter > 0) {
                    postData.append("event_filter", event_filter);
                }
                postData.append("rsvp", rsvp);
                $.ajax({ //start ajax post
                    type: "POST",
                    url: "scripts/guest_list.script",
                    data: postData,
                    contentType: false,
                    processData: false,
                    success: function (response, responseText) {
                        $("#guest_list").html(response);
                    }
                });
                closeCanvas();
            } else {
                $("#error-icon").show();
                $("#guest-check").hide();
                $("#guest-removed").hide();
                $("#response-card-title").text(response.response_message);
                $(".response-card").addClass("error-card");
                $(".response-card-wrapper").fadeIn(400);
                $(".response-card-wrapper").delay(2000).fadeOut(400);
            }



        }
    });

})

//!remove guest once confirm button has been clicked
$("#canvas ").on("click", "#remove_guest_confirm", function () {
    //empty the response card
    $(".response-card-text").empty();
    var guest_id = $(this).data("guest_id");
    //action post params
    var action = "remove_guest";
    //find the current url params
    var url = new URL(document.URL);
    var params = url.searchParams;
    //pass in RSVP type
    var rsvp = params.get("rsvp");
    //pass in event filter
    var event_filter = params.get("e");
    var postData = new FormData();
    if (event_filter > 0) {
        postData.append("event_filter", event_filter);
    }
    postData.append("rsvp", rsvp);
    postData.append("action", action);
    postData.append("guest_id", guest_id);
    $.ajax({ //start ajax post
        type: "POST",
        url: "scripts/functions.php",
        data: postData,
        contentType: false,
        processData: false,
        success: function (response_data, responseText) {
            //decode response from functions.php
            const response = JSON.parse(response_data);
            //!if successful then re load the guest list
            if (response.response_code == 200) {

                $("#error-icon").hide();
                $("#guest-check").hide();
                $("#guest-removed").show();
                $("#response-card-title").text(response.response_message);
                $(".response-card-text").append("<p>" + response.guest_name + " removed from your guest list</p>");
                $(".response-card").removeClass("error-card");
                $(".response-card-wrapper").fadeIn(400);
                $(".response-card-wrapper").delay(2000).fadeOut(400);
                //! re load guest list
                var postData = new FormData();
                if (event_filter > 0) {
                    postData.append("event_filter", event_filter);
                }
                postData.append("rsvp", rsvp);
                $.ajax({ //start ajax post
                    type: "POST",
                    url: "scripts/guest_list.script",
                    data: postData,
                    contentType: false,
                    processData: false,
                    success: function (response, responseText) {
                        $("#guest_list").html(response);
                    }
                });
                closeCanvas();
            } else {
                $("#error-icon").show();
                $("#guest-check").hide();
                $("#guest-removed").hide();
                $("#response-card-title").text(response.response_message);
                $(".response-card").addClass("error-card");
                $(".response-card-wrapper").fadeIn(400);
                $(".response-card-wrapper").delay(2000).fadeOut(400);
            }



        }
    });
})

//load guest from the ajax guest list that has been loaded after a save 
$("#guest_list").on("click", ".canvas_open", function (e) {
    e.preventDefault();
    canvas_data.guest_id = $(this).data("guest_id");
    canvas_data.canvas_type = $(this).data("data");
    openCanvas();
    guest_id = $(this).data("guest_id");
    //update URL params
    var url = new URL(document.URL);
    var params = url.searchParams;
    params.set("guest_id", guest_id);
    if (guest_id !== "") {
    }
    console.log("Guest Id", guest_id);
    window.history.pushState("state", "Guest List", url);
})