<?php
session_start();
require("scripts/functions.php");
check_login();
$user = new User();
$cms = new Cms();
db_connect($db);

if (isset($_POST['action']) && $_POST['action'] == "update") {
    $user->update();
}


//page meta variables
$meta_description = "Parrot Media - Client Admin Area";
$meta_page_title = "Mi-Admin | Profile - " . $user->name();
//load user details
$q = $db->query("SELECT * FROM users WHERE user_id=" . $user->user_id() . "");
$r = mysqli_fetch_assoc($q);


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php include("./inc/Page_meta.php"); ?>
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
            <div class="breadcrumbs mb-2"><a href="index.php" class="breadcrumb">Home</a> / Users</div>
            <?php if (isset($_GET['confirm']) && $_GET['confirm'] == "email" && $user->em_status() == "TEMP") : $user->verify_email(); ?>
                <div class="success-card my-2">
                    <h2>Success!</h2>
                    <p>Your email address has now been verified, you can now login with your new email address.</p>
                </div>
            <?php endif; ?>

            <div class="main-cards">
                <h1>Edit Profile</h1>
                <h2><?= $r['user_name']; ?></h2>
                <div class="grid-row-2col">
                    <div class="grid-col">
                        <form action="profile.php" method="POST">
                            <input type="hidden" name="action" value="update">
                            <h3>Personal information</h3>
                            <div class="form-input-wrapper">
                                <label for="user_name"><strong>Name</strong></label>
                                <input type="text" name="user_name" id="user_name" value="<?= $r['user_name']; ?>">
                            </div>
                            <div class="form-input-wrapper">
                                <label for="user_email"><strong>Email</strong></label>
                                <input type="text" name="user_email" id="user_email" value="<?= $r['user_email']; ?>" required>
                                <p>If you change this, an email will be sent to your new address to confirm it. The new address will not become active until confirmed.</p>
                            </div>
                            <button class="btn-primary my-2" type="submit">Save Changes</button>
                        </form>
                    </div>
                    <div class="grid-col">
                        <h3>Account Management</h3>
                        <h4>Change Password</h4>
                        <button class="btn-primary btn-secondary my-2" id="new-pw-form">Set Password</button>
                        <div class="pw-form d-none" id="pw-form">
                            <form action="" method="post" autocomplete="off" id="update_pw" data-user_id="<?=$user->user_id();?>">
                                <input type="hidden" name="action" value="pw">
                                <div class="form-input-wrapper">
                                    <label for="password" class="mb-2">New password</label>
                                    <!-- input -->
                                    <div class="pw-container">
                                        <input class="text-input input pw" type="password" name="password" id="password" placeholder="New Password*" required="" autocomplete="off">
                                        <button id="show_pw" class="show_pw" type="button" title="show password">
                                            <svg class="icon feather-icon show_pw_on hidden">
                                                <use xlink:href="assets/img/icons/feather.svg#eye"></use>
                                            </svg>
                                            <svg class="icon feather-icon show_pw_off">
                                                <use xlink:href="assets/img/icons/feather.svg#eye-off"></use>
                                            </svg>
                                        </button>
                                    </div>

                                    <p>You will be asked to confirm this by email</p>
                                </div>

                                <button class="btn-primary">Save Password</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
    <!-- Footer -->
    <?php include("./inc/footer.inc.php"); ?>
    <!-- /Footer -->
    <div class="d-none response-card-wrapper" id="response-card-wrapper">
            <div class="response-card">
                <div class="response-card-icon">
                <svg class="icon feather-icon"><use xlink:href="assets/img/icons/feather.svg#info"></use></svg>
                </div>
                <div class="response-card-body">
                    <p id="response-card-text"></p>
                </div>
            </div>
        </div>
    <script>
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>
</body>

</html>