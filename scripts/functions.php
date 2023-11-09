<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require $_SERVER['DOCUMENT_ROOT'] . '/wedmin/mailer/PHPMailer.php';
require $_SERVER['DOCUMENT_ROOT'] . '/wedmin/mailer/SMTP.php';
require $_SERVER['DOCUMENT_ROOT'] . '/wedmin/mailer/Exception.php';
function check_login()
{
    if (!isset($_SESSION['loggedin'])) {
        $location = urlencode($_SERVER['REQUEST_URI']);
        header("Location: login?location=" . $location);
    }
}
//# connect to database function
function db_connect(&$db)
{
    $code = 200;
    $msg = "";
    $config_file = $_SERVER['DOCUMENT_ROOT'] . "/config.json";
    //! check file exists
    if (!file_exists($config_file)) {
        $code = 404;
        $msg = "Config file not found";
        echo $code . " " . $msg;
        exit;
    }
    $config = file_get_contents($config_file);
    //decode json file
    $file = json_decode($config, TRUE);
    $DATABASE_HOST = $file['wedmin_db']['db_host'];
    $DATABASE_USER = $file['wedmin_db']['db_user'];
    $DATABASE_PASS = $file['wedmin_db']['db_pw'];
    $DATABASE_NAME = $file['wedmin_db']['db_name'];
    $db = mysqli_connect($DATABASE_HOST, $DATABASE_USER, $DATABASE_PASS, $DATABASE_NAME,);
}

class Cms
{
    //?variables 
    public $wedding_name;
    public $wedding_date;
    public $wedding_id;
    public $wedding_time;
    public $api_status;
    function __construct()
    {
        db_connect($db);
        $q = $db->query('SELECT wedding_name FROM wedding');
        if ($q->num_rows > 0) {
            $r = mysqli_fetch_assoc($q);
            $this->wedding_name = $r['wedding_name'];
        } else {
            return;
        }
    }
    //load the api and active modules loads from remote server location and updates the local database on each login

    function api_connect()
    {
        $code = 200;
        $msg = "";
        //load the api key from Database
        db_connect($db);
        $q = $db->query("SELECT api_status, api_key FROM api_key");
        if ($q->num_rows > 0) {
            $r = mysqli_fetch_assoc($q);
            $api_key = $r['api_key'];
            $api = file_get_contents("https://api.parrotmedia.co.uk/api_key.php?api_key=" . $api_key);
            $response = json_decode($api, TRUE);
            //update database with api status and set the api status in this class
            $this->api_status = $response['api_status'];
            //print_r($response);
            //check if there are modules set up for this cms
            if (isset($response['modules'])) {
                //update module status
                $module_u = $db->prepare("UPDATE modules SET module_status=? WHERE module_id=?");
                foreach ($response['modules'] as $module) {
                    $module_q = $db->query("SELECT module_name, module_id, module_status FROM modules WHERE module_name ='" . $module['module_name'] . "'");

                    //check if the module is in the db or not
                    if ($module_q->num_rows > 0) {
                        $module_r = mysqli_fetch_assoc($module_q);
                        //if the module matches the list, then update the record with correct status
                        $module_u->bind_param("si", $module['module_status'], $module_r['module_id']);
                        $module_u->execute();
                    }
                }
                $module_u->close();
            } else {
                //modules not set up
                $this->api_status = 500;
            }
        } else {
            //api key not found
            $this->api_status = 404;
        }
    }
    function api_status()
    {
        return $this->api_status;
    }
    function w_name()
    {
        return $this->wedding_name;
    }

    function setup()
    {
        //load cms and check it is set up, if not redirect to setup page
        db_connect($db);
        $q = $db->query('SELECT wedding_id FROM wedding');
        if ($q->num_rows > 0) {
            $r = mysqli_fetch_assoc($q);
        } else {
            header("Location: setup?action=setup_wedding");
        }
    }
}

class Module
{

    public $name;
    public $status;

    function module_name($name)
    {

        $this->name = $name;
    }
    function status()
    {
        include("../connect.php");
        $modules_query = $db->query('SELECT module_status FROM modules WHERE module_name= "' . $this->name . '"');
        $modules_r = mysqli_fetch_assoc($modules_query);
        $module_status = $modules_r['module_status'];
        $this->status = $module_status;
        return $this->status;
        $db->close();
    }
}

//*modules
$guest_list_m = new Module();
$guest_list_m->module_name("Guest List");

$news_m = new Module();
$news_m->module_name("News");

$image_gallery = new Module();
$image_gallery->module_name("Image Gallery");

$events = new Module();
$events->module_name("Events");

$invite_manager = new Module();
$invite_manager->module_name("Invite Manager");

$guest_messaging = new Module();
$guest_messaging->module_name("Guest Messaging");

$gift_list_m = new Module();
$gift_list_m->module_name("Gift List");

$menu_builder = new Module();
$menu_builder->module_name("Menu Builder");

$meal_choices_m = new Module();
$meal_choices_m->module_name("Meal Choices");

$guest_image_gallery = new Module();
$guest_image_gallery->module_name("Guest Image Gallery");

$forms = new Module();
$forms->module_name("Forms");

