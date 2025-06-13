<?php
/**
 * Template Name: Custom Checkout Page
 */
include get_template_directory() . '/layouts/header.php'; ?>



<?php
$order_id = absint( get_query_var( 'order-received' ) );
$order_key = isset( $_GET['key'] ) ? wc_clean( wp_unslash( $_GET['key'] ) ) : '';

if ( $order_id && $order_key ) {
    // This is the thank you page endpoint
    wc_get_template( 'checkout/thankyou.php', array( 'order' => wc_get_order( $order_id ) ) );
    return; // Stop further rendering of the checkout form
}
?>

  <h1 class="page-title"><?php the_title(); ?></h1>



<?php
$current_user = wp_get_current_user();
$is_logged_in = is_user_logged_in();
$login_error = '';

// Handle login if submitted
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
        // Only logout current user AFTER verifying new user is valid
        if ($is_logged_in && $user->ID !== $current_user->ID) {
            wp_logout(); // log out old user only if new login is different
        }

        wp_set_current_user($user->ID);
        wp_set_auth_cookie($user->ID);
        wp_redirect(wc_get_checkout_url());
        exit;
    }
}
?>

<div class="bg-white border border-gray-200 p-6 max-w-xl mx-auto rounded shadow-sm">
  <div class="flex justify-between items-center mb-2">
    <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-800">Sign In</h2>
    <?php if ($is_logged_in): ?>
      <button onclick="document.getElementById('wc-login-box').classList.toggle('hidden')" class="text-xs underline text-gray-600 hover:text-black">Modify</button>
    <?php endif; ?>
  </div>

  <hr class="mb-4">

  <?php if ($is_logged_in): ?>
    <p class="text-sm text-gray-400 mb-1">Your Email Is</p>
    <p class="text-sm text-black"><?php echo esc_html($current_user->user_email); ?></p>
  <?php endif; ?>

  <!-- Show login box if not logged in or error occurred -->
  <div id="wc-login-box" class="<?php echo (!$is_logged_in || $login_error) ? '' : 'hidden'; ?> mt-6 border-t pt-4">
    <?php if (!empty($login_error)): ?>
      <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded">
        <?php echo $login_error; ?>
      </div>
    <?php endif; ?>

    <form method="post" class="woocommerce-form woocommerce-form-login login space-y-4">
      <p class="text-sm text-gray-700">Email address</p>
      <input type="text" class="w-full border rounded px-3 py-2 text-sm" name="username" autocomplete="username" required>

      <p class="text-sm text-gray-700">Password</p>
      <input class="w-full border rounded px-3 py-2 text-sm" type="password" name="password" autocomplete="current-password" required>

      <div class="flex items-center justify-between mt-4">
        <label class="text-sm text-gray-600">
          <input class="mr-1" type="checkbox" name="rememberme"> Remember me
        </label>
        <a class="text-sm text-blue-600 underline" href="<?php echo esc_url(wp_lostpassword_url()); ?>">Forgot password?</a>
      </div>

      <input type="hidden" name="wc_login_submit" value="1" />

      <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded text-sm mt-4">
        Login
      </button>
    </form>
  </div>
</div>


<?php
$current_user = wp_get_current_user();
$user_id = $current_user->ID;
$is_logged_in = is_user_logged_in();


$address_fields = array(
  'first_name' => get_user_meta($user_id, 'shipping_first_name', true),
  'last_name'  => get_user_meta($user_id, 'shipping_last_name', true),
  'company'    => get_user_meta($user_id, 'shipping_company', true),
  'address_1'  => get_user_meta($user_id, 'shipping_address_1', true),
  'address_2'  => get_user_meta($user_id, 'shipping_address_2', true),
  'city'       => get_user_meta($user_id, 'shipping_city', true),
  'state'      => get_user_meta($user_id, 'shipping_state', true),
  'postcode'   => get_user_meta($user_id, 'shipping_postcode', true),
  'country'    => get_user_meta($user_id, 'shipping_country', true),
  'phone'      => get_user_meta($user_id, 'shipping_phone', true),
);
?>


