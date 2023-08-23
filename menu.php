<?php
session_start();
require("scripts/functions.php");
check_login();
include("connect.php");
//handle deleting menu, only process if confirm is yes, re direct to menu page
if (isset($_GET['confirm']) && $_GET['confirm'] == "yes") {

    $delete_menu = "DELETE FROM menu WHERE menu_id=" . $_GET['menu_id'];
    if (mysqli_query($db, $delete_menu)) {
        header("Location: menu");
        exit();
    }
}
include("inc/settings.php");
//load menu
if (empty($_GET)) {
    $menu_query = $db->query('SELECT menu.menu_name, menu.menu_id, menu.event_id, wedding_events.event_id, wedding_events.event_name FROM menu LEFT JOIN wedding_events ON wedding_events.event_id=menu.event_id');
}
if (isset($_GET['action']) && $_GET['action'] == "edit") {
    $menu_query = $db->query('SELECT menu.menu_name, menu.menu_id, menu.event_id, wedding_events.event_id, wedding_events.event_name FROM menu LEFT JOIN wedding_events ON wedding_events.event_id=menu.event_id WHERE menu.menu_id=' . $_GET['menu_id']);
    $menu_result = mysqli_fetch_assoc($menu_query);
    $menu_courses = $db->query('SELECT course_name, course_id FROM menu_courses');
    $course_res = mysqli_fetch_assoc($menu_courses);
}
if (isset($_GET['action']) && $_GET['action'] == "delete") {
    $menu_query = $db->query('SELECT menu.menu_name, menu.menu_id, menu.event_id, wedding_events.event_id, wedding_events.event_name FROM menu LEFT JOIN wedding_events ON wedding_events.event_id=menu.event_id WHERE menu.menu_id=' . $_GET['menu_id']);
    $menu_result = mysqli_fetch_assoc($menu_query);
}
$menu_courses = $db->query('SELECT course_name, course_id FROM menu_courses');
$course_res = mysqli_fetch_assoc($menu_courses);
include("inc/head.inc.php");
?>
<!-- Meta Tags For Each Page -->
<meta name="description" content="Parrot Media - Client Admin Area">
<meta name="title" content="Manage your website content">
<!-- /Meta Tags -->
<!-- Page Title -->
<title>Mi-Admin | Menu Builder</title>
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
                    echo "Menu Builder";
                }

                ?>
            </div>
            <div class="main-cards">
                <?php if (empty($_GET)) : ?>
                    <h1><svg class="icon">
                            <use xlink:href="assets/img/icons/solid.svg#bowl-food"></use>
                        </svg> Menu Builder</h1>
                    <p>Here you can create a menu for your events, this will allow guests to see what food is available or where they can make choices.</p>
                <?php endif; ?>
                <?php if (isset($_GET['action']) && $_GET['action'] == "edit" && $menu_builder->status() == "On") : ?>
                    <h1><svg class="icon">
                            <use xlink:href="assets/img/icons/solid.svg#bowl-food"></use>
                        </svg> Edit Menu for your <?= $menu_result['event_name']; ?></h1>
                <?php endif; ?>
                <?php if (isset($_GET['action']) && $_GET['action'] == "delete" && $menu_builder->status() == "On") : ?>
                    <h1><svg class="icon">
                            <use xlink:href="assets/img/icons/solid.svg#bowl-food"></use>
                        </svg> Delete Menu for your <?= $menu_result['event_name']; ?></h1>
                <?php endif; ?>
                <?php if ($user->user_type() == "Admin" || $user->user_type() == "Developer") : ?>
                    <?php if ($menu_builder->status() == "On") : ?>
                        <div class="menu-body" id="menu-body">
                            <?php if (empty($_GET)) :
                                $event_query = $db->query('SELECT * FROM wedding_events');
                            ?>
                                <div class="std-card">
                                    <div class="form-controls my-2">
                                        <button class="btn-primary" type="button" id="add-menu" data-action="create_menu"><svg class="icon">
                                                <use xlink:href="assets/img/icons/solid.svg#utensils"></use>
                                            </svg> Create Menu</button>
                                        <button class="btn-primary btn-secondary" id="edit-courses" type="button"><svg class="icon">
                                                <use xlink:href="assets/img/icons/solid.svg#pen-to-square"></use>
                                            </svg>Edit Courses</button>
                                    </div>
                                    <?php if ($menu_query->num_rows > 0) : ?>
                                        <?php foreach ($menu_query as $menu) :
                                            $menu_query = $db->query('SELECT menu.menu_name, menu.menu_id, menu.event_id, wedding_events.event_id, wedding_events.event_name FROM menu LEFT JOIN wedding_events ON wedding_events.event_id=menu.event_id WHERE menu.menu_id=' . $menu['menu_id']);
                                            $menu_result = mysqli_fetch_assoc($menu_query);
                                            $menu_courses = $db->query('SELECT course_name, course_id FROM menu_courses');
                                            $course_res = mysqli_fetch_assoc($menu_courses); ?>
                                            <?php if ($menu_query->num_rows > 0) : ?>
                                                <div class="menu my-3" id="menus">
                                                    <h2><?= $menu_result['menu_name']; ?></h2>
                                                    <p>For your</p>
                                                    <p><?= $menu['event_name']; ?></p>
                                                    <hr>
                                                    <?php
                                                    if ($menu_courses->num_rows > 0) :
                                                        foreach ($menu_courses as $course) :
                                                            $menu_item = $db->query('SELECT menu_item_id, menu_item_name, menu_item_desc, course_id, menu_id FROM menu_items WHERE course_id=' . $course['course_id'] . ' AND menu_id=' . $menu['menu_id']); ?>
                                                            <h3><?= $course['course_name']; ?></h3>
                                                            <?php if ($menu_item->num_rows > 0) :
                                                                foreach ($menu_item as $item) :  ?>
                                                                    <div class="menu-item my-2">
                                                                        <div class="menu-item-body">
                                                                            <h4 class="menu-item-name"><?= $item['menu_item_name']; ?></h4>
                                                                            <p class="menu-item-desc"><?= $item['menu_item_desc']; ?></p>
                                                                        </div>
                                                                    </div>
                                                <?php endforeach;
                                                            endif;
                                                            echo "<hr>";
                                                        endforeach;
                                                    endif;
                                                endif;
                                                ?>
                                                <div class="card-actions">
                                                    <a class="btn-primary" href="menu?action=edit&menu_id=<?= $menu['menu_id']; ?>"><svg class="icon">
                                                            <use xlink:href="assets/img/icons/solid.svg#pen-to-square"></use>
                                                        </svg> Edit Menu</a>
                                                    <a href="menu.php?action=delete&confirm=no&menu_id=<?= $menu['menu_id']; ?>" class="btn-primary btn-secondary"><svg class="icon">
                                                            <use xlink:href="assets/img/icons/solid.svg#trash"></use>
                                                        </svg> Delete Menu</a>
                                                </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php $db->close();
                                    endif; ?>
                                </div>
                                <div class="modal" id="menu-modal">

                                    <div class="modal-content">
                                        <div class="modal-header">
                                        <button class="btn-close close" type="button">
                                                <svg class="icon line">
                                                    <use href="assets/img/icons/solid.svg#minus" />
                                                </svg>
                                                <svg class="icon x-mark">
                                                    <use href="assets/img/icons/solid.svg#xmark" />
                                                </svg>
                                            </button>
                                            <h2 class="text-center">Create a New Menu</h2>
                                        </div>
                                        <?php if ($event_query->num_rows > 0) : ?>
                                            <?php if ($menu_courses->num_rows > 0) : ?>
                                                <form class="my-2" action="menu.script.php" method="POST" id="create-menu" data-action="new_menu">
                                                    <div id="dish-creator">
                                                        <div class="form-input-wrapper">
                                                            <label for="menu_name">Menu Name</label>
                                                            <input type="text" name="menu_name" id="menu_name" placeholder="Wedding Breakfast..." required>
                                                        </div>
                                                        <div class="form-input-wrapper">
                                                            <label for="event_id">Select Event This Menu Is For</label>
                                                            <select name="event_id" id="event_id" required>
                                                                <option value="">Select Event</option>
                                                                <?php foreach ($event_query as $event) : ?>
                                                                    <option value="<?= $event['event_id']; ?>"><?= $event['event_name']; ?></option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer my-2">
                                                        <button class="btn-primary" id="save-menu" type="submit">Save Menu</button>
                                                        <button class="btn-primary btn-secondary" type="button" id="close-menu-modal">Close</button>
                                                    </div>
                                                    <div class="d-none" id="response">
                                                    </div>
                                                </form>
                                            <?php else : ?>
                                                <p><strong>Before you continue, you need to set up courses for your menu.</strong></p>
                                                <div class="modal-footer my-2">
                                                    <button class="btn-primary btn-secondary" id="close-menu-modal" type="button">Close</button>
                                                </div>
                                            <?php endif; ?>
                                        <?php else : ?>
                                            <p>You have no events set up, please create an event first</p>
                                            <a href="event.php?action=create" class="btn-primary my-3">Create Events</a>
                                        <?php endif; ?>
                                    </div>

                                </div>
                            <?php endif; ?>
                            <?php if (isset($_GET['action']) && $_GET['action'] == "edit") : ?>
                                <div class="form-controls my-2">
                                    <button class="btn-primary" type="button" data-menu_id="<?= $_GET['menu_id']; ?>" data-action="add_dish" id="add-dish"><svg class="icon">
                                            <use xlink:href="assets/img/icons/solid.svg#utensils"></use>
                                        </svg> Add Dish</button>
                                    <a href="menu?action=delete&confirm=no&menu_id=<?= $_GET['menu_id']; ?>" class="btn-primary btn-secondary" type="button" data-menu_id="<?= $_GET['menu_id']; ?>" data-action="delete_menu"><svg class="icon">
                                            <use xlink:href="assets/img/icons/solid.svg#trash"></use>
                                        </svg> Delete Menu</a>
                                    <button class="btn-primary btn-secondary" id="edit-courses" type="button" data-menu_id="<?= $_GET['menu_id']; ?>"><svg class="icon">
                                            <use xlink:href="assets/img/icons/solid.svg#pen-to-square"></use>
                                        </svg>Edit Courses</button>
                                    <a href="menu" class="btn-primary btn-secondary"><svg class="icon">
                                            <use xlink:href="assets/img/icons/solid.svg#xmark"></use>
                                        </svg> Finish Editing Menu</a>
                                </div>
                                <div class="std-card" id="menu">
                                    <?php if ($menu_query->num_rows > 0) : ?>
                                        <p class="text-center">To edit the menu name: Click or tap on the menu name itself.</p>
                                        <div class="menu my-3">
                                            <h2 class="menu_name_edit" contenteditable="true" data-menu_id="<?= $menu_result['menu_id']; ?>" data-action="edit_menu_name"><?= $menu_result['menu_name']; ?></h2>
                                            <hr>
                                            <?php
                                            if ($menu_courses->num_rows > 0) :
                                                foreach ($menu_courses as $course) :
                                                    $menu_item = $db->query('SELECT menu_item_id, menu_item_name, menu_item_desc, course_id, menu_id FROM menu_items WHERE course_id=' . $course['course_id'] . ' AND menu_id=' . $_GET['menu_id']); ?>
                                                    <h3><?= $course['course_name']; ?></h3>
                                                    <?php if ($menu_item->num_rows > 0) :
                                                        foreach ($menu_item as $item) :  ?>
                                                            <div class="menu-item my-2">
                                                                <div class="menu-item-body">
                                                                    <h4 class="menu-item-name"><?= $item['menu_item_name']; ?></h4>
                                                                    <p class="menu-item-desc"><?= $item['menu_item_desc']; ?></p>
                                                                </div>
                                                                <div class="menu-item-actions">
                                                                    <button class="btn-primary btn-delete delete-dish" type="button" data-dish_id="<?= $item['menu_item_id']; ?>" data-menu_id="<?= $_GET['menu_id']; ?>" data-action="delete_dish"><svg class="icon">
                                                                            <use xlink:href="assets/img/icons/solid.svg#trash"></use>
                                                                        </svg></button>
                                                                    <button class="btn-primary btn-secondary edit-dish" data-dish_id="<?= $item['menu_item_id']; ?>" data-menu_id="<?= $_GET['menu_id']; ?>"><svg class="icon">
                                                                            <use xlink:href="assets/img/icons/solid.svg#pen-to-square"></use>
                                                                        </svg> Edit</button>
                                                                </div>

                                                            </div>
                                            <?php endforeach;
                                                    endif;
                                                    echo "<hr>";
                                                endforeach;
                                            endif;
                                            $db->close(); ?>
                                        </div>
                                </div>
                                <div class="modal" id="dish-modal">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <button class="btn-close close" type="button">
                                                <svg class="icon line">
                                                    <use href="assets/img/icons/solid.svg#minus" />
                                                </svg>
                                                <svg class="icon x-mark">
                                                    <use href="assets/img/icons/solid.svg#xmark" />
                                                </svg>
                                            </button>
                                            <h2 class="text-center">Add Dish</h2>
                                        </div>
                                        <?php if ($menu_courses->num_rows > 0) : ?>
                                            <form class="my-2" action="menu.script.php" method="POST" id="create-dish">
                                                <div id="dish-creator">
                                                    <div class="form-input-wrapper">
                                                        <label for="menu_item_name">Dish Name</label>
                                                        <input type="text" name="menu_item_name" id="menu_item_name" placeholder="Slow Roast Beef..." required>
                                                    </div>
                                                    <div class="form-input-wrapper">
                                                        <label for="menu_item_desc">Dish Description</label>
                                                        <input type="text" name="menu_item_desc" id="menu_item_desc" placeholder="A description to entice your guests" required>
                                                    </div>
                                                    <div class="form-input-wrapper">
                                                        <label for="course_id">Select Course</label>
                                                        <select name="course_id" id="course_id" required>
                                                            <option value="">Select Course</option>
                                                            <?php foreach ($menu_courses as $course) : ?>
                                                                <option value="<?= $course['course_id']; ?>"><?= $course['course_name']; ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="modal-footer my-2">
                                                    <button class="btn-primary" id="save-dish" data-action="save_dish" data-menu_id="<?= $_GET['menu_id']; ?>" type="submit">Save Dish</button>
                                                    <button class="btn-primary btn-secondary close" type="button">Close</button>
                                                </div>
                                                <div class="d-none" id="response">
                                                </div>
                                            </form>
                                        <?php else : ?>
                                            <p><strong>Before you continue, you need to set up courses for your menu.</strong></p>
                                            <div class="button-section">
                                                <button class="btn-primary btn-secondary" id="close-modal" type="button">Close</button>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                </div>

                                <div class="modal" id="edit-dish-modal">

                                </div>
                            <?php endif; ?>

                        </div>
                    <?php endif; ?>
                    <div class="modal" id="course-modal">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button class="btn-close close" type="button">
                                    <svg class="icon line">
                                        <use href="assets/img/icons/solid.svg#minus" />
                                    </svg>
                                    <svg class="icon x-mark">
                                        <use href="assets/img/icons/solid.svg#xmark" />
                                    </svg>
                                </button>
                                <h2 class="text-center">Edit Menu Courses</h2>
                            </div>
                            <form class="my-2" action="menu.script.php" method="POST" id="courses-editor">
                                <div id="course-editor">
                                    <?php if ($menu_courses->num_rows > 0) :
                                        $index = 0;
                                    ?>
                                        <?php foreach ($menu_courses as $course) : ?>
                                            <div class="form-input-wrapper">
                                                <label for="course_name">Course Name</label>
                                                <input type="hidden" name="course[<?= $index; ?>][course_id]" value="<?= $course['course_id']; ?>">
                                                <div class="form-input-row"><input type="text" name="course[<?= $index; ?>][course_name]" value="<?= $course['course_name']; ?>"><button class="btn-primary btn-secondary btn-delete" type="button" data-course_id="<?= $course['course_id']; ?>" data-action="delete"><svg class="icon">
                                                            <use href="assets/img/icons/solid.svg#trash" />
                                                        </svg></button></div>
                                            </div>
                                        <?php $index++;
                                        endforeach; ?>
                                        <div id="form-row"></div>
                                        <button class="btn-primary btn-secondary my-2" id="add-course" type="button">
                                            <svg class="icon">
                                                <use href="assets/img/icons/solid.svg#plus" />
                                            </svg> Add Course</button>
                                        <p><strong>Note:</strong> Removing a course will also remove any dishes associated with it.</p>
                                        <div class="modal-footer my-2">
                                            <button class="btn-primary" type="button" id="courses-save" data-action="save">Save Changes</button>
                                            <button class="btn-primary btn-secondary close" type="button">Cancel</button>
                                        </div>
                                    <?php else : ?>
                                        <?php $index = 0; ?>
                                        <div id="form-row"></div>
                                        <button class="btn-primary btn-secondary my-2" id="add-course" type="button">
                                            <svg class="icon">
                                                <use href="assets/img/icons/solid.svg#plus" />
                                            </svg> Add Course</button>
                                        <div class="modal-footer my-2">
                                            <button class="btn-primary" type="button" id="courses-save" data-action="save">Save Changes</button>
                                            <button class="btn-primary btn-secondary close" type="button">Cancel</button>
                                        </div>
                                        <p><strong>Note:</strong> Removing a course will also remove any dishes associated with it.</p>
                                        <?php $index++; ?>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>

                    </div>
                    <?php if (isset($_GET['action']) && $_GET['action'] == "delete") : ?>
                        <?php if ($_GET['confirm'] == "no") : ?>
                            <div class="std-card">
                                <?php if ($menu_query->num_rows > 0) : ?>
                                    <div class="menu my-3">
                                        <h2>Delete Your <?= $menu_result['menu_name']; ?> Menu?</h2>
                                        <p>For</p>
                                        <p><?= $menu_result['event_name']; ?></p>
                                        <p><strong>Note:</strong> This is not reversible!</p>
                                        <div class="card-actions">
                                            <a href="menu.php?action=delete&confirm=yes&menu_id=<?= $_GET['menu_id']; ?>" class="btn-primary btn-delete">Confirm</a>
                                            <a href="menu.php" class="btn-primary btn-secondary">Cancel</a>
                                        </div>
                                    </div>
                                <?php else : ?>
                                    <h1>Error</h1>
                                    <p>Request error</p>
                                <?php endif; ?>
                            <?php endif; ?>


                            </div>
                        <?php endif; ?>
            </div>
        <?php else : ?>
            <div class="std-card">
                <h2>Menu Builder</h2>
                <p>This feature is not available to you. Please contact us to have this feature activated.</p>
            </div>
        <?php endif; ?>
    <?php else : ?>
        <p class="font-emphasis">You do not have the necessary Administrator rights to view this page.</p>
    <?php endif; ?>
    </div>
        </section>
    </main>

    <!-- /Main Body Of Page -->

    <!-- Footer -->
    <?php include("./inc/footer.inc.php"); ?>
    <!-- /Footer -->

    <script src="assets/js/menu.js"></script>

    <script>
        let index = <?= $index; ?>;
        $("#course-editor").on("click", "#add-course", function() {

            let input = "<div class='appended d-none'><div class='form-input-wrapper'><label for='course_name'>Course Name</label><input type='hidden' name='course[" + index + "][course_id]' value=''><div class='form-input-row'><input type='text' name='course[" + index + "][course_name]'><button class='btn-primary btn-secondary btn-delete' type='button'><svg class='icon'><use href='assets/img/icons/solid.svg#trash'/></svg></button></div></div>";
            $("#form-row").append(input);
            $(".appended").slideDown(400);
            index++;
        })
    </script>

</body>

</html>