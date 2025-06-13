<?php foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item): 
  $_product   = $cart_item['data'];
  $product_id = $cart_item['product_id'];
  if ($_product && $_product->exists() && $cart_item['quantity'] > 0): 
    $product_permalink = $_product->is_visible() ? $_product->get_permalink($cart_item) : '';
    $brand = ''; // Add logic to get the brand, size, and color as per your original code
?>
<div x-data="cartItemHandler('<?php echo esc_js($cart_item_key); ?>', <?php echo esc_js($cart_item['quantity']); ?>)"
     x-show="visible"
     class="flex border-b pb-4 mb-4 items-start gap-4">
  <!-- Product Image -->
  <div class="w-24 h-24 bg-white border rounded overflow-hidden">
    <?php echo $_product->get_image('woocommerce_thumbnail', ['class' => 'w-full h-full object-cover']); ?>
  </div>

  <!-- Product Details -->
  <div class="flex-1 space-y-1">
    <p class="text-xs uppercase text-gray-400 font-semibold"><?php echo $brand ?: 'Brand'; ?></p>
    <h3 class="font-medium text-sm"><?php echo $_product->get_name(); ?></h3>
    <div class="flex gap-4 text-sm text-gray-600">
      <?php echo $size ? "<p>{$size}</p>" : ''; ?>
      <?php echo $color ? "<p>{$color}</p>" : ''; ?>
    </div>
    <div class="flex items-center mt-2 gap-2">
      <div class="flex border rounded text-sm">
        <button type="button" @click="updateQty(qty - 1)" class="px-2">−</button>
        <span class="px-3" x-text="qty"></span>
        <button type="button" @click="updateQty(qty + 1)" class="px-2">+</button>
      </div>
      <button type="button" class="text-xs text-red-500" @click="toggleItem()">Remove</button>
    </div>
  </div>

  <div class="text-right text-sm font-semibold text-red-500">
    <?php echo wc_price($_product->get_price()); ?>
  </div>
</div>
<?php endif; endforeach; ?>
