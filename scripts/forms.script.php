<?php
//prevent browsing to this script
if ($_SERVER['REQUEST_METHOD'] != "POST") {
    http_response_code(403);
    exit;
}
?>
<?php if (isset($_POST)) :
    $sort = $_POST['sort'];
    $filter = $_POST['filter'];
    $filter = "WHERE forms.form_status='" . $_POST['filter'] . "'";
    
    if ($_POST['filter'] == "all") {
        $filter = "WHERE forms.form_status='unread' OR forms.form_status='read'";
    }
    if ($_POST['search'] != "") {
        $status="";
if($_POST['filter']=="all"){
    $status=0;
}
        $filter = "WHERE forms.form_status='" . $_POST['filter'] . "' AND clients.client_name LIKE '" . $_POST['search'] . "%'";
        echo $filter;
    }
    include("../connect.php");
    //load all forms
    $form_q = $db->query("SELECT forms.form_id, forms.form_type, forms.client_id, forms.form_date, forms.form_status, clients.client_id, clients.client_name  FROM forms LEFT JOIN clients ON clients.client_id=forms.client_id " . $filter . " ORDER BY forms.form_date " . $sort);

?>
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
        <?php endforeach; ?>

    <?php else : ?>
        <h2>No results found</h2>
        <p>Please try again</p>
    <?php endif; ?>
<?php endif; ?>