<?php
session_start();
require("scripts/functions.php");
check_login();
include("connect.php");
include("inc/settings.php");
include("inc/head.inc.php");
?>
<!-- Meta Tags For Each Page -->
<meta name="description" content="Parrot Media - Client Admin Area">
<meta name="title" content="Manage your website content">

<!-- Page Title -->
<title>Mi-Admin | Invitation List</title>
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


            <div class="breadcrumbs mb-2"><a href="index.php" class="breadcrumb">Home</a> / Invitations</div>
            <div class="main-cards">
                <?php if ($user->user_type() == "Admin" || $user->user_type() == "Developer") : ?>
                    <?php if ($cms->type() == "Wedding") : ?>
                        <h2><svg class="icon">
                                <use xlink:href="assets/img/icons/solid.svg#champagne-glasses"></use>
                            </svg> Your Invitations</h2>
                        <p>This is your invite list, this list is automatically populated when you assign guests to events.</p>
                        <p>You can also keep up to date with guests that you have not had a response from.</p>
                        <p>This list will only show the main guest.</p>
                        <a class="btn-primary" href="invitations_dl.php">Download Invitations <svg class="icon">
                                <use xlink:href="assets/img/icons/solid.svg#download"></use>
                            </svg></a>
                        <form id="invite_search" action="./scripts/guest_list.script.php" method="POST">
                            <div class="search-controls">

                                <div class="form-input-wrapper">
                                    <label for="search">Search by guest name</label>
                                    <div class="search-input">
                                        <input type="text" id="search" name="search" placeholder="Search For A Guest ...">
                                        <button class="btn-primary form-controls-btn loading-btn" type="submit"> 
                                            <svg class="icon">
                                                <use xlink:href="assets/img/icons/solid.svg#magnifying-glass"></use>
                                            </svg></button>
                                    </div>
                                </div>
                                <div class="form-input-wrapper">
                                    <label for="user_email">Filter By Event</label>
                                    <select class="form-select" name="event_name" id="search_filter">
                                        <option value="">Show All Events</option>
                                        <?php foreach ($wedding_events as $event) : ?>
                                            <option value="<?= $event['event_name']; ?>"><?= $event['event_name']; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-input-wrapper">
                                    <label for="user_email">Filter By RSVP Status</label>
                                    <select class="form-select" name="rsvp_status" id="rsvp_status">
                                        <option value="">Show All Status</option>
                                        <option value="Not Replied">Not Replied</option>
                                        <option value="Attending">Attending</option>
                                        <option value="Not Attending">Not Attending</option>
                                    </select>
                                </div>


                            </div>
                        </form>
                        <div class="std-card d-none" id="invite_list">

                        </div>
                    <?php endif; ?>
                <?php else : ?>
                    <p class="font-emphasis">You do not have the necessary Administrator rights to view this page.</p>
                <?php endif; ?>
            </div>

        </section>


    </main>

    <?php include("./inc/footer.inc.php"); ?>
    <!-- /Footer -->

</body>
<script>
    $(document).ready(function() {
        url = "scripts/invitations.script.php?action=load_guest_list";
        $.ajax({ //load image gallery
            type: "GET",
            url: url,
            encode: true,
            success: function(data, responseText) {
                $("#invite_list").html(data);
                $("#invite_list").fadeIn(500);


            }
        });
    })
</script>
<script>
    //script for searching for guests
    $("#invite_search").on('submit keyup change', function(event) {
        event.preventDefault();
        var formData = new FormData($("#invite_search").get(0));
        formData.append("action", "invite_search");

        $.ajax({ //start ajax post
            type: "POST",
            url: "scripts/invitations.script.php",
            data: formData,
            contentType: false,
            processData: false,

            success: function(data, responseText) {
                $("#invite_list").html(data);
                $("#invite_list").fadeIn(500);
            }
        });

    });

</script>

</html>