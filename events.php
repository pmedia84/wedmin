<?php
session_start();
require("scripts/functions.php");
check_login();
$user = new User();
$user_type = $user->user_type();
$user_id = $user->user_id();

include("connect.php");
include("inc/head.inc.php");
include("inc/settings.php");
//find wedding events details
$wedding_events_query = ('SELECT * FROM wedding_events ORDER BY event_time');
$wedding_events = $db->query($wedding_events_query);
$wedding_events_result = $wedding_events->fetch_assoc();
include("inc/head.inc.php");
?>
<!-- Meta Tags For Each Page -->
<meta name="description" content="Parrot Media - Client Admin Area">
<meta name="title" content="Manage your website content">
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
            <div class="breadcrumbs mb-2"><a href="index.php" class="breadcrumb">Home</a> / Wedding Day Events</div>
            <div class="main-cards">
                <?php if ($user->user_type() == "Admin" || $user->user_type() == "Developer") : ?>
                    <?php if ($cms->type() == "Wedding") : ?>
                        <h2><svg class="icon">
                                <use xlink:href="assets/img/icons/solid.svg#calendar-day"></use>
                            </svg> Your Wedding Day Events</h2>
                        <p>Keep this information up to date as you plan for your big day. Information from this page will be displayed on your website.</p>

                        <a class="btn-primary" href="event.php?action=create">Create An Event <svg class="icon">
                                <use xlink:href="assets/img/icons/regular.svg#calendar-plus"></use>
                            </svg></a>
                        <?php foreach ($wedding_events as $event) :
                            $event_time = strtotime($event['event_time']);
                            $time = date('H:ia', $event_time);
                            $event_date = strtotime($event['event_date']);
                            $date = date('D d M Y', $event_date);
                        ?>
                            <div class="event-card">
                                <h3 class="event-card-title mb-3"> <a href="event.php?action=view&event_id=<?= $event['event_id']; ?>"><?= $event['event_name']; ?> <span class="event-card-title-time"><?= $time ?></a></span></h3>
                                <div class="event-card-details my-3">
                                    <div class="event-card-item">
                                        <h4>Location</h4>
                                        <p><?= $event['event_location']; ?></p>
                                    </div>
                                    <div class="event-card-item">
                                        <h4>Date</h4>
                                        <p><?= $date; ?></p>
                                    </div>
                                    <div class="event-card-item">
                                        <h4>Time</h4>
                                        <p><?= $time; ?></p>
                                    </div>
                                </div>
                                <h4>Address</h4>
                                <address class="my-2"><?= $event['event_address']; ?></address>
                                <div class="card-actions">
                                    <a class="my-2" href="event.php?action=view&event_id=<?= $event['event_id']; ?>"><svg class="icon">
                                            <use xlink:href="assets/img/icons/solid.svg#eye"></use>
                                        </svg> View Event</a>
                                    <a class="my-2" href="event.php?action=edit&event_id=<?= $event['event_id']; ?>"><svg class="icon">
                                            <use xlink:href="assets/img/icons/solid.svg#pen-to-square"></use>
                                        </svg> Edit Event </a>
                                    <a class="my-2" href="event.php?action=assign&event_id=<?= $event['event_id']; ?>"><svg class="icon">
                                            <use xlink:href="assets/img/icons/solid.svg#user-pen"></use>
                                        </svg> Edit Guest List </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
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

</html>