<div class="bg-white border border-gray-200 p-6 max-w-xl mx-auto rounded shadow-sm mt-8">
  <div class="flex justify-between items-center mb-2">
    <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-800">Deliver To</h2>
    <button onclick="document.getElementById('shippingEditBox').classList.toggle('hidden')" class="text-xs underline text-gray-600 hover:text-black">Edit/Change</button>
  </div>

  <hr class="mb-4">

  <div id="shipping-display" class="text-sm text-gray-800 leading-relaxed">
    <?php if ($is_logged_in): ?>
      <?php echo esc_html(get_user_meta($user_id, 'shipping_first_name', true)) . ' ' . esc_html(get_user_meta($user_id, 'shipping_last_name', true)); ?><br>
      <?php echo esc_html(get_user_meta($user_id, 'shipping_address_1', true)); ?><br>
      <?php echo esc_html(get_user_meta($user_id, 'shipping_city', true)); ?>, <?php echo esc_html(get_user_meta($user_id, 'shipping_state', true)); ?><br>
      <?php echo esc_html(get_user_meta($user_id, 'shipping_country', true)); ?><br>
      <?php echo esc_html(get_user_meta($user_id, 'shipping_phone', true)); ?>
    <?php else: ?>
      <p class="italic text-gray-500">Not logged in.</p>
    <?php endif; ?>
  </div>

<form id="shippingEditBox" class="hidden mt-6 space-y-4" onsubmit="return saveShippingAddress(event)">
  <div id="shipping-msg" class="text-sm"></div>

  <div class="grid grid-cols-2 gap-3">
    <input name="shipping_first_name" class="border px-3 py-2 text-sm rounded w-full"
      value="<?php echo esc_attr($address_fields['first_name']); ?>" placeholder="First Name" required>
    <input name="shipping_last_name" class="border px-3 py-2 text-sm rounded w-full"
      value="<?php echo esc_attr($address_fields['last_name']); ?>" placeholder="Last Name" required>
  </div>

  <input name="shipping_address_1" class="border px-3 py-2 text-sm rounded w-full"
    value="<?php echo esc_attr($address_fields['address_1']); ?>" placeholder="Address Line 1" required>

  <input name="shipping_address_2" class="border px-3 py-2 text-sm rounded w-full"
    value="<?php echo esc_attr($address_fields['address_2']); ?>" placeholder="Address Line 2">

  <input name="shipping_city" class="border px-3 py-2 text-sm rounded w-full"
    value="<?php echo esc_attr($address_fields['city']); ?>" placeholder="City" required>

  <div class="grid grid-cols-2 gap-3">
    <input name="shipping_state" class="border px-3 py-2 text-sm rounded w-full"
      value="<?php echo esc_attr($address_fields['state']); ?>" placeholder="State">
    <input name="shipping_postcode" class="border px-3 py-2 text-sm rounded w-full"
      value="<?php echo esc_attr($address_fields['postcode']); ?>" placeholder="Postcode">
  </div>

  <input name="shipping_country" class="border px-3 py-2 text-sm rounded w-full"
    value="<?php echo esc_attr($address_fields['country']); ?>" placeholder="Country" required>

  <input name="shipping_phone" class="border px-3 py-2 text-sm rounded w-full"
    value="<?php echo esc_attr($address_fields['phone']); ?>" placeholder="Phone">

  <div class="flex justify-end gap-3 mt-4">
    <button type="submit" class="bg-blue-600 text-white text-sm px-4 py-2 rounded hover:bg-blue-700">Save</button>
    <button type="button" onclick="document.getElementById('shippingEditBox').classList.add('hidden')"
      class="text-sm px-4 py-2 border rounded hover:bg-gray-100">Cancel</button>
  </div>
</form>

</div>

<script>
function saveShippingAddress(e) {
  e.preventDefault();
  const form = document.getElementById('shippingEditBox');
  const formData = new FormData(form);
  formData.append('action', 'save_shipping_address');

  fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
    method: 'POST',
    credentials: 'same-origin',
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    const msgBox = document.getElementById('shipping-msg');
    if (data.success) {
      msgBox.className = "text-green-600";
      msgBox.innerText = "Address updated!";
      document.getElementById('shippingEditBox').classList.add('hidden');
  // ✅ Update UI dynamically (no reload)
      document.getElementById('shipping-display').innerHTML = `
        ${form.shipping_first_name.value} ${form.shipping_last_name.value}<br>
        ${form.shipping_address_1.value}<br>
        ${form.shipping_city.value}, ${form.shipping_state.value}<br>
        ${form.shipping_address_2.value}<br>
      
              ${form.shipping_country.value}<br>
        ${form.shipping_phone.value}
      `;
    } else {
      msgBox.className = "text-red-600";
      msgBox.innerText = data.message || "Failed to update.";
    }
  });
  return false;
}
</script>