//*modules for guest area 
class Wedding_module
{
    public $name;
    public $status;

    function module_name($name)
    {

        $this->name = $name;
    }
    function status()
    {
        db_connect($db);
        $modules_query = $db->query('SELECT wedding_module_status FROM wedding_modules WHERE wedding_module_name= "' . $this->name . '"');
        $modules_r = mysqli_fetch_assoc($modules_query);
        $module_status = $modules_r['wedding_module_status'];
        $this->status = $module_status;
        return $this->status;
        $db->close();
    }
}
//* build the wedding modules
$guest_area = new Wedding_module();
$guest_area->module_name("Guest Area");
$guest_add_remove = new Wedding_module();
$guest_add_remove->module_name("Add & Remove Guests");
$meal_choices_wedmin = new Wedding_module();
$meal_choices_wedmin->module_name("Meal Choices");
$guest_area_gallery = new Wedding_module();
$guest_area_gallery->module_name("Guest Image Gallery");

//* User class for the login system etc
class User
{
    public $user_id;
    public $user_type;
    public $user_name;
    public $logged_in;
    public $user_email;
    public $user_em_status;

    function em_status()
    {
        //find email status of the user
        db_connect($db);
        $q = $db->query("SELECT user_em_status FROM users WHERE user_id=" . $this->user_id() . "");
        $r = mysqli_fetch_assoc($q);
        $status = $r['user_em_status'];
        $this->user_em_status = $status;
        return $this->user_em_status;
    }
    function user_id()
    {
        $this->user_id = $_SESSION['user_id'];
        return $this->user_id;
    }
    function logged_id()
    {
        $this->logged_in = $_SESSION['logged_in'];
        return $this->logged_in;
    }
    function user_type()
    {
        db_connect($db);
        $user_type_q = $db->query("SELECT user_type FROM users WHERE user_id=" . $this->user_id() . "");
        $user_type_r = mysqli_fetch_assoc($user_type_q);
        $type = $user_type_r['user_type'];
        $this->user_type = $type;
        return $this->user_type;
    }
    function name()
    {
        db_connect($db);
        $q = $db->query("SELECT user_name FROM users WHERE user_id=" . $this->user_id() . "");
        $r = mysqli_fetch_assoc($q);
        $name = $r['user_name'];
        $this->user_name = $name;
        return $this->user_name;
    }
    function update()
    {
        $email = $_POST['user_email'];
        $user_name = $_POST['user_name'];
        //url for confirming new emails
        $url = $_SERVER['SERVER_NAME'] . "/admin/profile.php?confirm=email";

        //update user details from form
        db_connect($db);
        //check if new email is different to saved email
        $q = $db->query("SELECT user_email FROM users WHERE user_id=" . $this->user_id() . "");
        $r = mysqli_fetch_assoc($q);
        $old_email = $r['user_email'];
        if ($old_email != $email) {
            //load email config file
            //config file name
            $config_file = "config.json";
            //load config file
            $config = file_get_contents($config_file);
            //decode json file
            $file = json_decode($config, TRUE);
            //set up variables
            $host = $file['email_config']['host'];
            $username = $file['email_config']['username'];
            $pw = $file['email_config']['pw'];
            $fromname = $file['email_config']['fromname'];
            //send email to get user to confirm email
            //set the user email status to unconfirmed
            $em_status = "TEMP";
            //load template
            $body = file_get_contents("inc/User_update_email.html");
            //set up email
            $body = str_replace(["{{user_name}}"], [$user_name], $body);
            $body = str_replace(["{{user_email}}"], [$email], $body);
            $body = str_replace(["{{url}}"], [$url], $body);
            //* Subject
            $subject = "New email address";
            $fromserver = $username;
            $email_to = $email;
            $mail = new PHPMailer(true);
            $mail->IsSMTP();
            $mail->Host = $host; // Enter your host here
            $mail->SMTPAuth = true;
            $mail->Username = $username; // Enter your email here
            $mail->Password = $pw; //Enter your password here
            $mail->Port = 25;
            $mail->From = $username;
            $mail->FromName = $fromname;
            $mail->Sender = $fromserver; // indicates ReturnPath header
            $mail->Subject = $subject;
            $mail->Body = $body;
            $mail->IsHTML(true);
            $mail->AddAddress($email_to);
            if (!$mail->Send()) {
                echo "Mailer Error: " . $mail->ErrorInfo;
            }
        }
        $update = $db->prepare("UPDATE users SET user_email=?, user_name=?, user_em_status=? WHERE user_id=?");
        $update->bind_param("sssi", $email, $user_name, $em_status,  $this->user_id);
        $update->execute();
        $update->close();
    }
    function verify_email()
    {
        //update users table email as set
        include("connect.php");
        $update = $db->prepare("UPDATE users SET user_em_status=? WHERE user_id=?");
        $this->user_em_status = "SET";
        $update->bind_param("si", $this->user_em_status,  $this->user_id);
        $update->execute();
        $update->close();
    }
    function new_pw($user_id, $pw)
    {
        //new password function
        $code = 200;
        include("../connect.php");
        $update = $db->prepare("UPDATE users SET user_pw=? WHERE user_id=?");
        $pw = password_hash($pw, PASSWORD_DEFAULT);
        $update->bind_param("si", $pw, $user_id);
        $update->execute();


        // find user email
        $q = $db->query("SELECT user_email, user_name FROM users WHERE user_id=" . $user_id);
        $r = mysqli_fetch_assoc($q);
        $email = $r['user_email'];
        $user_name = $r['user_name'];
        //load email config file
        //config file name
        $config_file = "../config.json";
        //load config file
        $config = file_get_contents($config_file);
        //decode json file
        $file = json_decode($config, TRUE);
        //set up variables
        $host = $file['email_config']['host'];
        $username = $file['email_config']['username'];
        $db_pw = $file['email_config']['pw'];
        $fromname = $file['email_config']['fromname'];
        //load template
        $body = file_get_contents("../inc/User_update_pw.html");
        //set up email
        $body = str_replace(["{{user_name}}"], [$user_name], $body);
        $subject = "New password";
        $fromserver = $username;
        $email_to = $email;
        $mail = new PHPMailer(true);
        $mail->IsSMTP();
        $mail->Host = $host; // Enter your host here
        $mail->SMTPAuth = true;
        $mail->Username = $username; // Enter your email here
        $mail->Password = $db_pw; //Enter your password here
        $mail->Port = 25;
        $mail->From = $username;
        $mail->FromName = $fromname;
        $mail->Sender = $fromserver; // indicates ReturnPath header
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->IsHTML(true);
        $mail->AddAddress($email_to);
        if (!$mail->Send()) {
            echo "Mailer Error: " . $mail->ErrorInfo;
        }
        $response = array("response_code" => $code);
        echo json_encode($response);
    }
}


