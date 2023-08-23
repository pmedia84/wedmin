<?php
include("../connect.php");
require("functions.php");
if (isset($_POST['action']) && $_POST['action'] == "delete") {
    $delete_image = $db->prepare('DELETE FROM images WHERE image_id =?');
    $image_id = $_POST['image_id'];

    foreach ($image_id as $id) {
        $img_file = "SELECT image_filename FROM images WHERE image_id=" . $id;
        $result =  mysqli_query($db, $img_file);
        $img = mysqli_fetch_assoc($result);

        $file = $_SERVER['DOCUMENT_ROOT'] . "/admin/assets/img/gallery/" . $img['image_filename'];
        $gallery = $_SERVER['DOCUMENT_ROOT'] . "/assets/img/gallery/" . $img['image_filename'];
        if (fopen($file, "w")) {
            unlink($file);
        };
        if (fopen($gallery, "w")) {
            unlink($gallery);
        };
        $delete_image->bind_param('i', $id);
        $delete_image->execute();
    }
    $delete_image->close();
    exit();
}

if (isset($_POST['action']) && $_POST['action'] == "edit_caption") {
    $image_caption = $db->prepare('UPDATE images SET image_description=? WHERE image_id=?');
    $image_id = $_POST['image_id'];
    $caption = mysqli_real_escape_string($db, $_POST['caption']);

    $image_caption->bind_param('si', $caption, $image_id);
    $image_caption->execute();

    $image_caption->close();
    exit();
}
if (isset($_POST['action']) && $_POST['action'] == "placement") {
    $image_place = $db->prepare('UPDATE images SET image_placement=? WHERE image_id=?');
    $image_id = $_POST['image_id'];
    $placement = $_POST['placement'];
    foreach ($image_id as $id) {
        $image_place->bind_param('si', $placement, $id);
        $image_place->execute();
    }
    $image_place->close();
    exit();
}
if (isset($_POST['action']) && $_POST['action'] == "upload") {
    $new_image = $db->prepare('INSERT INTO images (image_title, image_description, image_filename,  image_placement)VALUES(?,?,?,?)');
    //error codes
    // 0= Success
    // 1= Not Successful
    $response = 1;
    //check the post method has been sent
    if ($_SERVER['REQUEST_METHOD'] !== "POST") {
        $response = '<div class="form-response error"><p>Post Method Not Set</p></div>';
        echo $response;
    }
    //check the file upload is set
    if (empty($_FILES)) {
        $response = '<div class="form-response error"><p>File upload is empty, check your server settings and try again.</p></div>';
        echo $response;
        exit();
    }

    //check for error codes
    if ($_FILES['gallery_img']['error'] !== UPLOAD_ERR_OK) {

        switch ($_FILES["gallery_img"]["error"]) {
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
    // Reject uploaded file larger
    if ($_FILES["gallery_img"]["size"] > 3145728) {
        exit('File too large');
    }
    // Use fileinfo to get the mime type
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime_type = $finfo->file($_FILES["gallery_img"]["tmp_name"]);

    $mime_types = ["image/gif", "image/png", "image/jpeg"];

    if (!in_array($_FILES["gallery_img"]["type"], $mime_types)) {
        exit("Invalid file type");
    }
    //detect the orientation of the uploaded file
    $exif = exif_read_data($_FILES["gallery_img"]["tmp_name"]);


    //set the file name
    $newimgname = "gallery-img-0.webp";
    //set the upload path
    $dir = $_SERVER['DOCUMENT_ROOT'] . "/admin/assets/img/gallery/" . $newimgname;
    $i = 1;
    while (file_exists($dir)) {
        $newimgname = "gallery-img-" . $i . ".webp";
        $dir = $_SERVER['DOCUMENT_ROOT'] . "/admin/assets/img/gallery/" . $newimgname;
        $i++;
    }
    // convert into webp
    $info = getimagesize($_FILES['gallery_img']['tmp_name']);
    if ($info['mime'] == 'image/jpeg') {
        $image = imagecreatefromjpeg($_FILES['gallery_img']['tmp_name']);
    } elseif ($info['mime'] == 'image/gif') {
        $image = imagecreatefromgif($_FILES['gallery_img']['tmp_name']);
    } elseif ($info['mime'] == 'image/png') {
        $image = imagecreatefrompng($_FILES['gallery_img']['tmp_name']);
    }
    imagepalettetotruecolor($image);
    imagealphablending($image, true);
    imagesavealpha($image, true);
    //rotate the image after converting
    if (isset($exif['Orientation']) && $exif['Orientation'] == 6) {
        $image = imagerotate($image, -90, 0);
    }
    //set an image id to the img name and increment by 1


    if (!imagewebp($image, $dir, 60)) {
        exit('error');
    } else {
        //set up posting to db
        $image_title = mysqli_real_escape_string($db, $_POST['image_title']);
        $image_description = mysqli_real_escape_string($db, $_POST['image_description']);
        $image_filename = $newimgname;
        if (!isset($_POST['img_placement'])) {
            //if blank then set as Other
            $img_place = "Other";
        } else { //else set store in db as an array
            $img_place = implode(",", $_POST['img_placement']);
        }
        $new_image->bind_param('ssss', $image_title, $image_description, $image_filename,  $img_place);
        $new_image->execute();

        /// copy to website paths
        $website_dir = $_SERVER['DOCUMENT_ROOT'] . "/assets/img/gallery/";
        copy($dir, $website_dir . $newimgname);
        $response = 0;
    }
    $new_image->close();
    echo $response;
    exit();
}

if (isset($_GET['img_total'])) {
    include("../connect.php");
    $sub_count = $db->query("SELECT  COUNT(image_id) AS count FROM images");
    $count_r = mysqli_fetch_assoc($sub_count);
    $t = $count_r['count'];
    echo $t;
}
?>

<?php if (isset($_GET['action']) && $_GET['action'] == "load_gallery") :
    //load images
    $gallery_query = $db->query('SELECT images.image_id,  images.image_title, images.image_description, images.image_filename, images.image_upload_date, images.image_placement, images.guest_id, images.status, guest_list.guest_id, guest_list.guest_fname, guest_list.guest_sname FROM images LEFT JOIN guest_list ON guest_list.guest_id=images.guest_id');

?>

    <form action="" id="gallery" method="POST">
        <div class="grid-row-3col my-2 std-card">
            <?php if ($gallery_query->num_rows > 0) :
                $key = 0;
                foreach ($gallery_query as $image) :
            ?>
                    <div class="img-card" data-status="<?= $image['status']; ?>">
                        <a href="image?image_id=<?= $image['image_id']; ?>&action=edit" class="btn-primary btn-secondary btn-edit">
                            <svg class="icon">
                                <use href="assets/img/icons/solid.svg#pen" />
                            </svg>
                        </a>
                        <input class="img-select" data-select="false" data-image_filename="<?= $image['image_filename']; ?>" type="checkbox" name="gallery_img[<?= $key; ?>][image_id]" value="<?= $image['image_id']; ?>">
                        <img class="gallery-img" src="assets/img/gallery/<?= $image['image_filename']; ?>" alt="" data-img_id="<?= $image['image_id']; ?>">
                        <p class="img-card-caption"><?= $image['image_description']; ?></p>
                    </div>
            <?php $key++;
                endforeach;

            endif; ?>
        </div>
    </form>
<?php endif; ?>
<?php if (isset($_GET['term'])) :
    include("../inc/settings.php");
    $term = $_GET['term'];
    if ($term == "guest") {
        $gallery_query = $db->query('SELECT images.image_id,  images.image_title, images.image_description, images.image_filename, images.image_upload_date, images.image_placement, images.guest_id, images.status, guest_list.guest_id, guest_list.guest_fname, guest_list.guest_sname FROM images LEFT JOIN guest_list ON guest_list.guest_id=images.guest_id WHERE images.guest_id >""');
    }
    if ($term == "") {
        $gallery_query = $db->query('SELECT images.image_id,  images.image_title, images.image_description, images.image_filename, images.image_upload_date, images.image_placement, images.guest_id, images.status, guest_list.guest_id, guest_list.guest_fname, guest_list.guest_sname FROM images LEFT JOIN guest_list ON guest_list.guest_id=images.guest_id');
    }
    if ($term == "ours") {
        $gallery_query = $db->query('SELECT images.image_id,  images.image_title, images.image_description, images.image_filename, images.image_upload_date, images.image_placement, images.guest_id, images.status, guest_list.guest_id, guest_list.guest_fname, guest_list.guest_sname FROM images LEFT JOIN guest_list ON guest_list.guest_id=images.guest_id WHERE images.guest_id IS NULL');
    }

?>
    <form action="" id="gallery" method="POST">
        <div class="grid-row-3col my-2 std-card">
            <?php if ($gallery_query->num_rows > 0) :
                $key = 0;
                foreach ($gallery_query as $image) :
            ?>
                    <div class="img-card" data-status="<?= $image['status']; ?>">
                        <a href="image?image_id=<?= $image['image_id']; ?>&action=edit" class="btn-primary btn-secondary btn-edit">
                            <svg class="icon">
                                <use href="assets/img/icons/solid.svg#pen" />
                            </svg>
                        </a>
                        <input class="img-select" data-select="false" data-image_filename="<?= $image['image_filename']; ?>" type="checkbox" name="gallery_img[<?= $key; ?>][image_id]" value="<?= $image['image_id']; ?>">
                        <img class="gallery-img" src="assets/img/gallery/<?= $image['image_filename']; ?>" alt="" data-img_id="<?= $image['image_id']; ?>">
                        <p class="img-card-caption"><?= $image['image_description']; ?></p>
                    </div>
            <?php $key++;
                endforeach;

            endif; ?>
        </div>
    </form>
<?php endif; ?>