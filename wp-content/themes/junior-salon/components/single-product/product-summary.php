<?php
/**
 * Product Summary Component
 * Displays product brand, title, and price
 *
 * @package junior-salon
 */

defined('ABSPATH') || exit;

global $product;
?>

<div class="space-y-4">
  <!-- Brand and Product Title -->
  <div class="relative js-title-wrapper">
    <!-- Brand Name First -->
    <?php
    $brands = wp_get_post_terms(get_the_ID(), 'product_brand');
    if (!empty($brands) && !is_wp_error($brands)) {
        echo '<div class="text-lg uppercase font-semibold text-gray-500 mb-1">' . esc_html($brands[0]->name) . '</div>';
    }
    ?>

    <!-- Skeleton Title -->
    <div class="skeleton-title absolute inset-0 animate-pulse bg-gray-200 rounded h-8 w-2/3 z-0"></div>

    <!-- Product Title -->
    <h1 class="product-title text-2xl font-bold text-gray-900 relative z-10 opacity-0 transition-opacity duration-500">
      <?php the_title(); ?>
    </h1>
  </div>

  <!-- Product Price -->
  <div class="relative js-price-wrapper space-y-1">
    <div class="skeleton-price absolute inset-0 animate-pulse bg-gray-200 rounded h-6 w-1/4 z-0"></div>

    <div class="product-price text-red-600 font-bold text-lg relative z-10 opacity-0 transition-opacity duration-500">
      <?php echo $product->get_price_html(); ?>
    </div>

    <p class="text-xs text-black opacity-50">(Duties and taxes included)</p>
  </div>
</div>

<!-- Enqueue script or inline -->
<script>
  document.addEventListener('DOMContentLoaded', function () {
    document.querySelector('.product-title')?.classList.remove('opacity-0');
    document.querySelector('.skeleton-title')?.remove();

    document.querySelector('.product-price')?.classList.remove('opacity-0');
    document.querySelector('.skeleton-price')?.remove();
  });
</script>