//image class for all image operations
class Img
{
    public $img_id;
    //Image placemement
    public $placement;
    //ID of the guest image submission request
    public $submission_id;
    // Total amount of images posted from user submission
    public $img_total;
    public $msg;
    public $response_code;
    public $status;
    //image submission ID
    public $sub_id;
    //the amount of images that were not successful
    public $img_errors;
    //total amount of images successful
    public $success_img;
    //* Response Codes
    //200: Success
    //400: Error
    function __construct()
    {
        $this->status = "Approved";
        $this->img_errors = 0;
        $this->success_img = 0;
        $this->img_total = 0;
        $this->placement = "Gallery";
        if (isset($_POST['submission_id'])) {
            $this->submission_id = $_POST['submission_id'];
        }
    }

    //? Upload new images from admin panel
    function upload()
    {

        //check the post method has been sent
        if ($_SERVER['REQUEST_METHOD'] !== "POST") {
            $this->msg = "Request Method Not Set";
            $this->response_code = 400;
            return;
        }
        $this->img_total = count($_FILES['gallery_img']['name']);
        //insert into db    
        include("../connect.php");
        //prepare the insert query for images table
        $img = $db->prepare('INSERT INTO images (image_filename,  image_placement, status)VALUES(?,?,?)');
        //set the file name
        $newimgname = "gallery-img-0.webp";
        //set the upload path for admin
        $dir = $_SERVER['DOCUMENT_ROOT'] . "/admin/assets/img/gallery/" . $newimgname;
        foreach ($_FILES['gallery_img']['name'] as $key => $val) {
            // Reject uploaded file larger than 3MB
            //only process files that are below the max file size
            if ($_FILES["gallery_img"]["size"][$key] < 20971520) {
                //check for errors
                if ($_FILES['gallery_img']['error'][$key] !== UPLOAD_ERR_OK) {
                    switch ($_FILES['error']['gallery_img'][$key]) {
                        case UPLOAD_ERR_PARTIAL:
                            $this->msg = "File only partially uploaded";
                            $this->response_code = 400;
                            return;
                            break;
                        case UPLOAD_ERR_NO_FILE:
                            $this->msg = "No file was uploaded";
                            $this->response_code = 400;
                            return;
                            break;
                        case UPLOAD_ERR_EXTENSION:
                            $this->msg = "File upload stopped by a PHP extension";
                            $this->response_code = 400;
                            return;
                            break;
                        case UPLOAD_ERR_FORM_SIZE:
                            $this->msg = "File exceeds MAX_FILE_SIZE in the HTML form";
                            $this->response_code = 400;
                            return;
                            break;
                        case UPLOAD_ERR_INI_SIZE:
                            $this->msg = "File exceeds upload_max_filesize in php.ini";
                            $this->response_code = 400;
                            return;
                            break;
                        case UPLOAD_ERR_NO_TMP_DIR:
                            $this->msg = "Temporary folder not found";
                            $this->response_code = 400;
                            return;
                            break;
                        case UPLOAD_ERR_CANT_WRITE:
                            $this->msg = "Failed to write file";
                            $this->response_code = 400;
                            return;
                            break;
                        default:
                            $this->msg = "Unknown upload error";
                            $this->response_code = 400;
                            return;
                            break;
                    }
                }

                // Use fileinfo to get the mime type
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mime_type = $finfo->file($_FILES["gallery_img"]["tmp_name"][$key]);
                $mime_types = ["image/gif", "image/png", "image/jpeg", "image/jpg"];
                if (!in_array($_FILES["gallery_img"]["type"][$key], $mime_types)) {
                    $this->msg = "Invalid file type, only JPG, JPEG, PNG or Gif is allowed. One of your files has the type of: " . $mime_type;
                    $this->response_code = 400;
                    return;
                }
                $i = 0;
                //if the file exists already, set a prefix
                while (file_exists($dir)) {
                    $newimgname = "gallery-img-" . $i . ".webp";
                    $dir = $_SERVER['DOCUMENT_ROOT'] . "/admin/assets/img/gallery/" . $newimgname;
                    $i++;
                }

                // convert into webp
                $info = getimagesize($_FILES['gallery_img']['tmp_name'][$key]);
                if ($info['mime'] == 'image/jpeg') {
                    $image = imagecreatefromjpeg($_FILES['gallery_img']['tmp_name'][$key]);
                } elseif ($info['mime'] == 'image/gif') {
                    $image = imagecreatefromgif($_FILES['gallery_img']['tmp_name'][$key]);
                } elseif ($info['mime'] == 'image/png') {
                    $image = imagecreatefrompng($_FILES['gallery_img']['tmp_name'][$key]);
                }
                imagepalettetotruecolor($image);
                imagealphablending($image, true);
                imagesavealpha($image, true);
                if ($info['mime'] == 'image/jpeg') {
                    //detect the orientation of the uploaded file
                    @$exif = exif_read_data($_FILES["gallery_img"]["tmp_name"][$key]);
                }
                //rotate the image after converting
                if (isset($exif['Orientation']) && $exif['Orientation'] == 6) {
                    $image = imagerotate($image, -90, 0);
                }
                //convert into a webp image, if unsuccessful then increment into the error variable 
                if (!imagewebp($image, $dir, 60)) {
                    $this->img_errors++;
                    return;
                } else {
                    //set up posting to db
                    $image_filename = $newimgname;

                    //insert into database
                    $img->bind_param('sss',  $image_filename,  $this->placement,  $this->status);
                    $img->execute();
                    $image_id = $img->insert_id;
                    /// copy to website paths
                    $guests_dir = $_SERVER['DOCUMENT_ROOT'] . "/guests/assets/img/gallery/";
                    //copy the image to the guest directory
                    if (!copy($dir, $guests_dir . $newimgname)) {
                        //if unsuccessful
                        $this->msg = "Images were not copied successfully";
                        $this->response_code = 400;
                        return;
                    } else {
                        //if successful increment the successful img count
                        $this->success_img++;
                        $this->response_code = 200;
                    }
                }
            } else {
                $this->img_errors++;
            }
        }
        $img->close();
    }
    function save_submission()
    {
        //Only run this section if images have been posted from user 
        if (isset($_POST['gallery_img'])) {
            $this->img_total = count($_POST['gallery_img']);
            require("../connect.php");
            //update the individual submission items first
            $sub_item = $db->prepare('UPDATE image_sub_items SET sub_item_status=? WHERE image_id=?');
            $submission = $db->prepare('UPDATE image_submissions SET submission_status=? WHERE submission_id=?');
            //! finish from here, update submission table 
            $img = $db->prepare('UPDATE images SET status=? WHERE image_id=?');
            foreach ($_POST['gallery_img'] as $image) {
                $sub_item->bind_param("si", $this->status, $image['image_id']);
                $sub_item->execute();
                $img->bind_param("si", $this->status, $image['image_id']);
                $img->execute();
                $this->response_code = 200;
                $this->success_img++;
            }
            $sub_item->close();
            $img->close();
            //! find the total amount of images in the submission, if they have all been accepted then mark submission as approved. If not mark as partially approved so users can still see the images that have not been approved for the website.
            $sub_count = $db->query("SELECT  COUNT(sub_item_id) AS count FROM image_sub_items WHERE submission_id=" . $this->submission_id . " AND sub_item_status = 'Awaiting'");
            $count_r = mysqli_fetch_assoc($sub_count);
            $t = $count_r['count'];
            if ($t > $this->img_total) {
                $this->status = "Partial";
            }
            $submission->bind_param("si", $this->status, $this->submission_id);
            $submission->execute();
            $submission->close();
        } else {
            //if no images have been selected, return an error
            $this->response_code = 400;
            $this->msg = "No images have been submitted, please try again";
        }
    }
    //?Delete Images
    function delete()
    {
        //image array, contains the db image id and the filename
        $images = $_POST['gallery_img'];
        //define how many images have been request for delete
        $this->img_total = count($images);
        /// copy to website paths
        $guests_dir = $_SERVER['DOCUMENT_ROOT'] . "/guests/assets/img/gallery/";
        //admin file path for deleting the images
        $dir = $_SERVER['DOCUMENT_ROOT'] . "/admin/assets/img/gallery/";
        //$this->img_total = count($_POST['image_id']);
        //loop through the image ID array and delete images
        include("../connect.php");
        //test the connection
        if (mysqli_connect_error()) {
            $this->msg = "Connect error" . mysqli_connect_error();
            $this->response_code = 400;
            return;
        }
        //check the post method has been sent
        if ($_SERVER['REQUEST_METHOD'] !== "POST") {
            $this->msg = "Request Method Not Set";
            $this->response_code = 400;
            return;
        }
        //Loop through each image in the POST array, delete the files and the db entry
        foreach ($images as $image) {

            if ($db->query('DELETE FROM images WHERE image_id =' . $image['image_id'])) {
                //increment the success total by one for each successful image deleted
                $this->success_img++;
                $this->response_code = 200;
            } else {
                $this->img_errors++;
                $this->response_code = 400;
                $this->msg = "Could not delete image";
                return;
            }
            if (fopen($guests_dir . $image['image_filename'], "w")) {
                unlink($guests_dir . $image['image_filename']);
            } else {
                $this->img_errors++;
                $this->response_code = 400;
                $this->msg = "Could not delete image";
                return;
            }
            if (fopen($dir . $image['image_filename'], "w")) {
                unlink($dir . $image['image_filename']);
            } else {
                $this->img_errors++;
                $this->response_code = 400;
                $this->msg = "Could not delete image";
                return;
            };
        }
    }
    //total images in post request
    function img_total()
    {
        return $this->img_total;
    }
    //return the message if any
    function msg()
    {
        return $this->msg;
    }
    //return the response code
    function response_code()
    {
        return $this->response_code;
    }
    //return how many images have errors
    function img_error_amt()
    {
        return $this->img_errors;
    }
    //return how many images were successfully processed.
    function img_success_amt()
    {
        return $this->success_img;
    }
}

