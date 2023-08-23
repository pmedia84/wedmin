<?php
session_start();
require("scripts/functions.php");
check_login();
include("connect.php");
include("inc/settings.php");
$user = new User();
if ($cms->type() == "Business") {
    //look for the business set up and load information
    //find business details.
    $business = $db->prepare('SELECT * FROM business');

    $business->execute();
    $business->store_result();
    $business->bind_result($business_id, $business_name, $address_id, $business_phone, $business_email, $business_contact_name);
    $business->fetch();
    $business->close();
    //set cms name
    $cms_name = $business_name;
    //find user details for this business
    $business_users = $db->prepare('SELECT users.user_id, users.user_name, business_users.business_id, business_users.user_type FROM users NATURAL LEFT JOIN business_users WHERE users.user_id=' . $user->user_id());

    $business_users->execute();
    $business_users->bind_result($user_id, $user_name, $business_id, $user_type);
    $business_users->fetch();
    $business_users->close();
    //find business address details.
    $business = $db->prepare('SELECT * FROM addresses WHERE address_id =' . $address_id);

    $business->execute();
    $business->store_result();
    $business->bind_result($address_id, $address_house, $address_road, $address_town, $address_county, $address_pc);
    $business->fetch();
    $business->close();


    //find social media info
    $socials_query = ('SELECT business_socials.business_socials_id, business_socials.socials_type_id, business_socials.business_socials_url, business_socials.business_id, business_socials_types.socials_type_id, business_socials_types.socials_type_name   FROM business_socials  NATURAL LEFT JOIN business_socials_types WHERE  business_socials.business_id =' . $business_id);
    $socials = $db->query($socials_query);
    $social_result = $socials->fetch_assoc();
    $db->close();
}



//////////////////////////////////////////////////////////////////Everything above this applies to each page\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\

include("./inc/head.inc.php");

?>
<!-- Meta Tags For Each Page -->
<meta name="description" content="Parrot Media - Client Admin Area">
<meta name="title" content="Manage your website content">
<!-- /Meta Tags -->

<!-- / -->
<!-- Page Title -->
<title>Mi-Admin | Settings</title>
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


            <div class="breadcrumbs mb-2"><a href="index.php" class="breadcrumb">Home</a> / <?php if ($cms->type() == "Business") {
                                                                                                echo "Settings";
                                                                                            } else {
                                                                                                echo "Wedding Details";
                                                                                            } ?></div>
            <div class="main-cards">


                <?php if ($user->user_type == "Admin" || $user->user_type == "Developer") : ?>
                    <?php if ($cms->type() == "Business") : ?>
                        <h2>Settings</h2>
                        <div class="std-card">
                            <h3>Business Details</h3>
                            <p><strong>Business Name:</strong> <?= $business_name; ?></p>
                            <p><strong>Email Address:</strong> <?= $business_email; ?></p>
                            <p><strong>Primary Contact No.:</strong> <?= $business_phone; ?></p>
                            <p><strong>Business Contact Name.:</strong> <?= $business_contact_name; ?></p>

                            <a href="edit_businessdetails.php" class="my-2">Edit Business Details</a>
                        </div>
                        <div class="std-card">
                            <h3>Social Media Details</h3>
                            <p>These are your social media details, make sure these links are correct, clients will follow these links from your website to your social media pages.</p>
                            <?php

                            foreach ($socials as $social) : ?>
                                <p><strong>Name:</strong> <?= $social['socials_type_name']; ?></p>
                                <p><strong>URL:</strong> <?= $social['business_socials_url']; ?></p>

                            <?php endforeach; ?>
                            <a class="my-2" href="edit_socialmedia.php">Edit Social Media Details</a>

                        </div>
                        <div class="std-card">
                            <h3>Primary Business Address</h3>
                            <p>Make sure this is up to date, this address is displayed on your contact page.</p>
                            <p><?= $address_house ?></p>
                            <p><?= $address_road ?></p>
                            <p><?= $address_town ?></p>
                            <p><?= $address_county ?></p>
                            <p><?= $address_pc ?></p>
                            <a class="my-2" href="edit_address.php">Edit Address</a>
                        </div>
                    <?php endif; ?>

                <?php else : ?>
                    <p class="font-emphasis">You do not have the necessary Administrator rights to view this page.</p>
                <?php endif; ?>
            </div>

        </section>


    </main>



</body>

</html>