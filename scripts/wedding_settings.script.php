<?php
   include("../connect.php");
//update module status
if (isset($_POST['module_id'])){
   $module_id = $_POST['module_id'];
   $module_status = $_POST['module_status'];
   //update module setting

   $update_module = $db->prepare('UPDATE wedding_modules SET wedding_module_status=? WHERE wedding_module_id =?');
   $update_module->bind_param('si', $module_status, $module_id);
   $update_module->execute();
   $update_module->close();
}

if(isset($_POST['action']) && $_POST['action']== "newimg"){
   //handle new image upload for guest area
   //check that an image has been uploaded
   if ($_FILES['guest_home_img']['name'] == null) {
   //header image name is blank if no file uploaded
   $response = '<div class="alert-duration">
 
   <sl-alert variant="danger" duration="3000" closable>
     <sl-icon slot="icon" name="info-circle"></sl-icon>
     No image uploaded, please try again.
   </sl-alert>
 </div>';

   echo $response;
   exit();

   $img_filename = "";
}else { //if there is an image uploaded then save it to the folder
   //////////////////////sort the image upload first////////////////////////////////////////
   $dir = $_SERVER['DOCUMENT_ROOT']. "/guests/assets/img/";
   $file = $dir . "guest-home-img.jpg";
   $imageFileType = strtolower(pathinfo($file, PATHINFO_EXTENSION));
   // Check if image file is a actual image or fake image
   $check = getimagesize($_FILES["guest_home_img"]["tmp_name"]);
   if ($check !== false) {
       $upload = 1;
   } else {
       $response = '<div class="alert-duration">
 
       <sl-alert variant="danger" duration="3000" closable>
         <sl-icon slot="icon" name="info-circle"></sl-icon>
         File type not supported, please try again.
       </sl-alert>
     </div>';
   }

   // Check file size
   if ($_FILES["guest_home_img"]["size"] > 1048576) {
       $response = '<div class="alert-duration">
 
       <sl-alert variant="danger" duration="3000" closable>
         <sl-icon slot="icon" name="info-circle"></sl-icon>
         Image size too large!.
       </sl-alert>
     </div>';
       $upload = 0;
       echo $response;
       exit();
   }
   // Allow certain file formats
   if (
       $imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"

   ) {
       $response = '<div class="alert-duration">
 
       <sl-alert variant="danger" duration="3000" closable>
         <sl-icon slot="icon" name="info-circle"></sl-icon>
         Sorry, only jpg, png and jpeg image type are accepted.
       </sl-alert>
     </div>';
       $upload = 0;
       echo $response;
       exit();
   }

   // Check if $upload is set to 0 by an error
   if ($upload == 0) {
       $response = '<div class="form-response error"><p>Error, your article image was not saved. You can try again by editing your image</p></div>';
       // if everything is ok, try to upload file
   } else {
       if (move_uploaded_file($_FILES["guest_home_img"]["tmp_name"], $file)) {
           
          
           //define articles img variable
           $image_filename = "guest-home-img.jpg";


           $img_place = "Guest Home";



           //insert new image into table
           $new_image = $db->prepare('INSERT INTO images (image_title, image_description, image_filename,  image_placement)VALUES(?,?,?,?)');
           $new_image->bind_param('ssss', $image_title, $image_description, $image_filename,  $img_place);
           $new_image->execute();
           $new_image->close();
           $response =  'success';
       } else {
           echo "Sorry, there was an error uploading your file.";
       }
   }
}
$response = "success";
echo $response;
}
?>