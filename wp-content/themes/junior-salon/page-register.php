<?php
/* Template Name: Custom Register Page */

defined('ABSPATH') || exit;
include get_template_directory() . '/layouts/header.php'; 
?>
<div class="min-h-screen flex items-center justify-center bg-white py-12">
  <div class="w-full max-w-xl px-6">
    <h2 class="text-xl font-semibold text-gray-800 mb-6 border-b pb-2">CREATE YOUR ACCOUNT</h2>

    <?php
    if (!is_user_logged_in()) {
      wc_print_notices(); // Native WooCommerce error/success messages
    ?>
      <form method="post" class="space-y-4 woocommerce-form woocommerce-form-register register" <?php do_action('woocommerce_register_form_tag'); ?>>

        <?php do_action('woocommerce_register_form_start'); ?>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label for="reg_first_name" class="text-sm font-medium text-gray-700 mb-1 block">First name</label>
            <input type="text" name="first_name" id="reg_first_name" class="w-full border px-3 py-2 rounded text-sm" value="<?php echo esc_attr($_POST['first_name'] ?? ''); ?>" required />
          </div>
          <div>
            <label for="reg_last_name" class="text-sm font-medium text-gray-700 mb-1 block">Last name</label>
            <input type="text" name="last_name" id="reg_last_name" class="w-full border px-3 py-2 rounded text-sm" value="<?php echo esc_attr($_POST['last_name'] ?? ''); ?>" required />
          </div>
        </div>

        <div>
          <label for="reg_email" class="text-sm font-medium text-gray-700 mb-1 block">Email address</label>
          <input type="email" name="email" id="reg_email" class="w-full border px-3 py-2 rounded text-sm" value="<?php echo esc_attr($_POST['email'] ?? ''); ?>" required />
        </div>

        <div>
          <label for="reg_phone" class="text-sm font-medium text-gray-700 mb-1 block">Phone number</label>
          <input type="text" name="billing_phone" id="reg_phone" class="w-full border px-3 py-2 rounded text-sm" value="<?php echo esc_attr($_POST['billing_phone'] ?? ''); ?>" />
        </div>

        <div>
          <label for="reg_password" class="text-sm font-medium text-gray-700 mb-1 block">Password</label>
          <input type="password" name="password" id="reg_password" class="w-full border px-3 py-2 rounded text-sm" required />
        </div>

        <div>
          <label for="reg_password2" class="text-sm font-medium text-gray-700 mb-1 block">Confirm password</label>
          <input type="password" name="password2" id="reg_password2" class="w-full border px-3 py-2 rounded text-sm" required />
        </div>

        <?php do_action('woocommerce_register_form'); ?>

        <button type="submit" class="w-full bg-black text-white text-sm py-2 rounded hover:bg-gray-800 transition" name="register">
          Create account
        </button>

        <?php wp_nonce_field('woocommerce-register', 'woocommerce-register-nonce'); ?>

        <p class="text-xs text-gray-600 text-center mt-2">
          By creating an account, you’re accepting our 
          <a href="/terms" class="underline">Terms & Conditions</a> and 
          <a href="/privacy" class="underline">Privacy Policy</a>.
        </p>

        <div class="text-center mt-4">
          <p class="text-sm">Already have an account?</p>
         
          <a href="<?php echo esc_url(wc_get_page_permalink('myaccount')); ?>" class="mt-2 inline-block border px-4 py-1.5 rounded text-sm">Sign in</a>

        
        </div>

      </form>

    <?php } else { ?>
      <p class="text-center text-sm">You're already logged in. <a class="underline" href="<?php echo esc_url(wc_get_page_permalink('myaccount')); ?>">Go to My Account</a></p>
    <?php } ?>
  </div>
</div>

<?php include get_template_directory() . '/layouts/footer.php'; ?>
