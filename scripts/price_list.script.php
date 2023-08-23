<?php if (isset($_GET['action']) && $_GET['action'] == "load-price-list") :
    ///loads price list on first page load
    include("../connect.php");
    //define category variables

    //load table with categories
    $service_num = $db->query('SELECT COUNT(service_cat_id) AS num_services FROM services');
    $service_num_r = mysqli_fetch_assoc($service_num);
    $cat_q = "SELECT * FROM services_categories";
    $cat_r = mysqli_query($db, $cat_q);
?>
    <h2>Services <span class="notification"><?= $service_num_r['num_services']; ?></span></h2>
    <?php if ($cat_r->num_rows > 0) : foreach ($cat_r as $cat) :
            $service_q = $db->query('SELECT * FROM services WHERE service_cat_id=' . $cat['service_cat_id']);
    ?>

            <h3 class="my-2"><?= $cat['service_cat_name']; ?></h3>
            <div class="grid-row-3col">

                <?php foreach ($service_q as $service) : ?>
                    <div class="service-card">
                        <div class="service-card-banner" data-promo="<?= $service['service_promo']; ?>">
                            <span><?php if ($service['service_promo'] == "Yes") : ?>Promo<?php endif; ?></span>
                        </div>
                        <div class="service-card-body">
                            <h4 class="service-card-heading"><?= html_entity_decode($service['service_name']); ?></h4>
                            <p><?= html_entity_decode($service['service_description']); ?></p>
                            <p class="service-card-price">&#163;<?= $service['service_price']; ?></p>
                            <div class="service-card-featured" data-featured="<?= $service['service_featured']; ?>">
                                <span><?php if ($service['service_featured'] == "Yes") : ?>Featured Service<?php endif; ?></span>
                            </div>
                            <div class="service-card-actions my-2">
                                <a href="price_listitem?action=edit&service_id=<?= $service['service_id']; ?>"><i class="fa-solid fa-pen-to-square"></i> Edit</a>
                                <a href="price_listitem?action=delete&confirm=no&service_id=<?=$service['service_id'];?>"><i class="fa-solid fa-trash"></i> Delete</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
    <?php endforeach;
    endif; ?>
<?php endif;
?>

<?php
//price list controls from POST request Filter Select
if (isset($_POST['action']) && $_POST['action'] == "price_list_filter") :
    include("../connect.php");
    if ($_POST['service_cat_id'] == "") :
        $service_num = $db->query('SELECT COUNT(service_cat_id) AS num_services FROM services');
        $service_num_r = mysqli_fetch_assoc($service_num);

        //load table with categories
        $cat_q = "SELECT * FROM services_categories";
        $cat_r = mysqli_query($db, $cat_q);
?><h2>Services <span class="notification"><?= $service_num_r['num_services']; ?></span></h2>
        <?php if ($cat_r->num_rows > 0) : foreach ($cat_r as $cat) :
                $service_q = $db->query('SELECT * FROM services WHERE service_cat_id=' . $cat['service_cat_id']);
        ?>
                <h3 class="my-2"><?= $cat['service_cat_name']; ?></h3>
                <div class="grid-row-3col">

                    <?php foreach ($service_q as $service) : ?>
                        <div class="service-card">
                            <div class="service-card-banner" data-promo="<?= $service['service_promo']; ?>">
                                <span><?php if ($service['service_promo'] == "Yes") : ?>Promo<?php endif; ?></span>
                            </div>
                            <div class="service-card-body">
                                <h4 class="service-card-heading"><?= html_entity_decode($service['service_name']); ?></h4>
                                <p><?= html_entity_decode($service['service_description']); ?></p>
                                <p class="service-card-price">&#163;<?= $service['service_price']; ?></p>
                                <div class="service-card-featured" data-featured="<?= $service['service_featured']; ?>">
                                    <span><?php if ($service['service_featured'] == "Yes") : ?>Featured Service<?php endif; ?></span>
                                </div>
                                <div class="service-card-actions my-2">
                                    <a href="price_listitem?action=edit&service_id=<?= $service['service_id']; ?>"><i class="fa-solid fa-pen-to-square"></i> Edit</a>
                                    <a href="price_listitem?action=edit"><i class="fa-solid fa-trash"></i> Delete</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
        <?php endforeach;
        endif; ?>
    <?php else :
        //define category
        $search = mysqli_real_escape_string($db, $_POST['service_cat_id']);
        //find services details
        $service_q = $db->query('SELECT * FROM services WHERE service_cat_id=' . $_POST['service_cat_id']);
        $result_num = mysqli_num_rows($service_q);
        //find the service name
        $category_q = $db->query('SELECT * FROM services_categories WHERE service_cat_id=' . $_POST['service_cat_id']);
        $category_r = mysqli_fetch_assoc($category_q);
        $service_num_r = mysqli_num_rows($service_q);

    ?>
        <h2>Services <span class="notification"><?= $service_num_r ?></span></h2>
        <h3 class="my-2"><?= $category_r['service_cat_name']; ?></h3>
        <div class="grid-row-3col">

            <?php foreach ($service_q as $service) : ?>
                <div class="service-card">
                    <div class="service-card-banner" data-promo="<?= $service['service_promo']; ?>">
                        <span><?php if ($service['service_promo'] == "Yes") : ?>Promo<?php endif; ?></span>
                    </div>
                    <div class="service-card-body">
                        <h4 class="service-card-heading"><?= html_entity_decode($service['service_name']); ?></h4>
                        <p><?= html_entity_decode($service['service_description']); ?></p>
                        <p class="service-card-price">&#163;<?= $service['service_price']; ?></p>
                        <div class="service-card-featured" data-featured="<?= $service['service_featured']; ?>">
                            <span><?php if ($service['service_featured'] == "Yes") : ?>Featured Service<?php endif; ?></span>
                        </div>
                        <div class="service-card-actions my-2">
                            <a href="price_listitem?action=edit&service_id=<?= $service['service_id']; ?>"><i class="fa-solid fa-pen-to-square"></i> Edit</a>
                            <a href="price_listitem?action=edit"><i class="fa-solid fa-trash"></i> Delete</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php if (isset($_POST['action']) && $_POST['action'] == "price_list_search") :
    include("../connect.php");
    if ($_POST['search'] == "") :
        ///return the result of the search box after keyup
        $search = mysqli_real_escape_string($db, $_POST['search']);
        $service_num = $db->query('SELECT COUNT(service_cat_id) AS num_services FROM services');
        $service_num_r = mysqli_fetch_assoc($service_num);

        //load table with categories
        $cat_q = "SELECT * FROM services_categories";
        $cat_r = mysqli_query($db, $cat_q);
