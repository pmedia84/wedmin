<?php
//scripts for handling crud operations
if (isset($_POST['action']) && $_POST['action'] == "edit") {
    include("../connect.php");
    $response = "";
    //error codes
    //200: success
    $edit_service = $db->prepare('UPDATE services SET service_name=?, service_description=?, service_cat_id=?, service_price=?, service_promo=?, service_featured=? WHERE service_id=?');
    //set up variables
    $service_name = htmlentities(mysqli_real_escape_string($db, $_POST['service_name']));
    $service_description = htmlentities(mysqli_real_escape_string($db, $_POST['service_description']));
    $service_cat_id = $_POST['service_cat_id'];
    $service_price = mysqli_real_escape_string($db, $_POST['service_price']);
    $service_promo="";
    $service_featured="";
    if(array_key_exists("service_promo", $_POST)){
        $service_promo=$_POST['service_promo'];
    }
    if(array_key_exists("service_featured", $_POST)){
        $service_featured=$_POST['service_featured'];
    }
    $service_id = $_POST['service_id'];
    //trim whitespace and also validate there is no blank boxes
    if (!trim($service_name)) {
        $response = "Please give this service a name";
        exit($response);
    }
    if (!trim($service_price)) {
        $response = "Please give this service a price";
        exit($response);
    }
    $edit_service->bind_param('ssiissi', $service_name, $service_description, $service_cat_id, $service_price, $service_promo, $service_featured, $service_id);
    $edit_service->execute();
    $edit_service->close();
    exit("200");
}
if (isset($_POST['action']) && $_POST['action'] == "create") {
    include("../connect.php");
    $response = "";
    //error codes
    //200: success
    $create_service = $db->prepare('INSERT INTO services (service_name, service_description, service_cat_id, service_price, service_promo, service_featured) VALUES(?,?,?,?,?,?)');
    //set up variables
    $service_name = htmlentities(mysqli_real_escape_string($db, $_POST['service_name']));
    $service_description = htmlentities(mysqli_real_escape_string($db, $_POST['service_description']));
    $service_cat_id = $_POST['service_cat_id'];
    $service_price = mysqli_real_escape_string($db, $_POST['service_price']);
    $service_promo="";
    $service_featured="";
    if(array_key_exists("service_promo", $_POST)){
        $service_promo=$_POST['service_promo'];
    }
    if(array_key_exists("service_featured", $_POST)){
        $service_featured=$_POST['service_featured'];
    }

    //trim whitespace and also validate there is no blank boxes
    if (!trim($service_name)) {
        $response = "Please give this service a name";
        exit($response);
    }
    if (!trim($service_price)) {
        $response = "Please give this service a price";
        exit($response);
    }
    $create_service->bind_param('ssiiss', $service_name, $service_description, $service_cat_id, $service_price, $service_promo, $service_featured);
    if($create_service->execute()){
        $create_service->close();
        exit("200");
    }
    
    
    
    
}
