<?php
session_start();
require("scripts/functions.php");
check_login();
//detect if the request for loading submission is active or not. If not then redirect to photo gallery
if (!isset($_GET['submission_id'])) {
    header("Location: gallery");
}
$submission_id = $_GET['submission_id'];

$user = new User();
$user_type = $user->user_type();
$user_id = $user->user_id();
include("./connect.php");
include("inc/head.inc.php");
include("inc/settings.php");
////////////////Find details of the cms being used, on every page\\\\\\\\\\\\\\\
//Variable for name of CMS
//wedding is the name of people
//business name
$cms_name = "";
$user_id = $_SESSION['user_id'];


//run checks to make sure a wedding has been set up correctly
if ($cms_type == "Wedding") {
    //look for the Wedding set up and load information
    //find Wedding details.
    $wedding = $db->prepare('SELECT * FROM wedding');

    $wedding->execute();
    $wedding->store_result();
    $wedding->bind_result($wedding_id, $wedding_name, $wedding_date, $wedding_time,  $wedding_email, $wedding_phone, $wedding_contact_name);
    $wedding->fetch();
    $wedding->close();
    //set cms name
    $cms_name = $wedding_name;
    //find user details for this business
    $business_users = $db->prepare('SELECT users.user_id, users.user_name, wedding_users.wedding_id, wedding_users.user_type FROM users NATURAL LEFT JOIN wedding_users WHERE users.user_id=' . $user_id);

    $business_users->execute();
    $business_users->bind_result($user_id, $user_name, $business_id, $user_type);
    $business_users->fetch();
    $business_users->close();
}


//////////////////////////////////////////////////////////////////Everything above this applies to each page\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\

//load guest Submission details
$sub_q = $db->query('SELECT guest_list.guest_id, guest_list.guest_fname, guest_list.guest_sname, image_submissions.submission_id, image_submissions.guest_id FROM guest_list LEFT JOIN image_submissions ON image_submissions.guest_id=guest_list.guest_id WHERE image_submissions.submission_id=' . $submission_id);
$sub_r = mysqli_fetch_assoc($sub_q);
//submission items
$sub_i_q = $db->query('SELECT image_sub_items.submission_id, image_sub_items.image_id, image_sub_items.sub_item_status, images.image_id,images.image_description, images.image_filename, images.submission_id, images.status FROM image_sub_items LEFT JOIN images ON images.image_id=image_sub_items.image_id WHERE image_sub_items.submission_id=' . $submission_id . ' AND image_sub_items.sub_item_status="Awaiting"');
//load the total amt of submissions
$img_total = $sub_i_q->num_rows;
?>
<!-- Meta Tags For Each Page -->
<meta name="description" content="Parrot Media - Client Admin Area">
<meta name="title" content="Manage your website content">
<!-- /Meta Tags -->

<!-- / -->
<!-- Page Title -->
<title>Mi-Admin | Guest Submission</title>
<!-- /Page Title -->
</head>

<body>
    <!-- Main Body Of Page -->
    <main class="main col-2">


        <!-- Header Section -->
        <?php include("inc/header.inc.php"); ?>
        <!-- Nav Bar -->
        <?php include("./inc/nav.inc.php"); ?>
        <!-- /nav bar -->
        <section class="body">
            <div class="breadcrumbs mb-2">
                <a href="index.php" class="breadcrumb">Home</a> / <a href="gallery">Photo Gallery</a> / Guest Submission
            </div>
            <div class="main-cards">
                <h1 class="my-2 notification-header"><i class="fa-solid fa-images"></i> Guest Submission: <?= $sub_r['guest_fname'] . " " . $sub_r['guest_sname']; ?> <span class="notification" id=total><?= $img_total; ?></span></h1>

                <?php if ($user_type == "Admin" || $user_type == "Developer") : ?>

                    <div class="">
                        <div class="std-card my-2">
                            <p>You can approve all the submitted photos, or select individual photos to approve.</p>
                            <p>Photos that you do not select will remain in your gallery, but will not be visible to your website visitors.</p>
                        </div>
                        <div class="form-controls my-2 toolbar">
                            <a href="gallery" class="btn-primary btn-secondary"><svg class="icon">
                                    <use href="assets/img/icons/solid.svg#arrow-left" />
                                </svg> Return To Gallery</a>
                            <button class="btn-primary btn-secondary" id="check_all" title="Save">
                                <svg class="icon">
                                    <use href="assets/img/icons/solid.svg#check-double" />
                                </svg>Select All</button>
                            <button class="btn-primary" id="save">
                                <svg class="icon">
                                    <use href="assets/img/icons/regular.svg#floppy-disk" />
                                </svg>Save Photos</button>
                        </div>
                        <div id="submission">
                            <form action="scripts/gallery.scriptnew.php" id="submissions" method="POST" data-action="save_submission" data-submission_id="<?= $sub_r['submission_id']; ?>" >
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
                        </div>
                    </div>
                <?php else : ?>
                    <p class="font-emphasis">You do not have Administrator rights to view this page.</p>
                <?php endif; ?>
            </div>
        </section>
        <div class=" response-card-wrapper d-none" id="response-card-wrapper">
        <div class="response-card">
            <div class="response-card-icon">
                <i class="fa-solid fa-circle-info"></i>
            </div>
            <div class="response-card-body">
                <h2 id="response-card-title"></h2>
                <p id="response-card-text"></p>
            </div>
        </div>
    </div>
    </main>
    <div class="modal upload-modal" id="upload-modal">
        <div class="modal-content">
            <div class="close"><button class="btn-close" type="button" id="close-upload"><i class="fa-solid fa-minus"></i></button></div>
            <form action="scripts/gallerycrud.php" id="upload" method="POST" enctype="multipart/form-data" data-action="upload">
                <div class="form-input-wrapper gallery-card">
                    <label for="gallery_img">Upload Images</label>
                    <p class="form-hint-small">These can be in a JPG, JPEG or PNG format</p>
                    <!-- input -->
                    <input type="file" name="gallery_img[]" id="gallery_img" accept="image/*" multiple>
                    <div class="button-section"><button class="btn-primary my-2 form-controls-btn loading-btn" type="submit" id="upload-btn" data-action="upload"><span id="loading-btn-text" class="loading-btn-text"><i class="fa-solid fa-upload"></i>Submit</span> <i id="loading-icon" class="fa-solid fa-spinner fa-spin-pulse spinner-icon d-none"></i></button></div>
                </div>
            </form>
        </div>
    </div>

    </div>
    <!-- /Main Body Of Page -->

    <!-- Footer -->
    <?php include("./inc/footer.inc.php"); ?>
    <!-- /Footer -->
    <script>

    </script>
    <script src="assets/js/img_submission.js"></script>

</body>

</html>