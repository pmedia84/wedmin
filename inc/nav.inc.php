<nav class="nav-bar">
    <div class="nav-container">
        <div class="close">
            <button class="nav-btn-close" id="nav-btn-close"><svg class="icon"><use xlink:href="assets/img/icons/solid.svg#xmark"></use></svg></button>
        </div>
        <ul class="nav-links">
            <?php if ($cms->type() == "Business") : ?>
                <li><a class="nav-link <?php if(str_contains($_SERVER['REQUEST_URI'], "index")){echo"link-active";}?>" href="index.php">Home <svg class="icon"><use xlink:href="assets/img/icons/solid.svg#house"></use></svg></a></li>
                <?php if ($price_list->status()=="On") : ?>
                    <li><a class="nav-link <?php if(str_contains($_SERVER['REQUEST_URI'], "price_list")){echo"link-active";}?>" href="price_list.php">Price List <svg class="icon"><use xlink:href="assets/img/icons/solid.svg#tags"></use></svg></a></li>
                <?php endif; ?>
                <?php if ($image_gallery->status() == "On") : ?>
                    <li><a class="nav-link <?php if(str_contains($_SERVER['REQUEST_URI'], "gallery")){echo"link-active";}?>" href="gallery.php">Image Gallery <svg class="icon"><use xlink:href="assets/img/icons/solid.svg#image"></use></svg></a></li>
                <?php endif; ?>
                <?php if ($news_m->status() == "On") : ?>
                    <li><a class="nav-link <?php if(str_contains($_SERVER['REQUEST_URI'], "news")){echo"link-active";}?>" href="news.php">News <svg class="icon"><use xlink:href="assets/img/icons/solid.svg#newspaper"></use></svg></a></li>
                <?php endif;?>
                <?php if ($forms->status() == "On") : ?>
                    <li><a class="nav-link <?php if(str_contains($_SERVER['REQUEST_URI'], "forms") || str_contains($_SERVER['REQUEST_URI'], "form")){echo"link-active";}?>" href="forms">Forms <svg class="icon"><use xlink:href="assets/img/icons/solid.svg#clipboard-user"></use></svg></a></li>
                <?php endif;?>

                <?php if ($user->user_type() == "Admin" || $user->user_type() == "Developer") : ?>
                    <li><a class="nav-link <?php if(str_contains($_SERVER['REQUEST_URI'], "settings")){echo"link-active";}?>" href="settings.php">Settings <svg class="icon"><use xlink:href="assets/img/icons/solid.svg#gear"></use></svg></a></li>
                <?php endif; ?>
                <?php if ($user->user_type() == "Admin" || $user->user_type() == "Developer") : ?>
                    <li><a class="nav-link <?php if(str_contains($_SERVER['REQUEST_URI'], "reviews")){echo"link-active";}?>" href="reviews.php">Reviews <svg class="icon"><use xlink:href="assets/img/icons/solid.svg#comment-dots"></use></svg> </a></li>
                <?php endif; ?>
            <?php endif; ?>
            <?php if ($cms->type() == "Wedding") : ?>
                <li><a class="nav-link" href="index.php">Home <svg class="icon"><use xlink:href="assets/img/icons/solid.svg#house"></use></svg></a></li>
                <?php if ($guest_list_m->status()=="On") : ?>
                    <li><a class="nav-link <?php if(str_contains($_SERVER['REQUEST_URI'], "guest")){echo"link-active";}?>" href="guest_list">Guest List <svg class="icon"><use xlink:href="assets/img/icons/solid.svg#people-group"></use></svg></a></li>
                <?php endif; ?>
                <?php if ($user->user_type() == "Admin" || $user->user_type() == "Developer") : ?>
                    <li><a class="nav-link <?php if(str_contains($_SERVER['REQUEST_URI'], "event")){echo"link-active";}?>" href="events">Events <svg class="icon"><use xlink:href="assets/img/icons/solid.svg#calendar-day"></use></svg></a></li>
                <?php endif; ?>
                <?php if ($menu_builder->status()=="On") : ?>
                    <li><a class="nav-link <?php if(str_contains($_SERVER['REQUEST_URI'], "menu")){echo"link-active";}?>" href="menu">Menu Builder <svg class="icon"><use xlink:href="assets/img/icons/solid.svg#bowl-food"></use></svg></a></li>
                <?php endif; ?>
                <?php if ($meal_choices_m->status()=="On") : ?>
                    <li><a class="nav-link <?php if(str_contains($_SERVER['REQUEST_URI'], "meal_choices")){echo"link-active";}?>" href="meal_choices">Guest Meal Choices <svg class="icon"><use xlink:href="assets/img/icons/solid.svg#utensils"></use></svg></a></li>
                <?php endif; ?>
                <li><a class="nav-link <?php if(str_contains($_SERVER['REQUEST_URI'], "our_story")){echo"link-active";}?>" href="our_story">Our Story <svg class="icon"><use xlink:href="assets/img/icons/regular.svg#heart"></use></svg></a></li>
                <?php if ($invite_manager->status()=="On") : ?>
                    <li><a class="nav-link <?php if(str_contains($_SERVER['REQUEST_URI'], "invit")){echo"link-active";}?>" href="invitations">Invitations <svg class="icon"><use xlink:href="assets/img/icons/solid.svg#champagne-glasses"></use></svg></a></li>
                <?php endif; ?>
                <?php if ($guest_messaging->status()=="On") : ?>
                    <li><a class="nav-link" href="messaging">Guest Messages <svg class="icon"><use xlink:href="assets/img/icons/solid.svg#message"></use></svg></a></li>
                <?php endif; ?>
                <?php if ($gift_list_m->status()=="On") : ?>
                    <li><a class="nav-link <?php if(str_contains($_SERVER['REQUEST_URI'], "gift")){echo"link-active";}?>" href="gift_list">Gift List <svg class="icon"><use xlink:href="assets/img/icons/solid.svg#gifts"></use></svg></a></li>
                <?php endif; ?>
                <?php if ($image_gallery->status()=="On") : ?>
                    <li><a class="nav-link <?php if(str_contains($_SERVER['REQUEST_URI'], "gallery") || str_contains($_SERVER['REQUEST_URI'], "img")){echo"link-active";}?>" href="gallery">Photo Gallery <svg class="icon"><use xlink:href="assets/img/icons/solid.svg#images"></use></svg></a></li>
                <?php endif; ?>
                <?php if ($news_m->status()=="On") : ?>
                    <li><a class="nav-link <?php if(str_contains($_SERVER['REQUEST_URI'], "news")){echo"link-active";}?>" href="news">News <svg class="icon"><use xlink:href="assets/img/icons/solid.svg#newspaper"></use></svg></a></li>
                <?php endif; ?>
                <?php if ($user->user_type() == "Admin" || $user->user_type() == "Developer") : ?>
                    <li><a class="nav-link <?php if(str_contains($_SERVER['REQUEST_URI'], "wedding")){echo"link-active";}?>" href="wedding_settings">Website Settings <svg class="icon"><use xlink:href="assets/img/icons/solid.svg#laptop"></use></svg></a></li>
                <?php endif; ?>
                
            <?php endif; ?>
            <?php if ($user->user_type() == "Developer") : ?>
                <li><a class="nav-link <?php if(str_contains($_SERVER['REQUEST_URI'], "cms")){echo"link-active";}?>" href="cms_settings">CMS Settings <svg class="icon"><use xlink:href="assets/img/icons/solid.svg#gear"></use></svg></a></li>
            <?php endif; ?>
            <li><a class="nav-link" href="logout">Logout <svg class="icon"><use xlink:href="assets/img/icons/solid.svg#right-from-bracket"></use></svg></a></li>
        </ul>
    </div>
</nav>