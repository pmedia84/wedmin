<?php
//? CRUD operations for news articles
//* Response Codes
//! 200 = Success
//! 500 = Error
//! 400 Script Error
// * Variables
$response = ""; //response message to front end
$news_articles_img = ""; //default img variable if none is uploaded
$dir = $_SERVER['DOCUMENT_ROOT'] . "/assets/img/news/"; //image upload location for front end
$admin_dir = $_SERVER['DOCUMENT_ROOT'] . "/admin/assets/img/news/"; //save a copy of the image in the admin directory
date_default_timezone_set('Europe/London'); //set time zone
$article_date = date('y-m-d'); // timestamp for today
$article_id = "";
//* DB connection
include("../connect.php");
//?Create an article
if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['action']) && $_POST['action'] == "create") {
    //! insert
    $new_article = $db->prepare('INSERT INTO news_articles (news_articles_title, news_articles_date, news_articles_body, news_articles_img, news_articles_author, news_articles_status)VALUES(?,?,?,?,?,?)');
    //*check if an image has been uploaded and handle this first
    if (!$_FILES['news_articles_img']['name'] == null) {
        //*file name as uploaded
        $file = $dir . basename($_FILES['news_articles_img']['name']);
        //*get the file type
        $imageFileType = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        //* Check if image file is a actual image or fake image
        $check = getimagesize($_FILES["news_articles_img"]["tmp_name"]);
        //*check for error codes
        if ($_FILES['news_articles_img']['error'] !== UPLOAD_ERR_OK) {

            switch ($_FILES["news_articles_img"]["error"]) {
                case UPLOAD_ERR_PARTIAL:
                    exit('File only partially uploaded');
                    break;
                case UPLOAD_ERR_NO_FILE:
                    exit('No file was uploaded');
                    break;
                case UPLOAD_ERR_EXTENSION:
                    exit('File upload stopped by a PHP extension');
                    break;
                case UPLOAD_ERR_FORM_SIZE:
                    exit('File exceeds MAX_FILE_SIZE in the HTML form');
                    break;
                case UPLOAD_ERR_INI_SIZE:
                    exit('File exceeds upload_max_filesize in php.ini');
                    break;
                case UPLOAD_ERR_NO_TMP_DIR:
                    exit('Temporary folder not found');
                    break;
                case UPLOAD_ERR_CANT_WRITE:
                    exit('Failed to write file');
                    break;
                default:
                    exit('Unknown upload error');
                    break;
            }
        }
        //* Reject uploaded file larger
        if ($_FILES["news_articles_img"]["size"] > 3145728) {
            exit('File too large');
        }
        //* Use fileinfo to get the mime type
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime_type = $finfo->file($_FILES["news_articles_img"]["tmp_name"]);

        $mime_types = ["image/gif", "image/png", "image/jpeg"];

        if (!in_array($_FILES["news_articles_img"]["type"], $mime_types)) {
            exit("Invalid file type");
        }
        //* detect the orientation of the uploaded file, only jpg is supported by this function
        if ($imageFileType == "jpg") {
            $exif = exif_read_data($_FILES["news_articles_img"]["tmp_name"]);
        }
        //*set the file name
        $newimgname = "news-img-0.webp";
        //* Check that the image file name has not been used already, if it has increase by one until it is available
        $i = 1;
        while (file_exists($dir)) {
            $newimgname = "news-img-" . $i . ".webp";
            $dir = $_SERVER['DOCUMENT_ROOT'] . "/assets/img/news/" . $newimgname;
            $i++;
        }
        //* convert into webp
        $info = getimagesize($_FILES['news_articles_img']['tmp_name']);
        if ($info['mime'] == 'image/jpeg') {
            $image = imagecreatefromjpeg($_FILES['news_articles_img']['tmp_name']);
        } elseif ($info['mime'] == 'image/gif') {
            $image = imagecreatefromgif($_FILES['news_articles_img']['tmp_name']);
        } elseif ($info['mime'] == 'image/png') {
            $image = imagecreatefrompng($_FILES['news_articles_img']['tmp_name']);
        }
        imagepalettetotruecolor($image);
        imagealphablending($image, true);
        imagesavealpha($image, true);
        //* rotate the image after converting
        if (isset($exif['Orientation']) && $exif['Orientation'] == 6) {
            $image = imagerotate($image, -90, 0);
        }
        if (!imagewebp($image, $dir, 60)) {
            exit('error');
        } else {
            //*save to file locations
            $image_filename = $newimgname;
            //* copy to admin path
            copy($dir, $admin_dir . $newimgname);
            $response = 0;
        }
        //*define the image file name if one has been uploaded
        $news_articles_img = $newimgname;
    }

    //* set up news article for adding to DB
    //!define all other variables
    $news_articles_title = htmlentities(mysqli_real_escape_string($db, $_POST['news_articles_title']));
    $news_articles_body = htmlentities($_POST['news_article_body']);
    $news_articles_status = $_POST['news_articles_status'];
    $user_id = $_POST['user_id'];

    $new_article->bind_param('ssssis', $news_articles_title, $article_date, $news_articles_body, $news_articles_img, $user_id, $news_articles_status);
    if ($new_article->execute()) {
        $new_article->close();
    }
    $article_id = $db->insert_id;
    //*echo back to front end the new post and redirect to view it
    $response = "news_article.php?action=view&news_articles_id=" . $article_id;
    echo $response;
}
//?edit an article
if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['action']) && $_POST['action'] == "edit") {
    //!update
    $update_article = $db->prepare('UPDATE news_articles SET news_articles_title=?, news_articles_body=?, news_articles_img=?, news_articles_status=?  WHERE news_articles_id =?');
    //*Set the default file name to the existing one, this will only change if a new image has been uploaded.
    $news_articles_img = $_POST['img_filename'];
    //*check if an image has been uploaded and handle this first
    if (!$_FILES['news_articles_img']['name'] == null) {
        $old_img = $_POST['img_filename'];
        //*file name as uploaded
        $file = $dir . basename($_FILES['news_articles_img']['name']);
        //*get the file type
        $imageFileType = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        //* Check if image file is a actual image or fake image
        $check = getimagesize($_FILES["news_articles_img"]["tmp_name"]);
        //*check for error codes
        if ($_FILES['news_articles_img']['error'] !== UPLOAD_ERR_OK) {

            switch ($_FILES["news_articles_img"]["error"]) {
                case UPLOAD_ERR_PARTIAL:
                    exit('File only partially uploaded');
                    break;
                case UPLOAD_ERR_NO_FILE:
                    exit('No file was uploaded');
                    break;
                case UPLOAD_ERR_EXTENSION:
                    exit('File upload stopped by a PHP extension');
                    break;
                case UPLOAD_ERR_FORM_SIZE:
                    exit('File exceeds MAX_FILE_SIZE in the HTML form');
                    break;
                case UPLOAD_ERR_INI_SIZE:
                    exit('File exceeds upload_max_filesize in php.ini');
                    break;
                case UPLOAD_ERR_NO_TMP_DIR:
                    exit('Temporary folder not found');
                    break;
                case UPLOAD_ERR_CANT_WRITE:
                    exit('Failed to write file');
                    break;
                default:
                    exit('Unknown upload error');
                    break;
            }
        }
        //* Reject uploaded file larger
        if ($_FILES["news_articles_img"]["size"] > 3145728) {
            exit('File too large');
        }
        //* Use fileinfo to get the mime type
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime_type = $finfo->file($_FILES["news_articles_img"]["tmp_name"]);

        $mime_types = ["image/gif", "image/png", "image/jpeg"];

        if (!in_array($_FILES["news_articles_img"]["type"], $mime_types)) {
            exit("Invalid file type");
        }
        //* detect the orientation of the uploaded file, only jpg is supported by this function
        if ($imageFileType == "jpg") {
            $exif = exif_read_data($_FILES["news_articles_img"]["tmp_name"]);
        }
        //*set the file name
        $newimgname = "news-img-0.webp";
        //* Check that the image file name has not been used already, if it has increase by one until it is available
        $i = 1;
        while (file_exists($dir)) {
            $newimgname = "news-img-" . $i . ".webp";
            $dir = $_SERVER['DOCUMENT_ROOT'] . "/assets/img/news/" . $newimgname;
            $i++;
        }
        //* convert into webp
        $info = getimagesize($_FILES['news_articles_img']['tmp_name']);
        if ($info['mime'] == 'image/jpeg') {
            $image = imagecreatefromjpeg($_FILES['news_articles_img']['tmp_name']);
        } elseif ($info['mime'] == 'image/gif') {
            $image = imagecreatefromgif($_FILES['news_articles_img']['tmp_name']);
        } elseif ($info['mime'] == 'image/png') {
            $image = imagecreatefrompng($_FILES['news_articles_img']['tmp_name']);
        }
        imagepalettetotruecolor($image);
        imagealphablending($image, true);
        imagesavealpha($image, true);
        //* rotate the image after converting
        if (isset($exif['Orientation']) && $exif['Orientation'] == 6) {
            $image = imagerotate($image, -90, 0);
        }
        if (!imagewebp($image, $dir, 60)) {
            exit('error');
        } else {
            //*save to file locations
            $image_filename = $newimgname;
            //* copy to admin path
            copy($dir, $admin_dir . $newimgname);
            $response = 200;
        }
        //*define the image file name if one has been uploaded
        $news_articles_img = $newimgname;
        //* Delete the old image, but only if it had one set and was not the default
        if (!$old_img == "") {
            $old_path = $_SERVER['DOCUMENT_ROOT'] . "/assets/img/news/";
            $old_admin_path = $_SERVER['DOCUMENT_ROOT'] . "/admin/assets/img/news/";
            if (fopen($old_path . $old_img, "w")) {
                unlink($old_path . $old_img);
            };
            if (fopen($old_admin_path . $old_img, "w")) {
                unlink($old_admin_path . $old_img);
            };
        }
    }
    //* set up news article for adding to DB
    //!define all other variables
    $news_articles_title = htmlentities(mysqli_real_escape_string($db, $_POST['news_articles_title']));
    $news_articles_body = htmlentities($_POST['news_article_body']);
    $news_articles_status = $_POST['news_articles_status'];
    $news_article_id = $_POST['news_articles_id'];

    $update_article->bind_param('ssssi', $news_articles_title, $news_articles_body, $news_articles_img, $news_articles_status, $news_article_id);
    if ($update_article->execute()) {
        $update_article->close();
        $response = 200;
    }
    echo $response;
}
