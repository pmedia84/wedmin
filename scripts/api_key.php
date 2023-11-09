<?php

//ensure no user browses to this page
if (isset($_GET['api_key'])) {
    $api_key = $_GET['api_key'];
    $code = 200;
    $msg = "";
    $client_name = "";
    $api_status = "";
    $config_file = $_SERVER['DOCUMENT_ROOT'] . "/config.json";
    //! check file exists
    if (!file_exists($config_file)) {
        $code = 404;
        $msg = "Config file not found";
        $response = array("response_code" => $code, "message" => $msg, "api_key" => $api_key);
        echo json_encode($response);
        exit;
    }
    $config = file_get_contents($config_file);
    //decode json file
    $file = json_decode($config, TRUE);
    $DATABASE_HOST = $file['db']['db_host'];
    $DATABASE_USER = $file['db']['db_user'];
    $DATABASE_PASS = $file['db']['db_pw'];
    $DATABASE_NAME = $file['db']['db_name'];
    $db = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME,);

    $q = $db->query('SELECT api_keys.api_key, api_keys.client_id, api_keys.api_status, api_clients.client_id, api_clients.client_name  FROM api_keys LEFT JOIN api_clients ON api_clients.client_id=api_keys.client_id WHERE api_keys.api_key=' . $api_key);
    if ($q->num_rows > 0) {
        $r = mysqli_fetch_assoc($q);
        $client_name = $r['client_name'];
        $api_status=$r['api_status'];
        $client_id = $r['client_id'];
        //load active API's
        $modules_q=$db->query('SELECT client_modules.module_id, client_modules.client_id, client_modules.module_status, api_modules.module_id, api_modules.module_name, api_modules.module_desc FROM client_modules LEFT JOIN api_modules ON api_modules.module_id=client_modules.module_id  WHERE client_modules.client_id='.$client_id);
        if($modules_q->num_rows>0){
            $modules=array();
            foreach($modules_q as $module){
                array_push($modules, array("module_name"=>$module['module_name'],"module_desc"=>$module['module_desc'], "module_status"=>$module['module_status']));
               
            }
            $response = array("response_code" => $code, "message" => $msg, "api_key" => $api_key, "client_name" => $client_name, "api_status" => $api_status,"modules" =>$modules);
        }else{
            $response = array("response_code" => $code, "message" => $msg, "api_key" => $api_key, "client_name" => $client_name, "api_status" => $api_status);
        }
    } else {
        $code = 400;
        $msg = "API Key Error";
        $response = array("response_code" => $code, "message" => $msg, "api_key" => $api_key, "client_name" => $client_name, "api_status" => $api_status);
       // echo json_encode($response);
        exit;
    }
    echo json_encode($response);
} else {
    http_response_code(403);
    echo "<h1>" . http_response_code() . " Access Forbidden</h1>";
    exit;
}
