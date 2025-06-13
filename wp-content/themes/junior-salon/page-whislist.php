<?php
/**
 * Template Name: Custom Wishlist Page
 */
defined('ABSPATH') || exit;
include get_template_directory() . '/layouts/header.php';

// Fetch the current user's wishlist items using YITH WooCommerce Wishlist API
//$wishlist_items = YITH_WCWL()->get_wishlist();
?>

<div class="wishlist-page bg-gray-50 min-h-screen">
  <div class="container mx-auto px-4 py-8 grid grid-cols-1 lg:grid-cols-3 gap-8">
    <!-- Left: Wishlist Products -->
    <div class="lg:col-span-2 space-y-6">
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-bold text-gray-800">MY WISHLIST (<span class="wishlist-count"><?php //echo count($wishlist_items); ?></span> ITEMS)</h2>
        <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" class="text-xs text-gray-500 hover:text-gray-700 underline transition-colors">&larr; Continue shopping</a>
      </div>


<?php echo do_shortcode('[yith_wcwl_wishlist]'); ?>



 
        <?php   
      /*     <div id="wishlist-items">   if (empty($wishlist_items)) {
          echo '<p class="text-center text-gray-500 py-6 text-sm">Your wishlist is empty.</p>';
        } else {
          foreach ($wishlist_items as $item) {
            $product_id = $item['prod_id'];
            $product = wc_get_product($product_id);
            if (!$product || !$product->exists()) continue;
            $product_permalink = $product->is_visible() ? $product->get_permalink() : '';
            $brand_terms = get_the_terms($product_id, 'product_brand');
            $brand = !empty($brand_terms) && !is_wp_error($brand_terms) ? esc_html($brand_terms[0]->name) : 'Brand';
        ?>
        <div class="wishlist-item group transition-all duration-300 bg-white rounded-lg p-3 relative" data-product-id="<?php echo esc_attr($product_id); ?>">
          <div class="flex items-start gap-4">
            <div class="w-24 h-36 bg-gray-200 rounded overflow-hidden relative">
              <?php echo $product->get_image('woocommerce_thumbnail', ['class' => 'w-full h-full object-cover transition-transform duration-300 group-hover:scale-105']); ?>
            </div>

            <div class="flex-1 space-y-1">
              <p class="text-xs uppercase text-gray-500 font-semibold"><?php echo $brand; ?></p>
              <h3 class="font-medium text-sm text-gray-800 line-clamp-2">
                <a href="<?php echo esc_url($product_permalink); ?>" class="text-gray-800 no-underline hover:text-gray-900 transition-colors duration-200">
                  <?php echo esc_html($product->get_name()); ?>
                </a>
              </h3>

              <div class="flex gap-3 mt-2 text-sm">
                <button type="button" class="remove-wishlist-item text-red-500 hover:text-red-700 underline transition-colors" data-product-id="<?php echo esc_attr($product_id); ?>">Remove</button>
                <button type="button" class="add-to-cart-btn text-gray-600 hover:text-green-600 underline transition-colors" data-product-id="<?php echo esc_attr($product_id); ?>">Add to Cart</button>
              </div>
            </div>

            <div class="text-right text-sm font-semibold text-red-500">
              <?php echo wc_price($product->get_price()); ?>
            </div>
          </div>
        </div>
        <?php } } ?>
      </div>
    </div>

    <!-- Right: Summary -->
    <div class="lg:col-span-1 border border-gray-200 p-6 bg-white rounded-lg shadow-md">
      <h3 class="text-lg font-semibold mb-4 text-gray-800">WISHLIST SUMMARY</h3>
      <div class="text-sm space-y-2">
        <p class="text-gray-600">Products saved for later. Add them to your cart to purchase.</p>
      </div>

      <div class="border-t mt-6 pt-4 text-xs text-gray-600">
        <p class="text-green-600 font-medium flex items-center gap-2 mb-2">
          <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
            <path d="M10 18a8 8 0 100-16 8 8 0 000 16zm-1.5-4.5l5-5-1.5-1.5-3.5 3.5L7 9l-1.5 1.5 3 3z"/>
          </svg>
          Items in wishlist are saved to your account
        </p>
        <div class="flex gap-2 mt-3">
          <img src="<?php echo get_template_directory_uri(); ?>/assets/img/visa.svg" alt="Visa" class="h-5">
          <img src="<?php echo get_template_directory_uri(); ?>/assets/img/mastercard.svg" alt="Mastercard" class="h-5">
          <img src="<?php echo get_template_directory_uri(); ?>/assets/img/gpay.svg" alt="Google Pay" class="h-5">
          <img src="<?php echo get_template_directory_uri(); ?>/assets/img/applepay.svg" alt="Apple Pay" class="h-5">
        </div>
      </div>  */ ?>
    </div>
  </div>
</div>

<script>
jQuery(document).ready(function($) {
  function handleWishlistAjax(action, data, $item) {
    return $.ajax({
      url: ajaxurl,
      type: 'POST',
      data: $.extend({ action: action, security: '<?php echo wp_create_nonce('woocommerce-cart'); ?>' }, data),
      dataType: 'json',
      success: function(response) {
        if (response.success) {
          if (action === 'remove_from_wishlist') {
            $item.fadeOut(300, function () {
              $(this).remove();
              if ($('.wishlist-item').length === 0) {
                $('#wishlist-items').html('<p class="text-center text-gray-500 py-6 text-sm">Your wishlist is empty.</p>');
              }
            });
          } else if (action === 'add_to_cart') {
            alert('Item added to cart!');
          }
        } else {
          alert(response.data.message || 'Something went wrong.');
        }
      },
      error: function(xhr, status, error) {
        console.error('AJAX Error:', status, error);
      }
    });
  }

  $('.remove-wishlist-item').on('click', function(e) {
    e.preventDefault();
    const $btn = $(this);
    const $item = $btn.closest('.wishlist-item');
    handleWishlistAjax('remove_from_wishlist', { product_id: $btn.data('product-id') }, $item);
  });

  $('.add-to-cart-btn').on('click', function(e) {
    e.preventDefault();
    const $btn = $(this);
    const $item = $btn.closest('.wishlist-item');
    handleWishlistAjax('add_to_cart', { product_id: $btn.data('product-id'), quantity: 1 }, $item);
  });
});
</script>

<?php include get_template_directory() . '/layouts/footer.php'; ?>
