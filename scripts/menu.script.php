<?php
if (isset($_POST['action']) && $_POST['action'] == "save") {
    include("../connect.php");
    $new_course = $db->prepare('INSERT INTO menu_courses (course_name)VALUES(?)');
    $update_course = $db->prepare('UPDATE menu_courses SET course_name=? WHERE course_id=?');
    foreach ($_POST['course'] as $course) {
        if (empty($course['course_id'])) {
            //only process courses that have been given a name
            if (!empty($course['course_name'])) {
                //process empty course ID as new courses
                $new_course->bind_param('s', $course['course_name']);
                $new_course->execute();
            }
        } else {
            //update existing courses
            $update_course->bind_param('si', $course['course_name'], $course['course_id']);
            $update_course->execute();
        }
    }
    $new_course->close();
    $update_course->close();
}
if (isset($_POST['action']) && $_POST['action'] == "delete") {
    include("../connect.php");
    $delete_course = $db->prepare('DELETE FROM menu_courses WHERE course_id=?');
    $course_id = $_POST['course_id'];
    $delete_course->bind_param('i', $course_id);
    $delete_course->execute();
    $delete_course->close();
}

if (isset($_POST['action']) && $_POST['action'] == "save_dish") {
    include("../connect.php");
    $response = 0;
    //response codes
    //0 = success
    //1 = error
    //prepare insert statement
    $new_dish = $db->prepare('INSERT INTO menu_items (menu_item_name, menu_item_desc, course_id, menu_id)VALUES(?,?,?,?)');
    //set up variables and detect if empty
    $menu_item_name = filter_var($_POST['menu_item_name'], FILTER_SANITIZE_SPECIAL_CHARS);
    $menu_item_desc = filter_var($_POST['menu_item_desc'], FILTER_SANITIZE_SPECIAL_CHARS);
    $menu_id = $_POST['menu_id'];
    $course_id = $_POST['course_id'];
    //test if the course has been selected or not
    if (empty($_POST['course_id'])) {
        echo '<div id="response"></div><div class="form-response error">Course required, please select a course for this dish and try again.</div>';
        exit();
    }
    //test if the form has been sent with a blank dish name
    if (trim($menu_item_name) == '') { // name
        echo '<div id="response"></div><div class="form-response error">Dish Name Required, please try again.</div>';
        exit();
    }
    $new_dish->bind_param('ssii', $menu_item_name, $menu_item_desc, $course_id, $menu_id);
    if ($new_dish->execute()) {

        $response = 1;
    }
    $new_dish->close();
    echo $response;
    exit();
}

if (isset($_POST['action']) && $_POST['action'] == "edit_dish") {
    include("../connect.php");
    $response = 0;
    //response codes
    //0 = success
    //1 = error
    //prepare insert statement
    $update_dish = $db->prepare('UPDATE menu_items SET menu_item_name=?, menu_item_desc=?, course_id=? WHERE menu_item_id=?');
    //set up variables and detect if empty
    $menu_item_name = filter_var($_POST['menu_item_name'], FILTER_SANITIZE_SPECIAL_CHARS);
    $menu_item_desc = filter_var($_POST['menu_item_desc'], FILTER_SANITIZE_SPECIAL_CHARS);
    $menu_item_id = $_POST['menu_item_id'];
    $course_id = $_POST['course_id'];
    //test if the course has been selected or not
    if (empty($_POST['course_id'])) {
        echo '<div id="response"></div><div class="form-response error">Course required, please select a course for this dish and try again.</div>';
        exit();
    }
    //test if the form has been sent with a blank dish name
    if (trim($menu_item_name) == '') { // name
        echo '<div id="response"></div><div class="form-response error">Dish Name Required, please try again.</div>';
        exit();
    }
    $update_dish->bind_param('ssii', $menu_item_name, $menu_item_desc, $course_id, $menu_item_id);
    if ($update_dish->execute()) {

        $response = 1;
    }
    $update_dish->close();
    echo $response;
    exit();
}
if (isset($_POST['action']) && $_POST['action'] == "delete_dish") {
    include("../connect.php");
    $response = 0;
    //response codes
    //0 = error
    //1 = success
    //prepare insert statement
    $delete_dish = $db->prepare('DELETE FROM menu_items WHERE menu_item_id=?');
    $menu_item_id = $_POST['menu_item_id'];
    $delete_dish->bind_param('i', $menu_item_id);
    if ($delete_dish->execute()) {
        $response = 1;
    } else {
        $response = 0;
    }
    $delete_dish->close();
    echo $response;
    exit();
}

