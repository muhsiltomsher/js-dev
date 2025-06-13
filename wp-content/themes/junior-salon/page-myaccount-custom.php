<?php
/* Template Name: Custom My Account Page */
defined('ABSPATH') || exit;

if (!is_user_logged_in()) {
    wp_redirect(wc_get_page_permalink('myaccount'));
    exit;
}
include get_template_directory() . '/layouts/header.php';
?>

<div class="max-w-7xl mx-auto px-4 py-10">
    <div class="flex flex-col md:flex-row gap-6">
        <!-- Sidebar -->
        <aside class="w-full md:w-1/4 bg-white border p-4 rounded-lg shadow">
            <ul class="space-y-4 text-sm font-medium">
                <li><a href="?section=dashboard" class="flex items-center gap-2 text-black hover:text-blue-600">
                    <i class="fas fa-user-circle"></i> <?php echo apply_filters( 'wpml_translate_single_string', 'My Account', 'Header Texts', 'My Account' ); ?>
 </a></li>
                <li><a href="<?php echo home_url('/orders'); ?>" class="flex items-center gap-2 hover:text-blue-600">
                    <i class="fas fa-box"></i> <?php echo apply_filters( 'wpml_translate_single_string', 'My Orders', 'Header Texts', 'My Orders' ); ?>
 </a></li>
                    <li><a href="?section=profile" class="flex items-center gap-2 hover:text-blue-600">
    <i class="fas fa-id-badge"></i> <?php echo apply_filters( 'wpml_translate_single_string', 'My Profile', 'Header Texts', 'My Profile' ); ?>
 </a></li>
     
                <li><a href="?section=address" class="flex items-center gap-2 hover:text-blue-600">
                    <i class="fas fa-map-marker-alt"></i> <?php echo apply_filters( 'wpml_translate_single_string', 'My Address Book', 'Header Texts', 'My Address Book' ); ?>
  </a></li>
                <li><a href="?section=bank-details" class="flex items-center gap-2 hover:text-blue-600">
                    <i class="fas fa-university"></i>  <?php echo apply_filters( 'wpml_translate_single_string', 'Bank Details', 'Header Texts', 'Bank Details' ); ?>
</a></li>
                <li><a href="?section=change-password" class="flex items-center gap-2 hover:text-blue-600">
                    <i class="fas fa-lock"></i> <?php echo apply_filters( 'wpml_translate_single_string', 'Change Password', 'Header Texts', 'Change Password' ); ?>
 </a></li>
                <li><a href="<?php echo esc_url(wc_logout_url()); ?>" class="flex items-center gap-2 text-red-500">
                    <i class="fas fa-sign-out-alt"></i> <?php echo apply_filters( 'wpml_translate_single_string', 'Logout', 'Header Texts', 'Logout' ); ?>
</a></li>
            </ul>
        </aside>

        <!-- Section content -->
        <div class="w-full md:w-3/4 bg-white p-6 rounded-lg shadow" id="account-section">
            <?php
            $section = $_GET['section'] ?? 'dashboard';
$allowed = ['dashboard', 'orders', 'address', 'bank-details', 'change-password', 'profile'];
            if (in_array($section, $allowed)) {
                include locate_template("woocommerce/myaccount-custom/{$section}.php");
            } else {
                echo '<p class="text-red-500">Invalid section</p>';
            }
            ?>
        </div>
    </div>
</div>
<?php include get_template_directory() . '/layouts/footer.php'; ?>
