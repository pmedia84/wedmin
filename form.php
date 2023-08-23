<?php
session_start();
require("scripts/functions.php");
check_login();
include("connect.php");
include("inc/settings.php");
$user = new User();
include("inc/head.inc.php");
//load the form from GET request
if(isset($_GET['form_id'])){
    $form_id = $_GET['form_id'];
    include("../connect.php");
    $form_q=$db->query("SELECT forms.form_id, form_entries.form_id,form_entries.medical_history, form_entries.allergies, form_entries.allergies_other, form_entries.pregnant, form_entries.consent_1, form_entries.consent_2, form_entries.consent_3, form_entries.consent_4, form_entries.consent_5, form_entries.consent_6, form_entries.consent_7, form_entries.client_signature, forms.client_id, clients.client_name, clients.client_dob, clients.client_address_1, clients.client_address_towncity, clients.client_address_county, clients.client_address_pc, clients.client_phone, clients.client_email, clients.client_em_contact, clients.client_em_contact_tel, clients.client_mailing_list  FROM forms LEFT JOIN form_entries ON form_entries.form_id=forms.form_id LEFT JOIN clients ON clients.client_id=forms.client_id WHERE forms.form_id=".$form_id);
    $form_r=mysqli_fetch_assoc($form_q);

}
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
                <a href="index.php" class="breadcrumb">Home</a> /
                <a href="forms" class="breadcrumb">Forms</a> / View Submitted Form
            </div>
            <div class="main-cards">
                <h1 class="my-2"><svg class="icon">
                        <use href="assets/img/icons/solid.svg#clipboard-user" />
                    </svg> Consultation Form
                </h1>
                <?php if ($user->user_type() == "Admin" || $user->user_type() == "Developer") : ?>
                    <?php if (empty($_GET)) : ?>
                        <p class="font-emphasis">No form has been selected, please return to <a href="forms">Your Forms </a> and try again</p>
                    <?php endif; ?>
                    <?php if (isset($_GET['form_id']) && $_GET['action'] == "view") : ?>
                        <div class="card-actions client-form-response-actions">
                            <a href="" class="btn-primary">
                                <svg class="icon">
                                    <use href="assets/img/icons/solid.svg#print" />
                                </svg>
                                Print Form</a>
                            <a href="" class="btn-primary btn-secondary">
                                <svg class="icon">
                                    <use href="assets/img/icons/solid.svg#trash" />
                                </svg>
                                Delete Form</a>
                        </div>
                        <div class="std-card client-form-response">
                            <h2 class="my-2">Jimmy Bell</h2>
                            <h3>Eyelash Extensions</h3>
                            <p>Completed: 24/2/23</p>

                        </div>
                    <?php endif; ?>
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

</body>

</html>