<?php
session_start();
require("scripts/functions.php");
check_login();
include("connect.php");
include("inc/settings.php");
//guest variable, only required for edit and view actions
if ($_GET['action'] == "edit" || $_GET['action'] == "view" || $_GET['action'] == "delete") {
    $gift_item_id = $_GET['gift_item_id'];
    //find guest details
    
    $gift_item = $db->prepare('SELECT * FROM gift_list WHERE gift_item_id =' . $gift_item_id);
    
    $gift_item->execute();
    $gift_item->store_result();
} else {
    $gift_item_id = "";
}
include("inc/head.inc.php");
?>
<!-- Meta Tags For Each Page -->
<meta name="description" content="Parrot Media - Client Admin Area">
<meta name="title" content="Manage your website content">
<!-- /Meta Tags -->

<!-- / -->
<!-- Page Title -->
<title>Mi-Admin | Manage Gift List Item</title>
<!-- /Page Title -->
<!-- Tiny MCE -->
<script src="https://cdn.tiny.cloud/1/7h48z80zyia9jc41kx9pqhh00e1e2f4pw9kdcmhisk0cm35w/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<!-- / -->
<!-- Tiny MCE Plugin -->
<script>
    tinymce.init({
        selector: 'textarea#gift_item_desc',
        height: 500,

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
            <div class="breadcrumbs mb-2">
                <a href="index.php" class="breadcrumb">Home</a> /
                <a href="gift_list.php" class="breadcrumb">Gift List</a>
                <?php if ($_GET['action'] == "edit") : ?>
                    / Edit Gift List Item
                <?php endif; ?>
                <?php if ($_GET['action'] == "delete") : ?>
                    / Delete Gift List Item
                <?php endif; ?>
                <?php if ($_GET['action'] == "view") : ?>
                    / View Gift List Item
                <?php endif; ?>
                <?php if ($_GET['action'] == "create") : ?>
                    / Add Gift List Item
                <?php endif; ?>
            </div>
            <div class="main-cards">
                <?php if ($_GET['action'] == "edit") : ?>
                    <h1><svg class="icon"><use xlink:href="assets/img/icons/solid.svg#gift"></use></svg> Edit Gift List Item</h1>
                <?php endif; ?>
                <?php if ($_GET['action'] == "view") : ?>
                    <h1><svg class="icon"><use xlink:href="assets/img/icons/solid.svg#gift"></use></svg> View Gift List Item</h1>
                <?php endif; ?>
                <?php if ($_GET['action'] == "delete") : ?>
                    <h1><svg class="icon"><use xlink:href="assets/img/icons/solid.svg#gift"></use></svg> Remove Item </h1>
                <?php endif; ?>
                <?php if ($_GET['action'] == "create") : ?>
                    <h1><svg class="icon"><use xlink:href="assets/img/icons/solid.svg#gift"></use></svg> Add An Item</h1>
                <?php endif; ?>


                <?php if ($user->user_type() == "Admin" || $user->user_type() == "Developer") : //detect if user is an admin or developer 
                ?>
                    <?php if ($_GET['action'] == "delete") : //if action is delete, detect if the confirm is yes or no
                    ?>
                        <?php if ($_GET['confirm'] == "yes") : //if yes then delete the guest
                        ?>
                            <?php if (($gift_item->num_rows) > 0) :
                                //load guest information
                                $gift_item->bind_result($gift_item_id, $gift_item_name, $gift_item_desc, $gift_item_url, $gift_item_type, $gift_item_img);
                                $gift_item->fetch();

                                // connect to db and delete the item from gift list
                                $remove_item = "DELETE FROM gift_list WHERE gift_item_id=$gift_item_id";
                                if (mysqli_query($db, $remove_item)) {
                                    //delete image on server
                                    if(!$gift_item_img==""){
                                        $file =  "assets/img/gift_list/" . $gift_item_img;
                                        $website_asset =  $_SERVER['DOCUMENT_ROOT']. "/assets/img/gift-list/". $gift_item_img;

                                        if (fopen($file, "w")) {
                                            unlink($file);
                                        };
                                        if (fopen($website_asset, "w")) {
                                            unlink($website_asset);
                                        };
                                    }

                                    echo '<div class="std-card"><div class="form-response error"><p>' . $gift_item_name . ' Has been removed from your gift list</p> <a href="gift_list" class="btn-primary my-3">Return To Gift List </a></div></div>';
                                } else {
                                    echo '<div class="form-response error"><p>Error removing gift list item, please try again.</p></div>';
                                }
                            ?>
                            <?php else : ?>
                                <div class="std-card">
                                    <h2>Error</h2>
                                    <p>There has been an error, please return to the last page and try again.</p>
                                </div>
                            <?php endif; ?>
                        <?php else : //if not then display the message to confirm the user wants to delete the image
                        ?>
                            <?php if (($gift_item->num_rows) > 0) :
                                //load guest information
                                $gift_item->bind_result($gift_item_id, $gift_item_name, $gift_item_desc, $gift_item_url, $gift_item_type, $gift_item_img);
                                $gift_item->fetch();
                            ?>
                                <div class="std-card">
                                    <?php if ($gift_item_type == "message") : ?>
                                        <h2 class="text-alert">Remove your gift list message?</h2>
                                    <?php else : ?>
                                        <h2 class="text-alert">Remove <?= $gift_item_name; ?> from your gift list?</h2>
                                    <?php endif; ?>
                                    <img src="assets/img/gift_list/<?= $gift_item_img; ?>" alt="">
                                    <p>Are you sure you want to remove this gift list item?</p>
                                    <p><strong>This Cannot Be Reversed</strong></p>

                                    <div class="button-section">
                                        <a class="btn-primary btn-delete my-2" href="gift_item.php?action=delete&confirm=yes&gift_item_id=<?= $gift_item_id; ?>"><i class="fa-solid fa-trash"></i>Remove Item</a>
                                        <a class="btn-primary btn-secondary my-2" href="gift_list.php"><i class="fa-solid fa-ban"></i>Cancel</a>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>



                    <?php endif; ?>

                    <?php if ($_GET['action'] == "create") : ?>
                        <div class="std-card">
                            <form class="" id="add_gift_item" action="scripts/gift_item.script.php" method="POST" enctype="multipart/form-data">
                                <div class="form-input-wrapper">
                                    <label for="gift_item_name"><strong>Gift Name</strong></label>
                                    <p class="form-hint-small">This can be left blank if you are just wanting to leave a message on your gift list page. Such as asking for money towards a honeymoon.</p>
                                    <!-- input -->
                                    <input class="text-input input" type="text" name="gift_item_name" id="gift_item_name" placeholder="Gift List Item Name" maxlength="45">
                                </div>
                                <div class="form-input-wrapper">
                                    <label for="gift_item_desc"><strong>Item Description</strong></label>
                                    <!-- input -->
                                    <textarea name="gift_item_desc" id="gift_item_desc" rows="5" placeholder="Enter a description here about the item you would love to receive..."></textarea>
                                </div>

                                <div class="form-input-wrapper my-2">
                                    <label for="gift_item_url"><strong>URL</strong></label>
                                    <input class="text-input input" type="text" id="gift_item_url" name="gift_item_url" placeholder="URL to the item you would like if available...">
                                </div>
                                <div class="form-input-wrapper my-2">
                                    <label for="gift_item_img">Upload Image</label>
                                    <p class="form-hint-small">This can be in a JPG, JPEG or PNG format.</p>
                                    <!-- input -->
                                    <input type="file" name="gift_item_img" id="gift_item_img" accept=".jpg, .jpeg, .png, .gif">
                                </div>
                                <label for="gift_item_type"><strong>Select Item Type</strong></label>
                                <p class="form-hint-small">This can be a specific item, or can be displayed as a message to your guests on your gift list page.</p>
                                <select name="gift_item_type" id="gift_item_type" required="">
                                    <option value="" selected>Select Type</option>
                                    <option value="message">Message</option>
                                    <option value="item">Item</option>
                                </select>
                                <div class="button-section my-3">
                                    <button class="btn-primary form-controls-btn loading-btn" type="submit"><i class="fa-solid fa-floppy-disk"></i> Add Item <img id="loading-icon" class="loading-icon d-none" src="./assets/img/icons/loading.svg" alt=""></button>
                                </div>
                                <div id="response" class="d-none">
                              
                                </div>
                            </form>
                        </div>

                    <?php endif; ?>



                    <?php if ($_GET['action'] == "edit") : ?>
                        <?php if (($gift_item->num_rows) > 0) :
                            //load guest information
                            $gift_item->bind_result($gift_item_id, $gift_item_name, $gift_item_desc, $gift_item_url, $gift_item_type, $gift_item_img);
                            $gift_item->fetch();
                            $gift_item_desc = html_entity_decode($gift_item_desc);
                        ?>
                            <h2><?php if ($gift_item_name == "") {
                                    echo "Gift Message";
                                } else {
                                    echo $gift_item_name;
                                } ?></h2>
                            <div class="std-card">
                                <form class="" id="edit_gift_item" action="scripts/gift_item.script.php" method="POST" enctype="multipart/form-data">
                                    <div class="form-input-wrapper">
                                        <label for="gift_item_name"><strong>Gift Name</strong></label>
                                        <p class="form-hint-small">This can be left blank if you are just wanting to leave a message on your gift list page. Such as asking for money towards a honeymoon.</p>
                                        <!-- input -->
                                        <input class="text-input input" type="text" name="gift_item_name" id="gift_item_name" placeholder="Gift List Item Name" maxlength="45" value="<?= $gift_item_name; ?>">
                                    </div>
                                    <div class="form-input-wrapper">
                                        <label for="gift_item_desc"><strong>Item Description / Message</strong></label>
                                        <!-- input -->
                                        <textarea name="gift_item_desc" id="gift_item_desc" rows="5" placeholder="Enter a description here about the item you would love to receive, or if this is a message. Enter that here too..."><?= $gift_item_desc; ?></textarea>
                                    </div>

                                    <div class="form-input-wrapper my-2">
                                        <label for="gift_item_url"><strong>URL</strong></label>
                                        <input class="text-input input" type="text" id="gift_item_url" name="gift_item_url" placeholder="URL to the item you would like if available..." value="<?= $gift_item_url; ?>">
                                    </div>
                                    <div class="form-input-wrapper my-2">
                                        <label for="gift_item_img"><strong>Change Or Add An Image</strong></label>
                                        <p class="form-hint-small">This can be in a JPG, JPEG or PNG format.</p>
                                        <!-- input -->
                                        <input type="file" name="gift_item_img" id="gift_item_img" accept=".jpg, .jpeg, .png, .gif">
                                        <img class="gift-item-thumb" src="assets/img/gift_list/<?= $gift_item_img; ?>" alt="">
                                    </div>
                                    <label for="gift_item_type"><strong>Select Item Type</strong></label>
                                    <p class="form-hint-small">This can be a specific item, or can be displayed as a message to your guests on your gift list page.</p>
                                    <select name="gift_item_type" id="gift_item_type" required="">
                                        <?php if ($gift_item_type == "message") : ?>
                                            <option value="message" selected>Message</option>
                                            <option value="item">Item</option>
                                            
                                            
                                        <?php endif; ?>
                                        <?php if ($gift_item_type == "item") : ?>
                                            <option value="item" selected>Item</option>
                                       
                                            <option value="message">Message</option>
                                        <?php endif; ?>


                                    </select>
                                    <div class="button-section my-3">
                                        <button class="btn-primary form-controls-btn" type="submit"><i class="fa-solid fa-floppy-disk"></i> Save Item </button>
                                    </div>
                                    <div id="response" class="d-none">

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



                <?php else : ?>
                    <p class="font-emphasis">You do not have the necessary Administrator rights to view this page.</p>
                <?php endif; ?>
            </div>

        </div>


    </main>

    <!-- /Main Body Of Page -->
    <!-- Quote request form script -->

    <!-- /Quote request form script -->
    <!-- Footer -->
    <?php include("./inc/footer.inc.php"); ?>
    <!-- /Footer -->

    <script>
        //script for editing a guest
        $("#edit_gift_item").submit(function(event) {
            tinyMCE.triggerSave();
            event.preventDefault();
            //declare form variables and collect GET request information
            gift_item_id = '<?php echo $gift_item_id; ?>';
            gift_item_img_old = '<?php echo $gift_item_img; ?>'
            var formData = new FormData($("#edit_gift_item").get(0));
            formData.append("action", "edit");
            formData.append("gift_item_id", gift_item_id);
            formData.append("gift_item_img_old", gift_item_img_old);
            $.ajax({ //start ajax post
                type: "POST",
                url: "scripts/gift_item.script.php",
                data: formData,
                contentType: false,
                processData: false,
                success: function(data, responseText) {
                    if(data ==="success"){
                        window.location.replace('gift_list.php');
                    }
                    $("#response").html(data);
                    $("#response").slideDown(400);
                }
            });
        });
    </script>
    <script>
        //script for adding a guest
        $("#add_gift_item").submit(function(event) {
            tinyMCE.triggerSave();
            event.preventDefault();
            var formData = new FormData($("#add_gift_item").get(0));
            formData.append("action", "create");
            $.ajax({ //start ajax post
                type: "POST",
                url: "scripts/gift_item.script.php",
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
                    if(data ==="success"){
                        window.location.replace('gift_list.php');
                    }
                    $("#response").html(data);
                    $("#response").slideDown(400);
                }
                
               

            });

        });
    </script>

</body>

</html>