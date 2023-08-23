<?php
if (isset($_POST['action'])) {
    include("../connect.php");
    if ($_POST['action'] == "newimg") { //if add new img action exists then upload the image
        $response = "";
        //db connection

        //check that an image has been uploaded
        if ($_FILES['gallery_img']['name'] == null) {
            //header image name is blank if no file uploaded
            $response = '<div class="form-response error"><p>No image uploaded! Try again</p></div>';

            echo $response;
            exit();
            $img_filename = "";
        } else { //if there is an image uploaded then save it to the folder
            //////////////////////sort the image upload first////////////////////////////////////////
            $admin_dir = $_SERVER['DOCUMENT_ROOT'] . "/admin/assets/img/gallery/";
            $dir = $_SERVER['DOCUMENT_ROOT'] . "/assets/img/gallery/";
            $file = $dir . basename($_FILES['gallery_img']['name']);
            $imageFileType = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            // Check if image file is a actual image or fake image
            $check = getimagesize($_FILES["gallery_img"]["tmp_name"]);
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
                $response = '<div class="form-response error"><p>Sorry, only JPG, JPEG, PNG images are supported.</p><a href=""news_createarticle.php>Try Again</a></div>';
                $upload = 0;
                echo $response;
                exit();
            }

            // Check if $upload is set to 0 by an error
            if ($upload == 0) {
                $response = '<div class="form-response error"><p>Error, your article image was not saved. You can try again by editing your image</p></div>';
                // if everything is ok, try to upload file
            } else {
                if (move_uploaded_file($_FILES["gallery_img"]["tmp_name"], $file)) {

                    $response = '<div class="form-response"><p>success, your image has been added to your gallery.</p></div>';
                    //define articles img variable
                    $image_filename = basename($_FILES['gallery_img']['name']);
                    //define other variables
                    $image_title = mysqli_real_escape_string($db, $_POST['image_title']);
                    $image_description = mysqli_real_escape_string($db, $_POST['image_description']);
                    //handle the image placement checkboxes

                    if (!isset($_POST['img_placement'])) {
                        //if blank then set as Other
                        $img_place = "Other";
                    } else { //else set store in db as an array
                        $img_place = implode(",", $_POST['img_placement']);
                    }





                    //insert new image into table
                    $new_image = $db->prepare('INSERT INTO images (image_title, image_description, image_filename,  image_placement)VALUES(?,?,?,?)');
                    $new_image->bind_param('ssss', $image_title, $image_description, $image_filename,  $img_place);
                    $new_image->execute();
                    $new_image->close();
                    $response =  'success';
                    $image_id = $db->insert_id;

                    //change uploaded image to a webp image
                    //convert the file uploaded to a webp file
                    $cur_image_file = $image_filename; //current image to be converted to webp. find in the admin folder
                    $new_filename = "gallery-item-img-" . $image_id . ".webp";
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
                    imagewebp($image, $dir . $new_filename, 60);
                    //delete the old image
                    if (fopen($dir . $cur_image_file, "w")) {
                        unlink($dir . $cur_image_file);
                    };
                    copy($dir . $new_filename, $admin_dir . $new_filename);
                    //Update gift item
                    $gift_item = $db->prepare('UPDATE images SET image_filename=?  WHERE image_id =?');
                    $gift_item->bind_param('si', $new_filename, $image_id);
                    $gift_item->execute();
                    $gift_item->close();
                } else {
                    echo "Sorry, there was an error uploading your file.";
                }
            }
        }
    }

    if ($_POST['action'] == "edit") { //check if the action type of edit has been set in the post request
        //determine variables
        $image_id = $_POST['image_id'];
        $image_title = mysqli_real_escape_string($db, $_POST['image_title']);
        $image_description = mysqli_real_escape_string($db, $_POST['image_description']);
        if (!isset($_POST['img_placement'])) {
            //if blank then set as Other
            $img_placement = "Other";
        } else { //else set store in db as an array
            $img_placement = implode(",", $_POST['img_placement']);
        }

        //Update image
        $update_image = $db->prepare('UPDATE images SET image_title=?, image_description=?, image_placement=?  WHERE image_id =?');
        $update_image->bind_param('sssi', $image_title, $image_description, $img_placement, $image_id);
        $update_image->execute();
        $update_image->close();
        $response = '';
    }

    echo $response;
    exit();
}
///////Load image gallery from GET request
if (isset($_GET['action'])) {
    if ($_GET['action'] == "loadgallery") {
        include("../connect.php");
        //find images for each section 
        //find image details

        $home_query = ('SELECT * FROM images WHERE image_placement LIKE "%Home%"');
        $home = $db->query($home_query);

        $gallery_query = ('SELECT * FROM images WHERE image_placement LIKE "%Gallery%" ');
        $gallery = $db->query($gallery_query);

        $other_query = ('SELECT * FROM images WHERE image_placement ="Other" ');
        $other = $db->query($other_query);
        //home    
        echo " 
                <h2 class='my-2'>Home</h2>
                <div class='img-grid grid-auto-sm'  >";
        foreach ($home as $home_item) {
            //define image sections as set out from checkboxes 
            echo "<div class='img-card'>
                
                    
                        <div class='img-card-header-img'>
                            <a href='image.php?action=view&image_id=" . $home_item['image_id'] . "'><img src='assets/img/gallery/" . $home_item['image_filename'] . "'></a>
                            <h3>" . $home_item['image_title'] . "</h3>
                        </div>
                   
                    <div class='card-actions img-card-actions'>
                        <a href='image.php?action=edit&image_id=" . $home_item['image_id'] . "'><i class='fa-solid fa-pen-to-square'></i> Edit Image</a>
                        <a href='image.php?action=view&image_id=" . $home_item['image_id'] . "'><i class='fa-solid fa-eye'></i> View Image</a>
                        <a href='image.php?action=delete&confirm=no&image_id=" . $home_item['image_id'] . "'><i class='fa-solid fa-trash'></i> Delete Image</a>
                    </div>
                

            
            </div>";
        }
        echo "</div>";
        //gallery
        echo " 
        <h2 class='my-2'>Gallery</h2>
        <div class='img-grid grid-auto-sm'  >";
        foreach ($gallery as $gallery_item) {
            echo "<div class='img-card'>
        
           
                <div class='img-card-header-img'>
                <a href='image.php?action=view&image_id=" . $gallery_item['image_id'] . "'><img src='assets/img/gallery/" . $gallery_item['image_filename'] . "'></a>
                    <h3>" . $gallery_item['image_title'] . "</h3>
                </div>
            
            <div class='card-actions img-card-actions'>
                <a href='image.php?action=edit&image_id=" . $gallery_item['image_id'] . "'><i class='fa-solid fa-pen-to-square'></i> Edit Image</a>
                <a href='image.php?action=view&image_id=" . $gallery_item['image_id'] . "'><i class='fa-solid fa-eye'></i> View Image</a>
                <a href='image.php?action=delete&confirm=no&image_id=" . $gallery_item['image_id'] . "'><i class='fa-solid fa-trash'></i> Delete Image</a>
            </div>
       

    
    </div>";
        }
        echo "</div>";

        //other
        echo " 
        <h2 class='my-2'>Uncategorized</h2>
        <div class='img-grid grid-auto-sm'  >";
        foreach ($other as $other_item) {
            echo "<div class='img-card'>
        
            <div class='img-card-header-img'>
            <a href='image.php?action=view&image_id=" . $other_item['image_id'] . "'><img src='assets/img/gallery/" . $other_item['image_filename'] . "'></a>
            </div>
            
        <div class='img-card-body'>
            <h3>" . $other_item['image_title'] . "</h3>
           
            <div class='card-actions img-card-actions'>
            <a href='image.php?action=edit&image_id=" . $other_item['image_id'] . "'><i class='fa-solid fa-pen-to-square'></i> Edit Image</a>
            <a href='image.php?action=view&image_id=" . $other_item['image_id'] . "'><i class='fa-solid fa-eye'></i> View Image</a>
            <a href='image.php?action=delete&confirm=no&image_id=" . $other_item['image_id'] . "'><i class='fa-solid fa-trash'></i> Delete Image</a>
        </div>
        </div>

    
    </div>";
        }
        echo "</div>";
    }
}
