<?php
session_start();
require("scripts/functions.php");
check_login();
include("connect.php");
include("inc/head.inc.php");
include("inc/settings.php");

//guest variable, only required for edit and view actions
if ($_GET['action'] == "edit" || $_GET['action'] == "view" || $_GET['action'] == "delete") {
    $guest_id = $_GET['guest_id'];
    //find guest details

    $guest = $db->prepare('SELECT * FROM guest_list WHERE guest_id =' . $guest_id);

    $guest->execute();
    $guest->store_result();
    $guest_group_id = ""; //empty variable for pages that don't use the GET request

    if ($meal_choices_wedmin->status() == "On") {
        $meal_choices_q = $db->query('SELECT menu_items.menu_item_id, menu_items.menu_item_name, menu_items.course_id, meal_choices.menu_item_id, meal_choices.choice_order_id, meal_choice_order.choice_order_id, meal_choice_order.guest_id, menu_courses.course_name, menu_courses.course_id  FROM menu_items LEFT JOIN meal_choices ON meal_choices.menu_item_id=menu_items.menu_item_id LEFT JOIN meal_choice_order ON meal_choices.choice_order_id=meal_choice_order.choice_order_id LEFT JOIN menu_courses ON menu_courses.course_id=menu_items.course_id WHERE meal_choice_order.guest_id=' . $guest_id);
    }
} else {
    $guest_id = "";
}
//find wedding events details
$wedding_events_query = ('SELECT * FROM wedding_events ORDER BY event_time');
$wedding_events = $db->query($wedding_events_query);
$wedding_events_result = $wedding_events->fetch_assoc();
?>
<!-- Meta Tags For Each Page -->
<meta name="description" content="Parrot Media - Client Admin Area">
<meta name="title" content="Manage your website content">
<!-- /Meta Tags -->

