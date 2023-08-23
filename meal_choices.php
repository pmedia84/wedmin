<?php
session_start();
require("scripts/functions.php");
check_login();
include("connect.php");
$guestlist = fopen("scripts/choices " . date('d-m-y') . ".csv", "w") or die("Unable to open file!");
$query = ("SELECT meal_choices.menu_item_id, meal_choices.choice_order_id, menu_items.menu_item_name, menu_items.course_id, menu_courses.course_name, menu_courses.course_id, meal_choice_order.choice_order_id, meal_choice_order.guest_id, guest_list.guest_id, guest_list.guest_fname, guest_list.guest_sname FROM meal_choices LEFT JOIN menu_items ON menu_items.menu_item_id=meal_choices.menu_item_id LEFT JOIN menu_courses ON menu_courses.course_id=menu_items.course_id LEFT JOIN meal_choice_order ON meal_choice_order.choice_order_id=meal_choices.choice_order_id LEFT JOIN guest_list ON guest_list.guest_id=meal_choice_order.guest_id ORDER BY guest_list.guest_id, menu_courses.course_id 
  ");
$fetch = $db->query($query);
$query_fetch = $fetch->fetch_array();
$note = array("NOTE: Save this file as an Excel workbook and remove this line. If you make changes to your guest list then make sure you download this again.");
fputcsv($guestlist, $note);
$headers = array('Guest ID', 'First Name', 'Surname', 'Additional Invites', 'RSVP Code', 'Address', 'Postcode', '', 'Guest Group Name', 'Event ID', 'Event Name');
fputcsv($guestlist, $headers);
foreach ($fetch as $line) {
    fputcsv($guestlist, $line);
}
fclose($guestlist);
include("inc/head.inc.php");
include("inc/settings.php");
//select the orders first
$choices_query = $db->query('SELECT meal_choice_order.choice_order_id, meal_choice_order.guest_id, guest_list.guest_fname, guest_list.guest_id, guest_list.guest_sname FROM meal_choice_order LEFT JOIN guest_list ON guest_list.guest_id=meal_choice_order.guest_id');
?>
<!-- Meta Tags For Each Page -->
<meta name="description" content="Parrot Media - Client Admin Area">
<meta name="title" content="Manage your website content">
<!-- /Meta Tags -->
<!-- Page Title -->
<title>Mi-Admin | Guest Meal Choices</title>
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
            <div class="breadcrumbs mb-2">
                <a href="index.php" class="breadcrumb">Home</a> /
                <?php
                if (isset($_GET['action'])) {
                    switch ($_GET['action']) {
                        case $_GET['action'] == "edit":
                            echo "<a href='menu'>Menu Builder</a> / Edit Menu";
                            break;
                        case $_GET['action'] == "delete":
                            echo "<a href='menu'>Menu Builder</a> / Delete Menu";
                            break;
                    }
                } else {
                    echo "Guest Meal Choices";
                }

                ?>
            </div>
            <div class="main-cards">
                <?php if (empty($_GET)) : ?>
                    <h1><svg class="icon">
                            <use xlink:href="assets/img/icons/solid.svg#utensils"></use>
                        </svg> Guest Meal Choices</h1>
                    <p>This page will update as your guests let you know what their choices are from your menu.</p>
                    <p>Once all your guests have given you their choices, you will be able to download or print to a PDF and send to your venue.</p>
                <?php endif; ?>

                <?php if ($user->user_type() == "Admin" || $user->user_type() == "Developer") : ?>
                    <?php if ($meal_choices_m->status() == "On") :
                        $choices_totals = $db->query('SELECT meal_choices.menu_item_id, menu_items.menu_item_id, menu_items.menu_item_name, COUNT(meal_choices.choice_id) AS numberOfChoices FROM meal_choices LEFT JOIN menu_items ON menu_items.menu_item_id=meal_choices.menu_item_id GROUP BY menu_item_name');

                    ?>
                        <div class="std-card form-controls my-2">
                            <a href="scripts/choices_dl" class="btn-primary"><svg class="icon">
                                    <use xlink:href="assets/img/icons/solid.svg#file-excel"></use>
                                </svg> Download Meal Choices</a>
                            <a href="scripts/print_meal_options" download="Meal Options <?= date('d-m-y'); ?>" class="btn-primary"><svg class="icon">
                                    <use xlink:href="assets/img/icons/solid.svg#file-pdf"></use>
                                </svg> Print Meal Choices</a>
                        </div>
                        <div class="std-card">
                            <h2 class="my-2">Meal Choice Totals</h2>
                            <div class="grid-row-3col">
                                <?php foreach ($choices_totals as $choice_total) : ?>
                                    <div class="choice-stats-card">
                                        <p><?= $choice_total['menu_item_name']; ?></p>
                                        <span><?= $choice_total['numberOfChoices']; ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="std-card">
                            <h2>Meal Choices</h2>
                            <table class="meal-choices">
                                <tr>
                                    <th>Name</th>
                                    <th>Choices</th>
                                </tr>

                                <?php foreach ($choices_query as $guest) :
                                    $meal_choices = $db->query('SELECT meal_choices.menu_item_id, meal_choices.choice_order_id, menu_items.menu_item_name, menu_items.course_id, menu_courses.course_name, menu_courses.course_id FROM meal_choices LEFT JOIN menu_items ON menu_items.menu_item_id=meal_choices.menu_item_id LEFT JOIN menu_courses ON menu_courses.course_id=menu_items.course_id WHERE meal_choices.choice_order_id=' . $guest['choice_order_id'] . ' ORDER BY menu_courses.course_id');
                                ?>
                                    <tr>
                                        <td class="guest-name-col" rowspan="<?= $meal_choices->num_rows + 1; ?>"><a href="guest?action=view&guest_id=<?= $guest['guest_id']; ?>"><?= $guest['guest_fname'] . ' ' . $guest['guest_sname']; ?></a></td>
                                    </tr>

                                    <?php foreach ($meal_choices as $choice) : ?>
                                        <tr>
                                            <td><?= $choice['menu_item_name']; ?></td>
                                        </tr>
                                    <?php endforeach; ?>

                                <?php endforeach; ?>
                            </table>
                        <?php else : ?>
                            <div class="std-card">
                                <h2>Guest Meal Choices</h2>
                                <p>This feature is not available to you. Please contact us to have this feature activated.</p>
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

</html>