?>
        <h2>Services <span class="notification"><?= $service_num_r['num_services']; ?></span></h2>
        <?php if ($cat_r->num_rows > 0) : foreach ($cat_r as $cat) :
                $service_q = $db->query('SELECT * FROM services WHERE service_cat_id=' . $cat['service_cat_id']);
        ?>
                <h3 class="my-2"><?= $cat['service_cat_name']; ?></h3>
                <div class="grid-row-3col">

                    <?php foreach ($service_q as $service) : ?>
                        <div class="service-card">
                            <div class="service-card-banner" data-promo="<?= $service['service_promo']; ?>">
                                <span><?php if ($service['service_promo'] == "Yes") : ?>Promo<?php endif; ?></span>
                            </div>
                            <div class="service-card-body">
                                <h4 class="service-card-heading"><?= html_entity_decode($service['service_name']); ?></h4>
                                <p><?= html_entity_decode($service['service_description']); ?></p>
                                <p class="service-card-price">&#163;<?= $service['service_price']; ?></p>
                                <div class="service-card-featured" data-featured="<?= $service['service_featured']; ?>">
                                    <span><?php if ($service['service_featured'] == "Yes") : ?>Featured Service<?php endif; ?></span>
                                </div>
                                <div class="service-card-actions my-2">
                                    <a href="price_listitem?action=edit&service_id=<?= $service['service_id']; ?>"><i class="fa-solid fa-pen-to-square"></i> Edit</a>
                                    <a href="price_listitem?action=edit"><i class="fa-solid fa-trash"></i> Delete</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
        <?php endforeach;
        endif; ?>

    <?php else :
        $search = mysqli_real_escape_string($db, $_POST['search']);
        $service_q = $db->query('SELECT services.service_id, services.service_name, services.service_description, services.service_cat_id, services.service_price, services.service_promo, services.service_featured, services_categories.service_cat_id, services_categories.service_cat_name FROM services LEFT JOIN services_categories ON services_categories.service_cat_id=services.service_cat_id WHERE services.service_name  LIKE "%' . $search . '%" ');
        $service_r = mysqli_fetch_assoc($service_q); ?>
        <p><?php echo mysqli_num_rows($service_q); ?> Results found, matching <?= $search; ?></p>
        <h2>Services <span class="notification"><?php echo mysqli_num_rows($service_q); ?></span></h2>
        <div class="grid-row-3col">

            <?php foreach ($service_q as $service) : ?>
                <div class="service-card">
                    <div class="service-card-banner" data-promo="<?= $service['service_promo']; ?>">
                        <span><?php if ($service['service_promo'] == "Yes") : ?>Promo<?php endif; ?></span>
                    </div>
                    <div class="service-card-body">
                        <h4 class="service-card-heading"><?= html_entity_decode($service['service_name']); ?></h4>
                        <p><?= html_entity_decode($service['service_description']); ?></p>
                        <p class="service-card-price">&#163;<?= $service['service_price']; ?></p>
                        <div class="service-card-featured" data-featured="<?= $service['service_featured']; ?>">
                            <span><?php if ($service['service_featured'] == "Yes") : ?>Featured Service<?php endif; ?></span>
                        </div>
                        <div class="service-card-actions my-2">
                            <a href="price_listitem?action=edit&service_id=<?= $service['service_id']; ?>"><i class="fa-solid fa-pen-to-square"></i> Edit</a>
                            <a href="price_listitem?action=edit"><i class="fa-solid fa-trash"></i> Delete</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
<?php endif;
exit(); ?>