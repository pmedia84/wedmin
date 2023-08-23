<?php
session_start();
require("scripts/functions.php");
check_login();
include("connect.php");
include("inc/settings.php");
$user=new User();

////////////////Find details of the cms being used, on every page\\\\\\\\\\\\\\\
//Variable for name of CMS
//wedding is the name of people
//business name
$cms_name ="";
$user_id = $_SESSION['user_id'];
if ($cms->type() =="Business") {
    //look for the business set up and load information
    //find business details.
    $business = $db->prepare('SELECT * FROM business');

    $business->execute();
    $business->store_result();
    $business->bind_result($business_id, $business_name, $address_id, $business_phone, $business_email, $business_contact_name);
    $business->fetch();
    $business->close();
    //set cms name
    $cms_name = $business_name;
    //find user details for this business
    $business_users = $db->prepare('SELECT users.user_id, users.user_name, business_users.business_id, business_users.user_type FROM users NATURAL LEFT JOIN business_users WHERE users.user_id='.$user_id);

    $business_users->execute();
    $business_users->bind_result($user_id, $user_name,$business_id, $user_type);
    $business_users->fetch();
    $business_users->close();
}

//run checks to make sure a wedding has been set up correctly
if ($cms->type() == "Wedding") {
    //look for the Wedding set up and load information
    //find Wedding details.
    $wedding = $db->prepare('SELECT * FROM wedding');

    $wedding->execute();
    $wedding->store_result();
    $wedding->bind_result($wedding_id, $wedding_name, $wedding_date, $wedding_time, $wedding_email, $wedding_phone, $wedding_contact_name);
    $wedding->fetch();
    $wedding->close();
    //set cms name
    $cms_name = $wedding_name;

}

//////////////////////////////////////////////////////////////////Everything above this applies to each page\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\
$article_id = $_GET['news_articles_id'];

//load news article
$article = $db->prepare('SELECT * FROM news_articles  WHERE news_articles_id=' . $article_id);
$article->execute();
$article->store_result();

//find news articles
$news_query = ('SELECT * FROM news_articles WHERE news_articles_status="Published" ORDER BY news_articles_date LIMIT 3 ');
$news = $db->query($news_query);
include("inc/head.inc.php");
?>
<!-- Meta Tags For Each Page -->
<meta name="description" content="Parrot Media - Client Admin Area">
<meta name="title" content="Manage your website content">
<!-- /Meta Tags -->

<!-- / -->
<!-- Page Title -->
<title>Mi-Admin | News Posts</title>
<!-- /Page Title -->
<!-- Tiny MCE -->
<script src="https://cdn.tiny.cloud/1/7h48z80zyia9jc41kx9pqhh00e1e2f4pw9kdcmhisk0cm35w/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
</head>
<script>
    tinymce.init({
        selector: 'textarea#news_article_body',
        height: 500,

        plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
        toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table mergetags | addcomment showcomments | spellcheckdialog a11ycheck | align lineheight | checklist numlist bullist indent outdent | emoticons charmap | removeformat | ',
        tinycomments_mode: 'embedded',

        tinycomments_author: 'Author name',
        mergetags_list: [{
                value: 'First.Name',
                title: 'First Name'
            },
            {
                value: 'Email',
                title: 'Email'
            },
        ]
    });
</script>
</head>


