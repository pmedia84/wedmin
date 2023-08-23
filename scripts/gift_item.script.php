<?php
if ($_POST['action'] == "edit") { //check if the action type of edit has been set in the post request
    include("../connect.php");
    //determine variables
    $gift_item_id = $_POST['gift_item_id'];
    $gift_item_name = mysqli_real_escape_string($db, $_POST['gift_item_name']);
    $gift_item_desc = htmlentities($_POST['gift_item_desc']);
    $gift_item_url = mysqli_real_escape_string($db, $_POST['gift_item_url']);
    $gift_item_type = $_POST['gift_item_type'];
    $gift_item_img = $_FILES['gift_item_img']['name'];
    //use this for removing an old image if a new one has been uploaded
    $gift_item_img_old = $_POST['gift_item_img_old'];
    //check that an image has been uploaded
    if ($_FILES['gift_item_img']['name'] == null) {
    } else { //if there is an image uploaded then save it to the folder
        //////////////////////sort the image upload first////////////////////////////////////////
        $dir = $_SERVER['DOCUMENT_ROOT'] . "/assets/img/gift-list/"; //directory for website
        $admin_dir = $_SERVER['DOCUMENT_ROOT'] . "/admin/assets/img/gift_list/"; //copy to admin area also
        $file = $dir . basename($_FILES['gift_item_img']['name']);
        $imageFileType = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        // Check if image file is a actual image or fake image
        $check = getimagesize($_FILES["gift_item_img"]["tmp_name"]);
        if ($check !== false) {
            $upload = 1;
        } else {
            $response = '<div class="form-response error"><p>File type not supported. Please try again.</p><a href=""news_createarticle.php>Try Again</a></div>';
        }
        // Check if file already exists
        if (file_exists($file)) {
            $response = '<div class="form-response error"><p>Image already exists</p></div>';
            echo $response;
            exit();
            $uploadOk = 0;
        }

        // Allow certain file formats
        if (
            $imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
        ) {
            $upload = 0;
        }

        // Check if $upload is set to 0 by an error
        if ($upload == 0) {
            $response = '<div class="form-response error"><p>Error, your article image was not saved. You can try again by editing your image</p></div>';
            // if everything is ok, try to upload file
        } else {
            if (move_uploaded_file($_FILES["gift_item_img"]["tmp_name"], $file)) {
                
                //define articles img variable
                $image_filename = basename($_FILES['gift_item_img']['name']);
            }
        }
            //convert the file uploaded to a webp file
            $cur_image_file = $gift_item_img; //current image to be converted to webp. find in the admin folder
            $new_filename = "gift-item-img-" . $gift_item_id . ".webp";
            //create the images for jpeg gif or png
            $info = getimagesize($dir . $cur_image_file);
            if ($info['mime'] == 'image/jpeg') {
                $image = imagecreatefromjpeg($dir . $cur_image_file);
            } elseif ($info['mime'] == 'image/gif') {
                $image = imagecreatefromgif($dir . $cur_image_file);
            } elseif ($info['mime'] == 'image/png') {
                $image = imagecreatefrompng($dir . $cur_image_file);
            }
            imagepalettetotruecolor($image);
            imagealphablending($image, true);
            imagesavealpha($image, true);
            //imagejpeg($new, $new_img, 50);
            imagewebp($image, $dir . $new_filename, 60);
            //delete the old image
            if (fopen($dir . $cur_image_file, "w")) {
                unlink($dir . $cur_image_file);
            };
            copy($dir . $new_filename, $admin_dir . $new_filename);
            //Update gift item
            $gift_item = $db->prepare('UPDATE gift_list SET gift_item_img=?  WHERE gift_item_id =?');
            $gift_item->bind_param('si', $new_filename, $gift_item_id);
            $gift_item->execute();
            $gift_item->close();
     
        // //remove the old image if there was one
        // if (!$gift_item_img_old == "") {
        //     $old_img = $gift_item_img_old;
        //     $website_file_path = $dir . $old_img;
        //     $admin_file_path = $admin_dir . $old_img;
        //     if (fopen($website_file_path, "w")) {
        //         unlink($website_file_path);
        //     };
        //     if (fopen($admin_file_path, "w")) {
        //         unlink($admin_file_path);
        //     };
        //}
    }
    //Update gift item
    $gift_item = $db->prepare('UPDATE gift_list SET gift_item_name=?, gift_item_desc=?, gift_item_url=?, gift_item_type=?  WHERE gift_item_id =?');
    $gift_item->bind_param('ssssi', $gift_item_name, $gift_item_desc, $gift_item_url, $gift_item_type, $gift_item_id);
    $gift_item->execute();
    $gift_item->close();
    $response = "success";
    echo $response;
}
//////////////////////////

