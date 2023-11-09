<?php
session_start();
require("scripts/functions.php");
//load cms
$cms=new CMS;
$cms->setup();
//db_connect($db);
//find the referring page to redirect to once logged in
if (!empty($_GET)) {
    $location = urldecode($_GET['location']);
} else {
    $location = "index";
}
//page meta variables
$meta_description = "Parrot Media - Your Wedding Admin Area";
$meta_page_title = "Mi-Admin | Login";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include("./inc/Page_meta.php"); ?>
</head>

<body>
    <main class="login">
        <div class="login-wrapper">
            <img src="assets/img/logo.png" alt="">
            <?php if (isset($_COOKIE['user_name'])) : ?>
                <h1>Welcome back</h1>
                <h2><?= $_COOKIE['user_name']; ?></h2>
                <p>Please login to continue</p>
            <?php else : ?>
                <h1>Login</h1>
            <?php endif; ?>
            <form class="form-card" id="login" action="scripts/auth.php" method="post">
                <div class="form-input-wrapper">
                    <label for="user_email">eMail Address:</label>
                    <!-- input -->
                    <input type="text" name="user_email" id="user_email" placeholder="Enter Email Address" autocomplete="email" required="" <?php if (isset($_COOKIE['user_email'])) : ?>value="<?= $_COOKIE['user_email'];
                                                                                                                                                                                                            endif; ?>">
                </div>

                <div class="form-input-wrapper">
                    <label for="password">Password:</label>
                    <!-- input -->
                    <div class="pw-container"><input class="text-input input pw" type="password" name="password" id="password" placeholder="Your Password*" autocomplete="current-password" required="">
                    <button id="show_pw" class="show_pw" type="button" title="show password"><svg class="icon feather-icon show_pw_on hidden"><use xlink:href="assets/img/icons/feather.svg#eye"></use></svg><svg class="icon feather-icon show_pw_off" ><use xlink:href="assets/img/icons/feather.svg#eye-off"></use></svg></button>
                </div>
                    
                </div>

                
                <label class="checkbox-form-control my-2" for="remember_user"><input type="checkbox" name="remember_user" id="remember_user" <?php if (isset($_COOKIE['user_name'])) : ?>checked<?php endif; ?>>Remember me</label>
                <div class="button-section my-3">
                    <button class="btn-primary" type="submit">Login</button>
                    <a href="resetpw">Forgot Password</a>
                </div>
                <div id="response" class="d-none form-response">
                </div>
            </form>
        </div>
    </main>
    <script>
        $("#login").submit(function(event) {
            event.preventDefault();
            var redirect = '<?php echo $location; ?>';
            var formData = new FormData($("#login").get(0));
            var user_email = $("#user_email").val();
            var remember_user = $("#remember_user");

            $.ajax({ //start ajax post
                type: "POST",
                url: "scripts/auth.php",
                data: formData,
                contentType: false,
                processData: false,
                success: function(data, responseText) {
                    const response = JSON.parse(data);
                    if (remember_user.prop('checked')) {
                        let exdays = 182;
                        const d = new Date();
                        d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
                        let expires = "expires=" + d.toUTCString();
                        document.cookie = "user_name=" + response.user_name + ";" + expires;
                        document.cookie = "user_email=" + response.user_email + ";" + expires;
                    }else{
                        document.cookie = "user_name=; expires=Thu, 01 Jan 1970 00:00:00 UTC;";
                        document.cookie = "user_email=; expires=Thu, 01 Jan 1970 00:00:00 UTC;";
                    }
                    //$("#response").html(data);
                    console.log(response);
                    if (response.pw_status === 'correct') {
                        //confirm API Key status 
                        if(response.api_status ==="Active"){
                            window.location.replace(redirect);
                        }else{
                            $("#response").addClass("error");
                           $("#response").html(response.msg);
                           $("#response").slideDown(400);
                        }
                    }
                    if (response.pw_status === 'TEMP') {
                        window.location.replace('resetpw.php?action=temp&user_email=' + user_email);
                    }
                }
            });
        })
    </script>
    <script src="assets/js/app.js"></script>
</body>

</html>