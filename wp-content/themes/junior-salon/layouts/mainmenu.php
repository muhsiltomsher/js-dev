<!-- Desktop Mega Menu -->
<nav id="custom-desktop-menu" class="hidden lg:flex justify-between space-x-6">
    <?php
    wp_nav_menu(array(
        'theme_location' => 'primary',
        'container' => false,
        'menu_class' => 'flex space-x-8 text-sm font-medium',
        'fallback_cb' => false,
        'walker' => new Custom_Mega_Menu_Walker()
    ));
    ?>
</nav>
<style type="text/css">

    #mega-menu-wrap-primary #mega-menu-primary > li.mega-menu-megamenu > ul.mega-sub-menu .mega-description-group .mega-menu-description {
        display:none!mportant;
    }
/* Mega Menu Dropdown Visibility */
#custom-desktop-menu .group:hover > ul {
    display: grid !important;
}

/* Optional: Hide submenu by default */
#custom-desktop-menu ul ul {
    display: none;
}

/* Optional: Smooth transition */
#custom-desktop-menu ul ul {
    transition: all 0.3s ease-in-out;
}

/* Optional: Prevent submenu layout shift */
#custom-desktop-menu ul ul {
    position: absolute;
    top: 100%;
    left: 0;
    z-index: 9999;
}
</style>
