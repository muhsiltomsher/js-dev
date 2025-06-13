<?php /**
 * Template Name: Custom login  Page
 */
include get_template_directory() . '/layouts/header.php';   // Redirect logged-in users to the cart page (or change to another page as needed)
if (is_user_logged_in()) {
    wp_redirect(wc_get_cart_url()); // Redirect to the cart page
    exit;
}   else { ?>


<div class="min-h-screen flex items-center justify-center bg-white px-4 py-12">
  <div class="grid grid-cols-1 lg:grid-cols-2 max-w-6xl w-full shadow-md border border-gray-100">
    
    <!-- Login Form -->
    <div class="p-10 bg-white">
      <h2 class="text-2xl font-semibold mb-6">LOGIN TO JUNIORSALON</h2>

      <?php wc_print_notices(); ?>

      <form class="woocommerce-form woocommerce-form-login login space-y-6" method="post">

        <?php do_action('woocommerce_login_form_start'); ?>

        <div>
          <label for="username" class="block text-sm font-medium">Email address<span class="text-red-500">*</span></label>
          <input type="text" name="username" id="username" autocomplete="username"
            value="<?php echo (!empty($_POST['username'])) ? esc_attr(wp_unslash($_POST['username'])) : ''; ?>"
            class="w-full mt-1 px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-black text-sm"
            required>
        </div>

        <div>
          <label for="password" class="block text-sm font-medium">Password<span class="text-red-500">*</span></label>
          <input type="password" name="password" id="password" autocomplete="current-password"
            class="w-full mt-1 px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-black text-sm"
            required>
        </div>

        <div class="text-sm text-red-500">
          <?php if (isset($_GET['login']) && $_GET['login'] == 'failed') : ?>
            Incorrect email or password
          <?php endif; ?>
        </div>

        <div class="flex justify-between items-center">
          <a class="text-xs text-gray-500 hover:underline" href="<?php echo esc_url(wp_lostpassword_url()); ?>">Forgotten Password?</a>
        </div>

        <div>
          <?php wp_nonce_field('woocommerce-login', 'woocommerce-login-nonce'); ?>
          <button type="submit" name="login" value="Log in"
            class="w-full bg-black text-white text-sm uppercase py-2 hover:bg-gray-800 transition">Sign in</button>
        </div>

        <?php do_action('woocommerce_login_form_end'); ?>

      </form>

      <div class="mt-6 text-sm">
        <p class="text-gray-500">Don’t have an account?</p>
       
        <a href="<?php echo esc_url(home_url('/register/')); ?>" class="inline-block mt-2 w-full border border-black text-center py-2 text-sm uppercase hover:bg-black hover:text-white transition">Create account</a>


      </div>
    </div>

    <!-- Right Side Image -->
    <div class="hidden lg:block relative bg-gray-100">
    <?php
$login_banner = get_field('loginbanner'); // Make sure this is on the page where the template is applied
if ($login_banner):
?>
  <img src="<?php echo esc_url($login_banner['url']); ?>" alt="<?php echo esc_attr($login_banner['alt'] ?? 'Login Image'); ?>"
  class="object-cover w-full h-auto aspect-[1124/699]"
  loading="lazy">
<?php endif; ?>

    </div>
  </div>
</div>




<?php  } include get_template_directory() . '/layouts/footer.php'; ?>