$("#guest_list").on("click", ".group-btn", function(e){
    e.preventDefault();
    $(this).children(".icon").toggleClass("icon-clicked");
    $(this).parents(".guest-card").find(".guest-group-card").slideToggle(300);
    
})

    //script for searching for guests
    $("#guest_search").on('keyup', function(event) {
        event.preventDefault();
        var formData = new FormData($("#guest_search").get(0));
        formData.append("action", "search");

        $.ajax({ //start ajax post
            type: "POST",
            url: "scripts/guest_list.script.php",
            data: formData,
            contentType: false,
            processData: false,
            success: function(data, responseText) {
                $("#guest_list").html(data);
       
            }
        });

    });
    //script for searching for guests
    $("#guest_filter").on('change', function(event) {
        event.preventDefault();
        var formData = new FormData($("#guest_filter").get(0));
        formData.append("action", "guest_filter");

        $.ajax({ //start ajax post
            type: "POST",
            url: "scripts/guest_list.script.php",
            data: formData,
            contentType: false,
            processData: false,
            success: function(data, responseText) {
                $("#guest_list").html(data);
       
            }
        });

    });