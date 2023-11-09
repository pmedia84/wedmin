<?php
session_start();
require("scripts/functions.php");
check_login();
$cms = new Cms();
$cms->setup();
$user = new User();
//connect to DB
db_connect($db);
//T== Type of list
//Groups, shows all guest groups
// All guests
//variable for loading the query type, I.e all guests, confirmed guests or not replied etc
$rsvp="";
//e == the filter type, such as event, use the id number
$event_id=null;
//event filter
$e_filter="";
//search filter
$s = "";
$search="";
if(isset($_GET['rsvp'])){
    $rsvp=$_GET['rsvp'];
}
//change rsvp filter
switch($rsvp){
    case"confirmed":
        $rsvp="WHERE guest_list.guest_rsvp_status='Attending'";
        break;
    case"declined":
        $rsvp="WHERE guest_list.guest_rsvp_status='Not Attending'";
        break;
    case"awaiting":
        $rsvp="WHERE guest_list.guest_rsvp_status='Not Replied'";
        break;
    default:
    $rsvp="WHERE guest_list.guest_rsvp_status IN ('Not Replied','Not Attending', 'Attending')";
}
if(isset($_GET['e'])){
    $event_id=$_GET['e'];
}
//change rsvp filter
switch($event_id){
    case NULL:
        $e_filter=" ";
        break;
     case  !NULL:
        $e_filter="AND wedding_events.event_id=".$event_id;   



}

