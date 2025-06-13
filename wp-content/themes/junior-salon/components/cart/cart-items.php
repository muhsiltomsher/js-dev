<?php foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item): 
  $_product   = $cart_item['data'];
  $product_id = $cart_item['product_id'];

  if ($_product && $_product->exists() && $cart_item['quantity'] > 0): 
    $product_permalink = $_product->is_visible() ? $_product->get_permalink($cart_item) : '';

    // Get brand
    $brand = '';
    $brand_terms = get_the_terms($_product->get_id(), 'product_brand');
    if (!empty($brand_terms) && !is_wp_error($brand_terms)) {
        $brand = esc_html($brand_terms[0]->name);
    }

    // Get attributes
    $attributes = $cart_item['variation'] ?? [];
    $color = isset($attributes['attribute_pa_color']) ? wc_clean($attributes['attribute_pa_color']) : '';
    $size  = isset($attributes['attribute_pa_size'])  ? wc_clean($attributes['attribute_pa_size'])  : '';
?>

<div x-data="cartItemHandler('<?php echo esc_js($cart_item_key); ?>', <?php echo esc_js($cart_item['quantity']); ?>)"
     x-show="visible"
     class="cart-item group transition-all duration-300 bg-white p-0 overflow-hidden mb-5"
     data-cart-item-key="<?php echo esc_attr($cart_item_key); ?>">

  <div class="flex gap-0 relative">
    <!-- Skeleton Loader -->
    <div class="cart-skeleton hidden absolute inset-0 bg-gray-200 animate-pulse z-10">
      <div class="w-full max-w-[250px] h-full bg-gray-300"></div>
      <div class="flex-1 ml-2 p-4 space-y-4">
        <div class="h-4 bg-gray-300 rounded w-3/4"></div>
        <div class="h-4 bg-gray-300 rounded w-1/2"></div>
        <div class="h-4 bg-gray-300 rounded w-1/4"></div>
      </div>
    </div>

    <!-- Product Image -->
    <div class="w-full max-w-[250px] h-full p-0 m-0 overflow-hidden">
      <?php echo $_product->get_image('woocommerce_thumbnail', [
        'class' => 'w-full h-full object-cover', 
        'loading' => 'lazy'
      ]); ?>
    </div>

    <!-- Product Details -->
    <div class="flex-1 ml-2 text-sm flex flex-col justify-between border border-gray-300 transition-opacity duration-300">
      <div class="px-4 pt-2">
        <p class="text-[13px] font-semibold text-black leading-tight my-1"><?php echo $brand ?: 'Brand'; ?></p>
        <p class="text-[18px] text-black mb-2 leading-snug my-1"><?php echo $_product->get_name(); ?></p>
      </div>

      <div class="px-4 flex justify-between py-2 text-[13px] border-t border-gray-300 text-black">
        <div class="space-y-1">
          <p class="my-1">Size</p>
          <p class="my-1">Color</p>
        </div>
        <div class="text-right space-y-1 font-medium">
          <p class="my-1"><?php echo esc_html($size); ?></p>
          <p class="my-1"><?php echo esc_html($color); ?></p>
        </div>
      </div>

      <div class="px-4 flex justify-between items-center">
        <div class="flex divide-x w-fit text-[13px] overflow-hidden">
          <button type="button" @click="updateQty(qty - 1)"
                  class="px-3 py-1 text-[15px] border-y border-l border-r-0 border-black bg-white hover:bg-black hover:text-white transition-all duration-150 disabled:opacity-50 disabled:cursor-not-allowed"
                  aria-label="Decrease quantity">−</button>
          <span class="px-4 py-1 text-center !border-y !border-x-0" x-text="qty"></span>
          <button type="button" @click="updateQty(qty + 1)"
                  class="px-3 py-1 text-[15px] border-y border-l-0 border-r border-black bg-white hover:bg-black hover:text-white transition-all duration-150 disabled:opacity-50 disabled:cursor-not-allowed"
                  aria-label="Increase quantity">+</button>
        </div>

        <div class="text-right font-semibold">
          <span class="text-red-500 text-[15px]"><?php echo wc_price($_product->get_price() * $cart_item['quantity']); ?></span>
          <?php if ($_product->is_on_sale()): ?>
            <span class="text-gray-400 text-[13px] line-through ml-2"><?php echo wc_price($_product->get_regular_price()); ?></span>
          <?php endif; ?>
        </div>
      </div>

      <div class="px-4 py-2 flex gap-2 mt-2 border-t border-gray-300">
        <button type="button" @click="toggleItem()"
                class="flex items-center bg-white gap-2 border border-black text-black text-[14px] px-4 py-2 transition-all hover:bg-yellow-100 disabled:opacity-50 disabled:cursor-not-allowed">
          <img src="<?php echo get_template_directory_uri(); ?>/assets/images/icons/remove-icon.svg" alt="Remove" class="w-4 h-4" />
          Remove
        </button>

        <div class="text-xs text-gray-600">
          <?php echo do_shortcode('[yith_wcwl_add_to_wishlist product_id="' . esc_attr($product_id) . '"]'); ?>

        </div>
      </div>
    </div>
  </div>
</div>

<?php endif; endforeach; ?>
<?php do_action('woocommerce_after_cart_table'); ?>