<body>
    <!-- Main Body Of Page -->
    <main class="main col-2">
        <!-- Header Section -->
        <?php include("inc/header.inc.php"); ?>
        <!-- Nav Bar -->
        <?php include("./inc/nav.inc.php"); ?>
        <!-- /nav bar -->
        <section class="body">
            <div class="breadcrumbs mb-2">
                <a href="index.php" class="breadcrumb">Home</a> /
                <a href="news.php" class="breadcrumb">News</a>
                <?php if ($_GET['action'] == "edit") : ?>
                    / Edit Post
                <?php endif; ?>
                <?php if ($_GET['action'] == "delete") : ?>
                    / Delete Post
                <?php endif; ?>
                <?php if ($_GET['action'] == "view") : ?>
                    / View Post
                <?php endif; ?>
            </div>
            <div class="main-cards">
                <?php if ($_GET['action'] == "edit") : ?>
                <?php else : ?>
                <?php endif; ?>
                <?php if ($user_type == "Admin" || $user_type == "Developer") : //detect if user is an admin or not 
                ?>
                    <?php if ($_GET['action'] == "delete") : //if action is delete, detect if the confirm is yes or no
                    ?>
                        <?php if ($_GET['confirm'] == "yes") : //if yes then delete the article
                        ?>
                            <?php if (($article->num_rows) > 0) :
                                $article->bind_result($news_articles_id, $news_articles_title, $news_articles_date, $news_articles_body, $news_articles_img, $news_articles_author, $news_articles_status);
                                $article->fetch();
                                $news_articles_body = html_entity_decode($news_articles_body);
                                // connect to db and delete the record
                                $delete_article = "DELETE FROM news_articles WHERE news_articles_id=" . $news_articles_id;
                                if (mysqli_query($db, $delete_article)) {
                                    echo '<div class="news-create"><div class="form-response error"><p>' . $news_articles_title . ' Has Been Deleted</p></div></div>';
                                } else {
                                    echo '<div class="form-response error"><p>Error deleting article, please try again.</p></div>';
                                }
                            ?>

                            <?php endif; ?>
                        <?php else : //if not then display the message to confirm the user wants to delete the news article
                        ?>
                            <?php if (($article->num_rows) > 0) :
                                $article->bind_result($news_articles_id, $news_articles_title, $news_articles_date, $news_articles_body, $news_articles_img, $news_articles_author, $news_articles_status);
                                $article->fetch();
                                $news_articles_body = html_entity_decode($news_articles_body); ?>
                                <div class="news-create">
                                    <h2 class="text-alert">Delete: <?= $news_articles_title; ?></h2>
                                    <p>Are you sure you want to delete this article?</p>
                                    <p><strong>This Cannot Be Reversed</strong></p>
                                    <div class="button-section">
                                        <a class="btn-primary btn-delete my-2" href="news_article.php?action=delete&confirm=yes&news_articles_id=<?= $news_articles_id; ?>"><i class="fa-solid fa-trash"></i>Delete Article</a>
                                        <a class="btn-primary btn-secondary my-2" href="news_article.php?action=view&news_articles_id=<?= $news_articles_id; ?>"><i class="fa-solid fa-ban"></i>Cancel</a>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>



                    <?php endif; ?>

                    <?php if ($_GET['action'] == "edit") : ?>
                        <?php if (($article->num_rows) > 0) :
                            $article->bind_result($news_articles_id, $news_articles_title, $news_articles_date, $news_articles_body, $news_articles_img, $news_articles_author, $news_articles_status);
                            $article->fetch();
                            $news_articles_body = html_entity_decode($news_articles_body);
                        ?>
                            <h1><i class="fa-solid fa-newspaper"></i> Edit Post: <?= html_entity_decode($news_articles_title); ?></h1>
                            <div class="std-card">
                                <form class="" id="edit_news_article" action="scripts/news.script.php" method="post" enctype="multipart/form-data" data-article_id="<?= $news_articles_id; ?>" data-img_file="<?= $news_articles_img; ?>">
                                    <div class="form-input-wrapper">
                                        <label for="news_article_title">Title</label>
                                        <!-- input -->
                                        <input class="text-input input" type="text" name="news_articles_title" id="news_articles_title" placeholder="Article Title" required="" maxlength="45" value="<?= $news_articles_title; ?>">
                                    </div>
                                    <div class="form-input-wrapper my-2">
                                        <label for="business_email">Header Image</label>
                                        <img src="./assets/img/news/<?= $news_articles_img; ?>" alt="">
                                        <!-- input -->
                                        <p class="form-hint-small">Change the image by uploading a new one here:</p>
                                        <input type="file" name="news_articles_img" id="news_articles_img" accept="image/*">
                                    </div>
                                    <div class="form-input-wrapper my-2">
                                        <label for="news_article_body">Article Body</label>
                                        <textarea id="news_article_body" name="news_article_body">
                                            <?= $news_articles_body; ?>
                                        </textarea>
                                    </div>

                                    <div class="form-input-wrapper my-2">
                                        <label for="news_articles_status">Status</label>
                                        <p class="form-hint-small">Set as a draft to come back and finish, or set as published to publish to your website straight away.</p>
                                        <select name="news_articles_status" id="news_articles_status" required="">
                                            <option value="<?= $news_articles_status; ?>" selected><?= $news_articles_status; ?></option>
                                            <option value="Draft">Draft</option>
                                            <option value="Published">Published</option>
                                        </select>
                                    </div>
                                    <div class="button-section my-3">
                                        <button class="btn-primary form-controls-btn" type="submit"><i class="fa-solid fa-floppy-disk"></i> Update Post </button>
                                    </div>
                                    <div id="response" class="d-none">
                                        <p>Article Saved <img src="./assets/img/icons/check.svg" alt=""></p>
                                    </div>
                                </form>
                            </div>
                        <?php else : ?>
                            <div class="std-card">
                                <h2>Error</h2>
                                <p>There has been an error, please return to the last page and try again.</p>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php if ($_GET['action'] == "view") : ?>
                        <?php if (($article->num_rows) > 0) :
                            $article->bind_result($news_articles_id, $news_articles_title, $news_articles_date, $news_articles_body, $news_articles_img, $news_articles_author, $news_articles_status);
                            $article->fetch();
                            $news_articles_body = html_entity_decode($news_articles_body);
                            $news_articles_date = date('d-M-y');
                            if ($news_articles_status == "Published") {
                                $news_articles_status = "<p class='news-item-status published'>Published <i class='fa-solid fa-check'></i></p>";
                            }
                            if ($news_articles_status == "Draft") {
                                $news_articles_status = "<p class='news-item-status draft'>Draft <i class='fa-solid fa-flag'></i></p>";
                            } ?>
                            <h1><i class="fa-solid fa-newspaper"></i> <?= $news_articles_title; ?></h1>
                            <div class="std-card news-create">
                                
                                <span class="news-create-status">
                                    <?= $news_articles_status; ?>
                                </span>
                                <h2 class="my-2"><?= $news_articles_title; ?></h2>
                                <?php if ($news_articles_img == null) : ?>
                                    <img src="./assets/img/news/news-item.webp" alt="">
                                <?php else : ?>
                                    <img src="./assets/img/news/<?= $news_articles_img ?>" alt="">
                                <?php endif; ?>
                                <p class="news-create-date my-2"><?= $news_articles_date; ?></p>
                                <div class="news-create-body"><?= $news_articles_body; ?></div>
                                <div class="card-actions my-2">
                                    <a class="my-2" href="news_article.php?action=edit&news_articles_id=<?= $news_articles_id; ?>"><i class="fa-solid fa-pen-to-square"></i> Edit Post </a><br>
                                    <a class="my-2" href="news_article.php?action=delete&confirm=no&news_articles_id=<?= $news_articles_id; ?>"><i class="fa-solid fa-trash"></i> Delete Post </a>
                                </div>
                            <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        <h2>Recent Published Posts</h2>
                        <a class="my-2" href="news.php">View All</a>
                        <div class="news-grid">
                            <?php foreach ($news as $article) :
                                $news_article_body = html_entity_decode($article['news_articles_body']);
                                $news_articles_date = strtotime($article['news_articles_date']);

                                if ($article['news_articles_status'] == "Published") {
                                    $news_articles_status = "<p class='news-item-status published'>Published <i class='fa-solid fa-check'></i></p>";
                                }
                                if ($article['news_articles_status'] == "Draft") {
                                    $news_articles_status = "<p class='news-item-status draft'>Draft <i class='fa-solid fa-flag'></i></p>";
                                } ?>

                                <div class="news-card">
                                    <div class="news-card-header">
                                        <h2><a href="news_article.php?action=view&news_articles_id=<?= $article['news_articles_id']; ?>"><?= $article['news_articles_title']; ?></a></h2>
                                        <span class="news-create-status">
                                            <?= $news_articles_status; ?>
                                        </span>
                                    </div>
                                    <?php if ($article['news_articles_img'] == null) : ?>
                                        <a href="news_article.php?action=view&news_articles_id=<?= $article['news_articles_id']; ?>"><img src="./assets/img/news/news-item.webp" alt=""></a>
                                    <?php else : ?>
                                        <a href="news_article.php?action=view&news_articles_id=<?= $article['news_articles_id']; ?>"><img src="./assets/img/news/<?= $article['news_articles_img']; ?>" alt=""></a>
                                    <?php endif; ?>
                                    <p class="news-create-date my-2"><?= date('d-m-y', $news_articles_date); ?></p>
                                    <div class="news-card-body my-2">
                                        <p><?= $news_article_body; ?></p>
                                    </div>
                                    <div class="card-actions my-2">
                                        <a class="my-2" href="news_article.php?action=view&news_articles_id=<?= $article['news_articles_id']; ?>"><i class="fa-solid fa-eye"></i> View Article</a>
                                        <a class="my-2" href="news_article.php?action=edit&news_articles_id=<?= $article['news_articles_id']; ?>"><i class="fa-solid fa-pen-to-square"></i> Edit Article </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <p class="font-emphasis">You do not have the necessary Administrator rights to view this page.</p>
                        <?php endif; ?>
                        </div>
            </div>
        </section>


    </main>

    <!-- /Main Body Of Page -->
    <!-- Footer -->
    <?php include("./inc/footer.inc.php"); ?>
    <!-- /Footer -->
    <script src="assets/js/news.js"></script>
</body>

</html>