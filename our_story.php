<?php
session_start();
require("scripts/functions.php");
check_login();
include("connect.php");
include("inc/head.inc.php");
include("inc/settings.php");
?>
<!-- Meta Tags For Each Page -->
<meta name="description" content="Parrot Media - Client Admin Area">
<meta name="title" content="Manage your website content">
<!-- /Meta Tags -->
<!-- Tiny MCE -->
<script src="https://cdn.tiny.cloud/1/7h48z80zyia9jc41kx9pqhh00e1e2f4pw9kdcmhisk0cm35w/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<!-- / -->
<!-- Page Title -->
<title>Mi-Admin | Our Story</title>
<!-- /Page Title -->
</head>
<script>
    tinymce.init({
        selector: 'textarea#story_body',
        height: 800,

        plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
        toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table mergetags | addcomment showcomments | spellcheckdialog a11ycheck | align lineheight | checklist numlist bullist indent outdent | emoticons charmap | removeformat | ',
        tinycomments_mode: 'embedded',

        tinycomments_author: 'Author name',
        mergetags_list: [{
                value: 'First.Name',
                title: 'First Name'
            },
            {
                value: 'Email',
                title: 'Email'
            },
        ]
    });
</script>

<body>
    <!-- Main Body Of Page -->
    <main class="main col-2">
        <!-- Header Section -->
        <?php include("inc/header.inc.php"); ?>
        <!-- Nav Bar -->
        <?php include("./inc/nav.inc.php"); ?>
        <!-- /nav bar -->
        <div class="body">
            <div class="breadcrumbs mb-2"><a href="index.php" class="breadcrumb">Home</a> / Our Story</div>
            <div class="main-cards cms-settings-cards">
                <?php if ($user->user_type() == "Admin" || $user->user_type() == "Developer") : ?>
                    <h1><svg class="icon"><use xlink:href="assets/img/icons/solid.svg#heart"></use></svg> Our Story</h1>
                    <p>Edit or create your story for your wedding website.</p>
                    <?php
                    //connect to db and load story
                    $story_query = ('SELECT * FROM wedding_story LIMIT  1');
                    $story = $db->query($story_query);
                    $story_result = $story->fetch_assoc();

                    if ($story->num_rows > 0) :
                        $story_body = html_entity_decode($story_result['story_body']);
                    ?>
                        <form class="form-card" id="edit_story" action="scripts/our_story.script.php" method="POST" enctype="multipart/form-data">
                            <div class="form-input-wrapper my-2">
                                <textarea id="story_body" name="story_body">
                        <?= $story_body; ?>
                        </textarea>
                            </div>
                            <div class="form-input-wrapper my-2">
                                <label for="news_articles_status">Status</label>
                                <p class="form-hint-small">Set as a draft to come back and finish, or set as published to publish to your website straight away.</p>
                                <select <?php if ($story_result['story_status'] == "Draft") : ?>style="border-color: red; color: red;" <?php endif; ?> name="story_status" id="story_status" required="">
                                    <option value="<?= $story_result['story_status']; ?>" selected><?= $story_result['story_status'];  ?></option>
                                    <?php if ($story_result['story_status'] == "Published") : ?>
                                        <option value="Draft">Draft</option>
                                    <?php else : ?>
                                        <option value="Published">Published</option>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="button-section my-3">
                                <button class="btn-primary form-controls-btn" type="submit"><svg class="icon"><use xlink:href="assets/img/icons/solid.svg#floppy-disk"></use></svg> Save </button>
                            </div>
                            <div id="response" class="d-none">
                            </div>
                        </form>
                    <?php else : ?>
                        <form class="form-card" id="create_story" action="scripts/our_story.script.php" method="post" enctype="multipart/form-data">
                            <div class="form-input-wrapper my-2">
                                <label for="story_body">Story</label>
                                <textarea id="story_body" name="story_body"></textarea>
                            </div>

                            <div class="form-input-wrapper my-2">
                                <label for="story_status">Status</label>
                                <p class="form-hint-small">Set as a draft to come back and finish, or set as published to publish to your website straight away.</p>
                                <select name="story_status" id="story_status" required="">
                                    <option value="Draft">Draft</option>
                                    <option value="Published">Published</option>
                                </select>
                            </div>
                            <div class="button-section my-3">
                                <button class="btn-primary form-controls-btn" type="submit"><i class="fa-solid fa-floppy-disk"></i> Save </button>
                            </div>
                            <div id="response" class="d-none">
                                <p>Article Saved <img src="./assets/img/icons/check.svg" alt=""></p>
                            </div>
                        </form>
                    <?php endif; ?>

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
        //script for uploading a new image and posting to backend
        $("#create_story").on("submit", function(event) {
            tinyMCE.triggerSave();
            event.preventDefault();
            var formData = new FormData($("#create_story").get(0));
            formData.append("action", "create");
            $.ajax({ //start ajax post
                type: "POST",
                url: "scripts/our_story.script.php",
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
                    if (data === "success") {
                        window.location.reload();
                    }
                    $("#response").html(data);

                    $("#response").slideDown(400);

                }

            });
        });
        //script for uploading a new image and posting to backend
        $("#edit_story").on("submit", function(event) {
            tinyMCE.triggerSave();
            event.preventDefault();
            var story_id = '<?=$story_result['story_id'];?>';
            var formData = new FormData($("#edit_story").get(0));
            formData.append("action", "edit");
            formData.append("story_id", story_id);
            $.ajax({ //start ajax post
                type: "POST",
                url: "scripts/our_story.script.php",
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
                    if (data === "success") {
                        window.location.reload();
                    }
                    $("#response").html(data);

                    $("#response").slideDown(400);

                }

            });
        });
    </script>

</body>

</html>