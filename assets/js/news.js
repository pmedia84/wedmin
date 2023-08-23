//script for creating a news article
$("#create_news_article").submit(function (event) {
    tinyMCE.triggerSave();
    event.preventDefault();
    //declare form variables and collect GET request information
    user_id = $(this).data("user_id");
    var formData = new FormData($("#create_news_article").get(0));
    formData.append("action", "create");
    formData.append("user_id", user_id);
    $.ajax({ //start ajax post
        type: "POST",
        url: "scripts/news.script.php",
        data: formData,
        contentType: false,
        processData: false,
        success: function (data, responseText) {
            if (data === "400") {
                $("#response").html(data);
                $("#response").slideDown(400);
            } else {
                //redirect to new article page
                window.location.replace(data);
            }
        }
    });

});

//script for editing a news article
$("#edit_news_article").submit(function (event) {
    tinyMCE.triggerSave();
    event.preventDefault();
    //declare form variables and collect GET request information
    var news_article_id = $(this).data("article_id");
    var img_file = $(this).data("img_file");
    var formData = new FormData($("#edit_news_article").get(0));
    formData.append("action", "edit");
    formData.append("news_articles_id", news_article_id);
    formData.append("img_filename", img_file);
    $.ajax({ //start ajax post
        type: "POST",
        url: "scripts/news.script.php",
        data: formData,
        contentType: false,
        processData: false,
        success: function (data, responseText) {
            if (data === "200") {
                window.location.replace('news_article.php?action=view&news_articles_id=' + news_article_id);
            }
            $("#response").html(data);
            $("#response").slideDown(400);

        }
    });

});