if (isset($_POST['action']) && $_POST['action'] == "new_menu") {
    include("../connect.php");
    $response = 0;
    //response codes
    //0 = success
    //1 = error
    //prepare insert statement
    $new_menu = $db->prepare('INSERT INTO menu (menu_name, event_id)VALUES(?,?)');
    //set up variables and detect if empty
    $menu_name = filter_var($_POST['menu_name'], FILTER_SANITIZE_SPECIAL_CHARS);
    $event_id = $_POST['event_id'];
    //test if the event has been selected or not
    if (empty($_POST['event_id'])) {
        echo '<div id="response"></div><div class="form-response error">You need to specify what event this menu is for.</div>';
        exit();
    }
    //test if the form has been sent with a blank menu name
    if (trim($menu_name) == '') { // name
        echo '<div id="response"></div><div class="form-response error">Menu Name Required, please try again.</div>';
        exit();
    }
    $new_menu->bind_param('si', $menu_name, $event_id);
    if ($new_menu->execute()) {

        $response = 1;
    }
    $new_menu->close();
    echo $response;
    exit();
}
if (isset($_POST['action']) && $_POST['action'] == "edit_menu_name") {
    include("../connect.php");
    $response = 0;
    //response codes
    //0 = success
    //1 = error
    //prepare insert statement
    $update_menu_name = $db->prepare('UPDATE menu SET menu_name=? WHERE menu_id=?');
    //set up variables and detect if empty
    $menu_name = filter_var($_POST['menu_name'], FILTER_SANITIZE_SPECIAL_CHARS);
    $menu_id = $_POST['menu_id'];

    $update_menu_name->bind_param('si', $menu_name, $menu_id);
    if ($update_menu_name->execute()) {

        $response = 1;
    }
    $update_menu_name->close();
    echo $response;
    exit();
}
?>
<?php if (isset($_GET['action']) && $_GET['action'] == "load") :
    include("../connect.php");
    $menu_courses = $db->query('SELECT course_name, course_id FROM menu_courses');
?>
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
<?php exit();
endif; ?>

<?php if (isset($_GET['action']) && $_GET['action'] == "load_menu") :

    include("../connect.php");
    $menu_id = $_GET['menu_id'];
    $menu_query = $db->query('SELECT menu.menu_name, menu.menu_id, menu.event_id, wedding_events.event_id, wedding_events.event_name FROM menu LEFT JOIN wedding_events ON wedding_events.event_id=menu.event_id WHERE menu.menu_id=' . $menu_id);
    $menu_result = mysqli_fetch_assoc($menu_query);
    $menu_courses = $db->query('SELECT course_name, course_id FROM menu_courses');
    $course_res = mysqli_fetch_assoc($menu_courses);
?>
    <div class="menu my-3">
        <h2><?= $menu_result['menu_name']; ?></h2>
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
                                <button class="btn-primary btn-secondary btn-delete delete-dish" type="button" data-dish_id="<?= $item['menu_item_id']; ?>" data-menu_id="<?= $_GET['menu_id']; ?>" data-action="delete_dish"><svg class="icon">
                    <use href="assets/img/icons/solid.svg#trash" />
                </svg></button>
                                <button class="btn-primary btn-secondary edit-dish" data-dish_id="<?= $item['menu_item_id']; ?>" data-menu_id="<?= $_GET['menu_id']; ?>"><svg class="icon">
                    <use href="assets/img/icons/solid.svg#pen-to-square" />
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

