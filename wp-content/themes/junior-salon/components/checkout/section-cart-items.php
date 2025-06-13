<?php
/**
 * Cart Items Section for Custom Checkout (View Only)
 */

if (!function_exists('WC')) return;
$cart = WC()->cart->get_cart();
$cart_count = WC()->cart->get_cart_contents_count();
?>

<div class="bg-white border border-gray-200 p-6 rounded shadow-sm">
  <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-800 mb-4">
    Shopping Bag (<?php echo $cart_count; ?> <?php echo _n('item', 'items', $cart_count); ?>)
  </h2>

  <div class="space-y-6">
    <?php
    foreach ($cart as $cart_item_key => $cart_item) {
      $_product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
      $product_id = $cart_item['product_id'];

      if ($_product && $_product->exists() && $cart_item['quantity'] > 0) {
        $brand_terms = get_the_terms($_product->get_id(), 'product_brand');
        $brand = !empty($brand_terms) && !is_wp_error($brand_terms) ? esc_html($brand_terms[0]->name) : 'Brand';
        $attributes = $cart_item['variation'] ?? [];
        $color = $attributes['attribute_pa_color'] ?? '';
        $size = $attributes['attribute_pa_size'] ?? '';
    ?>
    <div class="cart-item group transition-all duration-300 bg-white p-0 overflow-hidden mb-5">
      <div class="flex gap-0 relative">
        <div class="w-full max-w-[70px] h-full p-0 m-0 overflow-hidden">
          <?php echo $_product->get_image('woocommerce_thumbnail', ['class' => 'w-full h-full object-cover', 'loading' => 'lazy']); ?>
        </div>

        <div class="flex-1 ml-2 text-sm flex flex-col justify-between border border-gray-300 transition-opacity duration-300">
          <div class="px-2 pt-1">
            <p class="text-[13px] font-semibold text-black leading-tight my-1"><?php echo $brand; ?></p>
            <p class="text-[13px] text-black mb-2 leading-snug my-1"><?php echo $_product->get_name(); ?></p>
          </div>

      <div class="px-2 flex justify-between py-2 text-[13px] border-t border-gray-300 text-black">
            <div class="flex gap-4">
              <span class="font-xs">Size: <?php echo esc_html($size); ?></span>
              <span class="font-xs">Color: <?php echo esc_html($color); ?></span>
              <span class="font-xs">Qty: <?php echo esc_html($cart_item['quantity']); ?></span>
            </div>
          </div>

          <div class="px-2 pb-1 pt-1 text-right font-semibold">
            <span class="text-red-500 text-[15px]"><?php echo wc_price($_product->get_price() * $cart_item['quantity']); ?></span>
            <?php if ($_product->is_on_sale()) : ?>
              <span class="text-gray-400 text-[13px] line-through ml-2"><?php echo wc_price($_product->get_regular_price()); ?></span>
            <?php endif; ?>
          </div>

        </div>
      </div>
    </div>
    <?php
      }
    }
    if (empty($cart)) {
      echo '<p class="text-center text-gray-500 py-6 text-sm">Your cart is empty.</p>';
    }
    ?>
  </div>
</div>
