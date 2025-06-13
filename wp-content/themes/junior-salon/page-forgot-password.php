<?php
/**
 * Template Name: WooCommerce Forgot Password
 */

defined('ABSPATH') || exit;

// Redirect logged-in users to My Account page
if (is_user_logged_in()) {
   wp_redirect(home_url('/my-profile/?section=dashboard'));
    exit;
}
include get_template_directory() . '/layouts/header.php';
?>

<div class="min-h-screen bg-gray-100 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 bg-white p-8 rounded-xl shadow-lg">
        <div>
            <h2 class="text-center text-3xl font-bold text-gray-900">
                Reset Your Password
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Enter your email address and we'll send you a link to reset your password
            </p>
        </div>

        <?php wc_print_notices(); ?>
<form class="mt-8 space-y-6" method="post" action="<?php echo admin_url('admin-ajax.php'); ?>" id="ajax-reset-form">
    <div class="rounded-md shadow-sm -space-y-px">
                <div>
                    <label for="user_login" class="sr-only">Email address</label>
                    <input 
                        id="user_login" 
                        name="user_login" 
                        type="email" 
                        autocomplete="email" 
                        required 
                        class="appearance-none rounded-md relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm" 
                        placeholder="Email address"
                    >
                </div>
            </div>

            <div>
                <button 
                    type="submit" 
                    name="wc_reset_password" 
                    value="Reset password"
                    class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                    Send Reset Link
                </button>
            </div>

            <?php wp_nonce_field('lost_password', 'woocommerce-lost-password-nonce'); ?>
            <input type="hidden" name="reset_password" value="true">
        </form>

        <div class="text-center">
            <a href="<?php echo esc_url(wc_get_page_permalink('myaccount')); ?>" 
               class="font-medium text-indigo-600 hover:text-indigo-500">
                Back to Login
            </a>
        </div>
    </div>
</div>
<script>
jQuery(document).ready(function($) {
    $('#ajax-reset-form').on('submit', function(e) {
        e.preventDefault();
        var $form = $(this);
        $('.woocommerce-message, .woocommerce-error').remove();

        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: $form.serialize() + '&action=custom_lost_password',
            success: function(response) {
                if (response.success) {
                    $form.before('<div class="woocommerce-message">' + response.data + '</div>');
                } else {
                    $form.before('<div class="woocommerce-error">' + response.data + '</div>');
                }
            },
            error: function() {
                $form.before('<div class="woocommerce-error">An error occurred. Please try again.</div>');
            }
        });
    });
});

    </script>
<?php
include get_template_directory() . '/layouts/footer.php';
?>