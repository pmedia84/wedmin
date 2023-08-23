<?php
session_start();
require("scripts/functions.php");
check_login();
include("./connect.php");
include("inc/settings.php");
//load images
$gallery_query = $db->query('SELECT images.image_id,  images.image_title, images.image_description, images.image_filename, images.image_upload_date, images.image_placement, images.guest_id, images.status, guest_list.guest_id, guest_list.guest_fname, guest_list.guest_sname FROM images LEFT JOIN guest_list ON guest_list.guest_id=images.guest_id');
$img_total = $gallery_query->num_rows;

//load any guest submissions
$sub_q = $db->query('SELECT image_submissions.submission_id, image_submissions.guest_id, image_submissions.submission_status, guest_list.guest_fname, guest_list.guest_sname, image_sub_items.sub_item_id, COUNT(CASE image_sub_items.sub_item_status WHEN "Awaiting" THEN 1 ELSE NULL END) AS img_total  FROM image_submissions LEFT JOIN guest_list ON image_submissions.guest_id=guest_list.guest_id LEFT JOIN image_sub_items ON image_sub_items.submission_id=image_submissions.submission_id WHERE image_submissions.submission_status="Awaiting" OR image_submissions.submission_status = "Partial" GROUP BY guest_list.guest_id ');

//load the total amt of submissions
$sub_total = $sub_q->num_rows;
include("inc/head.inc.php");
?>
<!-- Meta Tags For Each Page -->
<meta name="description" content="Parrot Media - Client Admin Area">
<meta name="title" content="Manage your website content">
<!-- /Meta Tags -->

