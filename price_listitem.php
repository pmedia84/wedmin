<?php
session_start();
require("scripts/functions.php");
check_login();
if(array_key_exists("confirm", $_GET) && $_GET['confirm']=="yes"){
    include("./connect.php");
    $service_id=$_GET['service_id'];
    $delete_service = "DELETE FROM services WHERE service_id=" . $service_id;
    if (!mysqli_query($db, $delete_service)) {
        $del_serv_error=500;
    }else{
        header("Location: price_list");
    }
}
include("connect.php");
include("inc/head.inc.php");
include("inc/settings.php");
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

//run checks to make sure a wedding has been set up correctly
if ($cms->type() == "Wedding") {

    //look for a wedding setup in the db, if not then direct to the setup page
    $wedding_query = ('SELECT wedding_id, wedding_name FROM wedding');
    $wedding = $db->query($wedding_query);
    $wedding_details = mysqli_fetch_assoc($wedding);
    if ($wedding->num_rows == 0) {
        header('Location: setup.php?action=setup_wedding');
    }
    //check that there are users set up 
    $wedding_user_query = ('SELECT wedding_user_id FROM wedding_users');
    $wedding_user = $db->query($wedding_user_query);
    if ($wedding_user->num_rows == 0) {
        header('Location: setup.php?action=check_users_wedding');
    }

    if (!$_SESSION['loggedin'] == true) {
        // Redirect to the login page:
        header('Location: login.php');
    }
}

//////////////////////////////////////////////////////////////////Everything above this applies to each page\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
//service variable
if ($_GET['action'] == "edit") {
    $service_id = $_GET['service_id'];
    //find service details

    $service = $db->prepare('SELECT * FROM services WHERE service_id =' . $service_id);

    $service->execute();
    $service->store_result();
}
if ($_GET['action'] == "delete") {
    $service_id = $_GET['service_id'];
    //find service details

    $service = $db->prepare('SELECT * FROM services WHERE service_id =' . $service_id);

    $service->execute();
    $service->store_result();
}



?>
<!-- Meta Tags For Each Page -->
<meta name="description" content="Parrot Media - Client Admin Area">
<meta name="title" content="Manage your website content">
<!-- /Meta Tags -->

<!-- / -->
<!-- Page Title -->
<title>Mi-Admin | Manage Price List</title>
<!-- /Page Title -->
</head>


