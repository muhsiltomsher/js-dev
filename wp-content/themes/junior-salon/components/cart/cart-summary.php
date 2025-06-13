<div class="lg:col-span-1 border p-6 bg-white rounded-lg shadow-sm" id="cart-totals-wrapper"
     x-data="{ loading: false }"
     x-init="
      document.addEventListener('refresh-cart-totals', () => {
        loading = true;
        fetch(window.location.href).then(res => res.text()).then(html => {
          const temp = document.createElement('div');
          temp.innerHTML = html;
          const newWrapper = temp.querySelector('#cart-totals-wrapper');
          $el.innerHTML = newWrapper.innerHTML;
          loading = false;
        });
      });
     ">
  <div x-show="loading"
       x-transition.opacity
       class="absolute inset-0 bg-white/80 backdrop-blur-sm z-10 flex items-center justify-center rounded">
    <div class="animate-spin rounded-full h-6 w-6 border-2 border-gray-800 border-t-transparent"></div>
  </div>

  <h3 class="text-lg font-semibold mb-4"><?php echo apply_filters( 'wpml_translate_single_string', 'ORDER SUMMARY', 'Cart Texts', 'ORDER SUMMARY' ); ?>
 </h3>
  <div class="text-sm space-y-2">
    <?php foreach (WC()->cart->get_cart() as $cart_item):
      $_product = $cart_item['data']; $quantity = $cart_item['quantity'];
      if ($_product && $_product->exists()): ?>
      <div class="flex justify-between">
        <span><?php echo $_product->get_name() . ' × ' . $quantity; ?></span>
        <span><?php echo wc_price($_product->get_price() * $quantity); ?></span>
      </div>
    <?php endif; endforeach; ?>
  </div>

  <hr class="my-3" />

  <div class="flex justify-between font-medium">
    <span><?php echo apply_filters( 'wpml_translate_single_string', 'Total Price (Item)', 'Cart Texts', 'Total Price (Item)' ); ?>
</span>
    <span><?php wc_cart_totals_subtotal_html(); ?></span>
  </div>

  <div class="flex justify-between">
    <span><?php echo apply_filters( 'wpml_translate_single_string', 'Shipping Tax & Fee', 'Cart Texts', 'Shipping Tax & Fee' ); ?>
</span>
    <span><?php wc_cart_totals_shipping_html(); ?></span>
  </div>

  <div class="flex justify-between font-bold text-lg mt-2">
    <span><?php echo apply_filters( 'wpml_translate_single_string', 'Grand Total', 'Cart Texts', 'Grand Total' ); ?>
</span>
    <span><?php wc_cart_totals_order_total_html(); ?></span>
  </div>

  <a href="<?php echo esc_url(wc_get_checkout_url()); ?>" class="mt-6 w-full block text-center bg-black text-white text-sm py-3 rounded">
    <?php esc_html_e('Checkout', 'woocommerce'); ?>
  </a>

  <div class="bg-gray-50 p-3 rounded text-xs text-gray-600 space-y-1 mt-4">
    <p><strong><?php echo apply_filters( 'wpml_translate_single_string', 'JuniorSalon Protects Your Payment/Information', 'Cart Texts', 'JuniorSalon Protects Your Payment/Information' ); ?>
</strong></p>
    <ul class="list-disc pl-4">
      <li><?php echo apply_filters( 'wpml_translate_single_string', 'Encrypted SSL secure checkout', 'Cart Texts', 'Encrypted SSL secure checkout' ); ?>
</li>
      <li><?php echo apply_filters( 'wpml_translate_single_string', 'We respect your privacy', 'Cart Texts', 'We respect your privacy' ); ?>
</li>
    </ul>
    <div class="flex gap-2 mt-2">
      <img src="visa.svg" alt="Visa" class="h-5">
      <img src="mastercard.svg" alt="Mastercard" class="h-5">
      <img src="paypal.svg" alt="PayPal" class="h-5">
    </div>
  </div>
</div>
