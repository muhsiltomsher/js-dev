<?php

/**
 * Handles plugin header
 * 
 * @package SCFW_Size_Chart_For_Woocommerce
 * @since   1.0.0
 */
// If this file is called directly, abort.
if ( !defined( 'WPINC' ) ) {
    exit;
}
global $scfw_fs;
$version_label = '';
$plugin_slug = '';
$version_label = __( 'Free', 'size-chart-for-woocommerce' );
$plugin_slug = 'basic_size_chart';
$plugin_name = __( 'Product Size Charts', 'size-chart-for-woocommerce' );
$plugin_version = 'v' . SCFW_PLUGIN_VERSION;
$current_page = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
$get_current_screen = get_current_screen();
$scfw_free_dashboard = ( isset( $current_page ) && 'scfw-upgrade-dashboard' === $current_page ? 'active' : '' );
$scfw_rules_list = ( (isset( $get_current_screen ) || isset( $current_page )) && ('size-chart' === $get_current_screen->post_type && 'size-chart-setting-page' !== $current_page) ? 'active' : '' );
$scfw_settings_menu = ( isset( $current_page ) && ('size-chart-import-export' === $current_page || 'size-chart-setting-page' === $current_page) || !(scfw_fs()->is__premium_only() && scfw_fs()->can_use_premium_code()) && 'size-chart-get-started-account' === $current_page ? 'active' : '' );
$scfw_get_started = ( isset( $current_page ) && 'size-chart-get-started' === $current_page ? 'active' : '' );
$scfw_import_export = ( isset( $current_page ) && 'size-chart-import-export' === $current_page ? 'active' : '' );
$scfw_global_settings = ( isset( $current_page ) && 'size-chart-setting-page' === $current_page ? 'active' : '' );
$scfw_account_page = ( isset( $current_page ) && 'size-chart-get-started-account' === $current_page ? 'active' : '' );
$scfw_free_dashboard = ( isset( $current_page ) && 'scfw-upgrade-dashboard' === $current_page ? 'active' : '' );
$scfw_display_submenu = ( !empty( $scfw_settings_menu ) && 'active' === $scfw_settings_menu ? 'display:inline-block' : 'display:none' );
$scfw_admin_object = new SCFW_Size_Chart_For_Woocommerce_Admin('', '', '');
?>
<div id="dotsstoremain">
    <div class="all-pad">
        <?php 
$scfw_admin_object->scfw_get_promotional_bar( $plugin_slug );
?>
        <hr class="wp-header-end" />
        <div class="dotstore_plugin_page_loader"></div>
        <header class="dots-header">
            <div class="dots-plugin-details">
                <div class="dots-header-left">
                    <div class="dots-logo-main">
                        <img src="<?php 
echo esc_url( plugin_dir_url( dirname( __FILE__, 2 ) ) . 'images/thedotstore-images/product-size-chart-logo-128x128.png' );
?>" alt="<?php 
esc_attr_e( SCFW_PLUGIN_NAME, 'size-chart-for-woocommerce' );
?>">
                    </div>
                    <div class="plugin-name">
                        <div class="title"><?php 
esc_html_e( $plugin_name, 'size-chart-for-woocommerce' );
?></div>
                    </div>
                    <span class="version-label <?php 
echo esc_attr( $plugin_slug );
?>"><?php 
esc_html_e( $version_label, 'size-chart-for-woocommerce' );
?></span>
                    <span class="version-number"><?php 
echo esc_html_e( $plugin_version, 'size-chart-for-woocommerce' );
?></span>
                </div>
                <div class="dots-header-right">
                    <div class="button-dots">
                        <a target="_blank" href="<?php 
echo esc_url( 'http://www.thedotstore.com/support/' );
?>"><?php 
esc_html_e( 'Support', 'size-chart-for-woocommerce' );
?></a>
                    </div>
                    <div class="button-dots">
                        <a target="_blank" href="<?php 
echo esc_url( 'https://www.thedotstore.com/feature-requests/' );
?>"><?php 
esc_html_e( 'Suggest', 'size-chart-for-woocommerce' );
?></a>
                    </div>
                    <?php 
$plugin_help_url = 'https://docs.thedotstore.com/category/239-premium-plugin-settings';
?>
                	<div class="button-dots <?php 
echo ( scfw_fs()->is__premium_only() && scfw_fs()->can_use_premium_code() ? '' : 'last-link-button' );
?>">
                        <a target="_blank" href="<?php 
echo esc_url( $plugin_help_url );
?>"><?php 
esc_html_e( 'Help', 'size-chart-for-woocommerce' );
?></a>
                    </div>
                    <div class="button-dots">
                        <?php 
