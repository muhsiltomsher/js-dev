<?php
/**
 * The template for displaying product content in the single-product.php template
 * @package WooCommerce\Templates
 * @version 3.6.0
 */

defined('ABSPATH') || exit;

global $product;

do_action('woocommerce_before_single_product');

if (post_password_required()) {
    echo get_the_password_form();
    return;
}
?>

<div id="product-<?php the_ID(); ?>" <?php wc_product_class('', $product); ?>>

  <div class="lg:flex lg:items-start gap-10">
    <!-- Sticky Left Gallery -->
    <div class="w-full lg:w-1/2 sticky top-24">
      <div class="">
        <?php get_template_part('components/single-product/product-image-gallery'); ?>
      </div>
    </div>

    <!-- Right Column: Summary, Cart, Accordion -->
    <div class="w-full lg:w-1/2 space-y-6">
      <?php get_template_part('components/single-product/product-summary'); ?>
      <?php get_template_part('components/single-product/product-add-to-cart'); ?>
      <?php get_template_part('components/single-product/product-accordion-details'); ?>
    </div>
  </div>

  <!-- Upsell Products -->
  <div class="mt-12">
    <?php get_template_part('components/single-product/product-upsell'); ?>
  </div>

  <!-- Related Products -->
  <div class="mt-12">
    <?php get_template_part('components/single-product/product-related'); ?>
  </div>
</div>

<?php do_action('woocommerce_after_single_product'); ?>
