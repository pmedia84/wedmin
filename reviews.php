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
}



//////////////////////////////////////////////////////////////////Everything above this applies to each page\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\

include("inc/head.inc.php");

?>
<!-- Meta Tags For Each Page -->
<meta name="description" content="Parrot Media - Client Admin Area">
<meta name="title" content="Manage your website content">
<!-- /Meta Tags -->

<!-- / -->
<!-- Page Title -->
<title>Mi-Admin | Reviews</title>
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
                <a href="index.php" class="breadcrumb">Home</a> / Manage Reviews
            </div>
            <div class="main-cards">
                <?php if ($reviews->status() == "On") : ?>

                    <h1>Reviews</h1>
                    <p>Your 5 most recent reviews are displayed here and on your website.</p>
                    <p>This is updated once a week, you can also update it here by clicking the below button.</p>
                    <button class="btn-primary" id="get_reviews_btn" type="button"><i class="fa-solid fa-download"></i>Fetch Recent Reviews</button>
                    <?php if ($user_type == "Admin" || $user_type == "Developer") : ?>
                        <div id="reviews">

                        </div>
            </div>

        <?php else : ?>
            <p class="font-emphasis">You do not have the necessary Administrator rights to view this page.</p>
        <?php endif; ?>
        </div>
    <?php else : ?>
        <h1>Module not activated for your website!</h1>
        <p>Contact us to find out how you can get this feature set up.</p>
    <?php endif; ?>
        </section>


    </main>

    <!-- /Main Body Of Page -->

    <!-- Footer -->
    <?php include("./inc/footer.inc.php"); ?>
    <!-- /Footer -->
    <script>
        $("document").ready(function() {
            url = "scripts/reviews-script.php?action=loadreviews";
            $.ajax({ //load reviews
                type: "GET",
                url: url,
                encode: true,

                success: function(data, responseText) {
                    $("#reviews").html(data);

                }
            });
        })
    </script>

    <script>
        //download reviews with button
        $("#get_reviews_btn").click(function() {
            url = "scripts/reviews-script.php?action=download";
            $.ajax({ //load reviews
                type: "GET",
                url: url,
                encode: true,

                success: function(data, responseText) {
                    $("#reviews").html(data);

                }
            });
        })
    </script>
</body>

</html>