<?php
/**
 * Template Name: Custom Cart Page
 */
include get_template_directory() . '/layouts/header.php'; ?>

<div class="custom-cart-container">
  <form class="woocommerce-cart-form" action="<?php echo esc_url(wc_get_cart_url()); ?>" method="post">
    <div class="container mx-auto px-4 py-8 grid grid-cols-1 lg:grid-cols-3 gap-8">

      <!-- Left: Cart Products -->
      <div class="lg:col-span-2 space-y-6">
        <h2 class="text-2xl font-bold mb-4">SHOPPING CART</h2>
       <p class="text-sm text-gray-500" x-data x-init="
  document.addEventListener('refresh-cart-totals', () => {
    fetch('<?php echo esc_url(wc_get_cart_url()); ?>?count_only=1')
      .then(res => res.text())
      .then(count => $el.textContent = `Showing ${count} products you added`);
  });
">
  Showing <?php echo WC()->cart->get_cart_contents_count(); ?> products you added 
</p>

        <?php do_action('woocommerce_before_cart_table'); ?>

        <?php foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item): 
          $_product   = $cart_item['data'];
          $product_id = $cart_item['product_id'];
          if ($_product && $_product->exists() && $cart_item['quantity'] > 0): 
            $product_permalink = $_product->is_visible() ? $_product->get_permalink($cart_item) : '';

// 1. Get brand (from taxonomy 'product_brand' or plugin like WooCommerce Brands)
$brand = '';
$brand_terms = get_the_terms($_product->get_id(), 'product_brand');
if (!empty($brand_terms) && !is_wp_error($brand_terms)) {
    $brand = esc_html($brand_terms[0]->name);
}

// 2. Get variation attributes (size, color)
$attributes = $cart_item['variation'] ?? [];

$color = isset($attributes['attribute_pa_color']) ? wc_attribute_label('color') . ': ' . wc_clean($attributes['attribute_pa_color']) : '';
$size  = isset($attributes['attribute_pa_size'])  ? wc_attribute_label('size')  . ': ' . wc_clean($attributes['attribute_pa_size'])  : '';
?>
 

       <div x-data="cartItemHandler('<?php echo esc_js($cart_item_key); ?>', <?php echo esc_js($cart_item['quantity']); ?>)"
     x-show="visible"
     class="flex border-b pb-4 mb-4 items-start gap-4">

  <div class="w-24 h-24 bg-white border rounded overflow-hidden">
    <?php echo $_product->get_image('woocommerce_thumbnail', ['class' => 'w-full h-full object-cover']); ?>
  </div>

  <div class="flex-1 space-y-1">
    <p class="text-xs uppercase text-gray-400 font-semibold"><?php echo $brand ?: 'Brand'; ?></p>
    <h3 class="font-medium text-sm"><?php echo $_product->get_name(); ?></h3>
    <div class="flex gap-4 text-sm text-gray-600">
     <?php if ($size): ?>
    <p><?php echo esc_html($size); ?></p>
  <?php endif; ?>
  <?php if ($color): ?>
    <p><?php echo esc_html($color); ?></p>
  <?php endif; ?>
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
<div class="text-xs text-gray-400 underline mt-1">
  <?php echo do_shortcode('[yith_wcwl_add_to_wishlist product_id="' . esc_attr($product_id) . '"]'); ?>
</div>


  <div class="text-right text-sm font-semibold text-red-500">
    <?php echo wc_price($_product->get_price()); ?>
  </div>
</div>


        <?php endif; endforeach; ?>

        <?php do_action('woocommerce_after_cart_table'); ?>
      </div>
<!-- Right: Order Summary -->
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
  <!-- Overlay Spinner -->
  <div x-show="loading"
       x-transition.opacity
       class="absolute inset-0 bg-white/80 backdrop-blur-sm z-10 flex items-center justify-center rounded">
    <div class="animate-spin rounded-full h-6 w-6 border-2 border-gray-800 border-t-transparent"></div>
  </div>

  <!-- Order Summary Content -->
  <h3 class="text-lg font-semibold mb-4">ORDER SUMMARY</h3>
  <div class="text-sm space-y-2">
    <?php foreach (WC()->cart->get_cart() as $cart_item):
      $_product = $cart_item['data']; $quantity = $cart_item['quantity'];
      if ($_product && $_product->exists()):
    ?>
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







    </div>
  </form>
</div>

    <?php get_template_part('components/products/popular-picks'); ?>


<script>
document.addEventListener('alpine:init', () => {
  Alpine.data('cartItemHandler', (key, initialQty) => ({
    qty: initialQty,
    visible: true,

    updateQty(newQty) {
      this.qty = newQty < 1 ? 0 : newQty;

      fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
          action: 'update_cart_item',
          cart_item_key: key,
          quantity: this.qty
        })
      }).then(() => {
        document.dispatchEvent(new CustomEvent('refresh-cart-totals'));
      });
    },

    toggleItem() {
      if (this.qty > 0) {
        this.visible = false;
        this.updateQty(0);
      }
    }
  }));
});
</script>

<script>
document.addEventListener('refresh-cart-totals', () => {
  const wrapper = document.querySelector('#cart-totals-wrapper');

  // Activate loader immediately
  if (wrapper && wrapper.__x) {
    wrapper.__x.$data.loading = true;
  }

  // Fetch only the order summary section
  fetch(window.location.href)
    .then(res => res.text())
    .then(html => {
      const temp = document.createElement('div');
      temp.innerHTML = html;

      const newWrapper = temp.querySelector('#cart-totals-wrapper');
      if (newWrapper && wrapper) {
        wrapper.innerHTML = newWrapper.innerHTML;
      }
    });
});




</script>

<?php include get_template_directory() . '/layouts/footer.php'; ?>
