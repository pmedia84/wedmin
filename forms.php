<?php
session_start();
require("scripts/functions.php");
check_login();
include("connect.php");
include("inc/settings.php");
$user = new User();
//load all forms
$form_q = $db->query("SELECT forms.form_id, forms.form_type, forms.client_id, forms.form_date, forms.form_status, clients.client_id, clients.client_name  FROM forms LEFT JOIN clients ON clients.client_id=forms.client_id");

///
include("inc/head.inc.php");
?>
<!-- Meta Tags For Each Page -->
<meta name="description" content="Parrot Media - Client Admin Area">
<meta name="title" content="Manage your website content">
<!-- /Meta Tags -->
<!-- / -->
<!-- Page Title -->
<title>Mi-Admin | Forms</title>
<!-- /Page Title -->
</head>

<body>
    <!-- Main Body Of Page -->
    <main class="main col-2">
        <!-- Header Section -->
        <?php include("./inc/header.inc.php"); ?>
        <!-- Nav Bar -->
        <?php include("./inc/nav.inc.php"); ?>
        <!-- /nav bar -->
        <section class="body">
            <div class="breadcrumbs mb-2">
                <a href="index.php" class="breadcrumb">Home</a> / Forms
            </div>
            <div class="main-cards">
                <h1 class="my-2"><svg class="icon">
                        <use href="assets/img/icons/solid.svg#clipboard-user" />
                    </svg> Forms
                </h1>
                <?php if ($user->user_type() == "Admin" || $user->user_type() == "Developer") : ?>
                    <div class="std-card">
                        <h2 class="my-2 notification-header"> Consultation Forms <span class="notification" id="img_total">0</span>
                        </h2>
                        <p>View and manage consultation forms that have been completed by your clients.</p>
                        <form action="/scripts/forms.script.php" method="POST" id="forms-filter">
                            <div class="toolbar forms-toolbar my-2">
                                <div class="form-input-wrapper">
                                    <label for="form_sort">Sort By</label>
                                    <select name="sort" id="form_sort">
                                        <option value="DESC" selected>Newest to oldest</option>
                                        <option value="ASC">Oldest to newest</option>
                                    </select>
                                </div>
                                <div class="form-input-wrapper">
                                    <label for="sort">Filter</label>
                                    <select name="filter" id="filter">
                                        <option value="all" selected>All</option>
                                        <option value="unread">Unread</option>
                                        <option value="read">Read</option>
                                    </select>
                                </div>
                                <div class="search-input">
                                    <input type="search" id="search" name="search" placeholder="Search for a client">
                                    <button class="btn-primary form-controls-btn loading-btn" type="button" id="search-btn"><svg class="icon">
                                            <use href="assets/img/icons/solid.svg#magnifying-glass" />
                                        </svg></button>
                                </div>
                            </div>
                        </form>
                        <div id="forms-container">
                            <?php if ($form_q->num_rows > 0) :
                                foreach ($form_q as $form) :
                            ?>
                                    <div class="client-form my-2" data-status="<?= $form['form_status']; ?>">
                                        <div class="client-form-status"></div>
                                        <div class="client-form-body">
                                            <a href="form?action=view&form_id=<?= $form['form_id']; ?>">
                                                <h3><?= $form['client_name']; ?></h3>
                                            </a>
                                            <p><?= $form['form_type']; ?></p>
                                            <p>Completed: <?php echo date('d / m  / y', strtotime($form['form_date'])); ?></p>
                                        </div>
                                        <div class="client-form-actions">
                                            <a href="form?action=view&form_id=<?= $form['form_id']; ?>">
                                                <svg class="icon">
                                                    <use href="assets/img/icons/solid.svg#eye" />
                                                </svg>
                                            </a>
                                            <a href="form?action=delete&form_id=1">
                                                <svg class="icon">
                                                    <use href="assets/img/icons/solid.svg#trash" />
                                                </svg>
                                            </a>
                                        </div>
                                    </div>
                            <?php endforeach;
                            endif; ?>
                        </div>
                    </div>
            </div>
        <?php else : ?>
            <p class="font-emphasis">You do not have the necessary Administrator rights to view this page.</p>
        <?php endif; ?>
        </div>
        </section>
    </main>


    </div>
    <!-- /Main Body Of Page -->

    <!-- Footer -->
    <?php include("./inc/footer.inc.php"); ?>
    <!-- /Footer -->
    <script src="./assets/js/forms.js"></script>
</body>

</html>