<?php
$current_user = wp_get_current_user();
$user_id = $current_user->ID;
$is_logged_in = is_user_logged_in();

$billing_fields = array(
  'first_name' => get_user_meta($user_id, 'billing_first_name', true),
  'last_name'  => get_user_meta($user_id, 'billing_last_name', true),
  'company'    => get_user_meta($user_id, 'billing_company', true),
  'address_1'  => get_user_meta($user_id, 'billing_address_1', true),
  'address_2'  => get_user_meta($user_id, 'billing_address_2', true),
  'city'       => get_user_meta($user_id, 'billing_city', true),
  'state'      => get_user_meta($user_id, 'billing_state', true),
  'postcode'   => get_user_meta($user_id, 'billing_postcode', true),
  'country'    => get_user_meta($user_id, 'billing_country', true),
  'phone'      => get_user_meta($user_id, 'billing_phone', true),
);
?>

<div class="bg-white border border-gray-200 p-6 max-w-xl mx-auto rounded shadow-sm mt-8">
  <div class="flex justify-between items-center mb-2">
    <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-800">Billing Address</h2>
    <button onclick="document.getElementById('billingEditBox').classList.toggle('hidden')" class="text-xs underline text-gray-600 hover:text-black">Edit/Change</button>
  </div>

  <hr class="mb-4">

  <label class="block mb-4 text-sm">
    <input type="checkbox" id="sameAsShipping" class="mr-2"> Billing address is same as shipping address
  </label>

  <div id="billing-display" class="text-sm text-gray-800 leading-relaxed">
    <?php if ($is_logged_in): ?>
      <?php echo esc_html($billing_fields['first_name'] . ' ' . $billing_fields['last_name']); ?><br>
      <?php echo esc_html($billing_fields['address_1']); ?><br>
      <?php echo esc_html($billing_fields['city'] . ', ' . $billing_fields['state']); ?><br>
      <?php echo esc_html($billing_fields['country']); ?><br>
      <?php echo esc_html($billing_fields['phone']); ?>
    <?php else: ?>
      <p class="italic text-gray-500">Not logged in.</p>
    <?php endif; ?>
  </div>

  <form id="billingEditBox" class="hidden mt-6 space-y-4" onsubmit="return saveBillingAddress(event)">
    <div id="billing-msg" class="text-sm"></div>

    <div class="grid grid-cols-2 gap-3">
      <input name="billing_first_name" class="border px-3 py-2 text-sm rounded w-full" value="<?php echo esc_attr($billing_fields['first_name']); ?>" placeholder="First Name" required>
      <input name="billing_last_name" class="border px-3 py-2 text-sm rounded w-full" value="<?php echo esc_attr($billing_fields['last_name']); ?>" placeholder="Last Name" required>
    </div>
    <input name="billing_address_1" class="border px-3 py-2 text-sm rounded w-full" value="<?php echo esc_attr($billing_fields['address_1']); ?>" placeholder="Address Line 1" required>
    <input name="billing_address_2" class="border px-3 py-2 text-sm rounded w-full" value="<?php echo esc_attr($billing_fields['address_2']); ?>" placeholder="Address Line 2">
    <input name="billing_city" class="border px-3 py-2 text-sm rounded w-full" value="<?php echo esc_attr($billing_fields['city']); ?>" placeholder="City" required>
    <div class="grid grid-cols-2 gap-3">
      <input name="billing_state" class="border px-3 py-2 text-sm rounded w-full" value="<?php echo esc_attr($billing_fields['state']); ?>" placeholder="State">
      <input name="billing_postcode" class="border px-3 py-2 text-sm rounded w-full" value="<?php echo esc_attr($billing_fields['postcode']); ?>" placeholder="Postcode">
    </div>
    <input name="billing_country" class="border px-3 py-2 text-sm rounded w-full" value="<?php echo esc_attr($billing_fields['country']); ?>" placeholder="Country" required>
    <input name="billing_phone" class="border px-3 py-2 text-sm rounded w-full" value="<?php echo esc_attr($billing_fields['phone']); ?>" placeholder="Phone">
  <input name="billing_email" value="<?php echo esc_attr($current_user->user_email); ?>">

    <div class="flex justify-end gap-3 mt-4">
      <button type="submit" class="bg-blue-600 text-white text-sm px-4 py-2 rounded hover:bg-blue-700">Save</button>
      <button type="button" onclick="document.getElementById('billingEditBox').classList.add('hidden')" class="text-sm px-4 py-2 border rounded hover:bg-gray-100">Cancel</button>
    </div>
  </form>