<body>
    <!-- Main Body Of Page -->
    <main class="main col-2">
        <!-- Header Section -->
        <?php include("./inc/header.inc.php"); ?>
        <!-- Nav Bar -->
        <?php include("./inc/nav.inc.php"); ?>
        <!-- /nav bar -->
        <section class="body">
            <div class="breadcrumbs mb-2">
                <a href="index.php" class="breadcrumb">Home</a> /
                <a href="price_list.php" class="breadcrumb">Price List</a>
                <?php if ($_GET['action'] == "edit") : ?>
                    / Edit Service
                <?php endif; ?>
                <?php if ($_GET['action'] == "delete") : ?>
                    / Delete Service
                <?php endif; ?>
                <?php if ($_GET['action'] == "add") : ?>
                    / Create Service
                <?php endif; ?>

            </div>
            <div class="main-cards">
                <?php if ($_GET['action'] == "edit") : ?>
                    <h1>Edit Service</h1>
                <?php endif; ?>
                <?php if ($_GET['action'] == "delete") : ?>
                    <h1>Delete Service</h1>
                <?php endif; ?>

                <?php if ($_GET['action'] == "edit") : ?>

                <?php endif; ?>
                <?php if ($user_type == "Admin" || $user_type == "Developer") : //detect if user is an admin or not 
                ?>

                    <?php if ($_GET['action'] == "add") :
                        $categories_query = ('SELECT * FROM services_categories');
                        $categories = $db->query($categories_query); ?>

                        <div class="std-card service-card-edit">
                            <h2>Create a new service</h2>

                            <form class="my-2" id="create_service" action="scripts/price_list.script.php" method="POST" enctype="multipart/form-data">
                                <div class="form-input-wrapper">

                                    <label for="service_name"><strong>Service Name</strong></label>
                                    <!-- input -->
                                    <input class="text-input input" type="text" name="service_name" id="service_name" placeholder="Service Name" size="25" required="" autofocus>
                                </div>
                                <div class="form-input-wrapper my-2">
                                    <label for="service_description">Service Description</label>
                                    <textarea name="service_description" id="service_description" rows="3" placeholder="A short description about this service"></textarea>
                                </div>

                                <div class="form-input-wrapper">
                                    <label for="service_price"><strong>Price</strong></label>
                                    <!-- input -->
                                    <input class="text-input input" type="number" name="service_price" id="service_price" placeholder="15.00" required="">
                                </div>
                                <div class="settings-card">
                                    <div class="settings-card-text">
                                        <h3>Featured Service</h3>
                                        <p>Set this service as a featured service, this will then be shown on your home page as a highlighted service.</p>
                                    </div>
                                    <label class="switch">
                                        <input class="switch-check" type="checkbox" name="service_featured" value="Yes">
                                        <span class="slider round"></span>
                                    </label>
                                </div>
                                <div class="settings-card">
                                    <div class="settings-card-text">
                                        <h3>Promotion</h3>
                                        <p>Make this service as a promotional service. This will be shown on your home page in the offers and promotion section. </p>
                                    </div>
                                    <label class="switch">
                                        <input class="switch-check" type="checkbox" name="service_promo" value="Yes">
                                        <span class="slider round"></span>
                                    </label>
                                </div>
                                <h3>Service Category</h3>
                                <div class="form-input-wrapper">
                                    <select name="service_cat_id" id="service_cat_id" required>
                                        <option value="">Select Category</option>
                                        <?php foreach ($categories as $category) : ?>
                                            <option value="<?= $category['service_cat_id']; ?>"><?= $category['service_cat_name']; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="button-section my-3">
                                    <button class="btn-primary form-controls-btn" type="submit"><i class="fa-solid fa-floppy-disk"></i> Save Service </button>
                                </div>

                            </form>
                        </div>

                    <?php endif; ?>



                    <?php if ($_GET['action'] == "delete") : //if action is delete, detect if the confirm is yes or no
                    ?>
                        <?php if ($_GET['confirm'] == "yes") : //if yes then delete the article
                        ?>
                            <?php if ($del_serv_error==500) : ?>
                                <div class="std-card">
                                    <h2>Error Deleting Service</h2>
                                    <p>The server has encountered an error, please try again.</p>
                                    <p>If this error persists, please contact us.</p>
                                </div>
                            <?php endif; ?>
                        <?php else : //if not then display the message to confirm the user wants to delete the news article
                        ?>
                            <?php if (($service->num_rows) > 0) :
                                $service->bind_result($service_id, $service_name, $service_description, $service_category, $service_price, $service_promo, $service_featured);
                                $service->fetch();



                            ?>
                                <div class="std-card">
                                <h2 class="text-alert">Delete: <?= $service_name; ?></h2>
                                <p>Are you sure you want to delete this service?</p>
                                    <p><strong>This Cannot Be Reversed</strong></p>
                                    <div class="service-card my-2">
                                        <div class="service-card-banner" data-promo="<?= $service_promo; ?>">
                                            <span><?php if ($service_promo == "Yes") : ?>Promo<?php endif; ?></span>
                                        </div>
                                        <div class="service-card-body">
                                            <h4 class="service-card-heading"><?= html_entity_decode($service_name); ?></h4>
                                            <p><?= html_entity_decode($service_description); ?></p>
                                            <p class="service-card-price">&#163;<?= $service_price; ?></p>
                                            <div class="service-card-featured" data-featured="<?= $service_featured; ?>">
                                                <span><?php if ($service_featured == "Yes") : ?>Featured Service<?php endif; ?></span>
                                            </div>
                                            <div class="service-card-actions my-2">
                                            <a class="btn-primary btn-secondary my-2" href="price_list.php"><i class="fa-solid fa-ban"></i>Cancel</a>
                                                <a class="btn-primary btn-delete my-2" href="price_listitem.php?action=delete&confirm=yes&service_id=<?= $service_id; ?>"><i class="fa-solid fa-trash"></i>Delete Service</a>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            <?php endif; ?>
                        <?php endif; ?>



                    <?php endif; ?>

                    <?php if ($_GET['action'] == "edit") : ?>
                        <?php if (($service->num_rows) > 0) :
                            $service->bind_result($service_id, $service_name, $service_description, $service_category, $service_price, $service_promo, $service_featured);
                            $service->fetch();
                            $categories_query = ('SELECT * FROM services_categories');
                            $categories = $db->query($categories_query);
                            $service_description = html_entity_decode($service_description);
                            $service_name = html_entity_decode($service_name);
                        ?>
                            <div class="std-card service-card-edit">
                                <h2><?= $service_name; ?></h2>
                                <div class="service-card-edit-header">
                                    <?php if ($service_featured == "Yes") : ?>
                                        <div class="service-card-featured" data-featured="<?= $service_featured; ?>">
                                            <span>Featured Service</span>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($service_promo == "Yes") : ?>
                                        <div class="service-card-banner" data-promo="<?= $service_promo; ?>">
                                            <span>Promo</span>

                                        </div>
                                    <?php endif; ?>
                                </div>
                                <form class="my-2" id="edit_service" action="scripts/price_list.script.php" method="POST" enctype="multipart/form-data" data-service_id="<?php echo $service_id; ?>">
                                    <div class="form-input-wrapper">

                                        <label for="service_name"><strong>Service Name</strong></label>
                                        <!-- input -->
                                        <input class="text-input input" type="text" name="service_name" id="service_name" placeholder="Service Name" size="25" required="" value="<?= $service_name; ?>" autofocus>
                                    </div>
                                    <div class="form-input-wrapper my-2">
                                        <label for="service_description">Service Description</label>
                                        <textarea name="service_description" id="service_description" rows="3" placeholder="A short description about this service"><?= $service_description; ?></textarea>
                                    </div>

                                    <div class="form-input-wrapper">
                                        <label for="service_price"><strong>Price</strong></label>
                                        <!-- input -->
                                        <input class="text-input input" type="number" name="service_price" id="service_price" placeholder="Price" required="" value="<?= $service_price; ?>">
                                    </div>
                                    <div class="settings-card">
                                        <div class="settings-card-text">
                                            <h3>Featured Service</h3>
                                            <p>Set this service as a featured service, this will then be shown on your home page as a highlighted service.</p>
                                        </div>
                                        <label class="switch">
                                            <input class="switch-check" type="checkbox" name="service_featured" value="<?php if ($service_featured == "Yes") : ?>Yes<?php else : ?>Yes<?php endif; ?>" <?php if ($service_featured == "Yes") : ?>checked<?php endif; ?>>
                                            <span class="slider round"></span>
                                        </label>
                                    </div>
                                    <div class="settings-card">
                                        <div class="settings-card-text">
                                            <h3>Promotion</h3>
                                            <p>Make this service as a promotional service. This will be shown on your home page in the offers and promotion section. </p>
                                        </div>
                                        <label class="switch">
                                            <input class="switch-check" type="checkbox" name="service_promo" value="<?php if ($service_promo == "Yes") : ?>Yes<?php else : ?>Yes<?php endif; ?>" <?php if ($service_promo == "Yes") : ?>checked<?php endif; ?>>
                                            <span class="slider round"></span>
                                        </label>
                                    </div>
                                    <h3>Service Category</h3>
                                    <div class="form-input-wrapper">
                                        <select name="service_cat_id" id="service_cat_id" required>
                                            <?php foreach ($categories as $category) : ?>
                                                <?php if ($category['service_cat_id'] == $service_category) : ?>
                                                    <option value="<?= $category['service_cat_id']; ?>" selected><?= $category['service_cat_name']; ?></option>
                                                <?php else : ?>
                                                    <option value="<?= $category['service_cat_id']; ?>"><?= $category['service_cat_name']; ?></option>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="button-section my-3">
                                        <button class="btn-primary form-controls-btn" type="submit"><i class="fa-solid fa-floppy-disk"></i> Save Changes </button>
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

        </section>
        <div class="d-none" id="response-card-wrapper">
            <div class="response-card">
                <div class="response-card-icon">
                    <i class="fa-solid fa-circle-info"></i>
                </div>
                <div class="response-card-body">
                    <p id="response-msg"></p>
                </div>
            </div>
        </div>

    </main>

    <!-- /Main Body Of Page -->

    <!-- Footer -->
    <?php include("./inc/footer.inc.php"); ?>
    <!-- /Footer -->
    <script src="assets/js/price_list.js"></script>
</body>

</html>