<!-- / -->
<!-- Page Title -->
<title>Mi-Admin | Photo Gallery</title>
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
                <a href="index.php" class="breadcrumb">Home</a> / Manage Image Gallery
            </div>
            <div class="main-cards">
                <h1 class="my-2 notification-header"><svg class="icon">
                        <use href="assets/img/icons/solid.svg#images" />
                    </svg> Photo Gallery <span class="notification" id="img_total"><?= $img_total; ?></span></h1>
                <?php if ($guest_image_gallery->status() == "On") : ?>
                    <div class="std-card">
                        <h2 class="notification-header">Guest Submissions <span class="notification"><?= $sub_total; ?></span></h2>
                        <?php if ($sub_q->num_rows > 0) : ?>
                            <p>You have the following guest submissions to your gallery that you can approve:</p>
                            <table class="std-table submission-table">
                                <th>Name</th>
                                <th>Photos</th>
                                <th>Status</th>
                                <?php foreach ($sub_q as $submission) : ?>
                                    <tr>
                                        <td><a href="img_submission?submission_id=<?= $submission['submission_id']; ?>"><?= $submission['guest_fname'] . " " . $submission['guest_sname']; ?></a></td>
                                        <td><?= $submission['img_total']; ?></td>
                                        <td><span class="status" data-status="<?= $submission['submission_status']; ?>"><?= $submission['submission_status']; ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </table>

                        <?php else : ?>
                            <p>You have no guest submissions right now.</p>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                <?php if ($user->user_type() == "Admin" || $user->user_type() == "Developer") : ?>
                    <div class="form-controls my-2 toolbar">
                        <button class="btn-primary" id="upload-btn" title="Save" data-action="upload">
                            <svg class="icon">
                                <use href="assets/img/icons/solid.svg#arrow-up-from-bracket" />
                            </svg>Upload Photos</button>
                        <button class="btn-primary btn-secondary" id="check_all">
                            <svg class="icon">
                                <use href="assets/img/icons/solid.svg#check-double" />
                            </svg>Select All</button>
                        <button class="btn-primary btn-secondary" id="delete-img" data-action="delete">
                            <svg class="icon">
                                <use href="assets/img/icons/solid.svg#trash" />
                            </svg> Delete Photos
                        </button>
                        <div class="form-input-wrapper">
                            <label for="placement">Filter Images</label>
                            <select name="term" id="term" data-action="term">
                                <option value="">Show All</option>
                                <option value="guest">Guest Images</option>
                                <option value="ours">Our Images</option>
                            </select>
                        </div>
                    </div>
                    <div class="gallery-body" id="gallery-body">
                        <form action="" id="gallery" method="POST">
                            <div class="grid-row-3col my-2 std-card">
                                <?php if ($gallery_query->num_rows > 0) :
                                    $key = 0;
                                    foreach ($gallery_query as $image) :
                                ?>
                                        <div class="img-card" data-status="<?= $image['status']; ?>">
                                            <a href="image?image_id=<?= $image['image_id']; ?>&action=edit" class="btn-primary btn-secondary btn-edit">
                                                <svg class="icon">
                                                    <use href="assets/img/icons/solid.svg#pen" />
                                                </svg>
                                            </a>
                                            <input class="img-select" data-select="false" data-image_filename="<?= $image['image_filename']; ?>" type="checkbox" name="gallery_img[<?= $key; ?>][image_id]" value="<?= $image['image_id']; ?>">
                                            <img class="gallery-img" src="assets/img/gallery/<?= $image['image_filename']; ?>" alt="" data-img_id="<?= $image['image_id']; ?>">
                                            <p class="img-card-caption"><?= $image['image_description']; ?></p>
                                        </div>
                                <?php $key++;
                                    endforeach;

                                endif; ?>
                            </div>
                        </form>
                    </div>
                <?php else : ?>
                    <p class="font-emphasis">You do not have the necessary Administrator rights to view this page.</p>
                <?php endif; ?>
            </div>
        </section>
        <div class="response-card-wrapper d-none" id="response-card-wrapper">
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
            <div class="modal-header">
                <button class="btn-close" type="button" id="close-upload">
                    <svg class="icon line">
                        <use href="assets/img/icons/solid.svg#minus" />
                    </svg>
                    <svg class="icon x-mark">
                        <use href="assets/img/icons/solid.svg#xmark" />
                    </svg>
                </button>
                <h2 class="modal-title">Upload Photos</h2>
            </div>

            <form action="scripts/gallerycrud.php" id="upload" method="POST" enctype="multipart/form-data" data-action="upload">
                <div class="form-input-wrapper">
                    <label for="gallery_img">Upload Images</label>
                    <p class="form-hint-small">These can be in a JPG, JPEG or PNG format</p>
                    <!-- input -->
                    <input type="file" name="gallery_img[]" id="gallery_img" accept="image/*" multiple>
                    <div class="modal-footer">
                        <button class="btn-primary my-2 form-controls-btn loading-btn" type="submit" id="upload-btn" data-action="upload"><span id="loading-btn-text" class="loading-btn-text"><svg class="icon spinner-icon ">
                                    <use href="assets/img/icons/solid.svg#upload" />
                                </svg>Submit</span> <svg class="icon spinner-icon d-none loader-spinner " id="loading-icon">
                                <use href="assets/img/icons/solid.svg#circle-notch" />
                            </svg></button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="modal confirm-modal" id="confirm-modal">
        <div class="modal-content">
            <div class="modal-header">
                <button class="btn-close" type="button" id="close-confirm">
                    <svg class="icon line">
                        <use href="assets/img/icons/solid.svg#minus" />
                    </svg>
                    <svg class="icon x-mark">
                        <use href="assets/img/icons/solid.svg#xmark" />
                    </svg>
                </button>

                <h2 class="modal-title" id="confirm-title"></h2>
            </div>
            <p class="my-2" id="confirm-text"></p>
            <div class="button-section">
                <button class="btn-primary btn-delete" id="delete-img-yes" data-action="delete">
                    <svg class="icon">
                        <use href="assets/img/icons/solid.svg#trash" />
                    </svg><span id="confirm-btn-text"></span>
                </button>
                <button class="btn-primary btn-secondary" id="delete-img-no">
                    <svg class="icon">
                        <use href="assets/img/icons/solid.svg#ban" />
                    </svg>Cancel
                </button>
            </div>
        </div>
    </div>
    <?php include("./inc/footer.inc.php"); ?>
    <!-- /Footer -->
    <script src="assets/js/gallery.js"></script>

</body>

</html>