//guest class for all guest operations
class Guest
{
    public $guest_fname;
    public $guest_sname;
    public $guest_email;
    public $guest_address;
    public $guest_postcode;
    public $guest_extra_invites;
    public $rsvp_code;
    public $guest_type;
    public $rsvp_status;
    public $guest_id;
    public $guest_group_id;
    //The event that the guest is associated with 
    public $event_id;
    //server response array, echo out as a JSON file
    public $response;

    //response code
    public $code;
    public $response_message = "";
    function __construct()
    {
        //set extra invites to zero until guest group has been added
        $this->guest_extra_invites = 0;
        //Set as a sole guest, change if guest has additional invites
        $this->guest_type = "Sole";
        //set RSVP status, if it has been changed in the form by the user then change it as per the post
        if(isset($_POST['rsvp_status']) && $_POST['rsvp_status']!=0){
            $this->rsvp_status=$_POST['rsvp_status'];
        }else{
            $this->rsvp_status = "Not Replied";
        }
        
        //set guest ID if POST variable is available and find any events they are associated with
        if (isset($_POST['guest_id'])) {
            $this->guest_id = $_POST['guest_id'];
            //find if this guest is associated with an event or not
            db_connect($db);
            $q = $db->query("SELECT guest_id, event_id FROM invitations WHERE guest_id=" . $this->guest_id);
            if($q->num_rows>0){
                $r=mysqli_fetch_assoc($q);
                $this->event_id=$r['event_id'];
            }else{
                $this->event_id=0;
            }
        }
        //set guest group ID if POST variable exists
        if (isset($_POST['guest_group_id'])) {
            $this->guest_group_id = $_POST['guest_group_id'];
        }
    }
    function create()
    {

        //make sure the required fields have been filled out and JS has not been altered
        if (!trim($_POST['guest_fname'])) {
            $this->code = 500;
            $this->response_message = "First name is required";
            $this->response = json_encode(array("response_code" => $this->code, "response_message" => $this->response_message));
            return $this->response;
            exit;
        }
        if (!trim($_POST['guest_sname'])) {
            $this->code = 500;
            $this->response_message = "Surname is required";
            $this->response = json_encode(array("response_code" => $this->code, "response_message" => $this->response_message));
            return $this->response;
            exit;
        }
        //create a new Guest
        db_connect($db);
        //determine variables
        $this->guest_fname = mysqli_real_escape_string($db, $_POST['guest_fname']);
        $this->guest_sname = mysqli_real_escape_string($db, $_POST['guest_sname']);
        $this->guest_email = mysqli_real_escape_string($db, $_POST['guest_email']);
        $this->guest_address = htmlspecialchars($_POST['guest_address']);
        $this->guest_postcode = mysqli_real_escape_string($db, $_POST['guest_postcode']);
        //create an RSVP CODE
        $random_code = rand(1000, 20000);
        //take first letter of surname
        $code_name = mb_substr($_POST['guest_sname'], 0, 1);
        //convert to upper case
        $code_name = strtoupper($code_name);
        //compile code and save in class variable
        $this->rsvp_code = $code_name . $random_code;

        //if the guest has 1 or more extra invites then add them as a group organiser
        if (isset($_POST['guest_group']) && count($_POST['guest_group']) >= 1) {
            $this->guest_type = "Group Organiser";
        }

        //insert lead guest
        $lead_guest = $db->prepare('INSERT INTO guest_list (guest_fname, guest_sname, guest_email, guest_address, guest_postcode, guest_rsvp_code, guest_rsvp_status,guest_extra_invites, guest_type) VALUES (?,?,?,?,?,?,?,?,?)');
        $lead_guest->bind_param('sssssssis', $this->guest_fname, $this->guest_sname, $this->guest_email, $this->guest_address, $this->guest_postcode, $this->rsvp_code, $this->rsvp_status, $this->guest_extra_invites, $this->guest_type);
        $lead_guest->execute();
        $lead_guest->close();
        $lead_guest_id = $db->insert_id; //last id entered

        //add this guest to the event guest List they have been added to.
        if (isset($_POST['event_id'])) {
            $event_id = $_POST['event_id'];
            $invite_rsvp_status = "Not Replied";
            $invite = $db->prepare('INSERT INTO invitations (guest_id, event_id, invite_rsvp_status) VALUES (?,?,?)');
            $invite->bind_param('iis', $lead_guest_id, $event_id, $invite_rsvp_status);
            $invite->execute();
            $invite->close();
        }
        //once the guest has been added, determine if the user has added other members to the lead guest.
        if (isset($_POST['guest_group'])) {
            //?insert each guest into the guest list from the POST request
            //create a guest group if the guest being added has one or more extra invites
            //set up a group name using first and last name of primary guest
            $group_name = $this->guest_fname . ' ' . $this->guest_sname;
            //insert into guest group tables
            $group = $db->prepare('INSERT INTO guest_groups (guest_group_name, guest_group_organiser) VALUES (?,?)');
            $group->bind_param('si', $group_name, $lead_guest_id);
            $group->execute();
            $group->close();
            $new_group_id = $db->insert_id;
            //update guest list with the guest group id
            $guest = $db->prepare('UPDATE guest_list SET guest_group_id=?  WHERE guest_id =?');
            $guest->bind_param('ii', $new_group_id, $lead_guest_id);
            $guest->execute();
            $guest->close();

            //guest array for all new added guests at the time of making the guest
            $guest_group = $_POST['guest_group'];
            $guest_array = array();
            $guest_type = "Member"; //only set as a member, these guests are a group member
            $new_guest = $db->prepare('INSERT INTO guest_list (guest_fname, guest_sname, guest_rsvp_status, guest_type, guest_group_id) VALUES (?,?,?,?,?)');
            foreach ($guest_group as $group_member) {
                // if the plus one box has been ticked then add them as a plus one
                $fname = $group_member['guest_fname'];
                if (isset($group_member['plus_one']) && $group_member['plus_one'] == "plus_one") {
                    $fname = $this->guest_fname . " " . $this->guest_sname . "'s +1";
                }
                $new_guest->bind_param('ssssi', $fname, $group_member['guest_sname'], $this->rsvp_status, $guest_type, $new_group_id);
                $new_guest->execute();
                //insert into an array for adding to the invites table
                $new_guest_id = $db->insert_id;
                array_push($guest_array, $new_guest_id);
            }
            $new_guest->close();
            if (isset($_POST['event_id'])) {

                /////Add to invites table for each guest 
                $set_invites = $db->prepare('INSERT INTO invitations (guest_id, event_id, guest_group_id, invite_rsvp_status) VALUES (?,?,?,?)');
                foreach ($guest_array as $guest) {
                    $set_invites->bind_param('iiis', $guest, $event_id, $new_group_id, $this->rsvp_status);
                    $set_invites->execute();
                }

                $set_invites->close();
            }
            //update the guest list with the amount of extra invites that they have based on how many guests have been added.

            $guest_extra_invites = count($guest_array);
            //update guest list with the guest group id
            $guest = $db->prepare('UPDATE guest_list SET guest_extra_invites=?  WHERE guest_id =?');
            $guest->bind_param('ii', $guest_extra_invites, $lead_guest_id);
            $guest->execute();
            $guest->close();
            //update invitations with the lead guest group id as well
            $lead_guest_inv = $db->prepare('UPDATE invitations SET guest_group_id=?  WHERE guest_id =?');
            $lead_guest_inv->bind_param('ii', $new_group_id, $lead_guest_id);
            $lead_guest_inv->execute();
            $lead_guest_inv->close();
            //only insert into invites table if the user has selected an event to add the guests to


        }
        $this->response_message = "Success";
        $this->code = 200;
        $this->response = json_encode(array("response_code" => $this->code, "response_message" => $this->response_message, "guest_name" => $this->guest_fname . ' ' . $this->guest_sname));
        return $this->response;
    }

