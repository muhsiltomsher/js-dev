<?php
/**
 * Order Summary Section for Custom Checkout
 */

$cart = WC()->cart;
$totals = $cart->get_totals();
$chosen_shipping = WC()->session->get('chosen_shipping_methods')[0] ?? '';
$shipping_total = $cart->get_shipping_total();
$has_shipping_fee = $shipping_total !== null && $shipping_total > 0;
?>

<div class="bg-white border border-gray-200 p-6 rounded shadow-sm space-y-6">
  <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-800"><?php echo apply_filters( 'wpml_translate_single_string', 'ORDER SUMMARY', 'Cart Texts', 'ORDER SUMMARY' ); ?>
 </h2>

  <!-- Coupon Field -->
  <form class="flex gap-2" method="post" id="apply-coupon-form">
    <input type="text" name="coupon_code" placeholder="Coupon Code" required
      class="border w-full px-3 py-2 text-sm rounded" />
    <button type="submit"
      class="bg-black text-white px-5 py-2 text-sm font-medium uppercase hover:bg-gray-900"><?php echo apply_filters( 'wpml_translate_single_string', 'Apply', 'Checkout Texts', 'Apply' ); ?>
</button>
  </form>

  <?php wc_print_notices(); ?>

  <div id="coupon-msg" class="mb-4"></div>

  <hr />

  <!-- Totals -->
  <div class="text-sm text-gray-800 space-y-3">
    <div class="flex justify-between">
      <span><?php echo apply_filters( 'wpml_translate_single_string', 'Subtotal', 'Checkout Texts', 'Subtotal' ); ?>
</span>
      <span class="subtotal-placeholder"><?php wc_cart_totals_subtotal_html(); ?></span>
    </div>

    <?php if ($cart->get_shipping_total() > 0): ?>
    <div class="flex justify-between">
      <span><?php echo apply_filters( 'wpml_translate_single_string', 'Delivery', 'Checkout Texts', 'Delivery' ); ?>
</span>
      <span><?php echo wc_price($cart->get_shipping_total()); ?></span>
    </div>
    <?php endif; ?>

    <?php if (WC()->shipping->get_shipping_methods()): ?>
      <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-2 text-sm rounded mt-2">
        ✅<?php echo apply_filters( 'wpml_translate_single_string', 'Your order qualifies for FREE shipping', 'Checkout Texts', 'Your order qualifies for FREE shipping' ); ?>

      </div>
    <?php endif; ?>

    <div class="flex justify-between font-semibold text-lg pt-4 border-t mt-4">
      <span>Total</span>
      <span class="total-placeholder"><?php wc_cart_totals_order_total_html(); ?></span>
    </div>

    <p class="text-xs text-gray-500"><?php echo apply_filters( 'wpml_translate_single_string', 'Duties and taxes included', 'Checkout Texts', 'Duties and taxes included' ); ?>
</p>
  </div>

  <!-- Checkout Form Submit -->
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
        do_action( 'woocommerce_checkout_after_customer_details' );
        do_action( 'woocommerce_checkout_before_order_review' );
        do_action( 'woocommerce_checkout_order_review' );
        do_action( 'woocommerce_checkout_after_order_review' );
        ?>
      </div>
      <div id="order-loader" class="hidden absolute inset-0 bg-white/80 backdrop-blur-sm z-50 flex items-center justify-center">
        <div class="animate-spin rounded-full h-8 w-8 border-2 border-gray-800 border-t-transparent"></div>
      </div>

      <button type="submit"
        class="w-full bg-black text-white py-3 rounded text-sm font-semibold uppercase hover:bg-gray-900">
        <?php echo apply_filters( 'wpml_translate_single_string', 'Place Order', 'Checkout Texts', 'Place Order' ); ?>

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
    body: new URLSearchParams({ action: 'apply_coupon', coupon: code }),
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