if ($_POST['action'] == "create") { //check if the action type of create has been set in the post request
    include("../connect.php");
    //determine variables
    $gift_item_name = mysqli_real_escape_string($db, $_POST['gift_item_name']);
    $gift_item_desc = htmlentities($_POST['gift_item_desc']);
    $gift_item_url = mysqli_real_escape_string($db, $_POST['gift_item_url']);
    $gift_item_type = $_POST['gift_item_type'];
    $gift_item_img = $_FILES['gift_item_img']['name'];
    //insert gift item
    $guest = $db->prepare('INSERT INTO gift_list (gift_item_name, gift_item_desc, gift_item_url, gift_item_type, gift_item_img) VALUES (?,?,?,?,?)');
    $guest->bind_param('sssss', $gift_item_name, $gift_item_desc, $gift_item_url, $gift_item_type, $gift_item_img);
    $guest->execute();
    $guest->close();
    $gift_item_id = $db->insert_id;
    //process any images that have been uploaded
    //check that an image has been uploaded
    if ($_FILES['gift_item_img']['name'] == null) {

        $gift_item_img = "";
    } else { //if there is an image uploaded then save it to the folder
        //////////////////////sort the image upload first////////////////////////////////////////
        $dir = $_SERVER['DOCUMENT_ROOT'] . "/assets/img/gift-list/"; //directory for website
        $admin_dir = $_SERVER['DOCUMENT_ROOT'] . "/admin/assets/img/gift_list/"; //copy to admin area also
        $file = $dir . basename($_FILES['gift_item_img']['name']);
        $imageFileType = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        // Check if image file is a actual image or fake image
        $check = getimagesize($_FILES["gift_item_img"]["tmp_name"]);
        if ($check !== false) {
            $upload = 1;
        } else {
            $response = '<div class="form-response error"><p>File type not supported. Please try again.</p><a href=""news_createarticle.php>Try Again</a></div>';
        }
        // Check if file already exists
        if (file_exists($file)) {
            $response = '<div class="form-response error"><p>Image already exists</p></div>';
            echo $response;
            exit();
            $uploadOk = 0;
        }

        // Allow certain file formats
        if (
            $imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
        ) {
            $upload = 0;
        }

        // Check if $upload is set to 0 by an error
        if ($upload == 0) {
            $response = '<div class="form-response error"><p>Error, your article image was not saved. You can try again by editing your image</p></div>';
            // if everything is ok, try to upload file
        } else {
            if (move_uploaded_file($_FILES["gift_item_img"]["tmp_name"], $file)) {

                //define articles img variable
                $image_filename = basename($_FILES['gift_item_img']['name']);
            }
        }
            //convert the file uploaded to a webp file
    $cur_image_file = $gift_item_img; //current image to be converted to webp. find in the admin folder
    $new_filename = "gift-item-img-" . $gift_item_id . ".webp";
    //create the images for jpeg gif or png
    $info = getimagesize($dir . $cur_image_file);
    if ($info['mime'] == 'image/jpeg') {
        $image = imagecreatefromjpeg($dir . $cur_image_file);
    } elseif ($info['mime'] == 'image/gif') {
        $image = imagecreatefromgif($dir . $cur_image_file);
    } elseif ($info['mime'] == 'image/png') {
        $image = imagecreatefrompng($dir . $cur_image_file);
    }
    imagepalettetotruecolor($image);
    imagealphablending($image, true);
    imagesavealpha($image, true);
    //imagejpeg($new, $new_img, 50);
    imagewebp($image, $dir . $new_filename, 60);
    //delete the old image
    if (fopen($dir . $cur_image_file, "w")) {
        unlink($dir . $cur_image_file);
    };
    copy($dir . $new_filename, $admin_dir . $new_filename);
    //Update gift item
    $gift_item = $db->prepare('UPDATE gift_list SET gift_item_img=?  WHERE gift_item_id =?');
    $gift_item->bind_param('si', $new_filename, $gift_item_id);
    $gift_item->execute();
    $gift_item->close();
    }



    $response = "success";
    echo $response;
}