</div>

<script>
function saveBillingAddress(e) {
  e.preventDefault();
  const form = document.getElementById('billingEditBox');
  const formData = new FormData(form);
  formData.append('action', 'save_billing_address');

  fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
    method: 'POST',
    credentials: 'same-origin',
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    const msgBox = document.getElementById('billing-msg');
    if (data.success) {
      msgBox.className = "text-green-600";
      msgBox.innerText = "Billing address updated!";
      document.getElementById('billingEditBox').classList.add('hidden');

      // Update UI
      document.getElementById('billing-display').innerHTML = `
        ${form.billing_first_name.value} ${form.billing_last_name.value}<br>
        ${form.billing_address_1.value}<br>        ${form.billing_address_2.value}<br>
        ${form.billing_city.value}, ${form.billing_state.value}<br>
        ${form.billing_postcode.value}<br> ${form.billing_country.value}<br> 
        ${form.billing_phone.value}
      `;
    } else {
      msgBox.className = "text-red-600";
      msgBox.innerText = data.message || "Failed to update.";
    }
  });
  return false;
}

// Copy shipping to billing if checkbox is clicked


const checkbox = document.getElementById('sameAsShipping');
checkbox?.addEventListener('change', async function () {
  const formBox = document.getElementById('billingEditBox');

  if (this.checked) {
    formBox.classList.remove('hidden'); // ✅ show the editable box

    const response = await fetch('<?php echo admin_url('admin-ajax.php'); ?>?action=get_shipping_address', { credentials: 'same-origin' });
    const data = await response.json();
    if (data.success) {
      const form = document.getElementById('billingEditBox');
      for (const key in data.data) {
        const input = form.querySelector(`[name="${key}"]`);
        if (input) input.value = data.data[key];
      }
    }
  } else {
    formBox.classList.add('hidden'); // optional: hide if unchecked
  }
});


</script>

<?php
if (!function_exists('wc')) return;

$available_gateways = WC()->payment_gateways->get_available_payment_gateways();
$chosen_gateway     = WC()->session->get('chosen_payment_method');
?>

<div class="bg-white border border-gray-200 p-6 max-w-xl mx-auto rounded shadow-sm mt-8">
  <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-800 mb-4">Payment Methods</h2>

  <form id="payment-methods" class="space-y-4">
    <?php foreach ($available_gateways as $gateway): ?>
      <label class="flex items-center border px-4 py-3 rounded cursor-pointer hover:border-blue-500 transition <?php echo $gateway->id === $chosen_gateway ? 'border-blue-600 bg-blue-50' : 'border-gray-300'; ?>">
        <input type="radio" name="payment_method" value="<?php echo esc_attr($gateway->id); ?>" class="mr-3"
               <?php checked($gateway->id, $chosen_gateway); ?>>
        <div class="flex flex-col text-sm text-gray-700">
          <span class="font-medium"><?php echo esc_html($gateway->get_title()); ?></span>
          <?php if ($gateway->get_description()): ?>
            <span class="text-xs text-gray-500 mt-1"><?php echo wp_kses_post($gateway->get_description()); ?></span>
          <?php endif; ?>
        </div>
        <?php if ($gateway->id === 'tabby'): ?>
          <img src="/wp-content/uploads/tabby-logo.svg" alt="Tabby" class="ml-auto h-6">
        <?php endif; ?>
        <?php if ($gateway->id === 'cod'): ?>
          <span class="ml-auto text-xs text-gray-400">AED 10 fee applies</span>
        <?php endif; ?>
      </label>
    <?php endforeach; ?>
  </form>