<?php exit();
endif;  ?>
<!-- line234 -->
<?php if (isset($_GET['action']) && $_GET['action'] == "edit_dish") :
    include("../connect.php");
    $menu_courses = $db->query('SELECT course_name, course_id FROM menu_courses');
    $menu_item = $db->query('SELECT menu_items.menu_item_id, menu_items.menu_item_name, menu_items.menu_item_desc, menu_items.course_id, menu_items.menu_id, menu_courses.course_name FROM menu_items LEFT JOIN menu_courses ON menu_courses.course_id=menu_items.course_id WHERE menu_item_id=' . $_GET['dish_id'] . ' AND menu_id=' . $_GET['menu_id']);
    $menu_item_res = mysqli_fetch_assoc($menu_item);
?>

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
            <h2 class="text-center">Edit Dish</span></h2>
        </div>
        <?php if ($menu_courses->num_rows > 0) : ?>
            <form class="my-2" action="scripts/menu.script.php" method="POST" id="edit-dish">
                <div id="dish-editor">
                    <div class="form-input-wrapper">
                        <label for="menu_item_name">Dish Name</label>
                        <input type="text" name="menu_item_name" id="menu_item_name" placeholder="Slow Roast Beef..." required value="<?= $menu_item_res['menu_item_name']; ?>">
                    </div>
                    <div class="form-input-wrapper">
                        <label for="menu_item_desc">Dish Description</label>
                        <input type="text" name="menu_item_desc" id="menu_item_desc" placeholder="A description to entice your guests" value="<?= $menu_item_res['menu_item_desc']; ?>">
                    </div>
                    <div class="form-input-wrapper">
                        <label for="course_id">Select Course</label>
                        <select name="course_id" id="course_id" required>
                            <option value="<?= $menu_item_res['course_id']; ?>"><?= $menu_item_res['course_name']; ?></option>
                            <?php foreach ($menu_courses as $course) : ?>
                                <option value="<?= $course['course_id']; ?>"><?= $course['course_name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer my-2">
                    <button class="btn-primary" id="edit-save-dish" data-action="edit_dish" data-dish_id="<?= $menu_item_res['menu_item_id']; ?>" data-menu_id="<?= $_GET['menu_id']; ?>" type="submit">Save Dish</button>
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

<?php $db->close();
    exit();
endif;
?>

<?php if (isset($_GET['action']) && $_GET['action'] == "load_menus") :
    include("../connect.php");
    $menu_query = $db->query('SELECT menu.menu_name, menu.menu_id, menu.event_id, wedding_events.event_id, wedding_events.event_name FROM menu LEFT JOIN wedding_events ON wedding_events.event_id=menu.event_id'); ?>
    <div class="std-card">
        <div class="form-controls my-2">
            <button class="btn-primary" type="button" id="add-menu" data-action="create_menu"><i class="fa-solid fa-utensils"></i> Create Menu</button>
        </div>
        <?php if ($menu_query->num_rows > 0) : ?>
            <?php foreach ($menu_query as $menu) :
                $menu_query = $db->query('SELECT menu.menu_name, menu.menu_id, menu.event_id, wedding_events.event_id, wedding_events.event_name FROM menu LEFT JOIN wedding_events ON wedding_events.event_id=menu.event_id WHERE menu.menu_id=' . $menu['menu_id']);
                $menu_result = mysqli_fetch_assoc($menu_query);
                $menu_courses = $db->query('SELECT course_name, course_id FROM menu_courses');
                $course_res = mysqli_fetch_assoc($menu_courses); ?>

                <?php if ($menu_query->num_rows > 0) : ?>
                    <div class="menu my-3">
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
                        <a class="btn-primary" href="menu?action=edit&menu_id=<?= $menu['menu_id']; ?>"><i class="fa-solid fa-pen-to-square"></i> Edit Menu</a>
                        <a href="menu.php?action=delete&confirm=no&menu_id=<?= $menu['menu_id']; ?>" class="btn-primary btn-secondary"><i class="fa-solid fa-trash"></i> Delete Menu</a>
                    </div>
                    </div>
                <?php endforeach; ?>
            <?php $db->close();
        endif; ?>
    </div>
<?php endif;
exit(); ?>