<?php
/**
 * Product Related Component
 * Uses custom product card layout from components/products/product-card.php
 *
 * @package junior-salon
 */

defined('ABSPATH') || exit;

$related_products = wc_get_related_products(get_the_ID(), 4); // Adjust count if needed

if ($related_products) :
?>
  <div class="product-related-wrapper mt-16">
    <h2 class="text-xl font-semibold text-gray-900 mb-6 border-b pb-2">Related Products</h2>

    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
      <?php
      foreach ($related_products as $related_product_id) :
        $post_object = get_post($related_product_id);
        if (!$post_object) continue;

        setup_postdata($GLOBALS['post'] =& $post_object);
        wc_setup_product_data($post_object);
        get_template_part('components/products/product-card');
      endforeach;
      wp_reset_postdata();
      ?>
    </div>
  </div>
<?php endif; ?>