//load the search filter if set from GET request
if(isset($_GET['s'])){
    $s=$_GET['s'];
//change rsvp filter
switch($search){
    case NULL:
        $search="AND guest_list.guest_fname LIKE '%".$s."%' OR guest_list.guest_sname LIKE '%".$s."%'";
        break;
     case  !NULL:
        $search="AND guest_list.guest_fname LIKE '%".$s."%' OR guest_list.guest_sname LIKE '%".$s."%'";   



}
}
//find wedding guest list
$guest_list_q = $db->query('SELECT guest_list.guest_id, guest_list.guest_fname, guest_list.guest_sname, guest_list.guest_type, guest_list.guest_extra_invites, guest_list.guest_group_id, guest_list.guest_rsvp_code,guest_list.guest_rsvp_status, invitations.event_id, invitations.invite_rsvp_status, wedding_events.event_id, wedding_events.event_name, guest_groups.guest_group_id, guest_groups.guest_group_name  FROM guest_list LEFT JOIN invitations ON invitations.guest_id=guest_list.guest_id LEFT JOIN guest_groups ON guest_groups.guest_group_id=guest_list.guest_group_id LEFT JOIN wedding_events ON wedding_events.event_id=invitations.event_id ' . $rsvp . " " . $e_filter . ' ' . $search . '');
switch ($event_id) {
    case NULL:
        $totals_filter = " ";
        break;
    case  !NULL:
        $totals_filter = "WHERE event_id=" . $event_id;
}
//load different totals query if the event filter has been selected
if (isset($_GET['e'])) {
    $totals_q = $db->query("SELECT 
        COUNT(guest_id) AS all_guests, 
        COUNT(CASE invite_rsvp_status WHEN 'Attending' THEN 1 ELSE NULL END) AS attending, 
        COUNT(CASE invite_rsvp_status WHEN 'Not Attending' THEN 1 ELSE NULL END) AS declined,
        COUNT(CASE invite_rsvp_status WHEN 'Not Replied' THEN 1 ELSE NULL END) AS awaiting
         FROM invitations " . $totals_filter);
    $all_guests = 0;
    $attending = 0;
    $declined = 0;
    $awaiting = 0;
    if ($totals_q->num_rows > 0) {
        $totals_r = mysqli_fetch_assoc($totals_q);
        $all_guests = $totals_r['all_guests'];
        $attending = $totals_r['attending'];
        $declined = $totals_r['declined'];
        $awaiting = $totals_r['awaiting'];
    }
} else {
    //if it has not been selected, then fetch the totals from the guest list rather than the invites table

    $totals_filter = "";
    switch ($event_id) {
        case NULL:
            $totals_filter = " ";
            break;
        case  !NULL:
            $totals_filter = "WHERE event_id=" . $event_id;
    }
    $totals_q = $db->query("SELECT 
    COUNT(guest_id) AS all_guests, 
    COUNT(CASE guest_rsvp_status WHEN 'Attending' THEN 1 ELSE NULL END) AS attending, 
    COUNT(CASE guest_rsvp_status WHEN 'Not Attending' THEN 1 ELSE NULL END) AS declined,
    COUNT(CASE guest_rsvp_status WHEN 'Not Replied' THEN 1 ELSE NULL END) AS awaiting
     FROM guest_list " . $totals_filter);
    $all_guests = 0;
    $attending = 0;
    $declined = 0;
    $awaiting = 0;
    if ($totals_q->num_rows > 0) {
        $totals_r = mysqli_fetch_assoc($totals_q);
        $all_guests = $totals_r['all_guests'];
        $attending = $totals_r['attending'];
        $declined = $totals_r['declined'];
        $awaiting = $totals_r['awaiting'];
    }
}

//load events for the filters
$events = $db->query("SELECT event_id, event_name FROM wedding_events");
//page meta variables
$meta_description = "Parrot Media - Manage your guest list ";
$meta_page_title = "Mi-Admin | Guest List - " . $cms->w_name();

if (isset($_GET['e'])) {
    //event filter heading only load if get request is loaded
    $event_q = $db->query("SELECT event_id, event_name FROM wedding_events WHERE event_id=" . $event_id);
    if ($event_q->num_rows > 0) {
        $event_r = mysqli_fetch_assoc($event_q);
    }
}

//load events for the filters
$events = $db->query("SELECT event_id, event_name FROM wedding_events");
//page meta variables
$meta_description = "Parrot Media - Manage your guest list ";
$meta_page_title = "Mi-Admin | Guest List - ".$cms->w_name();

if(isset($_GET['e'])){
    //event filter heading only load if get request is loaded
    $event_q= $db->query("SELECT event_id, event_name FROM wedding_events WHERE event_id=".$event_id);
    if($event_q->num_rows>0){
        $event_r=mysqli_fetch_assoc($event_q);
    }
}
//find wedding events details
$wedding_events_query = ('SELECT * FROM wedding_events ORDER BY event_time');
$wedding_events = $db->query($wedding_events_query);
$wedding_events_result = $wedding_events->fetch_assoc();
//!load guest canvas if teh get request with GUEST_ID has been set

 if(isset($_GET['guest_id'])){

     //load guest information
     $guest_id=$_GET['guest_id'];
     $guest_q=$db->query("SELECT * FROM guest_list WHERE guest_id=".$guest_id);
     //search for any events the guest is associated with
     $guest_invites = $db->query('SELECT wedding_events.event_id, wedding_events.event_name, invitations.guest_id, invitations.event_id, guest_list.guest_id, guest_list.guest_fname FROM wedding_events
     LEFT JOIN invitations ON invitations.event_id=wedding_events.event_id
     LEFT JOIN guest_list ON guest_list.guest_id=invitations.guest_id
     WHERE guest_list.guest_id=' . $guest_id);
     if ($guest_invites->num_rows > 1) {
         $guest_invites->fetch_array();
     }
     if ($meal_choices_wedmin->status() == "On") {
         $meal_choices_q = $db->query('SELECT menu_items.menu_item_id, menu_items.menu_item_name, menu_items.course_id, meal_choices.menu_item_id, meal_choices.choice_order_id, meal_choice_order.choice_order_id, meal_choice_order.guest_id, menu_courses.course_name, menu_courses.course_id  FROM menu_items LEFT JOIN meal_choices ON meal_choices.menu_item_id=menu_items.menu_item_id LEFT JOIN meal_choice_order ON meal_choices.choice_order_id=meal_choice_order.choice_order_id LEFT JOIN menu_courses ON menu_courses.course_id=menu_items.course_id WHERE meal_choice_order.guest_id=' . $guest_id);
     }
 }
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
        <?php include("inc/header.inc.php"); ?>
        <!-- Nav Bar -->
        <?php include("./inc/nav.inc.php"); ?>
        <!-- /nav bar -->
        <section class="body">
            <!-- <div class="breadcrumbs mb-2"><a href="index.php" class="breadcrumb">Home</a> / Guest List</div> -->
            <div class="main-cards">
                <div class="main-cards_header">
                    <h1 class="notification-header">
                        Guest List <span class="notification"><?= $totals_r['all_guests']; ?></span>
                    </h1>
                    <button  class="btn-primary" data-data="add_guest" id="add_guest_btn" ><svg class="icon feather-icon">
                            <use xlink:href="assets/img/icons/feather.svg#user-plus" ></use>
                        </svg>Add Guest
                    </button>
                    
                </div>
                <div class="std-card">
                    <?php if ($user->user_type() == "Admin" || $user->user_type() == "Developer") : ?>
                        <div class="my-2">
                            <form action="" method="POST" id="guest_list_filter">
                                <div class="form-controls">
                                    <div class="form-input-wrapper">
                                        <label for="event_filter">Filter By Event</label>
                                        <select name="event_filter" id="event_filter">
                                            <option value="" selected>Show All</option>
                                            <?php if ($events->num_rows > 0) : ?>
                                                <?php foreach ($events as $event) : ?>

                                                    <option value="<?= $event['event_id']; ?>" <?php if(isset($_GET['e']) && $event['event_id']== $_GET['e']):?>selected<?php endif?>><?= $event['event_name']; ?></option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                    <div class="form-input-wrapper">
                                <div class="search-input guest-search">
                                    <svg class="icon feather-icon">
                                        <use xlink:href="assets/img/icons/feather.svg#search"></use>
                                    </svg>
                                    <input type="text" id="guest_search" name="search" placeholder="Search for a guest..." <?php if(isset($_GET['s'])):?>value="<?=$_GET['s'];?>"<?php endif?>>
                                </div>
                            </div>
                                </div>

                            </form>
                        </div>
                        <div id="guest_list">
                            <?php if(isset($_GET['e'])):?>
                                <h2 class="my-2">Guest List for <?=$event_r['event_name'];?></h2>
                                <?php else:?>
                                    <h2 class="my-2">Guest List for all events</h2>
                                <?php endif;?>
                                <?php if(isset($_GET['s'])):?>
                                    <h3 class="my-2"><?= $guest_list_q->num_rows;?> Guests found matching "<?=$_GET['s'];?>"</h3>
                                <?php endif;?>
                            <div class="table-filter-header">
                                <a href="guest_list?rsvp=all<?php if(isset($_GET['e'])){echo "&e=".$_GET['e'];} if(isset($_GET['s'])){echo "&s=".$_GET['s'];}?>" class="table-filter-header_link <?php if(isset($_GET['rsvp']) && $_GET['rsvp']=="all"){echo "active";}?>">All Guests (<?=$all_guests;?>)</a >
                                <a href="guest_list?rsvp=confirmed<?php if(isset($_GET['e'])){echo "&e=".$_GET['e'];} if(isset($_GET['s'])){echo "&s=".$_GET['s'];}?>" class="<?php if(isset($_GET['rsvp']) && $_GET['rsvp']=="confirmed"){echo "active";}?> table-filter-header_link">Attending (<?=$attending;?>)</a >
                                <a href="guest_list?rsvp=declined<?php if(isset($_GET['e'])){echo "&e=".$_GET['e'];} if(isset($_GET['s'])){echo "&s=".$_GET['s'];}?>" class="table-filter-header_link <?php if(isset($_GET['rsvp']) && $_GET['rsvp']=="declined"){echo "active";}?>">Declined (<?=$declined;?>)</a >
                                <a href="guest_list?rsvp=awaiting<?php if(isset($_GET['e'])){echo "&e=".$_GET['e'];} if(isset($_GET['s'])){echo "&s=".$_GET['s'];}?>" class="table-filter-header_link <?php if(isset($_GET['rsvp']) && $_GET['rsvp']=="awaiting"){echo "active";}?>">Awaiting (<?=$awaiting;?>)</a >
                            </div>
                            <?php if($guest_list_q->num_rows>0):?>
                                <div class="my-2 table-wrapper">
                                    <table class="std-table">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>RSVP</th>
                                                <th>Event</th>
                                                <th>Group</th>
                                            </tr>
                                        </thead>
                                        <?php foreach($guest_list_q as $guest):?>
                                            <tr>
                                                <td><a href="" data-guest_id="<?=$guest['guest_id'];?>" data-data="load_guest" class="canvas_open" data-state="closed"><?=$guest['guest_fname']." ".$guest['guest_sname'];?></a></td>
                                                <td><span class="status-pill" data-rsvp="<?=$guest['guest_rsvp_status'];?>"><svg class="icon feather-icon"><use xlink:href="assets/img/icons/feather.svg#circle"></use></svg> <?=$guest['guest_rsvp_status'];?></span></td>
                                                <td><a href="event?event_id=<?=$guest['event_id'];?>&action=view"><?=$guest['event_name'];?></a></td>
                                                <td><a href="event?event_id=<?=$guest['event_id'];?>&action=view"><?=$guest['guest_group_name'];?></a></td>
                                            </tr>
                                        <?php endforeach;?>
                                    </table>
                                </div>
                            <?php endif;?>
                        </div>
        <?php else : ?>
            <p class="font-emphasis">You do not have access rights to this page</p>
        <?php endif; ?>
            </div>

        </section>

<div class="offcanvas_bg" <?php if(isset($_GET['guest_id'])):?>data-state="opened"<?php else:?>data-state="closed"<?php endif;?>>
</div>
<div class="offcanvas_canvas" <?php if(isset($_GET['guest_id'])):?>data-state="opened"<?php else:?>data-state="closed"<?php endif;?> id="canvas">

    
    <?php if(isset($_GET['guest_id'])):?>
    <?php if($guest_q->num_rows >0):
        $guest_r=mysqli_fetch_assoc($guest_q);
        ?>
    <div class="modal-header offcanvas_header">
        <h2 class="modal-title"><svg class="icon feather-icon">
            <use xlink:href="assets/img/icons/feather.svg#user-check"></use>
        </svg> <span id="canvas-title">Edit Guest</span></h2>
        <button class="btn-close" type="button" id="close-canvas">
            <svg class="icon x-mark">
                <use href="assets/img/icons/solid.svg#xmark" />
            </svg>
        </button>
    </div>
    <div class="offcanvas_body">
        <form id="edit_guest" action="scripts/guest.script.php" method="POST" >
        <div class="std-card">
            <h2><?=$guest_r['guest_fname']." ".$guest_r['guest_sname']; if($guest_r['guest_extra_invites']>0){echo " +".$guest_r['guest_extra_invites'];}?></h2>
            <div class="guest-card-tags my-2">
            <span class="guest-card-tag" data-guest-type="<?= $guest_r['guest_type']; ?>">
                <svg class="icon feather-icon">
                    <use xlink:href="assets/img/icons/feather.svg#user"></use>
                </svg>
                <?= $guest_r['guest_type']; ?>
            </span>
            <span class="guest-card-tag" data-invite-status="<?= $guest_r['guest_rsvp_status']; ?>">
                    <svg class="icon feather-icon">
                        <use xlink:href="assets/img/icons/feather.svg#message-square"></use>
                    </svg>
                    <?= $guest_r['guest_rsvp_status']; ?>
                </span>

        </div>
            <div class="form-input-wrapper">
                <label for="guest_fname"><strong>First Name</strong></label>
                <!-- input -->
                <input class="text-input input" type="text" name="guest_fname" id="guest_fname" placeholder="First Name" required="" value="<?=$guest_r['guest_fname'];?>" title="First Name">
            </div>
            <div class="form-input-wrapper">
                <label for="guest_sname"><strong>Surname</strong></label>
                <!-- input -->
                <input class="text-input input" type="text" name="guest_sname" id="guest_sname" placeholder="Surname" required="" value="<?=$guest_r['guest_sname'];?>" title="Surname">
            </div>
            <div class="form-input-wrapper my-2">
                <label for="guest_email"><strong>Email Address</strong></label>
                <p class="form-hint-small">Optional, guests that use your guest area will update this themselves.</p>
                <input class="text-input input" type="text" id="guest_email" name="guest_email" placeholder="Email Address" value="<?=$guest_r['guest_email'];?>">
            </div>
            <button class="btn-primary btn-secondary my-2" type="button" id="show_address"><svg class="icon">
                    <use xlink:href="assets/img/icons/solid.svg#map-location-dot"></use>
                </svg> Address</button>
            <div class="form-hidden d-none">
                <div class="form-input-wrapper my-2">
                    <label for="guest_address"><strong>Address</strong></label>
                    <p class="form-hint-small">Optional</p>
                    <textarea name="guest_address" id="guest_address"><?=$guest_r['guest_address'];?></textarea>
                </div>
                <div class="form-input-wrapper my-2">
                    <label for="guest_postcode"><strong>Postcode</strong></label>
                    <p class="form-hint-small">Optional</p>
                    <input class="text-input input" type="text" id="guest_postcode" name="guest_postcode" placeholder="Postcode" value="<?=$guest_r['guest_postcode'];?>">
                </div>
            </div>
        </div>
        <div class="tab-buttons">
            <button class="tablinks active" onclick="openTab(event, 'rsvp')" type="button">
                <svg class="icon feather-icon">
                    <use xlink:href="assets/img/icons/feather.svg#message-square"></use>
                </svg> RSVP
            </button>
            <button class="tablinks" onclick="openTab(event, 'group')" type="button">
                <svg class="icon feather-icon">
                    <use xlink:href="assets/img/icons/feather.svg#users"></use>
                </svg> Guest Group
            </button>
            <?php if ($meal_choices_wedmin->status() == "On"):?>
                <?php if ($meal_choices_q->num_rows > 0) :?>
            <button class="tablinks" onclick="openTab(event, 'meal-choices')" type="button">
                <svg class="icon">
                    <use xlink:href="assets/img/icons/solid.svg#utensils"></use>
                </svg> Meal Choices
            </button>
            <?php endif;?>
            <?php endif;?>
        </div>
        <!-- Tab content -->
        <div id="rsvp" class="tabcontent " style="display: block;">
        <?php if($guest_r['guest_rsvp_code']!=NULL):?>
            <div class="guest-card-tags">
                <span class="guest-card-tag">
                    <svg class="icon">
                        <use xlink:href="assets/img/icons/solid.svg#reply"></use>
                    </svg>
                    RSVP CODE: <?= $guest_r['guest_rsvp_code']; ?>
                </span>
            </div>
            <?php endif;?>
            <div class="my-2">
                <h3>Invitation</h3>
                <p><?=$guest_r['guest_fname'];?> is invited to:</p>
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
            <?php if($guest_r['guest_rsvp_message']!=""):?>
        
                <h3>RSVP Message</h3>
            <p><svg class="icon">
                    <use xlink:href="assets/img/icons/solid.svg#quote-left"></use>
                </svg> <?= html_entity_decode($guest_r['guest_rsvp_message']);?> <svg class="icon">
                    <use xlink:href="assets/img/icons/solid.svg#quote-right"></use>
                </svg></p>
            
            <?php endif;?>
            <h3>RSVP Status</h3>
            <p>This will update as your guests respond to their invite. You can do this manually if you need to as well.</p>

            <div class="form-input-wrapper">
                <label for="rsvp_status">Update RSVP Status</label>
                <select name="rsvp_status" id="rsvp_status" class="guest-rsvp-status-select" data-invite-status="<?= $guest_r['guest_rsvp_status']; ?>">
                    <option value="0" selected><?= $guest_r['guest_rsvp_status']; ?></option>
                    <?php if($guest_r['guest_rsvp_status']!="Attending"):?>
                        <option value="Attending">Attending</option>
                    <?php endif;?>
                    <?php if($guest_r['guest_rsvp_status']!="Not Replied"):?>
                        <option value="Not Replied">Awaiting</option>
                    <?php endif;?>
                    <?php if($guest_r['guest_rsvp_status']!="Not Attending"):?>
                        <option value="Not Attending">Declined</option>
                    <?php endif;?>
                </select>
            </div>
        </div>

        <div id="group" class="tabcontent">
                <?php if($guest_r['guest_type']=="Sole"): ?>
                    <div class="std-card my-2">
                        <h3>
                            <svg class="icon feather-icon">
                                <use xlink:href="assets/img/icons/feather.svg#users"></use>
                            </svg>
                            Additional Invites</h3>
                        <div id="guest-group-row"></div>
                        <button class="btn-primary btn-secondary my-2" type="button" id="add-member" data-guest_sname=""><svg class="icon">
                                <use xlink:href="assets/img/icons/solid.svg#user-plus"></use>
                            </svg> Add Guests</button>
                    </div>
                <?php endif;?>
                <?php if($guest_r['guest_type']=="Group Organiser" ):
                $guest_group_query = ('SELECT guest_id, guest_fname, guest_sname, guest_group_id, guest_rsvp_status FROM guest_list WHERE guest_group_id=' . $guest_r['guest_group_id'] . ' AND guest_type ="Member"');
                $guest_group = $db->query($guest_group_query); ?>

                    <div class="my-2">
                        <h3>
                            <svg class="icon feather-icon">
                                <use xlink:href="assets/img/icons/feather.svg#users"></use>
                            </svg>
                            Additional Invites</h3>
                            <?php if($guest_r['guest_extra_invites']>0):  ?>
                                <table class="std-table">
                                    <tr>
                                        <th>Name</th>
                                        <th>RSVP</th>
                                    </tr>
                                    <?php foreach ($guest_group as $member) : ?>
                                <tr>
                                    <td><a href="#" data-guest_id="<?=$member['guest_id'];?>" data-data="load_guest" class="canvas_open" data-state="closed"><?=$member['guest_fname']." ".$member['guest_sname'];?></a></td>
                                    <td>
                                    <div class="guest-card-tags">
                                        <span class="guest-card-tag" data-invite-status="<?= $member['guest_rsvp_status']; ?>">
                                            <svg class="icon feather-icon">
                                                <use xlink:href="assets/img/icons/feather.svg#message-square"></use>
                                            </svg>
                                                <?= $member['guest_rsvp_status']; ?>
                                        </span>
                                    </div>
                                    </td>
                                </tr>
                               
                                
                            <?php endforeach; ?>
                                </table>
                                <?php endif;?>
                        <div id="guest-group-row"></div>
                        <button class="btn-primary btn-secondary my-2" type="button" id="add-member" data-guest_sname="<?=$guest_r['guest_sname'];?>"><svg class="icon">
                                <use xlink:href="assets/img/icons/solid.svg#user-plus"></use>
                            </svg> Add Guests</button>
                    </div>
                <?php endif;?>
                <?php if($guest_r['guest_type']=="Member"):
                    $group_details_query = ('SELECT guest_id, guest_fname, guest_sname, guest_group_id FROM guest_list WHERE guest_group_id=' . $guest_r['guest_group_id'] . ' AND guest_type = "Group Organiser"');
                    $group_details = $db->query($group_details_query);
                    $group_details_result = $group_details->fetch_assoc();
                
                    ?>
                    <div class="my-2">
                        <h3>Guest Group</h3>
                        <p><?=$guest_r['guest_fname'];?> belongs to a guest group organised by <a href="#"data-guest_id="<?=$group_details_result['guest_id'];?>" data-data="load_guest" class="canvas_open" data-state="opened"><?=$group_details_result['guest_fname']." ".$group_details_result['guest_sname'];?></a></p>
                    </div>
                <?php endif;?>
        </div>

        <div id="meal-choices" class="tabcontent">
                <?php if ($meal_choices_wedmin->status() == "On") : ?>
                    <?php if ($meal_choices_q->num_rows > 0) : ?>
                    <div class="">
                        <h3>Meal Choices</h3>
                            <div class="menu my-2">
                                <?php foreach ($meal_choices_q as $choice) : ?>
                                    <h4><?= $choice['course_name']; ?></h4>
                                    <p><?= $choice['menu_item_name']; ?></p>
                                    <hr>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                <?php endif; ?>
                <?php if($guest_r['guest_dietery']!=NULL):?>
                    <div class="my-2">
                        <h3>Dietary Requirements </h3>
                        <p><?= $guest_r['guest_dietery'] ?></p>
                    </div>
                <?php endif;?>
                <?php if($meal_choices_q->num_rows>0):?>
                    <a href="" class="btn-primary btn-secondary">
                        <svg class="icon feather-icon">
                            <use xlink:href="assets/img/icons/feather.svg#external-link"></use>
                        </svg>
                        Edit Meal Choices
                    </a>
        <?php endif;?>
        </div>







        <div class="button-section my-3">
            <button class="btn-primary form-controls-btn" type="button" id="edit-guest" title="Save this guest and return to your guest list" data-guest_id="<?=$guest_r['guest_id'];?>" data-data="remove_guest"  data-state="closed"> 
                <svg class="icon feather-icon">
                    <use xlink:href="assets/img/icons/feather.svg#save"></use>
                </svg> Save & Close</button>
            <a href="#" class="btn-primary btn-secondary form-controls-btn canvas_open" type="button" id="remove_guest" title="Remove Guest" data-guest_id="<?=$guest_r['guest_id'];?>" data-data="remove_guest"  data-state="closed"> <svg class="icon feather-icon">
                    <use xlink:href="assets/img/icons/feather.svg#user-minus"></use>
                </svg>Remove Guest</a>
        </div>
        <input type="hidden" name="guest_group_id" value="<?=$guest_r['guest_group_id'];?>">
        </form>

        <?php else:?>
            <h2>Script error</h2>
        <?php endif;?>
    </div>

    <?php endif;?>

        
    </div>
</div>       
<div class="response-card-wrapper d-none" id="response-card-wrapper">
    <div class="response-card">
        <div class="response-card-icon">
        <svg class="icon feather-icon " id="error-icon">
            <use href="assets/img/icons/feather.svg#alert-circle" />
        </svg>
        <svg class="icon feather-icon d-none" id="guest-check">
            <use href="assets/img/icons/feather.svg#user-check" />
        </svg>
        <svg class="icon feather-icon d-none" id="guest-removed">
            <use href="assets/img/icons/feather.svg#user-minus" />
        </svg>
        </div>
        <div class="response-card-body">
            <h2 id="response-card-title"></h2>
            <div class="response-card-text">

            </div>
        </div>
    </div>
</div> 
    </main>
    <!-- Footer -->
    <?php include("./inc/footer.inc.php"); ?>
    <!-- /Footer -->

</body>


<script src="assets/js/guest_list.js"></script>

</html>