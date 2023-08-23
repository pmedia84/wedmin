<?php
session_start();
require("scripts/functions.php");
check_login();
include("connect.php");
include("inc/settings.php");
include("inc/head.inc.php");
//find wedding gift list
$gift_list_query = ('SELECT * FROM gift_list');
$gift_list = $db->query($gift_list_query);
$gift_list_result = $gift_list->fetch_assoc();
?>
<!-- Meta Tags For Each Page -->
<meta name="description" content="Parrot Media - Client Admin Area">
<meta name="title" content="Manage your website content">
<!-- /Meta Tags -->

<!-- / -->
<!-- Page Title -->
<title>Mi-Admin | Gift List</title>
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
        <div class="body">


            <div class="breadcrumbs mb-2"><a href="index.php" class="breadcrumb">Home</a> / Gift List</div>
            <div class="main-cards">
                <?php if ($user->user_type() == "Admin" || $user->user_type() == "Developer") : ?>
                    <?php if ($cms->type() == "Wedding") : ?>
                        <h1><svg class="icon">
                                <use xlink:href="assets/img/icons/solid.svg#gifts"></use>
                            </svg> Your Gift List</h1>
                        <p>Keep this information up to date as you plan for big day. Your invites will be sent out from this information.</p>
                        <a href="gift_item.php?action=create" class="btn-primary">Add Item <svg class="icon">
                                <use xlink:href="assets/img/icons/solid.svg#gift"></use>
                            </svg></a>

                        <?php if ($gift_list->num_rows > 0) : ?>
                            <div class="grid-row-2col">
                                <?php foreach ($gift_list as $gift_item) :
                                    $gift_item_desc = html_entity_decode($gift_item['gift_item_desc']); ?>
                                    <div class="std-card">
                                        <div class="std-card-body">
                                            <?php if ($gift_item['gift_item_name'] == "") : ?>
                                                <h1>Gift List Message</h1>
                                                <p><?= $gift_item_desc; ?></p>
                                                <p><strong>Image:</strong></p>
                                                <img class="gift-item-thumb" src="assets/img/gift_list/<?= $gift_item['gift_item_img']; ?>" alt="">
                                            <?php endif; ?>
                                            <?php if ($gift_item['gift_item_name'] > "") : ?>
                                                <h1><?= $gift_item['gift_item_name']; ?></h1>
                                                <p><?= $gift_item_desc ?></p>
                                                <p><strong>URL: </strong><a href="http://<?= $gift_item['gift_item_url']; ?>" target="_blank"><?= $gift_item['gift_item_url']; ?></a></p>
                                                <p><strong>Image:</strong></p>
                                                <img class="gift-item-thumb" src="assets/img/gift_list/<?= $gift_item['gift_item_img']; ?>" alt="">
                                            <?php endif; ?>
                                        </div>
                                        <div class="card-actions">
                                            <a class="my-2" href="gift_item.php?action=edit&gift_item_id=<?= $gift_item['gift_item_id']; ?>"><svg class="icon"><use xlink:href="assets/img/icons/solid.svg#pen-to-square"></use></svg> Edit Item </a><br>
                                            <a class="my-2" href="gift_item.php?action=delete&confirm=no&gift_item_id=<?= $gift_item['gift_item_id']; ?>"><svg class="icon"><use xlink:href="assets/img/icons/solid.svg#trash"></use></svg> Remove Item </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
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

</body>



</html>