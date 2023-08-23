<?php
session_start();
require("scripts/functions.php");
check_login();
include("./connect.php");


$guestlist = fopen("scripts/guestlist.csv", "w") or die("Unable to open file!");

$query =("SELECT  guest_list.guest_id, guest_list.guest_fname, guest_list.guest_sname,guest_list.guest_extra_invites, guest_list.guest_rsvp_code,guest_list.guest_address, guest_list.guest_postcode, guest_list.guest_group_id, guest_groups.guest_group_id, guest_groups.guest_group_name, invitations.guest_id, invitations.event_id, wedding_events.event_id, wedding_events.event_name FROM guest_list  
   LEFT JOIN invitations ON guest_list.guest_id = invitations.guest_id
   LEFT JOIN wedding_events ON wedding_events.event_id=invitations.event_id
   LEFT JOIN guest_groups ON guest_groups.guest_group_id=guest_list.guest_group_id
  WHERE invitations.guest_id=guest_list.guest_id
  ORDER BY wedding_events.event_id 
  ");

$fetch = $db->query($query);
$query_fetch = $fetch->fetch_array();

$note=array("NOTE: Save this file as an Excel workbook and remove this line. If you make changes to your guest list then make sure you download this again.");
fputcsv($guestlist, $note);
$headers = array('Guest ID', 'First Name', 'Surname','Additional Invites', 'RSVP Code', 'Address', 'Postcode', '','Guest Group Name', 'Event ID', 'Event Name');
fputcsv($guestlist, $headers);
foreach ($fetch as $line) {
  fputcsv($guestlist, $line);
}
fclose($guestlist);
include("inc/head.inc.php");
include("inc/settings.php");

//////////////////////////////////////////////////////////////////Everything above this applies to each page\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\



?>
<!-- Meta Tags For Each Page -->
<meta name="description" content="Parrot Media - Client Admin Area">
<meta name="title" content="Manage your website content">
<!-- /Meta Tags -->

<!-- / -->
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


            <div class="breadcrumbs mb-2"><a href="index.php" class="breadcrumb">Home</a> / Download Invitations</div>
            <div class="main-cards">


                <?php if ($user->user_type() == "Admin" || $user->user_type() == "Developer") : ?>


                    <?php if ($cms->type() == "Wedding") : ?>
                        <h2><i class="fa-solid fa-champagne-glasses"></i> Download Your Invitations</h2>
                        <p>Only do this once you are happy with your guest list and you have assigned all guests to the correct event.</p>
                        <p>Your guest list is now ready to download. Click the button below.</p>
                        <a class="btn-primary" href="scripts/guestlist.csv" download="Guest List <?= date('d-m-y');?>.csv"><svg class="icon"><use xlink:href="assets/img/icons/solid.svg#download"></use></svg> Download  </a>
                        

                        <div class="std-card d-none" id="invite_list">

                        </div>
                    <?php endif; ?>
                <?php else : ?>
                    <p class="font-emphasis">You do not have the necessary Administrator rights to view this page.</p>
                <?php endif; ?>
            </div>

        </section>


    </main>

    <!-- /Main Body Of Page -->
    <!-- Quote request form script -->

    <!-- /Quote request form script -->
    <!-- Footer -->
    <?php include("./inc/footer.inc.php"); ?>
    <!-- /Footer -->

</body>
<script>
    $(document).ready(function() {
        url = "scripts/invitations_dl.script.php?action=load_guest_list";
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

</script>

</html>