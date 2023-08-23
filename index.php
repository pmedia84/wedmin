<?php
session_start();
require("scripts/functions.php");
check_login();
include("connect.php");
include("inc/settings.php");
$user=new User();
//find news articles
$news_query = ('SELECT * FROM news_articles WHERE news_articles_status="Published" ORDER BY news_articles_date LIMIT 3 ');
$news = $db->query($news_query);
$num_articles = $news->num_rows;
//find the amount of articles listed
$article_num = ('SELECT news_articles_id FROM news_articles  ');
$article_num = $db->query($article_num);
$article_amt = $article_num->num_rows;
//find the amount of images listed
$image_num = ('SELECT image_id FROM images  ');
$image_num = $db->query($image_num);
$image_amt = $image_num->num_rows;
include("inc/head.inc.php");
?>
<!-- Meta Tags For Each Page -->
<meta name="description" content="Parrot Media - Client Admin Area">
<meta name="title" content="Manage your website content">
<!-- Page Title -->
<title>Mi-Admin | Dashboard</title>
<!-- /Page Title -->
</head>

<body>
    <!-- Main Body Of Page -->
    <main class="main">
        <!-- Header Section -->
        <?php include("inc/header.inc.php"); ?>
        <!-- Nav Bar -->
        <?php include("./inc/nav.inc.php"); ?>
        <!-- /nav bar -->
        <section class="body">
            <div class="breadcrumbs"><span><i class="fa-solid fa-house"></i> Home / </span></div>
            <div class="main-dashboard">
                <?php if ($news_m->status() == "On") : ?>
                    <div class="dashboard-card">
                        <div class="dashboard-card-header">
                            <span><?= $article_amt; ?></span>
                            <img src="assets/img/icons/newspaper.svg" alt="">
                        </div>
                        <h2>News Posts</h2>
                        <a href="news.php">Manage</a>
                    </div>
                <?php endif; ?>
                <div class="dashboard-card">
                    <div class="dashboard-card-header">
                        <span><?= $image_amt; ?></span>
                        <i class="fa-solid fa-images"></i>
                    </div>
                    <h2>Photo Gallery</h2>
                    <a href="gallery.php">Manage</a>
                </div>

                <?php if ($cms->type() == "Wedding") :
                    //find the amount of guests
                    $guest_num = ('SELECT guest_id FROM guest_list');
                    $guest_num = $db->query($guest_num);
                    $guest_amt = $guest_num->num_rows;
                    //find the amount of guests
                    $invite_num = ('SELECT invite_id FROM invitations');
                    $invite_num = $db->query($invite_num);
                    $invite_num = $invite_num->num_rows;

                ?>
                    <?php if ($invite_manager->status() == "On") : ?>
                        <div class="dashboard-card">
                            <div class="dashboard-card-header">
                                <span><?= $guest_amt; ?></span>
                                <i class="fa-solid fa-people-group"></i>
                            </div>
                            <h2>Guest List</h2>
                            <a href="guest_list.php">Manage</a>
                        </div>
                    <?php endif; ?>

                    <div class="dashboard-card">
                        <div class="dashboard-card-header">
                            <span><?= $invite_num; ?></span>
                            <i class="fa-solid fa-champagne-glasses"></i>
                        </div>
                        <h2>Invitations</h2>
                        <a href="invitations">Manage</a>
                    </div>
                <?php endif; ?>
            </div>


        </section>
        <?php if ($news_m->status() == "On") : ?>
            <div class="main-cards">
                <h2>Published Posts</h2>
                <?php foreach ($news as $article) :
                    $news_article_body = html_entity_decode($article['news_articles_body']);
                    $news_articles_date = strtotime($article['news_articles_date']);

                    if ($article['news_articles_status'] == "Published") {
                        $news_articles_status = "<p class='news-item-status published'>Published <i class='fa-solid fa-check'></i></p>";
                    }
                    if ($article['news_articles_status'] == "Draft") {
                        $news_articles_status = "<p class='news-item-status draft'>Draft <i class='fa-solid fa-flag'></i></p>";
                    }
                ?>
                    <div class="news-card news-card-dashboard">
                        <?php if ($article['news_articles_img'] == null) : ?>
                            <a href="news_article.php?action=view&news_articles_id=<?= $article['news_articles_id']; ?>"><img src="./assets/img/news/news-item.webp" alt=""></a>
                        <?php else : ?>
                            <a href="news_article.php?action=view&news_articles_id=<?= $article['news_articles_id']; ?>"><img src="./assets/img/news/<?= $article['news_articles_img']; ?>" alt=""></a>
                        <?php endif; ?>
                        <p class="news-create-date my-2"><?= date('d-M-y', $news_articles_date); ?></p>
                        <h3><a href="news_article.php?action=view&news_articles_id=<?= $article['news_articles_id']; ?>"><?= $article['news_articles_title']; ?></a></h3>
                        <div class="news-card-body">
                            <p><?= $news_article_body; ?></p>
                        </div>
                        <div class="card-actions"><a href="news_article.php?action=view&news_articles_id=<?= $article['news_articles_id']; ?>"><i class="fa-solid fa-eye"></i> View Article</a></div>
                    </div>

                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </main>
    <!-- Footer -->
    <?php include("./inc/footer.inc.php"); ?>
    <!-- /Footer -->
</body>

</html>