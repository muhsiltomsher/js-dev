<h3 class="text-lg font-semibold mb-4">ORDER SUMMARY</h3>
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
  <span>Total Price (Item)</span>
  <span><?php wc_cart_totals_subtotal_html(); ?></span>
</div>

<div class="flex justify-between">
  <span>Shipping Tax & Fee</span>
  <span><?php wc_cart_totals_shipping_html(); ?></span>
</div>

<div class="flex justify-between font-bold text-lg mt-2">
  <span>Grand Total</span>
  <span><?php wc_cart_totals_order_total_html(); ?></span>
</div>

<a href="<?php echo esc_url(wc_get_checkout_url()); ?>" class="mt-6 w-full block text-center bg-black text-white text-sm py-3 rounded">
  <?php esc_html_e('Checkout', 'woocommerce'); ?>
</a>

<!-- Trust Info -->
<div class="bg-gray-50 p-3 rounded text-xs text-gray-600 space-y-1">
  <p><strong>JuniorSalon Protects Your Payment/Information</strong></p>
  <ul class="list-disc pl-4">
    <li>Encrypted SSL secure checkout</li>
    <li>We respect your privacy</li>
  </ul>
  <div class="flex gap-2 mt-2">
    <img src="visa.svg" alt="Visa" class="h-5">
    <img src="mastercard.svg" alt="Mastercard" class="h-5">
    <img src="paypal.svg" alt="PayPal" class="h-5">
  </div>
</div>