    function remove_guest()
    {
        //remove guest from Post request
        db_connect($db);
        //load current guest info
        $q = $db->query("SELECT guest_fname, guest_sname, guest_type, guest_group_id FROM guest_list WHERE guest_id= " . $this->guest_id);
        if ($q->num_rows > 0) {
            $r = mysqli_fetch_assoc($q);
            $this->guest_type = $r['guest_type'];
            $this->guest_group_id = $r['guest_group_id'];
            $this->guest_fname = $r['guest_fname'];
            $this->guest_sname = $r['guest_sname'];
        } else {
            $this->response_message = "Guest Not found";
            $this->code = 500;
            $this->response = json_encode(array("response_code" => $this->code, "response_message" => $this->response_message, "guest_name" => $this->guest_fname . ' ' . $this->guest_sname));
            return $this->response;
        }

        //update lead guest if this guest is a group member
        $guest_extra_inv_num = $db->prepare('UPDATE guest_list SET guest_extra_invites=?  WHERE guest_id =?');
        //remove user account if they have set one up
        $remove_user = "DELETE FROM users WHERE guest_id=" . $this->guest_id;
        if (!mysqli_query($db, $remove_user)) {
            $this->response_message = "Script Error";
            $this->code = 500;
            $this->response = json_encode(array("response_code" => $this->code, "response_message" => $this->response_message, "guest_name" => $this->guest_fname . ' ' . $this->guest_sname));
            return $this->response;
        }
        //remove any guests this user has made and if they are a group organiser
        if ($this->guest_type == "Group Organiser") {
            $remove_group_guests = "DELETE FROM guest_list WHERE guest_group_id=" . $this->guest_group_id;
            if (mysqli_query($db, $remove_group_guests)) {
                echo mysqli_error($db);
            }
        }
        // connect to db and delete the guest
        $remove_guest = "DELETE FROM guest_list WHERE guest_id=" . $this->guest_id;
        if (mysqli_query($db, $remove_guest)) {
            // find the new extra invites amount an update the lead guest
            if ($this->guest_type == "Member") {
                $guest_group_manager = $db->query("SELECT guest_group_organiser FROM guest_groups WHERE guest_group_id=" . $this->guest_group_id);
                $guest_group_manager_res = $guest_group_manager->fetch_assoc();
                $guest_org_id = $guest_group_manager_res['guest_group_organiser'];
                $guest_group = $db->query("SELECT guest_id FROM guest_list WHERE guest_group_id=" . $this->guest_group_id . " AND guest_type='Member'");

                $guest_extra_invites_num = $guest_group->num_rows;
                $guest_extra_inv_num->bind_param('ii', $guest_extra_invites_num, $guest_org_id);
                $guest_extra_inv_num->execute();
                $guest_extra_inv_num->close();
            }
        }
        $this->response_message = "Success";
        $this->code = 200;
        $this->response = json_encode(array("response_code" => $this->code, "response_message" => $this->response_message, "guest_name" => $this->guest_fname . ' ' . $this->guest_sname));
        return $this->response;
    }
    function update_guest()
    {
        db_connect($db);
        //make sure the required fields have been filled out and JS has not been altered
        if (!trim($_POST['guest_fname'])) {
            $this->code = 500;
            $this->response_message = "First name is required";
            $this->response = json_encode(array("response_code" => $this->code, "response_message" => $this->response_message));
            return $this->response;
            exit;
        }
        if (!trim($_POST['guest_sname'])) {
            $this->code = 500;
            $this->response_message = "Surname is required";
            $this->response = json_encode(array("response_code" => $this->code, "response_message" => $this->response_message));
            return $this->response;
            exit;
        }
        //!determine variables
        $this->guest_fname = mysqli_real_escape_string($db, $_POST['guest_fname']);
        $this->guest_sname = mysqli_real_escape_string($db, $_POST['guest_sname']);
        $this->guest_email = mysqli_real_escape_string($db, $_POST['guest_email']);
        $this->guest_address = htmlspecialchars($_POST['guest_address']);
        $this->guest_postcode = mysqli_real_escape_string($db, $_POST['guest_postcode']);
        //Prepare Update guest 
        $guest = $db->prepare('UPDATE guest_list SET guest_fname=?, guest_sname=?, guest_email=?, guest_rsvp_status=?, guest_address=?, guest_postcode=?  WHERE guest_id =?');
        $guest->bind_param('ssssssi', $this->guest_fname, $this->guest_sname, $this->guest_email, $this->rsvp_status, $this->guest_address, $this->guest_postcode,  $this->guest_id);
        $guest->execute();
        $guest->close();
        //update invites table
        $invite_table = $db->prepare('UPDATE invitations SET invite_rsvp_status=?  WHERE guest_id =?');
        $invite_table->bind_param('si', $this->rsvp_status, $this->guest_id);
        $invite_table->execute();
        $invite_table->close();
        //once the guest has been updated, determine if the user has added other members to the lead guest.
        //only set up a guest group if one is not present in the post request which would mean that the lead guest was a sole invite to start with
        if (isset($_POST['guest_group']) && $_POST['guest_group_id'] == null) {
            /////insert each guest into the guest list from the POST request
            //create a guest group if the guest being added has one or more extra invites
            //set up a group name using first and last name of primary guest
            $group_name = $this->guest_fname . ' ' . $this->guest_sname;
            //insert into guest group tables
            $group = $db->prepare('INSERT INTO guest_groups (guest_group_name, guest_group_organiser) VALUES (?,?)');
            $group->bind_param('si', $group_name, $this->guest_id);
            $group->execute();
            $group->close();
            $new_group_id = $db->insert_id;
            //change the lead guest to a group organsier
            $guest_type = "Group Organiser";
            //update guest list with the guest group id
            $guest = $db->prepare('UPDATE guest_list SET guest_group_id=?, guest_type=?  WHERE guest_id =?');
            $guest->bind_param('isi', $new_group_id, $guest_type,  $this->guest_id);
            $guest->execute();
            $guest->close();

            //guest array for all new added guests at the time of making the guest
            $guest_group = $_POST['guest_group'];
            $guest_array = array();
            $guest_type = "Member"; //only set as a member, these guests are a group member
            $new_guest = $db->prepare('INSERT INTO guest_list (guest_fname, guest_sname, guest_type, guest_group_id, guest_rsvp_status) VALUES (?,?,?,?,?)');
            foreach ($guest_group as $group_member) {
                // if the plus one box has been ticked then add them as a plus one
                $fname = $group_member['guest_fname'];
                if (isset($group_member['plus_one']) && $group_member['plus_one'] == "plus_one") {
                    $fname = $this->guest_fname . " " . $this->guest_sname . "'s +1";
                }
                $new_guest->bind_param('sssis', $fname, $group_member['guest_sname'], $guest_type, $new_group_id, $this->rsvp_status);
                $new_guest->execute();
                //insert into an array for adding to the invites table
                $new_guest_id = $db->insert_id;
                array_push($guest_array, $new_guest_id);
            }
            $new_guest->close();
            if ($this->event_id>0) {
                //!Add to invites table for each guest 
                $set_invites = $db->prepare('INSERT INTO invitations (guest_id, event_id, invite_rsvp_status, guest_group_id) VALUES (?,?,?,?)');
                foreach ($guest_array as $guest) {
                    $set_invites->bind_param('iisi', $guest, $this->event_id, $this->rsvp_status, $new_group_id);
                    $set_invites->execute();
                }

                $set_invites->close();
            }
            //update the guest list with the amount of extra invites that they have based on how many guests have been added.

            $guest_extra_invites = count($guest_array);
            //update guest list with the guest group id
            $guest = $db->prepare('UPDATE guest_list SET guest_extra_invites=?  WHERE guest_id =?');
            $guest->bind_param('ii', $guest_extra_invites, $this->guest_id);
            $guest->execute();
            $guest->close();
            //only insert into invites table if the user has selected an event to add the guests to


        }
        //update the guest group with any guests that have been added and if this guest is a group organiser
        if (isset($_POST['guest_group']) && $_POST['guest_group_id'] > 0) {

            //guest array for all new added guests at the time of making the guest
            $guest_group = $_POST['guest_group'];
            $guest_array = array();
            $guest_type = "Member"; //only set as a member, these guests are a group member
            $new_guest = $db->prepare('INSERT INTO guest_list (guest_fname, guest_sname, guest_type, guest_group_id, guest_rsvp_status) VALUES (?,?,?,?,?)');
            foreach ($guest_group as $group_member) {
                // if the plus one box has been ticked then add them as a plus one
                $fname = $group_member['guest_fname'];
                if (isset($group_member['plus_one']) && $group_member['plus_one'] == "plus_one") {
                    $fname = $this->guest_fname . " " . $this->guest_sname . "'s +1";
                }
                $new_guest->bind_param('sssis', $fname, $group_member['guest_sname'], $guest_type, $this->guest_group_id, $this->rsvp_status);
                $new_guest->execute();
                //insert into an array for adding to the invites table
                $new_guest_id = $db->insert_id;
                array_push($guest_array, $new_guest_id);
            }
            $new_guest->close();

            //update the guest list with the amount of extra invites that they have based on how many guests have been added.
            //count how many members exist and update the table
            $guest_group = $db->query("SELECT guest_id FROM guest_list WHERE guest_group_id=" . $this->guest_group_id . " AND guest_type='Member'");

            $guest_extra_invites = $guest_group->num_rows;
            $guest_type = "Group Organiser";
            //update guest list with the guest group id and make sure they are now a Group Organiser
            $guest = $db->prepare('UPDATE guest_list SET guest_extra_invites=?, guest_group_id=?, guest_type=?  WHERE guest_id =?');
            $guest->bind_param('iisi', $guest_extra_invites, $this->guest_group_id, $guest_type, $this->guest_id);
            $guest->execute();
            $guest->close();
            //only insert into invites table if the user has selected an event to add the guests to
            if ($this->event_id>0) {
                //!Add to invites table for each guest 
                $set_invites = $db->prepare('INSERT INTO invitations (guest_id, event_id, invite_rsvp_status, guest_group_id) VALUES (?,?,?,?)');
                foreach ($guest_array as $member) {
                    $set_invites->bind_param('iisi', $member, $this->event_id, $this->rsvp_status, $this->guest_group_id);
                    $set_invites->execute();
                }

                $set_invites->close();
            }

        }
        $this->code = 200;
        $this->response_message="Guest Updated";
        $this->response = json_encode(array("response_code" => $this->code, "response_message" => $this->response_message));
        return $this->response;
    }
}

if (isset($_POST['action']) && $_POST['action'] == "new_guest") {
    $guest = new Guest;
    echo $guest->create();
}
if (isset($_POST['action']) && $_POST['action'] == "remove_guest") {
    $guest = new Guest;
    echo $guest->remove_guest();
}
if (isset($_POST['action']) && $_POST['action'] == "update_guest") {
    $guest = new Guest;
    echo $guest->update_guest();
}