</div>
<script>
document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
  radio.addEventListener('change', function () {
    fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      credentials: 'same-origin',
      body: new URLSearchParams({
        action: 'woocommerce_update_payment_method',
        payment_method: this.value
      })
    });
  });
});
</script>

<?php
$cart = WC()->cart->get_cart();
$cart_count = WC()->cart->get_cart_contents_count();
?>

<div class="bg-white border border-gray-200 p-6 max-w-xl mx-auto rounded shadow-sm mt-8">
  <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-800 mb-4">
    Shopping Bag (<?php echo $cart_count; ?> <?php echo _n('item', 'items', $cart_count); ?>)
  </h2>

  <div class="space-y-6">
    <?php foreach ($cart as $cart_item_key => $item): 
      $product = $item['data'];
      $product_id = $item['product_id'];
      $product_name = $product->get_name();
      $product_price = wc_price($product->get_price());
      $product_permalink = $product->get_permalink();
      $product_image = wp_get_attachment_image_url($product->get_image_id(), 'thumbnail');
      $variation = wc_get_formatted_cart_item_data($item, true);
    ?>
      <div class="flex items-start gap-4 border-b pb-4">
        <img src="<?php echo esc_url($product_image); ?>" alt="<?php echo esc_attr($product_name); ?>" class="w-20 h-24 object-cover rounded" />

        <div class="flex-1">
          <p class="text-xs text-gray-500 font-semibold"><?php echo esc_html($product->get_attribute('brand') ?: $product->get_name()); ?></p>
          <p class="text-sm font-medium text-gray-800"><?php echo esc_html($product_name); ?></p>
          <?php if ($variation): ?>
            <div class="text-xs text-gray-500 mt-1"><?php echo $variation; ?></div>
          <?php endif; ?>
          <p class="text-sm font-semibold text-red-600 mt-2"><?php echo $product_price; ?></p>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>


<?php
$cart = WC()->cart;
$totals = $cart->get_totals();
$chosen_shipping = WC()->session->get('chosen_shipping_methods')[0] ?? '';
$shipping_total = $cart->get_shipping_total();
$has_shipping_fee = $shipping_total !== null && $shipping_total > 0;

?>

<div class="bg-white border border-gray-200 p-6 max-w-xl mx-auto rounded shadow-sm mt-8 space-y-6">
  <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-800">Order Summary</h2>

  <!-- Coupon Field -->
  <form class="flex gap-2" method="post" id="apply-coupon-form">
    <input type="text" name="coupon_code" placeholder="Coupon Code" required
      class="border w-full px-3 py-2 text-sm rounded" />
    <button type="submit"
      class="bg-black text-white px-5 py-2 text-sm font-medium uppercase hover:bg-gray-900">Apply</button>
  </form>

  <?php wc_print_notices(); ?>

  <hr />

  <!-- Totals -->
  <div class="text-sm text-gray-800 space-y-3">
    <div class="flex justify-between">
      <span>Subtotal</span>
<span class="total-placeholder"><?php wc_cart_totals_order_total_html(); ?></span>

    </div>

    <?php if ($cart->get_shipping_total() > 0): ?>
    <div class="flex justify-between">
      <span>Delivery</span>
      <span><?php echo wc_price($cart->get_shipping_total()); ?></span>
    </div>
    <?php endif; ?>

    <?php if (WC()->shipping->get_shipping_methods()): ?>
      <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-2 text-sm rounded mt-2">
        ✅ Your order qualifies for FREE shipping
      </div>
    <?php endif; ?>
<div id="coupon-msg" class="mb-4"></div>
    <div class="flex justify-between font-semibold text-lg pt-4 border-t mt-4">
      <span>Total</span>
     <span class="total-placeholder"><?php wc_cart_totals_order_total_html(); ?></span>

    </div>
    <p class="text-xs text-gray-500">Duties and taxes included</p>
  </div>
<div class="relative">
 <form name="checkout" method="post" class="checkout woocommerce-checkout" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data">
  <!-- Inside form.checkout before submit button -->
<div style="display: none;">
  <input type="text" name="billing_first_name">
  <input type="text" name="billing_last_name">
  <input type="text" name="billing_address_1">
  <input type="text" name="billing_address_2">
  <input type="text" name="billing_city">
  <input type="text" name="billing_state">
  <input type="text" name="billing_postcode">
  <input type="text" name="billing_country">
  <input type="text" name="billing_phone">
  <input type="email" name="billing_email">
