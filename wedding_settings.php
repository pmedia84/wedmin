<?php
session_start();
require("scripts/functions.php");
check_login();
include("connect.php");
include("inc/settings.php");
$guest_home_img = ('SELECT * FROM images WHERE image_placement ="Guest Home"');
$guest_home_img = $db->query($guest_home_img);
$guest_home_img_res = $guest_home_img->fetch_assoc();
include("inc/head.inc.php");
?>
<!-- Meta Tags For Each Page -->
<meta name="description" content="Parrot Media - Client Admin Area">
<meta name="title" content="Manage your website content">

<title>Mi-Admin | Wedding Website Settings</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@shoelace-style/shoelace@2.0.0/dist/themes/light.css" />
<script type="module" src="https://cdn.jsdelivr.net/npm/@shoelace-style/shoelace@2.0.0/dist/shoelace.js"></script>
</head>

<body>
    <!-- Main Body Of Page -->
    <main class="main col-2">
        <!-- Header Section -->
        <?php include("inc/header.inc.php"); ?>
        <!-- Nav Bar -->
        <?php include("./inc/nav.inc.php"); ?>
        <!-- /nav bar -->
        <div class="body">
            <div class="breadcrumbs mb-2"><a href="index.php" class="breadcrumb">Home</a> / Website Settings</div>
            <div class="main-cards cms-settings-cards my-2">
                <?php if ($user->user_type() == "Admin" || $user->user_type() == "Developer") : ?>
                    <h1><svg class="icon"><use xlink:href="assets/img/icons/solid.svg#laptop"></use></svg> Website Settings</h1>
                    <p>Manage your setting for your website and your guest area.</p>
                    <p>You can turn on RSVP and Guest area features from here too</p>
                    <?php
                    //connect to db and load module settings
                    $modules_query = ('SELECT * FROM wedding_modules');
                    $modules = $db->query($modules_query);
                    ?>
                    <h2>Features</h2>
                    <form action="wedding_settings.script.php" method="POST" enctype="multipart/form-data" id="cms_modules">
                        <?php foreach ($modules as $module) : ?>
                            <?php if($module['wedding_module_name'] =="Meal Choices") :?>
                                <?php if($meal_choices_wedmin->status()=="On"):?>
                                <div class="settings-card">
                                <div class="settings-card-text">
                                    <h3><?= $module['wedding_module_name']; ?></h3>
                                    <p><?= $module['wedding_module_desc']; ?></p>
                                </div>
                                <label class="switch">
                                    <input class="switch-check" type="checkbox" value="<?= $module['wedding_module_id']; ?>" <?php if ($module['wedding_module_status'] == "On") : ?>checked<?php endif; ?>>
                                    <span class="slider round"></span>
                                </label>
                            </div>
                            <?php endif;?>
                            <?php else:?>
                                <div class="settings-card">
                                <div class="settings-card-text">
                                    <h3><?= $module['wedding_module_name']; ?></h3>
                                    <p><?= $module['wedding_module_desc']; ?></p>
                                </div>
                                <label class="switch">
                                    <input class="switch-check" type="checkbox" value="<?= $module['wedding_module_id']; ?>" <?php if ($module['wedding_module_status'] == "On") : ?>checked<?php endif; ?>>
                                    <span class="slider round"></span>
                                </label>
                            </div>
                            <?php endif;?>
                        <?php endforeach; ?>
                    </form>
            </div>

            <div class="main-cards cms-settings-cards">
                <h2>Guest Area</h2>
                <div class="settings-card">
                    <div class="settings-card-text">
                        <h3>Guest Area Home Page</h3>
                        <p>Your home page for all guests, this contains a countdown timer and a picture of yourselves.</p>
                        <p>Customise these features here.</p>

                        <h4 class="mb-2">Welcome Image</h4>
                        <img src="../guests/assets/img/guest-home-img.jpg" alt="">
                        <form action="scripts/wedding_settings.script.php" method="POST" enctype="multipart/form-data" id="guest_area_img">
                            <div class="form-input-wrapper my-2">
                                <label for="gallery_img">Change Image</label>
                                <p class="form-hint-small">This can be in a JPG, JPEG or PNG format. And no larger than 1MB.</p>
                                <!-- input -->
                                <input type="file" name="guest_home_img" id="guest_home_img" accept="image/*">
                            </div>
                            <div class="button-section">
                                <button class="btn-primary form-controls-btn loading-btn" type="submit"><span id="loading-btn-text" class="loading-btn-text"><svg class="icon"><use xlink:href="assets/img/icons/solid.svg#upload"></use></svg>Upload Image</span> <img id="loading-icon" class="loading-icon d-none" src="./assets/img/icons/loading.svg" alt=""></button>

                            </div>
                        </form>
                    </div>
                   
                </div>

              
            </div>
            <div class=" response-alert response d-none" id="response"></div>
        <?php else : ?>
            <p class="font-emphasis">You do not have the necessary Administrator rights to view this page.</p>
        <?php endif; ?>
        </div>
    </main>
    <!-- /Main Body Of Page -->

    <!-- Footer -->
    <?php include("./inc/footer.inc.php"); ?>
    <!-- /Footer -->
    <script>
        //script for updating module status
        $(".switch-check").on('click', function(event) {

            var module_id = $(this).attr("value");
            var module_status = "Off";
            if ($(this).is(":checked")) {
                module_status = "On";
            }
            //collect form data and GET request information to pass to back end script
            var formData = new FormData();
            formData.append("module_id", module_id);
            formData.append("module_status", module_status);
            $.ajax({ //start ajax post
                type: "POST",
                url: "scripts/wedding_settings.script.php",
                data: formData,
                contentType: false,
                processData: false,

                success: function(data, responseText) {


                    $("#response").html(data);
                    $("#response").slideDown(400);


                }
            });
        });
    </script>
    <script>
        //script for uploading a new image and posting to backend
        $("#guest_area_img").on("change submit", function(event) {
            event.preventDefault();
            var formData = new FormData($("#guest_area_img").get(0));
            formData.append("action", "newimg");
            $.ajax({ //start ajax post
                type: "POST",
                url: "scripts/wedding_settings.script.php",
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
                    if(data === "success"){
                        window.location.reload();
                    }
                   $("#response").html(data);
                   
                   $("#response").slideDown(400);     
                   const container = document.querySelector('.alert-duration');
                    const button = container.querySelector('sl-button');
                    const alert = container.querySelector('sl-alert');
                    alert.show()
                }

            });
        });
    </script>

</body>

</html>