?>
                            <a class="dots-upgrade-btn" target="_blank" href="<?php 
echo esc_url( $scfw_fs->get_upgrade_url() );
?>"><?php 
esc_html_e( 'Upgrade Now', 'size-chart-for-woocommerce' );
?></a>
                            <?php 
?>
                    </div>
                </div>
            </div>
            <div class="dots-bottom-menu-main">
                <div class="dots-menu-main">
                    <nav>
                        <ul>
                            <li>
                                <a class="dotstore_plugin <?php 
echo esc_attr( $scfw_rules_list );
?>" href="<?php 
echo esc_url( add_query_arg( array(
    'post_type' => 'size-chart',
), admin_url( 'edit.php' ) ) );
?>"><?php 
esc_html_e( 'Size Charts', 'size-chart-for-woocommerce' );
?></a>
                            </li>
                            <li>
                                <a class="dotstore_plugin <?php 
echo esc_attr( $scfw_settings_menu );
?>" href="<?php 
echo esc_url( add_query_arg( array(
    'page' => 'size-chart-setting-page',
), admin_url( 'edit.php?post_type=size-chart' ) ) );
?>"><?php 
esc_html_e( 'Settings', 'size-chart-for-woocommerce' );
?></a>
                            </li>
                            <?php 
if ( scfw_fs()->is__premium_only() && scfw_fs()->can_use_premium_code() ) {
    ?>
                                <li>
                                    <a class="dotstore_plugin <?php 
    echo esc_attr( $scfw_account_page );
    ?>" href="<?php 
    echo esc_url( $scfw_fs->get_account_url() );
    ?>"><?php 
    esc_html_e( 'License', 'size-chart-for-woocommerce' );
    ?></a>
                                </li>
                                <?php 
} else {
    ?>
                                <li>
                                    <a class="dotstore_plugin dots_get_premium <?php 
    echo esc_attr( $scfw_free_dashboard );
    ?>" href="<?php 
    echo esc_url( add_query_arg( array(
        'page' => 'scfw-upgrade-dashboard',
    ), admin_url( 'admin.php' ) ) );
    ?>"><?php 
    esc_html_e( 'Get Premium', 'size-chart-for-woocommerce' );
    ?></a>
                                </li>
                                <?php 
}
?>
                        </ul>
                    </nav>
                </div>
                <div class="dots-getting-started">
                    <a href="<?php 
echo esc_url( add_query_arg( array(
    'page' => 'size-chart-get-started',
), admin_url( 'admin.php' ) ) );
?>" class="<?php 
echo esc_attr( $scfw_get_started );
?>"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false"><path d="M12 4.75a7.25 7.25 0 100 14.5 7.25 7.25 0 000-14.5zM3.25 12a8.75 8.75 0 1117.5 0 8.75 8.75 0 01-17.5 0zM12 8.75a1.5 1.5 0 01.167 2.99c-.465.052-.917.44-.917 1.01V14h1.5v-.845A3 3 0 109 10.25h1.5a1.5 1.5 0 011.5-1.5zM11.25 15v1.5h1.5V15h-1.5z" fill="#a0a0a0"></path></svg></a>
                </div>
            </div>
        </header>
        <!-- Upgrade to pro popup -->
        <?php 
if ( !(scfw_fs()->is__premium_only() && scfw_fs()->can_use_premium_code()) ) {
    require_once SCFW_PLUGIN_DIR_PATH . 'admin/partials/dots-upgrade-popup.php';
}
?>
        <div class="dots-settings-inner-main">
            <div class="dots-settings-left-side">
                <div class="dotstore-submenu-items" style="<?php 
echo esc_attr( $scfw_display_submenu );
?>">
                    <ul>
                        <li><a class="<?php 
echo esc_attr( $scfw_global_settings );
?>" href="<?php 
echo esc_url( add_query_arg( array(
    'page' => 'size-chart-setting-page',
), admin_url( 'edit.php?post_type=size-chart' ) ) );
?>"><?php 
esc_html_e( 'Global Settings', 'size-chart-for-woocommerce' );
?></a></li>
                        <li><a class="<?php 
echo esc_attr( $scfw_import_export );
?>" href="<?php 
echo esc_url( add_query_arg( array(
    'page' => 'size-chart-import-export',
), admin_url( 'admin.php' ) ) );
?>"><?php 
esc_html_e( 'Import / Export', 'size-chart-for-woocommerce' );
?></a></li>
                    </ul>
                </div>
                