//load price list once loaded
$("document").ready(function() {
    url = "scripts/price_list.script.php?action=load-price-list";
    $.ajax({ //load price list
        type: "GET",
        url: url,
        encode: true,

        success: function(data, responseText) {
            $("#price_list").html(data);

        }
    });
})

//edit a service
$("#edit_service").submit(function (event) {
    const form = document.getElementById("edit_service");
    const pos = form.offsetTop;
    event.preventDefault();
    //declare form variables and collect GET request information
    service_id = $(this).data("service_id");
    var formData = new FormData($("#edit_service").get(0));
    formData.append("action", "edit");
    formData.append("service_id", service_id);
    $.ajax({ //start ajax post
        type: "POST",
        url: "scripts/pl_crud.script.php",
        data: formData,
        contentType: false,
        processData: false,
        success: function (data, responseText) {
            if (data === "200") {
                window.location.replace('price_list');
            } else {
                $("#response-msg").html(data);
                $(".response-card").addClass("error-card");
                window.scrollTo("0", pos);
                $("#response-card-wrapper").fadeIn(400);
                $("#response-card-wrapper").delay(5000).fadeOut(400);
            }

        }
    });

});
//create a service
$("#create_service").submit(function (event) {
    const form = document.getElementById("create_service");
    const pos = form.offsetTop;
    event.preventDefault();
    //declare form variables and collect GET request information
    var formData = new FormData($("#create_service").get(0));
    formData.append("action", "create");
    $.ajax({ //start ajax post
        type: "POST",
        url: "scripts/pl_crud.script.php",
        data: formData,
        contentType: false,
        processData: false,
        success: function (data, responseText) {

            if (data === "200") {
                window.location.replace('price_list');
            } else {
                $("#response-msg").html(data);
                $(".response-card").addClass("error-card");
                window.scrollTo("0", pos);
                $("#response-card-wrapper").fadeIn(400);
                $("#response-card-wrapper").delay(5000).fadeOut(400);
            }

        }
    });

});

//script for searching for loading price list
$("#price_list_search").on('keyup submit', function (event) {
    event.preventDefault();
    var formData = new FormData($("#price_list_search").get(0));
    formData.append("action", "price_list_search");
    $.ajax({ //start ajax post
        type: "POST",
        url: "scripts/price_list.script.php",
        data: formData,
        contentType: false,
        processData: false,
        success: function (data, responseText) {
            $("#price_list").html(data);
        }
    });

});

//script for searching for loading price list
$("#category_search_filter").on('change', function (event) {
    event.preventDefault();
    var formData = new FormData($("#category_search_filter").get(0));
    formData.append("action", "price_list_filter");
    $.ajax({ //start ajax post
        type: "POST",
        url: "scripts/price_list.script.php",
        data: formData,
        contentType: false,
        processData: false,

        success: function (data, responseText) {
            $("#price_list").html(data);
        }
    });

});