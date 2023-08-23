<?php
session_start();
require("scripts/functions.php");
check_login();
include("connect.php");
include("inc/settings.php");
$user=new User();

////////////////Find details of the cms being used, on every page\\\\\\\\\\\\\\\
//Variable for name of CMS
//wedding is the name of people
//business name
$cms_name = "";
$user_id = $_SESSION['user_id'];
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
    $business_users = $db->prepare('SELECT users.user_id, users.user_name, business_users.business_id, business_users.user_type FROM users NATURAL LEFT JOIN business_users WHERE users.user_id=' . $user_id);

    $business_users->execute();
    $business_users->bind_result($user_id, $user_name, $business_id, $user_type);
    $business_users->fetch();
    $business_users->close();
}




//////////////////////////////////////////////////////////////////Everything above this applies to each page\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
//load all service names for the search bar
$service_names_q=$db->query('SELECT service_name FROM services');
include("inc/head.inc.php");

?>
<!-- Meta Tags For Each Page -->
<meta name="description" content="Parrot Media - Client Admin Area">
<meta name="title" content="Manage your website content">
<!-- /Meta Tags -->

<!-- / -->
<!-- Page Title -->
<title>Mi-Admin | Price List</title>
<!-- /Page Title -->
</head>

<body>
    <!-- Main Body Of Page -->
    <main class="main col-2">


        <!-- Header Section -->
        <?php include("./inc/header.inc.php");?>
        <!-- Nav Bar -->
        <?php include("./inc/nav.inc.php"); ?>
        <!-- /nav bar -->
        <section class="body">


            <div class="breadcrumbs mb-2">
                <a href="index.php" class="breadcrumb">Home</a> / Manage Price List
            </div>
            <div class="main-cards loading">

                <h2>Price List</h2>
                <?php
                $categories = "SELECT * FROM services_categories";
                $categories_result = mysqli_query($db, $categories);
                ?>
                <?php foreach ($categories_result as $category) : ?>

                <?php endforeach; ?>
                <div class="container price-list-controls my-3 ">
                    <form action="scripts/price_list.script.php" method="POST" id="price_list_search">

                        <div class="form-input-wrapper my-3">
                            <div class="search-input">

                                <input type="search" id="search" name="search" list="services" placeholder="Search For A Service ...">
                                <datalist id="services">
                                    <?php foreach($service_names_q as $service_name):?>
                                        <option value="<?php echo html_entity_decode($service_name['service_name']) ;?>"></option>
                                        <?php endforeach;?>
                                </datalist>
                                <button class="btn-primary form-controls-btn loading-btn" type="submit"><i class="fa-solid fa-magnifying-glass" id="search-icon"></i></button>

                            </div>
                        </div>
                    </form>
                    <form id="category_search_filter" action="./scripts/guest_list.script.php" method="POST">

                        <div class="form-input-wrapper">
                            <label for="search">Filter By Category</label>
                            <select class="form-select" name="service_cat_id" id="search_filter">
                                <option value="" selected>Show All</option>
                                <?php
                                $categories_query = ('SELECT * FROM services_categories');
                                $categories = $db->query($categories_query);
                                ?>
                                <?php foreach ($categories as $category) : ?>
                                    <option value="<?= $category['service_cat_id']; ?>"><?= $category['service_cat_name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </form>
                </div>

                <?php if ($user_type == "Admin" ||$user_type=="Developer") : ?>
                    <a class="btn-primary" id="upload_image" href="price_listitem.php?action=add"><i class="fa-solid fa-square-plus"></i>Create Service </a>
                    <div class="std-card price-list" id="price_list">

                    </div>
                <?php else : ?>
                    <p class="font-emphasis">You do not have the necessary Administrator rights to view this page.</p>
                <?php endif; ?>
            </div>
        </section>
    </main>


    </div>
    <!-- /Main Body Of Page -->

    <!-- Footer -->
    <?php include("./inc/footer.inc.php"); ?>
    <!-- /Footer -->
<script src="assets/js/price_list.js"></script>

</body>

</html>