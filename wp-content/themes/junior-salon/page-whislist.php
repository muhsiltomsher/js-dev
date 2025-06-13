<?php
/**
 * Template Name: Custom Wishlist Page
 */
defined('ABSPATH') || exit;
include get_template_directory() . '/layouts/header.php';
?>

<div class="wishlist-page bg-gray-50 min-h-screen py-12">
  <div class="container mx-auto px-4">
    <div class="flex items-center justify-between mb-8">
      <h2 class="text-2xl font-bold text-gray-800">
        <?php echo apply_filters('wpml_translate_single_string', 'MY WISHLIST', 'Auth Texts', 'MY WISHLIST'); ?>
      </h2>
      <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" class="text-sm text-gray-500 hover:text-gray-700 underline transition-colors">
        &larr; <?php echo apply_filters('wpml_translate_single_string', 'Continue shopping', 'Auth Texts', 'Continue shopping'); ?>
      </a>
    </div>

    <div id="wishlist-container" class="grid grid-cols-1 gap-6">
      <?php echo do_shortcode('[yith_wcwl_wishlist]'); ?>
    </div>

    <!-- Optional: Empty State -->
    <div id="empty-state" class="hidden text-center py-16 bg-white rounded-2xl shadow-sm border border-gray-100 mt-8">
      <i data-lucide="heart" class="w-16 h-16 text-gray-300 mx-auto mb-4"></i>
      <h3 class="text-xl font-semibold text-gray-900 mb-2">Your wishlist is empty</h3>
      <p class="text-gray-600 mb-6">Save items you love for later</p>
      <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" class="bg-gray-900 text-white px-6 py-3 rounded-lg font-medium hover:bg-gray-800 transition-colors">
        Start Shopping
      </a>
    </div>
  </div>
</div>

<script>
jQuery(document).ready(function ($) {
  function handleWishlistAjax(action, data, $item) {
    return $.ajax({
      url: ajaxurl,
      type: 'POST',
      data: $.extend({ action: action, security: '<?php echo wp_create_nonce('woocommerce-cart'); ?>' }, data),
      dataType: 'json',
      success: function (response) {
        if (response.success) {
          if (action === 'remove_from_wishlist') {
            $item.fadeOut(300, function () {
              $(this).remove();
              if ($('.wishlist-item').length === 0) {
                $('#empty-state').removeClass('hidden');
              }
            });
          } else if (action === 'add_to_cart') {
            alert('Item added to cart!');
          }
        } else {
          alert(response.data.message || 'Something went wrong.');
        }
      },
      error: function (xhr, status, error) {
        console.error('AJAX Error:', status, error);
      }
    });
  }

  $(document).on('click', '.remove-wishlist-item, .remove-btn', function (e) {
    e.preventDefault();
    const $btn = $(this);
    const $item = $btn.closest('.wishlist-item');
    handleWishlistAjax('remove_from_wishlist', { product_id: $btn.data('product-id') }, $item);
  });

  $(document).on('click', '.add-to-cart-btn', function (e) {
    e.preventDefault();
    const $btn = $(this);
    const $item = $btn.closest('.wishlist-item');
    if (!$btn.prop('disabled')) {
      handleWishlistAjax('add_to_cart', { product_id: $btn.data('product-id'), quantity: 1 }, $item);
    }
  });
});
</script>

<?php include get_template_directory() . '/layouts/footer.php'; ?>


<style>

.wishlist-title-container{
  display: none !important;
}
/* Make text black and remove underline from links */
.wishlist-page a {
  color: #111 !important;
  text-decoration: none !important;
}

.wishlist-page a:hover {
  color: #000 !important;
  text-decoration: none !important;
}

/* Hide share section */
.yith-wcwl-share {
  display: none !important;
}

/* Remove wishlist title edit UI if not needed */
.wishlist-title-container .btn,
.wishlist-title-container .hidden-title-form {
  display: none !important;
}

/* Style wishlist table cleanly */
table.wishlist_table {
  width: 100%;
  border-collapse: collapse;
  background-color: #fff;
  border-radius: 0.5rem;
  overflow: hidden;
}

.wishlist_table th,
.wishlist_table td {
  padding: 1rem;
  border-bottom: 1px solid #e5e7eb;
  text-align: left;
  vertical-align: middle;
  font-size: 14px;
  color: #111;
}

.wishlist_table th {
  font-weight: 600;
  background-color: #f9fafb;
}

/* Wishlist buttons */
.product-add-to-cart a.add_to_cart_button,
.product-remove a.remove_from_wishlist {
  display: inline-block;
  padding: 8px 16px;
  background-color: #111;
  color: #fff !important;
  font-size: 13px;
  font-weight: 500;
  border-radius: 6px;
  transition: all 0.2s ease;
}

.product-add-to-cart a.add_to_cart_button:hover,
.product-remove a.remove_from_wishlist:hover {
  background-color: #000;
  color: #fff !important;
}

/* Table row hover effect */
.wishlist_table tbody tr:hover {
  background-color: #f5f5f5;
}

/* Product name styling */
.wishlist_table .product-name a {
  font-weight: 500;
  font-size: 15px;
  color: #111 !important;
}

/* Stock status */
.wishlist_table .product-stock-status span {
  font-weight: 600;
  color: #16a34a;
}

/* Responsive fix for table */
@media screen and (max-width: 768px) {
  .wishlist_table thead {
    display: none;
  }
  .wishlist_table, 
  .wishlist_table tbody, 
  .wishlist_table tr, 
  .wishlist_table td {
    display: block;
    width: 100%;
  }
  .wishlist_table tr {
    margin-bottom: 1.5rem;
    border: 1px solid #ddd;
    border-radius: 0.5rem;
    padding: 1rem;
  }
  .wishlist_table td {
    text-align: left;
    padding: 0.5rem 0;
    border: none;
  }
}
</style>
