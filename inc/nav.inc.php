<nav class="nav-bar" data-state="closed">
    <div class="nav-links-header">
        <button class="btn-close" aria-label="Close Menu" id="nav-close">
            <svg class="icon feather-icon">
                <use xlink:href="./assets/img/icons/feather.svg#x"></use>
            </svg>
        </button>
    </div>
    <div class="nav-container">
        <ul class="nav-links">
            <li class="nav-link <?php if(str_contains($_SERVER['REQUEST_URI'], "index")){echo"link-active";}?>"><a  href="/wedmin"><svg class="icon"><use xlink:href="assets/img/icons/solid.svg#house"></use></svg>Home</a></li>
            <li class="nav-link  <?php if(str_contains($_SERVER['REQUEST_URI'], "guest_list")){echo"link-active menu-active";}?>">
                <a class="nav-link_sub-menu_heading " href="guest_list?rsvp=all"><svg class="icon feather-icon"><use xlink:href="assets/img/icons/feather.svg#users"></use></svg>Guest List</a>
                <div class="nav-link_sub-menu">
                    <a class="nav-link_sub-menu_link" href="guest_list?q=groups"><svg class="icon"><use xlink:href="assets/img/icons/solid.svg#tags"></use></svg>Guest Groups</a>
                </div>
            </li>
            

        </ul>
        <div class="user">
            <div class="user__name">
                <span class="user__avatar">
                    <svg class="icon feather-icon">
                        <use xlink:href="assets/img/icons/feather.svg#user"></use>
                    </svg>
                </span>
                <span class="user__name_text"><?= $user->name();?></span>
                <button class="btn-primary btn-expand">
                    <svg class="icon feather-icon">
                        <use xlink:href="assets/img/icons/feather.svg#chevron-down"></use>
                    </svg>
                </button>
                
            </div>
            <div class="user__actions d-none my-2">
                <div class="user__actions_links">
                    <a href="profile">
                        <svg class="icon feather-icon">
                        <use xlink:href="assets/img/icons/feather.svg#user"></use>
                    </svg>
                        Edit Profile</a>
                    <a href="logout">
                        <svg class="icon feather-icon">
                        <use xlink:href="assets/img/icons/feather.svg#log-out"></use>
                    </svg>
                    Logout</a>
                </div>
            </div>
        </div>
    </div>
</nav>