<?php
session_start();
require("scripts/functions.php");
check_login();
include("connect.php");
include("inc/head.inc.php");
include("inc/settings.php");

//find wedding guest list
$guest_list_query = ('SELECT guest_list.guest_id, guest_list.guest_fname, guest_list.guest_sname, guest_list.guest_type, guest_list.guest_extra_invites, guest_list.guest_group_id, guest_list.guest_rsvp_code, invitations.event_id, invitations.invite_rsvp_status, wedding_events.event_id, wedding_events.event_name  FROM guest_list LEFT JOIN invitations ON invitations.guest_id=guest_list.guest_id LEFT JOIN wedding_events ON wedding_events.event_id=invitations.event_id WHERE guest_list.guest_type="Group Organiser" OR guest_list.guest_type="Sole" ORDER BY guest_list.guest_sname');
$guest_list = $db->query($guest_list_query);
//load events for the filters
$events = $db->query("SELECT event_id, event_name FROM wedding_events");
$total_guests = $db->query("SELECT COUNT(guest_id) AS guest_total FROM guest_list");
$total_guests_r = mysqli_fetch_assoc($total_guests);
?>
<!-- Meta Tags For Each Page -->
<meta name="description" content="Parrot Media - Client Admin Area">
<meta name="title" content="Manage your website content">
<!-- /Meta Tags -->

