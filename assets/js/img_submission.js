//check all check boxes
$("#check_all").on("click", function () {
  $(".img-select").each(function () {
    if ($(this).prop("checked")) {
      $(this).parent().removeClass("img-checked");
      $(this).prop('checked', false);
    } else {
      $(this).parent().addClass("img-checked");
      $(this).prop('checked', true);
    }
  })

})
//add and remove selected class with checkboxes
$("#submission").on("click", ".img-select", function () {
  $(this).parent().toggleClass("img-checked");
})

//?process approved images 
$("#save").on("click", function () {
  var formData = new FormData($("#submissions").get(0));
  let action = $("#submissions").data("action");
  let submission_id = $("#submissions").data("submission_id");
  let key = 0;
  $(".img-select").each(function () {
    let filename = $(this).data("submission_id");
    if ($(this).prop("checked")) {
      formData.append("gallery_img[" + key + "][submission_id]", filename);
    }
    key++;
  })
  formData.append("action", action);
  formData.append("submission_id", submission_id);
  $.ajax({ //start ajax post
    type: "POST",
    url: "scripts/img_sub.script.php",
    data: formData,
    contentType: false,
    processData: false,
    success: function (data, responseText) {
      //on success, show a message with the amount of images deleted etc
      const response = JSON.parse(data);
      if (response.response_code == 400) {
        $(".response-card-wrapper").fadeIn(400);
        $(".response-card").addClass("error-card");
        $(".response-card-wrapper").delay(5000).fadeOut(400);
        $("#loading-icon").hide(400);
      }
      if (response.response_code == 200) {
        $(".response-card").removeClass("error-card");
        $(".response-card-wrapper").fadeIn(400);
        $(".response-card-wrapper").delay(5000).fadeOut(400);
        $("#submission").load("scripts/img_sub.script.php?action=reload_submission&submission_id="+submission_id);
        $("#total").load("scripts/img_sub.script.php?img_total&submission_id="+submission_id);
      }
      $("#response-card-title").text(response.img_success_amt + " of " + response.img_total + " images saved");
      $("#response-card-text").text(response.message);
    }
  });
})