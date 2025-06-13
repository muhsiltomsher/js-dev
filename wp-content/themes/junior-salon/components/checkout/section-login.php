<?php
/**
 * Login Section for Custom Checkout
 */

$current_user = wp_get_current_user();
$is_logged_in = is_user_logged_in();
$login_error = '';

if (isset($_POST['wc_login_submit'])) {
    $creds = array(
        'user_login'    => sanitize_user($_POST['username']),
        'user_password' => $_POST['password'],
        'remember'      => isset($_POST['rememberme']),
    );

    $user = wp_signon($creds, false);

    if (is_wp_error($user)) {
        $login_error = $user->get_error_message();
    } else {
        if ($is_logged_in && $user->ID !== $current_user->ID) {
            wp_logout();
        }

        wp_set_current_user($user->ID);
        wp_set_auth_cookie($user->ID);
        wp_redirect(wc_get_checkout_url());
        exit;
    }
}
?>

<div x-data="{ tab: '<?php echo ($is_logged_in || $login_error) ? 'signin' : 'guest'; ?>' }" class="bg-white border border-gray-200 p-6 rounded shadow-sm">
  <!-- Tab Headers -->
  <div class="flex mb-4 border-b border-gray-200">
    <button @click="tab = 'guest'" :class="tab === 'guest' ? 'border-blue-600 text-blue-600' : 'text-gray-600'" class="px-4 py-2 text-sm font-medium border-b-2"><?php echo apply_filters( 'wpml_translate_single_string', 'Guest Checkout', 'Checkout Texts', 'Guest Checkout' ); ?>
 </button>
    <button @click="tab = 'signin'" :class="tab === 'signin' ? 'border-blue-600 text-blue-600' : 'text-gray-600'" class="ml-4 px-4 py-2 text-sm font-medium border-b-2">
       <?php echo apply_filters( 'wpml_translate_single_string', 'Sign in', 'Auth Texts', 'Sign in' ); ?>

  
  
  </button>
  </div>

  <!-- Guest Checkout Message -->
  <div x-show="tab === 'guest'" class="text-sm text-gray-700">
    <p><?php echo apply_filters( 'wpml_translate_single_string', 'You can proceed as a guest. We\'ll only ask for necessary details on the next step.', 'Checkout Texts', 'You can proceed as a guest. We\'ll only ask for necessary details on the next step.' ); ?>
</p>
  </div>

  <!-- Sign In Section -->
  <div x-show="tab === 'signin'" class="mt-4" x-cloak>
    <div class="flex justify-between items-center mb-2">
      <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-800">  <?php echo apply_filters( 'wpml_translate_single_string', 'Sign in', 'Auth Texts', 'Sign in' ); ?>
</h2>
      <?php if ($is_logged_in): ?>
        <button onclick="document.getElementById('wc-login-box').classList.toggle('hidden')" class="text-xs underline text-gray-600 hover:text-black"><?php echo apply_filters( 'wpml_translate_single_string', 'Modify', 'Checkout Texts', 'Modify' ); ?>
</button>
      <?php endif; ?>
    </div>

    <hr class="mb-4">

    <?php if ($is_logged_in): ?>
      <p class="text-sm text-gray-400 mb-1"> <?php echo apply_filters( 'wpml_translate_single_string', 'Your Email Is', 'Checkout Texts', 'Your Email Is' ); ?>
</p>
      <p class="text-sm text-black"><?php echo esc_html($current_user->user_email); ?></p>
    <?php endif; ?>

    <div id="wc-login-box" class="<?php echo (!$is_logged_in || $login_error) ? '' : 'hidden'; ?>">
      <?php if (!empty($login_error)): ?>
        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded">
          <?php echo $login_error; ?>
        </div>
      <?php endif; ?>

      <form method="post" class="woocommerce-form woocommerce-form-login login space-y-4">
        <p class="text-sm text-gray-700">       
          
        <?php echo apply_filters( 'wpml_translate_single_string', 'Email address', 'Auth Texts', 'Email address' ); ?>
 </p>
        <input type="text" class="w-full border rounded px-3 py-2 text-sm" name="username" autocomplete="username" required>

        <p class="text-sm text-gray-700">    <?php echo apply_filters( 'wpml_translate_single_string', 'Password', 'Auth Texts', 'Password' ); ?>
</p>
        <input class="w-full border rounded px-3 py-2 text-sm" type="password" name="password" autocomplete="current-password" required>

        <div class="flex items-center justify-between mt-4">
          <label class="text-sm text-gray-600">
            <input class="mr-1" type="checkbox" name="rememberme">  <?php echo apply_filters( 'wpml_translate_single_string', 'Remember me', 'Checkout Texts', 'Remember me' ); ?>

          </label>
          <a class="text-sm text-blue-600 underline" href="<?php echo esc_url(wp_lostpassword_url()); ?>"> <?php echo apply_filters( 'wpml_translate_single_string', 'Forgot password?', 'Checkout Texts', 'Forgot password?' ); ?>
</a>
        </div>

        <input type="hidden" name="wc_login_submit" value="1" />

        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded text-sm mt-4">
          <?php echo apply_filters( 'wpml_translate_single_string', 'LOGIN TO JUNIORSALON', 'Header Texts', 'LOGIN TO JUNIORSALON' ); ?>

        </button>
      </form>
    </div>
  </div>
</div>