<!-- / -->
<!-- Page Title -->
<title>Mi-Admin | Guest List</title>
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
            <div class="breadcrumbs mb-2"><a href="index.php" class="breadcrumb">Home</a> / Guest List</div>
            <div class="main-cards">
                <div class="std-card">
                    <?php if ($user->user_type() == "Admin" || $user->user_type() == "Developer") : ?>
                        <?php if ($cms->type() == "Wedding") : ?>
                            <div class="guest-list-header">
                                <h2 class="notification-header">
                                    <svg class="icon feather-icon">
                                        <use xlink:href="assets/img/icons/feather.svg#users"></use>
                                    </svg> Guest List <span class="notification"><?= $total_guests_r['guest_total']; ?></span>
                                </h2>

                                <form action="" method="POST" id="guest_search">
                                    <div class="form-input-wrapper">
                                        <div class="search-input guest-search">
                                            <svg class="icon feather-icon">
                                                <use xlink:href="assets/img/icons/feather.svg#search"></use>
                                            </svg>
                                            <input type="text" id="guest_search" name="search" placeholder="Search for a guest...">
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="grid-row-3-col">
                                <div class="choice-stats-card">
                                    <p>Attending</p> <span>75</span>
                                </div>
                                <div class="choice-stats-card">
                                    <p>Attending</p> <span>75</span>
                                </div>
                                <div class="choice-stats-card">
                                    <p>Attending</p> <span>75</span>
                                </div>
                            </div>
                            <p>Attending: 70</p>
                                <p>Not Replied: 70</p>
                                <p>Not Attending: 70</p>
                            <div class="my-2 sticky">
                                <form action="" method="POST" id="guest_filter">
                                    <div class="form-controls">
                                        <a href="guest.php?action=create" class="btn-primary">Add Guest
                                            <svg class="icon feather-icon">
                                                <use xlink:href="assets/img/icons/feather.svg#user-plus"></use>
                                            </svg>
                                        </a>
                                        <div class="form-input-wrapper">
                                            <label for="event_filter">Filter By Event</label>
                                            <select name="event_filter" id="event_filter">
                                                <option value="" selected>Show All</option>
                                                <?php if ($events->num_rows > 0) : ?>
                                                    <?php foreach ($events as $event) : ?>

                                                        <option value="<?= $event['event_id']; ?>"><?= $event['event_name']; ?></option>
                                                    <?php endforeach; ?>
                                                <?php endif; ?>
                                            </select>
                                        </div>
                                        <div class="form-input-wrapper">
                                            <label for="rsvp_filter">Filter By RSVP</label>
                                            <select name="rsvp_filter" id="rsvp_filter">
                                                <option value="" selected>Show All</option>
                                                <option value="Attending">Attending</option>
                                                <option value="Not Attending">Not Attending</option>
                                                <option value="Not Replied">Not Replied</option>
                                            </select>
                                        </div>
                                    </div>

                                </form>
                            </div>
                            <div id="guest_list">
                                <?php if ($guest_list->num_rows > 0) : ?>
                                    <?php foreach ($guest_list as $guest) : ?>
                                        <div class="guest-card my-2">
                                            <div class="guest-card-body">
                                                <div class="guest-card-title">
                                                    <h3>
                                                        <a href="guest?action=view&guest_id=<?= $guest['guest_id']; ?>" class="guest_name">
                                                            <?php echo $guest['guest_fname'] . ' ' . $guest['guest_sname'];
                                                            if ($guest['guest_extra_invites'] > 0) {
                                                                echo " +" . $guest['guest_extra_invites'];
                                                            } ?>
                                                        </a>
                                                    </h3>
                                                    <?php if ($guest['guest_type'] == "Group Organiser") : ?>
                                                        <a href="" class="group-btn">View Group
                                                            <svg class="icon">
                                                                <use xlink:href="assets/img/icons/solid.svg#chevron-down"></use>
                                                            </svg>
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="guest-card-tags">
                                                    <?php if ($guest['event_id'] > 0) : ?>
                                                        <span class="guest-card-tag">
                                                            <svg class="icon feather-icon">
                                                                <use xlink:href="assets/img/icons/feather.svg#calendar"></use>
                                                            </svg>
                                                            <a href="event?event_id=<?= $guest['event_id']; ?>&action=view"><?= $guest['event_name']; ?></a>
                                                        </span>
                                                    <?php endif; ?>
                                                    <?php if ($guest['invite_rsvp_status'] > 0) : ?>
                                                        <span class="guest-card-tag" data-invite-status="<?= $guest['invite_rsvp_status']; ?>">
                                                            <svg class="icon feather-icon">
                                                                <use xlink:href="assets/img/icons/feather.svg#message-square"></use>
                                                            </svg>
                                                            <?= $guest['invite_rsvp_status']; ?>
                                                        </span>
                                                    <?php endif; ?>
                                                    <span class="guest-card-tag">
                                                        <svg class="icon">
                                                            <use xlink:href="assets/img/icons/solid.svg#reply"></use>
                                                        </svg>
                                                        RSVP CODE: <?= $guest['guest_rsvp_code']; ?>
                                                    </span>
                                                    <span class="guest-card-tag" data-guest-type="<?= $guest['guest_type']; ?>">
                                                        <svg class="icon feather-icon">
                                                            <use xlink:href="assets/img/icons/feather.svg#user"></use>
                                                        </svg>
                                                        <?= $guest['guest_type']; ?>
                                                    </span>
                                                </div>
                                            </div>
                                            <?php if ($guest['guest_type'] == "Group Organiser") : ?>
                                                <div class="guest-group-card d-none">
                                                    <div class="guest-group">
                                                        <h3><svg class="icon feather-icon">
                                                                <use xlink:href="assets/img/icons/feather.svg#users"></use>
                                                            </svg> <?= $guest['guest_fname']; ?>'s extra invites </h3>
                                                        <?php $guest_group = $db->query("SELECT guest_id, guest_fname, guest_sname, guest_rsvp_status FROM guest_list WHERE guest_group_id=" . $guest['guest_group_id'] . " AND guest_type='Member'"); ?>
                                                        <?php foreach ($guest_group as $member) : ?>
                                                            <a href="guest?action=view&guest_id=<?= $member['guest_id']; ?>" data-rsvp="<?= $member['guest_rsvp_status']; ?>"><svg class="icon feather-icon">
                                                                    <use xlink:href="assets/img/icons/feather.svg#user"></use>
                                                                </svg> <?= $member['guest_fname'] . " " . $member['guest_sname']; ?> <svg class="icon feather-icon d-none">
                                                                    <use xlink:href="assets/img/icons/feather.svg#alert-circle"></use>
                                                                </svg></a>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                </div>


            <?php endif; ?>
        <?php else : ?>
            <p class="font-emphasis">You do not have the necessary Administrator rights to view this page.</p>
        <?php endif; ?>
            </div>

        </section>


    </main>
    <!-- Footer -->
    <?php include("./inc/footer.inc.php"); ?>
    <!-- /Footer -->

</body>


<script src="assets/js/guest_list.js"></script>

</html>