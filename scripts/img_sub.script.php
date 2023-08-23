<?php

//? saving images as approved from guest submissions
if (isset($_POST['action']) && $_POST['action'] == "save_submission") {
    require("../scripts/functions.php");
    //check the post method has been sent
    if ($_SERVER['REQUEST_METHOD'] !== "POST") {

        $status = array("img_total" => $img_total, "img_success_amt" => $success_amt, "img_error_count" => $img_error_amt, "response_code" => $code, "message" => $msg);
        //echo out the response in a JSON file
        echo json_encode($status);
        exit();
    }

    $img = new Img;
    //save all images from post request as approved.
    $img->save_submission();
    //total amount of images requested for delete
    $img_total = $img->img_total();
    //return the message from img function
    $msg = $img->msg();
    //return the response code
    $code = $img->response_code();
    //return the amount of images with errors
    $img_error_amt = $img->img_error_amt();
    //return the amount of images that were successfully deleted
    $success_amt = $img->img_success_amt();
    $status = array("img_total" => $img_total, "img_success_amt" => $success_amt, "img_error_count" => $img_error_amt, "response_code" => $code, "message" => $msg);
    echo json_encode($status);
}
?>
<?php if (isset($_GET['action']) && $_GET['action'] == "reload_submission") :
    ///Load image submissions
    //load guest Submission details
    include("../connect.php");
    $submission_id = $_GET['submission_id'];
    $sub_q = $db->query('SELECT guest_list.guest_id, guest_list.guest_fname, guest_list.guest_sname, image_submissions.submission_id, image_submissions.guest_id FROM guest_list LEFT JOIN image_submissions ON image_submissions.guest_id=guest_list.guest_id WHERE image_submissions.submission_id=' . $submission_id);
    $sub_r = mysqli_fetch_assoc($sub_q);
    //submission items
    $sub_i_q = $db->query('SELECT image_sub_items.submission_id, image_sub_items.image_id, image_sub_items.sub_item_status, images.image_id,images.image_description, images.image_filename, images.submission_id, images.status FROM image_sub_items LEFT JOIN images ON images.image_id=image_sub_items.image_id WHERE image_sub_items.submission_id=' . $submission_id . ' AND image_sub_items.sub_item_status="Awaiting"');
?>
    <form action="scripts/gallery.scriptnew.php" id="submissions" method="POST" data-action="save_submission" data-submission_id="<?= $sub_r['submission_id']; ?>">
        <div class="grid-row-3col my-2 std-card">
            <?php if ($sub_i_q->num_rows > 0) :
                $key = 0;
                foreach ($sub_i_q as $image) :
            ?>
                    <div class="img-card" data-status="<?= $image['status']; ?>">
                        <input class="img-select" data-submission_id="<?= $image['submission_id']; ?>" type="checkbox" name="gallery_img[<?= $key; ?>][image_id]" value="<?= $image['image_id']; ?>">
                        <img loading="lazy" class="gallery-img" src="assets/img/gallery/<?= $image['image_filename']; ?>" alt="" data-img_id="<?= $image['image_id']; ?>">
                    </div>
            <?php $key++;
                endforeach;
            endif; ?>
        </div>
    </form>
<?php endif; ?>
<?php if (isset($_GET['img_total'])) {
    include("../connect.php");
    $submission_id = $_GET['submission_id'];
    $sub_count = $db->query("SELECT  COUNT(sub_item_id) AS count FROM image_sub_items WHERE submission_id=" . $submission_id . " AND sub_item_status = 'Awaiting'");
    $count_r = mysqli_fetch_assoc($sub_count);
    $t = $count_r['count'];
    echo $t;
}

?>