<!-- / -->
<!-- Page Title -->
<title>Mi-Admin | Manage Guest</title>
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
            <div class="breadcrumbs mb-2">
                <a href="index.php" class="breadcrumb">Home</a> /
                <a href="guest_list.php" class="breadcrumb">Guest List</a>
                <?php if ($_GET['action'] == "edit") : ?>
                    / Edit Guest
                <?php endif; ?>
                <?php if ($_GET['action'] == "delete") : ?>
                    / Delete Guest
                <?php endif; ?>
                <?php if ($_GET['action'] == "view") : ?>
                    / View Guest
                <?php endif; ?>
                <?php if ($_GET['action'] == "create") : ?>
                    / Add Guest
                <?php endif; ?>
            </div>
            <div class="main-cards">
                <?php if ($_GET['action'] == "edit") : ?>
                    <h1><svg class="icon">
                            <use xlink:href="assets/img/icons/feather.svg#user"></use>
                        </svg> Edit Guest</h1>
                <?php endif; ?>
                <?php if ($_GET['action'] == "view") : ?>
                    <h1><svg class="icon">
                            <use xlink:href="assets/img/icons/feather.svg#user"></use>
                        </svg> View Guest</h1>
                <?php endif; ?>
                <?php if ($_GET['action'] == "delete") : ?>
                    <h1><svg class="icon">
                            <use xlink:href="assets/img/icons/feather.svg#user"></use>
                        </svg> Remove Guest</h1>
                <?php endif; ?>
                <?php if ($_GET['action'] == "create") : ?>
                    <h1><svg class="icon">
                            <use xlink:href="assets/img/icons/feather.svg#user"></use>
                        </svg> Add Guest</h1>
                <?php endif; ?>


                <?php if ($user->user_type() == "Admin" || $user->user_type() == "Developer") : //detect if user is an admin or developer 
                ?>
                    <?php if ($_GET['action'] == "delete") : //if action is delete, detect if the confirm is yes or no
                    ?>
                        <?php if ($_GET['confirm'] == "yes") : //if yes then delete the guest
                        ?>
                            <?php if (($guest->num_rows) > 0) :
                                //load guest information
                                $guest->bind_result($guest_id, $guest_fname, $guest_sname, $guest_email, $guest_address, $guest_postcode, $guest_rsvp_code, $guest_rsvp_status, $guest_extra_invites,$guest_rsvp_message, $guest_type, $guest_group_id, $guest_events, $guest_dietery);
                                $guest->fetch();
                                $guest->close();

                                if ($guest_type == "Member") {
                                    $guest_group_manager = $db->query("SELECT guest_group_organiser FROM guest_groups WHERE guest_group_id=" . $guest_group_id);
                                    $guest_group_manager_res = $guest_group_manager->fetch_assoc();
                                    $guest_org_id = $guest_group_manager_res['guest_group_organiser'];
                                }

                                $guest_extra_inv_num = $db->prepare('UPDATE guest_list SET guest_extra_invites=?  WHERE guest_id =?');
                                //delete the user acc
                                $remove_user = "DELETE FROM users WHERE guest_id=$guest_id";
                                if (mysqli_query($db, $remove_user)) {
                                    echo mysqli_error($db);
                                }
                                //remove any guests this user has made and if they are a group organiser
                                if ($guest_type == "Group Organiser") {
                                    $remove_group_guests = "DELETE FROM guest_list WHERE guest_group_id=$guest_group_id";
                                    if (mysqli_query($db, $remove_group_guests)) {
                                        echo mysqli_error($db);
                                    }
                                }
                                // connect to db and delete the guest
                                $remove_guest = "DELETE FROM guest_list WHERE guest_id=$guest_id";
                                if (mysqli_query($db, $remove_guest)) {
                                    // find the new extra invites amount an update the lead guest
                                    if ($guest_type == "Member") {
                                        $guest_group = $db->query("SELECT guest_id FROM guest_list WHERE guest_group_id=" . $guest_group_id . " AND guest_type='Member'");

                                        $guest_extra_invites_num = $guest_group->num_rows;
                                        $guest_extra_inv_num->bind_param('ii', $guest_extra_invites_num, $guest_org_id);
                                        $guest_extra_inv_num->execute();
                                        $guest_extra_inv_num->close();
                                    }

                                    echo '<div class="std-card"><div class="form-response error"><p>' . $guest_fname . ' ' . $guest_sname . ' Has been removed from your guest list</p> <a href="guest_list" class="btn-primary my-2">Return To Guest List</a></div></div>';
                                } else {
                                    echo '<div class="form-response error"><p>Error removing guest, please try again.</p></div>';
                                }
                            ?>
                            <?php else : ?>
                                <div class="std-card">
                                    <h2>Error</h2>
                                    <p>There has been an error, please return to the last page and try again.</p>
                                </div>
                            <?php endif; ?>
                        <?php else : //if not then display the message to confirm the user wants to delete the news article
                        ?>
                            <?php if (($guest->num_rows) > 0) :
                                //load guest information
                                $guest->bind_result($guest_id, $guest_fname, $guest_sname, $guest_email, $guest_address, $guest_postcode, $guest_rsvp_code, $guest_rsvp_status, $guest_extra_invites, $guest_rsvp_message, $guest_type, $guest_group_id, $guest_events, $guest_dietery);
                                $guest->fetch();
                            ?>
                                <div class="std-card">
                                    <h2 class="text-alert">Remove: <?= $guest_fname . ' ' . $guest_sname; ?> From your guest list?</h2>
                                    <p>Are you sure you want to remove this guest from your guest list?</p>
                                    <p><strong>This Cannot Be Reversed</strong></p>
                                    <p><strong>Note:</strong> This will also remove any assignments they have to your events.</p>
                                    <p><strong>Note:</strong> Removing this guest will also remove any extra guests they may have added, as well as their access to your guest area.</p>
                                    <h3>RSVP Status</h3>
                                    <p><?php if ($guest_rsvp_status == "") : ?>Not Responded<?php else : echo $guest_rsvp_status;
                                                                                        endif; ?></p>
                                    <div class="button-section">
                                        <a class="btn-primary btn-delete my-2" href="guest.php?action=delete&confirm=yes&guest_id=<?= $guest_id; ?>"><svg class="icon">
                                                <use xlink:href="assets/img/icons/solid.svg#user-minus"></use>
                                            </svg>Remove Guest</a>
                                        <a class="btn-primary btn-secondary my-2" href="guest.php?action=view&guest_id=<?= $guest_id; ?>"><svg class="icon">
                                                <use xlink:href="assets/img/icons/solid.svg#ban"></use>
                                            </svg>Cancel</a>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>



                    <?php endif; ?>

                    <?php if ($_GET['action'] == "create") : ?>
                        <form id="add_guest" action="scripts/guest.script.php" method="POST" enctype="multipart/form-data">
                            <div class="std-card my-3">
                                <h2>Main Guest</h2>
                                <div class="form-input-wrapper">
                                    <label for="guest_fname"><strong>First Name</strong></label>
                                    <!-- input -->
                                    <input class="text-input input" type="text" name="guest_fname" id="guest_fname" placeholder="Guest First Name" required="" maxlength="45">
                                </div>
                                <div class="form-input-wrapper">
                                    <label for="guest_sname"><strong>Surname</strong></label>
                                    <!-- input -->
                                    <input class="text-input input" type="text" name="guest_sname" id="guest_sname" placeholder="Guest Surname" required="" maxlength="45">
                                </div>
                                <div class="form-input-wrapper my-2">
                                    <label for="guest_email"><strong>Email Address</strong></label>
                                    <p class="form-hint-small">Optional, guests that use your guest area will update this themselves.</p>
                                    <input class="text-input input" type="text" id="guest_email" name="guest_email" placeholder="Email Address">
                                </div>
                                <button class="btn-primary btn-secondary my-2" type="button" id="show_address"><svg class="icon">
                                        <use xlink:href="assets/img/icons/solid.svg#map-location-dot"></use>
                                    </svg> Address</button>
                                <div class="form-hidden d-none">
                                    <div class="form-input-wrapper my-2">
                                        <label for="guest_address"><strong>Address</strong></label>
                                        <p class="form-hint-small">Optional</p>
                                        <textarea name="guest_address" id="guest_address"></textarea>
                                    </div>
                                    <div class="form-input-wrapper my-2">
                                        <label for="guest_postcode"><strong>Postcode</strong></label>
                                        <p class="form-hint-small">Optional</p>
                                        <input class="text-input input" type="text" id="guest_postcode" name="guest_postcode" placeholder="Postcode">
                                    </div>
                                </div>

                            </div>

                            <div class="std-card my-3">
                                <h2>Additional Guests</h2>
                                <p>You can assign this guest extra invites here, if you know who they will be bringing with them.</p>
                                <p>If you are unsure of their name, tick the box below each guest and they will be added as a plus one.</p>
                                <div id="guest-group-row"></div>
                                <button class="btn-primary btn-secondary my-2" type="button" id="add-member"><svg class="icon">
                                        <use xlink:href="assets/img/icons/solid.svg#user-plus"></use>
                                    </svg> Add Guests</button>
                            </div>
                            <div class="std-card my-3">
                                <h2>Assign To Events</h2>
                                <p>You can assign this guest to your events now, or you can do this within the event manager tab.</p>

                                <?php if ($wedding_events->num_rows > 0) : ?>
                                    <?php foreach ($wedding_events as $event) : ?>
                                        <div class="form-input-wrapper my-2">
                                            <label class="radio-form-control" for="eventid<?= $event['event_id']; ?>"> <strong><?= $event['event_name']; ?></strong>
                                                <input class="radio" type="radio" id="eventid<?= $event['event_id']; ?>" name="event_id" value="<?= $event['event_id']; ?>" />

                                            </label>
                                        </div>

                                    <?php endforeach; ?>
                                <?php else : ?>
                                    <p>There are no events set up, you will need to create them in your <a href="events">Events Tab</a></p>
                                <?php endif; ?>
                            </div>
                            <div class="button-section my-3">
                                <button class="btn-primary form-controls-btn" type="button" id="save-and-new" title="Save this guest and add another"> Save & Add Another Guest</button>
                                <button class="btn-primary  btn-secondary form-controls-btn" type="button" id="save-guest" title="Save this guest and return to your guest list"> Save & Return To Guest List</button>
                                <a href="guest_list" class="btn-primary btn-secondary">Return To Guest List</a>
                            </div>

                            <div id="response" class="d-none">
                            </div>
                        </form>


                    <?php endif; ?>



                    <?php if ($_GET['action'] == "edit") : ?>
                        <?php if (($guest->num_rows) > 0) :
                            //load guest information
                            $guest->bind_result($guest_id, $guest_fname, $guest_sname, $guest_email, $guest_address, $guest_postcode, $guest_rsvp_code, $guest_rsvp_status, $guest_extra_invites, $guest_rsvp_message, $guest_type, $guest_group_id, $guest_events, $guest_dietery);
                            $guest->fetch();
                            //search for any events the guest is associated with
                            $guest_invites = $db->query('SELECT wedding_events.event_id, wedding_events.event_name, invitations.guest_id, invitations.event_id, guest_list.guest_id, guest_list.guest_fname FROM wedding_events
                            LEFT JOIN invitations ON invitations.event_id=wedding_events.event_id
                            LEFT JOIN guest_list ON guest_list.guest_id=invitations.guest_id
                            WHERE guest_list.guest_id=' . $guest_id);
                            if ($guest_invites->num_rows > 1) {
                                $guest_invites->fetch_array();
                            }
                            if ($guest_type == "Group Organiser") {
                                $guest_group_query = ('SELECT guest_id, guest_fname, guest_sname, guest_rsvp_status, guest_events FROM guest_list  WHERE guest_group_id=' . $guest_group_id . ' AND guest_type="Member"');
                                $guest_group = $db->query($guest_group_query);
                                $guest_group_result = $guest_group->fetch_assoc();
                            }
                            //load guest group information if they are an organiser
                            if ($guest_type == "Group Organiser") {
                                $group_details = $db->prepare('SELECT guest_group_status FROM guest_groups  WHERE guest_group_id=' . $guest_group_id);

                                $group_details->execute();
                                $group_details->bind_result($guest_group_status);
                                $group_details->fetch();
                                //$group_details->close();
                            } else {
                                $guest_group_status = "";
                            }
                        ?>
                            <h2><?= $guest_fname . ' ' . $guest_sname; ?></h2>
                            <div class="card-actions">
                                <a class="" href="guest_list?"><svg class="icon">
                                        <use xlink:href="assets/img/icons/solid.svg#left-long"></use>
                                    </svg> Return To Guest List </a>
                            </div>

                            <form id="edit_guest" action="scripts/guest.script.php" method="POST" enctype="multipart/form-data">
                                <div class="std-card">
                                    <?php if ($guest_type == "Group Organiser") : ?>
                                        <h2>Main Guest</h2>
                                    <?php endif; ?>
                                    <?php if ($guest_type == "Sole") : ?>
                                        <h2>Guest</h2>
                                    <?php endif; ?>
                                    <?php if ($guest_type == "Member") : ?>
                                        <h2>Group Member</h2>
                                        <p><?= $guest_fname; ?> is a group member, you won't be able to assign extra invites to them. If you want to do that you will need to remove them and create them as a new guest with extra invites.</p>
                                    <?php endif; ?>
                                    <div class="form-input-wrapper">
                                        <label for="guest_fname"><strong>First Name</strong></label>
                                        <!-- input -->
                                        <input class="text-input input" type="text" name="guest_fname" id="guest_fname" placeholder="Guest First Name" required="" maxlength="45" value="<?= $guest_fname; ?>">
                                    </div>
                                    <div class="form-input-wrapper">
                                        <label for="guest_sname"><strong>Surname</strong></label>
                                        <!-- input -->
                                        <input class="text-input input" type="text" name="guest_sname" id="guest_sname" placeholder="Guest Surname" required="" maxlength="45" value="<?= $guest_sname; ?>">
                                    </div>
                                    <div class="form-input-wrapper my-2">
                                        <label for="guest_email"><strong>Email Address</strong></label>
                                        <p class="form-hint-small">Optional, guests that use your guest area will update this themselves.</p>
                                        <input class="text-input input" type="text" id="guest_email" name="guest_email" placeholder="Email Address" value="<?= $guest_email; ?>">
                                    </div>
                                    <button class="btn-primary btn-secondary my-2" type="button" id="show_address"><svg class="icon">
                                            <use xlink:href="assets/img/icons/solid.svg#map-location-dot"></use>
                                        </svg> Add Address</button>
                                    <div class="form-hidden d-none">
                                        <div class="form-input-wrapper my-2">
                                            <label for="guest_address"><strong>Address</strong></label>
                                            <p class="form-hint-small">Optional</p>
                                            <textarea name="guest_address" id="guest_address"><?= $guest_address; ?></textarea>
                                        </div>
                                        <div class="form-input-wrapper my-2">
                                            <label for="guest_postcode"><strong>Postcode</strong></label>
                                            <p class="form-hint-small">Optional</p>
                                            <input class="text-input input" type="text" id="guest_postcode" name="guest_postcode" placeholder="Postcode" value="<?= $guest_postcode; ?>">
                                        </div>
                                    </div>


                                </div>
                                <?php if (isset($guest_group) && $guest_group->num_rows > 0) : ?>
                                    <div class="std-card my-2">
                                        <h3>Group</h3>
                                        <p>The guest group that <?= $guest_fname; ?> is organising.</p>

                                        <table class="std-table">

                                            <tr>
                                                <th>Name</th>
                                                <th>Manage</th>

                                            </tr>
                                            <?php foreach ($guest_group as $guest) : ?>
                                                <tr>
                                                    <td><a href="guest.php?action=view&guest_id=<?= $guest['guest_id']; ?>"><?= $guest['guest_fname'] . " " . $guest['guest_sname']; ?></a></td>
                                                    <td>
                                                        <div class="guest-list-actions">
                                                            <a href="guest.php?guest_id=<?= $guest['guest_id']; ?>&action=edit"><svg class="icon">
                                                                    <use xlink:href="assets/img/icons/solid.svg#pen-to-square"></use>
                                                                </svg></a>
                                                            <a href="guest.php?guest_id=<?= $guest['guest_id']; ?>&action=delete&confirm=no"><svg class="icon">
                                                                    <use xlink:href="assets/img/icons/solid.svg#user-minus"></use>
                                                                </svg></a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>

                                        </table>
                                    </div>
                                <?php endif; ?>
                                <?php if ($guest_type == "Group Organiser" || $guest_type == "Sole") : ?>
                                    <div class="std-card my-2">
                                        <h2>Additional Guests</h2>
                                        <p>You can assign this guest extra invites here, if you know who they will be bringing with them.</p>
                                        <p>If you are unsure of their name, tick the box below each guest and they will be added as a plus one.</p>
                                        <div id="guest-group-row"></div>
                                        <button class="btn-primary btn-secondary my-2" type="button" id="add-member"><svg class="icon">
                                                <use xlink:href="assets/img/icons/solid.svg#user-plus"></use>
                                            </svg> Add Guests</button>
                                    </div>
                                <?php endif; ?>
                                <div class="std-card my-2">
                                    <h2>Events</h2>
                                    <?php if ($guest_invites->num_rows >= 1) : ?>

                                        <?php foreach ($guest_invites as $invite) : ?>
                                            <p><?= $guest_fname; ?> is attending the following: </p>
                                            <p><a href="event.php?action=view&event_id=<?= $invite['event_id']; ?>"><?= $invite['event_name']; ?></a></p>
                                            <p>If you want to change this you can do that in your event manager <a href="events.php">Click Here</a></p>
                                        <?php endforeach; ?>
                                    <?php else : ?>
                                        <p><?= $guest_fname; ?> Has not been assigned to any events yet. You can do that in your event manager <a href="events.php">Click Here</a></p>
                                    <?php endif; ?>
                                </div>
                                <div class="button-section my-3">
                                    <button class="btn-primary form-controls-btn" type="submit"><svg class="icon">
                                            <use xlink:href="assets/img/icons/solid.svg#floppy-disk"></use>
                                        </svg> Save Changes</button>
                                </div>

                                <div id="response" class="d-none">
                                </div>
                            </form>


                        <?php else : ?>
                            <div class="std-card">
                                <h2>Error</h2>
                                <p>There has been an error, please return to the last page and try again.</p>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php if ($_GET['action'] == "view") : ?>
                        <?php if (($guest->num_rows) > 0) :
                            $guest->bind_result($guest_id, $guest_fname, $guest_sname, $guest_email, $guest_address, $guest_postcode, $guest_rsvp_code, $guest_rsvp_status, $guest_extra_invites,$guest_rsvp_message, $guest_type, $guest_group_id, $guest_events, $guest_dietery);
                            $guest->fetch();
                            //search for any events the guest is associated with
                            $guest_invites = $db->query('SELECT wedding_events.event_id, wedding_events.event_name, invitations.guest_id, invitations.event_id, guest_list.guest_id, guest_list.guest_fname FROM wedding_events
                            LEFT JOIN invitations ON invitations.event_id=wedding_events.event_id
                            LEFT JOIN guest_list ON guest_list.guest_id=invitations.guest_id
                            WHERE guest_list.guest_id=' . $guest_id);
                            if ($guest_invites->num_rows > 1) {
                                $guest_invites->fetch_array();
                            }
                        ?>
                            <h2><?= $guest_fname . ' ' . $guest_sname; ?><?php if($guest_extra_invites>0){echo " +".$guest_extra_invites;}; ?></h2>
                            <div class="card-actions my-2">
                                <a class="my-2" href="guest_list?"> <svg class="icon feather-icon">
                                            <use xlink:href="assets/img/icons/feather.svg#arrow-left"></use>
                                        </svg> Return To Guest List </a>
                            </div>
                            <div class="std-card">
                                <div class="guest-card-tags">
                                    <span class="guest-card-tag" data-guest-type="<?= $guest_type; ?>">
                                        <svg class="icon feather-icon">
                                            <use xlink:href="assets/img/icons/feather.svg#user"></use>
                                        </svg>
                                        <?= $guest_type; ?>
                                    </span>
                                    <span class="guest-card-tag" data-invite-status="<?= $guest_rsvp_status; ?>">
                                        <svg class="icon feather-icon">
                                            <use xlink:href="assets/img/icons/feather.svg#message-square"></use>
                                        </svg>
                                        <?= $guest_rsvp_status; ?>
                                    </span>
                                    <span class="guest-card-tag">
                                        <svg class="icon">
                                            <use xlink:href="assets/img/icons/solid.svg#reply"></use>
                                        </svg>
                                        RSVP CODE: <?= $guest_rsvp_code; ?>
                                    </span>
                                    <?php if ($guest_invites->num_rows >= 1) : ?>
                                        <?php foreach ($guest_invites as $invite) : ?>
                                            <span class="guest-card-tag">
                                        <svg class="icon feather-icon">
                                            <use xlink:href="assets/img/icons/feather.svg#calendar"></use>
                                        </svg>
                                        <a href="event.php?action=view&event_id=<?= $invite['event_id']; ?>"><?= $invite['event_name']; ?></a>
                                    </span>
                                    <?php endforeach; ?>
                                    <?php endif; ?>

                                </div>
                                <?php if($guest_email!=NULL):?>
                                <h3>Contact Details</h3>
                                <p><strong>eMail: </strong><a href="mailto:<?= $guest_email; ?>"><?= $guest_email; ?></a></p>
                                <?php endif;?>
                                <?php if($guest_address>NULL):?>
                                <h4><strong>Address</strong></h4>
                                <address>
                                    <?= $guest_address; ?>,
                                    <?= $guest_postcode; ?>
                                </address>
                                <?php endif;?>
                                <?php if($guest_rsvp_message!=""):?>
                                 <h3>RSVP Message</h3>
                                 <div class="highlight-card my-2">
                                    <p><svg class="icon">
                                            <use xlink:href="assets/img/icons/solid.svg#quote-left"></use>
                                        </svg> <?= html_entity_decode($guest_rsvp_message);?> <svg class="icon">
                                            <use xlink:href="assets/img/icons/solid.svg#quote-right"></use>
                                        </svg></p>
                                 </div>           
                                 <?php endif;?>
                                 <?php if($guest_dietery!=NULL):?>
                                <h3>Dietary Requirements </h3>
                                <p><?= $guest_dietery; ?></p>
                                <?php endif;?>
                                <div class="card-actions my-2">
                                    <a class="my-2" href="guest.php?action=edit&guest_id=<?= $guest_id ?>"><svg class="icon">
                                            <use xlink:href="assets/img/icons/solid.svg#pen-to-square"></use>
                                        </svg> Edit Guest </a><br>
                                    <a class="my-2" href="guest.php?action=delete&confirm=no&guest_id=<?= $guest_id; ?>"><svg class="icon">
                                            <use xlink:href="assets/img/icons/solid.svg#user-minus"></use>
                                        </svg> Remove Guest </a>
                                    <a class="my-2" href="events.php"><svg class="icon">
                                            <use xlink:href="assets/img/icons/solid.svg#user-plus"></use>
                                        </svg> Assign Guest To Events </a>
                                </div>
                            </div>

                            <?php if ($guest_type == "Group Organiser") :
                                $guest_group_query = ('SELECT guest_id, guest_fname, guest_sname, guest_rsvp_status, guest_events FROM guest_list  WHERE guest_group_id=' . $guest_group_id . ' AND guest_type ="Member" ORDER BY guest_sname');
                                $guest_group = $db->query($guest_group_query);
                                $guest_group_result = $guest_group->fetch_assoc();

                            ?>
                                <div class="std-card">
                                    <h3>Guest Group</h3>
                                    <p>The guest group that <?= $guest_fname; ?> is organising.</p>

                                    <table class="std-table">

                                        <tr>
                                            <th>Name</th>
                                            <th>Attending</th>
                                            <th>RSVP Status</th>

                                        </tr>
                                        <?php foreach ($guest_group as $guest) : ?>
                                            <tr>
                                                <td><a href="guest.php?action=view&guest_id=<?= $guest['guest_id']; ?>"><?= $guest['guest_fname'] . " " . $guest['guest_sname']; ?></a></td>
                                                <td><?= $guest['guest_events']; ?></td>
                                                <td><?= $guest['guest_rsvp_status']; ?></td>
                                            </tr>
                                        <?php endforeach; ?>

                                    </table>
                                </div>
                            <?php endif; ?>
                            <?php if ($guest_type == "Member") :
                                $group_details_query = ('SELECT guest_id, guest_fname, guest_sname, guest_group_id FROM guest_list WHERE guest_group_id=' . $guest_group_id . ' AND guest_type = "Group Organiser"');
                                $group_details = $db->query($group_details_query);
                                $group_details_result = $group_details->fetch_assoc();

                            ?>
                                <?php if ($group_details->num_rows >= 1) : ?>
                                    <div class="std-card">
                                        <h3>Guest Group</h3>
                                        <p><?= $guest_fname; ?> is a member of a guest group that is managed by <a href="guest.php?action=view&guest_id=<?= $group_details_result['guest_id']; ?>"><?= $group_details_result['guest_fname'] . " " . $group_details_result['guest_sname']; ?></a></p>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                            <?php if ($guest_type == "Sole") : ?>
                                <div class="std-card">
                                    <h3>Guest Group</h3>
                                    <p><?= $guest_fname; ?> is not associated with a group, they are a sole invite. If you want them to bring guests, you can assign them invites by editing their details. </p>
                                </div>
                            <?php endif; ?>
                            <?php if ($meal_choices_wedmin->status() == "On") : ?>
                                <div class="std-card">
                                    <h2>Meal Choices</h2>
                                    <?php if ($meal_choices_q->num_rows > 0) : ?>

                                        <div class="menu my-3">
                                            <?php foreach ($meal_choices_q as $choice) : ?>
                                                <h3><?= $choice['course_name']; ?></h3>
                                                <p><?= $choice['menu_item_name']; ?></p>
                                                <hr>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        <?php else : ?>
                            <div class="std-card">
                                <h2>Error</h2>
                                <p>There has been an error, please return to the last page and try again.</p>
                            </div>
                        <?php endif; ?>


            </div>



        </div>
    <?php endif; ?>

<?php else : ?>
    <p class="font-emphasis">You do not have the necessary Administrator rights to view this page.</p>
<?php endif; ?>

    </main>
    <?php include("./inc/footer.inc.php"); ?>
    <!-- /Footer -->
    <?php if (isset($_GET['action']) && $_GET['action'] == "edit") : ?>
        <script>
            //script for editing a guest
            $("#edit_guest").submit(function(event) {
                event.preventDefault();
                //declare form variables and collect GET request information
                guest_id = '<?php echo $guest_id; ?>';
                guest_group_id = '<?php echo $guest_group_id; ?>';
                guest_type = '<?php echo $guest_type; ?>';
                event_id = '<?php echo $invite['event_id']; ?>';
                var formData = new FormData($("#edit_guest").get(0));
                formData.append("action", "edit");
                formData.append("guest_id", guest_id);
                formData.append("guest_group_id", guest_group_id);
                formData.append("guest_type", guest_type);
                formData.append("event_id", event_id);
                $.ajax({ //start ajax post
                    type: "POST",
                    url: "scripts/guest.script.php",
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(data, responseText) {
                        if (data === "success") {
                            window.location.replace('guest.php?action=view&guest_id=' + guest_id);
                        }
                    }
                });
            });
        </script>
    <?php endif; ?>
    <script>
        //script for adding a guest
        $("#save-guest").on("click", function(event) {
            event.preventDefault();
            var formData = new FormData($("#add_guest").get(0));
            formData.append("action", "create");
            $.ajax({ //start ajax post
                type: "POST",
                url: "scripts/guest.script.php",
                data: formData,
                contentType: false,
                processData: false,
                success: function(data, responseText) {
                    if (data === "success") {
                        window.location.replace('guest_list.php');
                    }

                }
            });

        });
    </script>
    <script>
        //script for adding a guest
        $("#save-and-new").on("click", function(event) {
            event.preventDefault();
            var formData = new FormData($("#add_guest").get(0));
            formData.append("action", "create");
            $.ajax({ //start ajax post
                type: "POST",
                url: "scripts/guest.script.php",
                data: formData,
                contentType: false,
                processData: false,
                success: function(data, responseText) {
                    if (data === "success") {
                        window.location.replace('guest?action=create');
                    }

                }
            });

        });
    </script>
    <script>
        $("#show_address").on("click", function() {
            $(".form-hidden").slideToggle(500);
        })
    </script>

    <script>
        var arrcount = 0;
        var guestnum = 1;
        var error = $("error");
        $("#add-member").on("click", function() {
            var inputs = $("<div class='guest-group-member my-3 d-none' ><div class='form-row'><div class='form-input-col'> <label for='guest_group[" + arrcount + "][guest_fname]'><strong>First Name</strong></label><input class='text-input input' type='text' name='guest_group[" + arrcount + "][guest_fname]' placeholder='Guest First Name' required='' id='guest_group[" + arrcount + "][guest_fname]'></div><div class='form-input-col'><label for='guest_group[" + arrcount + "][guest_sname]'><strong>Surname</strong></label><input class='text-input input' type='text' name='guest_group[" + arrcount + "][guest_sname]' id='guest_group[" + arrcount + "][guest_sname]' placeholder='Guest Surname' required=''></div><button class='btn-remove-guest btn-primary btn-delete' type='button'>Remove &#10006;</i></button></div><label class='checkbox-form-control my-2' for='guest_group[" + arrcount + "][plus_one]'><input type='checkbox' id='guest_group[" + arrcount + "][plus_one]' name='guest_group[" + arrcount + "][plus_one]' value='plus_one'><strong>Add as a plus one if unsure of name</strong></label></div>");
            $("#guest-group-row").append(inputs);
            $(".guest-group-member").slideDown(400);


            arrcount++;
        });

        $("#guest-group-row").on("click", ".btn-remove-guest", function() {
            $(this).parents('.guest-group-member').remove();
        });
        $("#guest-group-row").on("change", "input[type=checkbox]", function() {
            if ($(this).is(":checked")) {
                console.log("hello");
                $(this).parents('.guest-group-member').find('input[type=text]').removeAttr('required').addClass('disabled').val('');

            } else {
                $(this).parents('.guest-group-member').find('input[type=text]').attr('required', '').removeClass('disabled');
            };
        })
    </script>
</body>

</html>