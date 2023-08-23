//Editing multiple images
$("#gallery-body").on("click", "#delete-btn", function (e) {
  e.preventDefault();
  var formData = new FormData($("#gallery").get(0));

  let action = $(this).data("action");
  formData.append("action", action);
  $.ajax({ //start ajax post
    type: "POST",
    url: "scripts/gallery.scriptnew.php",
    data: formData,
    contentType: false,
    processData: false,
    success: function (data, responseText) {
      ///need script to catch errors
      $("#gallery-body").load("scripts/gallery.scriptnew.php?action=load_gallery");
    }
  });

});


//check all check boxes
$("#check_all").on("click", function () {
  $("#gallery-body .img-select").each(function () {
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
$("#gallery-body").on("click", ".img-select", function () {
  $(this).parent().toggleClass("img-checked");
})


//? Uploading images. Use AJAX request
$("#upload").on("submit", function (e) {
  e.preventDefault();
  //show an error message if no images have been selected and stop the script
  if (!$("#gallery_img").val()) {
    let errmsg = "Error, no images have been selected for upload.";
    $("#response-card-text").text(errmsg);
    $("#response-card-title").text("Error");

    $(".response-card").addClass("error-card");
    $(".response-card-wrapper").fadeIn(400);
    $(".response-card-wrapper").delay(5000).fadeOut(400);
    return false
  }
  var formData = new FormData($("#upload").get(0));
  let action = $(this).data("action");
  formData.append("action", action);
  $.ajax({ //start ajax post
    type: "POST",
    url: "scripts/gallerycrud.php",
    data: formData,
    contentType: false,
    processData: false,
    beforeSend: function () {
      $("#loading-icon").show(400);
    },
    success: function (data, responseText) {
      //on success, show a message with the amount of images uploaded etc
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
        $("#upload-modal").removeClass("modal-active");
        $(".response-card-wrapper").delay(5000).fadeOut(400);
        $("#loading-icon").hide(400);
        $("#gallery").load("scripts/gallery.scriptnew.php?action=load_gallery");
        $("#img_total").load("scripts/gallery.scriptnew.php?img_total");
        document.getElementById("upload").reset();
      }
      $("#response-card-title").text(response.img_success_amt + " of " + response.img_total + " images were uploaded");
      $("#response-card-text").text(response.message);
    }
  });
})
//toggle the upload modal
$("#upload-btn").on("click", function () {
  $("#upload-modal").addClass("modal-active");
})
$("#close-upload").on("click touchstart", function () {
  $("#upload-modal").removeClass("modal-active");
})


//filter select - allows users to filter out images and just show guest images
$("#term").on("change", function () {
  let action = $(this).data("action");
  let term = $(this).val();
  $("#gallery-body").load("scripts/gallery.scriptnew.php?action=" + action + "&term=" + term + "");
})


//?Deleting Images
//! Confirm delete pop up need to show to confirm that all images will be deleted.
$("#delete-img").on("click", function () {
  let img_num = 0;
  let text = "Photos";
  $("#gallery-body .img-select").each(function () {
    if ($(this).prop("checked")) {
      img_num++;
    }

  })
  if (img_num <= 1) {
    text = "Photo";
  }
  if (img_num == 0) {
    let errmsg = "No images have been selected to delete.";
    $("#response-card-text").text(errmsg);
    $("#response-card-title").text("Error");
    $(".response-card").addClass("error-card");
    $(".response-card-wrapper").fadeIn(400);
    $(".response-card-wrapper").delay(5000).fadeOut(400);
    return false
  }
  $("#confirm-text").text("Are you sure you want to delete " + img_num + " " + text + "? This cannot be reversed.")
  $("#confirm-btn-text").text("Delete " + img_num + " " + text);
  $("#confirm-title").text("Delete " + img_num + " " + text);
  $("#confirm-modal").addClass("modal-active");
})

$("#close-confirm").on("click touchstart", function () {
  $("#confirm-modal").removeClass("modal-active");
})
//user has clicked cancel, hide the confirm modal and unselect all ticked images.
$("#delete-img-no").on("click", function () {
  $("#confirm-modal").removeClass("modal-active");
  $("#gallery-body .img-select").each(function () {
    if ($(this).prop("checked")) {
      $(this).parent().removeClass("img-checked");
      $(this).prop('checked', false);
    }
  })
})
$("#delete-img-yes").on("click", function () {
  var formData = new FormData($("#gallery").get(0));
  let action = $(this).data("action");
  let key = 0;
  $("#gallery-body .img-select").each(function () {
    let filename = $(this).data("image_filename");
    if ($(this).prop("checked")) {
      formData.append("gallery_img[" + key + "][image_filename]", filename);
    }
    key++;
  })
  formData.append("action", action);
  $.ajax({ //start ajax post
    type: "POST",
    url: "scripts/gallerycrud.php",
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
        $("#gallery-body").load("scripts/gallery.scriptnew.php?action=load_gallery");
        $("#img_total").load("scripts/gallery.scriptnew.php?img_total");
        $("#confirm-modal").removeClass("modal-active");
      }
      $("#response-card-title").text(response.img_success_amt + " of " + response.img_total + " images were deleted");
      $("#response-card-text").text(response.message);
    }
  });
})


//hide the  modals when tapping off them

var upload_modal = document.getElementById('upload-modal');
var confirm_modal = document.getElementById('confirm-modal');
window.onclick = function (event) {
  if (event.target == upload_modal) {
    upload_modal.classList.remove("modal-active");
  }
  if (event.target == confirm_modal) {
    confirm_modal.classList.remove("modal-active");
  }
}