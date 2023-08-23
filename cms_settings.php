<?php
session_start();
require("scripts/functions.php");
check_login();
include("./connect.php");
include("./inc/settings.php");
include("./inc/head.inc.php");
?>
<!-- Meta Tags For Each Page -->
<meta name="description" content="Parrot Media - Client Admin Area">
<meta name="title" content="Manage your website content">
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
        <div class="body">
            <div class="breadcrumbs mb-2"><a href="index.php" class="breadcrumb">Home</a> / CMS Settings</div>
            <div class="main-cards cms-settings-cards">
                <?php if ($user->user_type() == "Admin" || $user->user_type() == "Developer") : ?>

                    <h1><svg class="icon">
                            <use xlink:href="assets/img/icons/solid.svg#gear"></use>
                        </svg> CMS Settings</h1>
                    <p>Manage modules and features that are available to the end user.</p>
                    <?php
                    //connect to db and load module settings
                    $modules_query = ('SELECT * FROM modules');
                    $modules = $db->query($modules_query);
                    //connect to db and load cms settings
                    $settings = $db->prepare('SELECT setting_id,cms_type FROM settings');

                    $settings->execute();
                    $settings->store_result();
                    $settings->bind_result($setting_id, $cms_type);
                    $settings->fetch();
                    $settings->close();
                    ?>
                    <h2>CMS Type</h2>
                    <p>The system will work for a business website as well as a wedding website. This can be changed here.</p>
                    <div class="settings-card">
                        <div class="settings-card-text">
                            <h3>Business Or Wedding Website</h3>
                            <form action="cms_settings.script.php" method="POST" enctype="multipart/form-data" id="cms_settings">
                                <select name="cms_type" id="cms_type" required="">
                                    <option value="<?= $cms_type; ?>" selected><?= $cms_type; ?></option>
                                    <?php
                                    if ($cms_type == "Wedding") :
                                    ?>

                                        <option value="Business">Business</option>
                                    <?php else : ?>
                                        <option value="Wedding">Wedding</option>
                                    <?php endif; ?>
                                </select>
                            </form>
                        </div>
                    </div>
                    <h2>Modules</h2>
                    <form action="cms_settings.script.php" method="POST" enctype="multipart/form-data" id="cms_modules">
                        <?php foreach ($modules as $module) : ?>
                            <div class="settings-card">
                                <div class="settings-card-text">
                                    <h3><?= $module['module_name']; ?></h3>
                                    <p><?= $module['module_desc']; ?></p>
                                </div>
                                <label class="switch">
                                    <input class="switch-check" type="checkbox" value="<?= $module['module_id']; ?>" <?php if ($module['module_status'] == "On") : ?>checked<?php endif; ?>>
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </form>
            </div>
        <?php else : ?>
            <p class="font-emphasis">You do not have the necessary Administrator rights to view this page.</p>
        <?php endif; ?>
        </div>
    </main>
    <?php include("./inc/footer.inc.php"); ?>
    <!-- /Footer -->
    <script>
        //script for updating module status
        $(".switch-check").on('click', function(event) {

            var module_id = $(this).attr("value");
            var module_status = "Off";
            if ($(this).is(":checked")) {
                module_status = "On";
            }
            //collect form data and GET request information to pass to back end script
            var formData = new FormData();
            formData.append("module_id", module_id);
            formData.append("module_status", module_status);
            $.ajax({ //start ajax post
                type: "POST",
                url: "scripts/cms_settings.script.php",
                data: formData,
                contentType: false,
                processData: false,

                success: function(data, responseText) {


                    $("#response").html(data);
                    $("#response").slideDown(400);


                }
            });
        });
    </script>

    <script>
        //script for setting CMS Type
        $("#cms_type").on('change', function(event) {

            var cms_type = $("#cms_type").val();
            var formData = new FormData($("#cms_settings").get(0));
            var setting_id = <?php echo $setting_id; ?>;
            formData.append("setting_id", setting_id);
            $.ajax({ //start ajax post
                type: "POST",
                url: "scripts/cms_settings.script.php",
                data: formData,
                contentType: false,
                processData: false,

                success: function(data, responseText) {


                    $("#response").html(data);
                    $("#response").slideDown(400);


                }
            });
        });
    </script>
</body>

</html>