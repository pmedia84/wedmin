<?php
session_start();
require("scripts/functions.php");
check_login();
$cms = new Cms();
$cms->setup();
$user = new User();
//connect to DB
db_connect($db);
//page meta variables
$meta_description = "Parrot Media - Wedding Admin Area";
$meta_page_title = "Mi-Admin | Dashboard - " . $cms->w_name();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php include("./inc/Page_meta.php"); ?>
</head>

<body>
    <!-- Main Body Of Page -->
    <main class="main">
        <!-- Header Section -->
        <?php include("inc/header.inc.php"); ?>
        <!-- Nav Bar -->
        <?php include("./inc/nav.inc.php"); ?>
        <!-- /nav bar -->
        <section class="body">
            <div class="breadcrumbs"><span><svg class="icon feather-icon">
                        <use xlink:href="assets/img/icons/feather.svg#home"></use>
                    </svg> Home / </span></div>
            <div class="main-dashboard">
                <div class="dashboard-card">
                    <div class="dashboard-card-header">
                        <span></span>
                        <svg class="icon">
                            <use xlink:href="assets/img/icons/solid.svg#tags"></use>
                        </svg>
                    </div>
                    <h2>Guests</h2>
                    <a href="price_list.php">Manage</a>
                </div>


            </div>


        </section>

        <div class="main-cards sidebar">


        </div>


    </main>
    <!-- Footer -->
    <?php include("./inc/footer.inc.php"); ?>
    <!-- /Footer -->
</body>

</html>