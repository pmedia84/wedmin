<?php
session_start();
if (empty($_GET)) {
    //if user arrives at this page without a get request, redirect to the index page
    //header('location: index.php');
}
require("scripts/functions.php");
db_connect($db);
$cms = new Cms();
$wedding_query = ('SELECT wedding_id FROM wedding');
$wedding = $db->query($wedding_query);
if ($wedding->num_rows > 0) {
    $ar = mysqli_fetch_assoc($wedding);
    $wedding_id = $ar['wedding_id'];
} else {
    $wedding_id = "";
}
//page meta variables
$meta_description = "Parrot Media - Wedding Admin Area";
$meta_page_title = "Mi-Admin | Setup";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php include("./inc/Page_meta.php"); ?>
</head>

<body>
    <!-- Main Body Of Page -->
    <main class="login">

        <div class="login-wrapper">
            <img src="assets/img/logo.png" alt="">

            <?php if ($_GET['action'] == "setup_wedding") : ?>
                <?php if ($wedding->num_rows == 0) : ?>
                    <h1>Setup Wedding</h1>
                    <p>You need to set up your Wedding First.</p>
                    <p><strong>Note:</strong> This can only be done by a developer. Contact us if you are not a developer.</p><br>
                    <form class="form-card" id="setup-wedding" action="scripts/setup.php" method="post">
                        <div class="form-input-wrapper">
                            <h2>Wedding Name</h2>
                            <label for="wedding_name">Wedding Name</label>
                            <!-- input -->
                            <input type="text" name="wedding_name" id="wedding_name" placeholder="The Wedding Of:" required="" maxlength="45">
                        </div>

                        <h2>Wedding Primary Contact Details</h2>
                        <div class="form-input-wrapper">
                            <label for="wedding_email">eMail Address:</label>

                            <!-- input -->
                            <input type="text" name="wedding_email" id="wedding_email" placeholder="Email Address" autocomplete="email" required="" maxlength="45">
                        </div>
                        <div class="form-input-wrapper">
                            <label for="wedding_phone">Primary Phone No.:</label>
                            <!-- input -->
                            <input type="text" name="wedding_phone" id="wedding_phone" placeholder="Primary Phone No." autocomplete="tel" required="" maxlength="45">
                        </div>
                        <div class="form-input-wrapper">
                            <label for="wedding_contact_name">Primary Contact Name:</label>
                            <!-- input -->
                            <input type="text" name="wedding_contact_name" id="wedding_contact_name" placeholder="Contact Name" autocomplete="given-name" required="" maxlength="45">
                        </div>
                        <div class="button-section my-3">
                            <button class="btn-primary form-controls-btn loading-btn" type="submit">Set Up Wedding <img id="loading-icon" class="loading-icon d-none" src="./assets/img/icons/loading.svg" alt=""></button>
                        </div>
                        <div id="response" class="d-none">
                        </div>
                    </form>
                <?php else : ?>
                    <h1>Setup Wedding</h1>
                    <p><strong>Wedding already setup!</strong></p>
                    <a href="setup?action=check_users_wedding" class="btn-primary">Add Users</a>
                <?php endif; ?>
            <?php endif; ?>

            <?php if ($_GET['action'] == "check_users_wedding") : ?>

                <?php
                //display wedding name
                $wedding_query = ('SELECT wedding_id, wedding_name FROM wedding LIMIT 1');
                $wedding = $db->query($wedding_query);
                $wedding_result = $wedding->fetch_array();
                ?>
                <?php
                //find an admin for this wedding
                $admin_user_query = ('SELECT user_type, wedding_id FROM wedding_users WHERE wedding_id=' . $wedding_id . ' AND user_type = "Admin" ');
                $admin_user = $db->query($admin_user_query);
                //find a Developer for this wedding
                $dev_user_query = ('SELECT user_type, wedding_id FROM wedding_users WHERE wedding_id=' . $wedding_id . ' AND user_type = "Developer" ');
                $dev_user = $db->query($dev_user_query);

                ?>
                <?php if ($admin_user->num_rows == 0) : ?>
                    <h1>The Wedding of <?= $wedding_result['wedding_name']; ?></h1>
                    <p><strong>Admin User Required</strong></p>
                    <p>You need to set up an admin user for your wedding website.</p>
                    <p><strong>Note:</strong> This can only be done by a developer. Contact us if you are not a developer.</p>
                    <p>Two user types are required: Admin and Developer</p><br>
                    <h2>Setup Admin User</h2>
                    <form class="form-card" id="add_user_wedding" action="scripts/setup.script.php" method="post">
                        <div class="form-input-wrapper">
                            <label for="user_name">Name:</label>
                            <!-- input -->
                            <input class="text-input input" type="text" name="username" id="username" placeholder="Name" required="" maxlength="45">
                        </div>
                        <div class="form-input-wrapper">
                            <label for="user_email">eMail Address:</label>
                            <!-- input -->
                            <input type="text" name="user_email" id="user_email" placeholder="Email Address" autocomplete="email" required="" maxlength="45">
                        </div>
                        <div class="form-input-wrapper">
                            <label for="user_email">Access Level</label>
                            <p><strong>Admin:</strong> For Clients login</p>
                            <!-- input -->
                            <select class="form-select" name="user_type" id="user_type">
                                <option value="Admin">Admin</option>
                            </select>
                            <p>A password will be randomly generated and emailed to the user to make their own password.</p>
                        </div>
                        <div class="button-section my-3">
                            <button class="btn-primary form-controls-btn loading-btn" type="submit">Add User <img id="loading-icon" class="loading-icon d-none" src="./assets/img/icons/loading.svg" alt=""></button>
                        </div>
                        <div id="response" class="d-none">
                        </div>
                    </form>
                <?php else : ?>
                    <?php if ($dev_user->num_rows == 0) : ?>
                        <h1><?= $wedding_result['wedding_name']; ?></h1>
                        <p><strong>Developer User Required</strong></p>
                        <p>You need to set up a Developer user for this business.</p>
                        <p><strong>Note:</strong> This can only be done by a developer. Contact us if you are not a developer.</p>
                        <p>Two user types are required: Admin and Developer</p><br>
                        <h2>Setup Developer User</h2>
                        <form class="form-card" id="add_user_wedding" action="scripts/setup.script.php" method="post">
                            <div class="form-input-wrapper">
                                <label for="user_name">Name:</label>
                                <!-- input -->
                                <input class="text-input input" type="text" name="username" id="username" placeholder="Name" required="" maxlength="45">
                            </div>
                            <div class="form-input-wrapper">
                                <label for="user_email">eMail Address:</label>
                                <!-- input -->
                                <input type="text" name="user_email" id="user_email" placeholder="Email Address" autocomplete="email" required="" maxlength="45">
                            </div>
                            <div class="form-input-wrapper">
                                <label for="user_email">Access Level</label>
                                <p><strong>Developer:</strong> For Developer to setup business and provide tech support.</p>
                                <!-- input -->
                                <select class="form-select" name="user_type" id="user_type">
                                    <option value="Developer">Developer</option>
                                </select>
                                <p>A password will be randomly generated and emailed to the user to make their own password.</p>
                            </div>
                            <div class="button-section my-3">
                                <button class="btn-primary form-controls-btn loading-btn" type="submit">Add User <img id="loading-icon" class="loading-icon d-none" src="./assets/img/icons/loading.svg" alt=""></button>
                            </div>
                            <div id="response" class="d-none">
                            </div>
                        </form>

                    <?php else : ?>
                        <script>
                            window.location.replace('login.php');
                        </script>
                    <?php endif; ?>
                <?php endif; ?>

            <?php endif; ?>








        </div>
    </main>



    <script>
        //script for saving new wedding details then redirects to set up users
        $("#setup-wedding").submit(function(event) {
            event.preventDefault();
            var formData = new FormData($("#setup-wedding").get(0));
            var url = "setup.php?action=check_users_wedding"
            formData.append("action", "create_wedding");

            $.ajax({ //start ajax post
                type: "POST",
                url: "scripts/setup.script.php",
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
                    //window.location.replace(url);
                }
            });
        });
    </script>

    <script>
        //script for adding new users
        $("#add_user_wedding").submit(function(event) {
            event.preventDefault();
            var formData = new FormData($("#add_user_wedding").get(0));
            var wedding_id = <?php echo $wedding_id; ?>;
            var url = "setup.php?action=check_users_wedding";
            formData.append("action", "create_user_wedding");
            formData.append("wedding_id", wedding_id);
            $.ajax({ //start ajax post
                type: "POST",
                url: "scripts/setup.script.php",
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
                    if (data == "User already exists with that email address") {
                        $("#response").addClass("form-response error");
                        $("#response").html(data);
                        $("#response").slideDown(400);

                    }
                    window.location.replace(url);



                }
            });

        });
    </script>
</body>

</html>