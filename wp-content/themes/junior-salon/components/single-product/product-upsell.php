<?php
/**
 * Product Upsell Component
 * Uses custom product card layout from components/products/product-card.php
 *
 * @package junior-salon
 */

defined('ABSPATH') || exit;

global $product;

$upsells = $product->get_upsell_ids();

if (!empty($upsells)) :
?>
  <div class="product-upsell-wrapper mt-16">
    <h2 class="text-xl font-semibold text-gray-900 mb-6 border-b pb-2">You may also like</h2>

    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
      <?php
      foreach ($upsells as $upsell_id) :
        $post_object = get_post($upsell_id);
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
