
$("#form_sort, #filter").on('change', function (event) {
    event.preventDefault();
    var formData = new FormData($("#forms-filter").get(0));
    $.ajax({ //start ajax post
        type: "POST",
        url: "scripts/forms.script.php",
        data: formData,
        contentType: false,
        processData: false,

        success: function (data, responseText) {
            $("#forms-container").html(data);
        }
    });

});
$("#search-btn").on('click', function (event) {
    event.preventDefault();
    var formData = new FormData($("#forms-filter").get(0));
    $.ajax({ //start ajax post
        type: "POST",
        url: "scripts/forms.script.php",
        data: formData,
        contentType: false,
        processData: false,

        success: function (data, responseText) {
            $("#forms-container").html(data);
        }
    });

});

$("#search").on('click, keyup', function (event) {
    event.preventDefault();
    var formData = new FormData($("#forms-filter").get(0));
    $.ajax({ //start ajax post
        type: "POST",
        url: "scripts/forms.script.php",
        data: formData,
        contentType: false,
        processData: false,

        success: function (data, responseText) {
            $("#forms-container").html(data);
        }
    });

});