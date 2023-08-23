<?php
session_start();
require("scripts/functions.php");
check_login();
include("connect.php");
include("inc/head.inc.php");
include("inc/settings.php");
//image variable
if (isset($_GET['image_id'])) {
    $image_id = $_GET['image_id'];
    //find image details

    $image = $db->prepare('SELECT * FROM images WHERE image_id =' . $image_id);

    $image->execute();
    $image->store_result();
} else {
    $image_id = "";
}
?>
<!-- Meta Tags For Each Page -->
<meta name="description" content="Parrot Media - Client Admin Area">
<meta name="title" content="Manage your website content">
<title>Mi-Admin | Manage Image Gallery</title>
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
                <a href="index.php" class="breadcrumb">Home</a> /
                <a href="gallery.php" class="breadcrumb">Photo Gallery</a>
                <?php if ($_GET['action'] == "edit") : ?>
                    / Edit Image
                <?php endif; ?>
                <?php if ($_GET['action'] == "delete") : ?>
                    / Delete Image
                <?php endif; ?>
                <?php if ($_GET['action'] == "view") : ?>
                    / View Image
                <?php endif; ?>
            </div>
            <div class="main-cards">
                <?php if ($_GET['action'] == "edit") : ?>
                    <h1><svg class="icon">
                            <use href="assets/img/icons/solid.svg#image" />
                        </svg> Edit Photo</h1>
                <?php endif; ?>
                <?php if ($_GET['action'] == "view") : ?>
                    <h1><svg class="icon">
                            <use href="assets/img/icons/solid.svg#image" />
                        </svg> View Photo</h1>
                <?php endif; ?>
                <?php if ($_GET['action'] == "delete") : ?>
                    <h1><svg class="icon">
                            <use href="assets/img/icons/solid.svg#image" />
                        </svg> Delete Photo</h1>
                <?php endif; ?>
                <?php if ($user->user_type() == "Admin" || $user->user_type() == "Developer") : //detect if user is an admin or not 
                ?>
                    <?php if ($_GET['action'] == "edit") : ?>
                        <?php if (($image->num_rows) > 0) :
                            $image->bind_result($image_id, $image_title, $image_description, $image_filename, $image_upload_date, $image_placement, $guest_id, $status, $submission_id);
                            $image->fetch();

                        ?>
                            <div class="std-card">
                                <form id="edit_image" action="scripts/gallery.script.php" method="POST" enctype="multipart/form-data">
                                    <div class="form-input-wrapper my-2">
                                        <img src="./assets/img/gallery/<?= $image_filename ?>" alt="">
                                    </div>
                                    <div class="form-input-wrapper my-2">
                                        <label for="image_description"><strong>Image Caption</strong></label>
                                        <input class="text-input input" type="text" id="image_description" name="image_description" placeholder="Image Caption" value="<?= $image_description; ?>">
                                    </div>

                                    <div class="button-section my-3">
                                        <button class="btn-primary form-controls-btn" type="submit"><svg class="icon"><use xlink:href="assets/img/icons/solid.svg#floppy-disk"></use></svg> Save Changes </button>
                                        <a href="gallery" class="btn-primary btn-secondary form-controls-btn"><svg class="icon"><use xlink:href="assets/img/icons/solid.svg#ban"></use></svg> Cancel Changes</a>
                                    </div>
                                    <div id="response" class="d-none">
                                        <p>Article Saved <img src="./assets/img/icons/check.svg" alt=""></p>
                                    </div>
                                </form>
                            </div>
                        <?php else : ?>
                            <div class="std-card">
                                <h2>Error</h2>
                                <p>There has been an error, please return to the last page and try again.</p>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                    <?php if ($_GET['action'] == "view") : ?>
                        <?php if (($image->num_rows) > 0) :
                            $image->bind_result($image_id, $image_title, $image_description, $image_filename, $image_upload_date, $image_placement, $guest_id, $status, $submission_id);
                            $image->fetch();
                            $upload_date = strtotime($image_upload_date);
                        ?>
                            <div class="std-card">
                                <h2 class="my-2"><?= $image_title; ?></h2>
                                <img src="./assets/img/gallery/<?= $image_filename ?>" alt="">
                                <p class="my-2">Image Uploaded: <?= date('d-m-y', $upload_date); ?></p>
                                <div class="news-create-body"><?= $image_description; ?></div>
                                <p><strong>Image Placement:</strong></p>
                                <p><?= $image_placement; ?></p>
                                <div class="card-actions">
                                    <a class="my-2" href="image.php?action=edit&image_id=<?= $image_id; ?>"><i class="fa-solid fa-pen-to-square"></i> Edit Image </a><br>
                                    <a class="my-2" href="image.php?action=delete&confirm=no&image_id=<?= $image_id; ?>"><i class="fa-solid fa-trash"></i> Delete Image </a>
                                </div>
                            <?php else : ?>
                                <div class="std-card">
                                    <h2>Error</h2>
                                    <p>There has been an error, please return to the last page and try again.</p>
                                </div>
                            <?php endif; ?>
                            </div>

            </div>




        <?php endif; ?>

    <?php else : ?>
        <p class="font-emphasis">You do not have the necessary Administrator rights to view this page.</p>
    <?php endif; ?>


        </section>


    </main>

    <!-- /Main Body Of Page -->
    <!-- Quote request form script -->

    <!-- /Quote request form script -->
    <!-- Footer -->
    <?php include("./inc/footer.inc.php"); ?>
    <!-- /Footer -->

    <script>
        //script for editing a news article
        $("#edit_image").submit(function(event) {

            event.preventDefault();
            //declare form variables and collect GET request information
            image_id = '<?php echo $image_id; ?>';
            var formData = new FormData($("#edit_image").get(0));
            formData.append("action", "edit");
            formData.append("image_id", image_id);
            $.ajax({ //start ajax post
                type: "POST",
                url: "scripts/gallery.script.php",
                data: formData,
                contentType: false,
                processData: false,
                success: function(data, responseText) {
                    $("#response").html(data);
                    $("#response").slideDown(400);
                    window.location.replace('gallery');
                }
            });

        });
    </script>
    <script>
        //script for uploading a new image and posting to backend article
        $("#img-upload").submit(function(event) {
            event.preventDefault();


            var formData = new FormData($("#img-upload").get(0));

            formData.append("action", "newimg");

            $.ajax({ //start ajax post
                type: "POST",
                url: "scripts/gallery-multiple.php",
                data: formData,
                contentType: false,
                processData: false,
                beforeSend: function() { //animate button
                    $("#loading-icon").show(400);
                },
                complete: function() {
                    $("#loading-icon").hide(400);
                },
                success: function(data, responseText) {
                    $("#response").html(data);
                    $("#response").slideDown(400);
                    if (data === "0") {
                        window.location.replace("gallery");
                    } else {
                        $("#response").html(data);
                        $("#response").slideDown(400);
                    }
                    $("#img-upload")[0].reset();


                }
            });

        });
    </script>
</body>

</html>