</div>
<!-- Hidden shipping fields (required by WooCommerce) -->
<div style="display: none;">
  <input type="text" name="shipping_first_name">
  <input type="text" name="shipping_last_name">
  <input type="text" name="shipping_address_1">
  <input type="text" name="shipping_address_2">
  <input type="text" name="shipping_city">
  <input type="text" name="shipping_state">
  <input type="text" name="shipping_postcode">
  <input type="text" name="shipping_country">
  <input type="text" name="shipping_phone">
</div>

  <div class="hidden">
    <?php
    do_action( 'woocommerce_checkout_before_customer_details' );
   // do_action( 'woocommerce_checkout_billing' );
   // do_action( 'woocommerce_checkout_shipping' );
    do_action( 'woocommerce_checkout_after_customer_details' );
    do_action( 'woocommerce_checkout_before_order_review' );
    do_action( 'woocommerce_checkout_order_review' );
    do_action( 'woocommerce_checkout_after_order_review' );
    ?>
  </div>
<div id="order-loader" class="hidden absolute inset-0 bg-white/80 backdrop-blur-sm z-50 flex items-center justify-center">
  <div class="animate-spin rounded-full h-8 w-8 border-2 border-gray-800 border-t-transparent"></div>
</div>

  <!-- ✅ Your custom visible Place Order button -->
  <button type="submit"
    class="w-full bg-black text-white py-3 rounded text-sm font-semibold uppercase hover:bg-gray-900">
    Place Order
  </button>
</form>
</div>
</div>



<script>
document.getElementById('finalPlaceOrder')?.addEventListener('click', function () {

   const loader = document.getElementById('order-loader');
  if (loader) loader.classList.remove('hidden');

  // Disable form elements
  this.querySelectorAll('input, button, select, textarea').forEach(el => el.disabled = true);

  document.querySelector('form.checkout')?.requestSubmit(); // Modern & safe
});
</script>
<script>
document.getElementById('apply-coupon-form')?.addEventListener('submit', function (e) {
  e.preventDefault();

  const code = this.coupon_code.value.trim();
  if (!code) return;

  fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: new URLSearchParams({
      action: 'apply_coupon',
      coupon: code
    }),
    credentials: 'same-origin'
  })
  .then(res => res.json())
  .then(data => {
    const msg = document.getElementById('coupon-msg');
    if (data.success) {
      msg.innerHTML = '<div class="text-green-600 text-sm">Coupon applied!</div>';
      document.querySelector('.subtotal-placeholder').innerHTML = data.subtotal;
      document.querySelector('.total-placeholder').innerHTML = data.total;
    } else {
      msg.innerHTML = '<div class="text-red-600 text-sm">' + (data.data.message || 'Invalid coupon') + '</div>';
    }
  });
});
</script>

<script>
document.querySelector('form.checkout').addEventListener('submit', function () {
  const shippingForm = document.getElementById('shippingEditBox');
  if (shippingForm) {
    const shippingFields = [
      'shipping_first_name',
      'shipping_last_name',
      'shipping_address_1',
      'shipping_address_2',
      'shipping_city',
      'shipping_state',
      'shipping_postcode',
      'shipping_country',
      'shipping_phone'
    ];

    shippingFields.forEach(field => {
      const from = shippingForm.querySelector(`[name="${field}"]`);
      const to = this.querySelector(`[name="${field}"]`);
      if (from && to) {
        to.value = from.value;
      }
    });
  }

  const billingForm = document.getElementById('billingEditBox');
  if (billingForm) {
    const billingFields = [
      'billing_first_name',
      'billing_last_name',
      'billing_address_1',
      'billing_address_2',
      'billing_city',
      'billing_state',
      'billing_postcode',
      'billing_country',
      'billing_phone',
      'billing_email'
    ];

    billingFields.forEach(field => {
      const from = billingForm.querySelector(`[name="${field}"]`);
      const to = this.querySelector(`[name="${field}"]`);
      if (from && to) {
        to.value = from.value;
      }
    });
  }
});
</script>

<?php include get_template_directory() . '/layouts/footer.php'; ?>

