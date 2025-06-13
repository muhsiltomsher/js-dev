<?php
/**
 * Product Image Gallery Component
 *
 * @package junior-salon
 */

defined('ABSPATH') || exit;

global $product;

$attachment_ids = $product->get_gallery_image_ids();
$main_image_id = $product->get_image_id();
$all_ids = array_merge([$main_image_id], $attachment_ids);
?>

<div class="product-gallery-wrapper mb-6 flex flex-col lg:flex-row gap-6">
  <!-- Thumbnails -->
  <div class="hidden lg:flex flex-col space-y-3 w-20">
    <?php foreach ($all_ids as $index => $image_id) :
      $image_url = wp_get_attachment_image_url($image_id, 'woocommerce_thumbnail');
      if (!$image_url) continue;
    ?>
      <div class="relative border-2 <?php echo $index === 0 ? 'border-yellow-400' : 'border-transparent'; ?> hover:border-yellow-400 rounded-lg overflow-hidden cursor-pointer transition-all duration-200">
        <!-- Skeleton -->
        <div class="absolute inset-0 animate-pulse bg-gray-200 z-0"></div>
        <img
          src="<?php echo esc_url($image_url); ?>"
          alt="Product Thumbnail"
          class="w-20 h-20 object-cover relative z-10 opacity-0 transition-opacity duration-500"
          onload="this.style.opacity=1; this.previousElementSibling.style.display='none';"
        />
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Main Image -->
  <div class="w-full border rounded-lg overflow-hidden shadow-sm">
    <div class="woocommerce-product-gallery relative">
      <!-- Skeleton -->
      <div class="absolute inset-0 animate-pulse bg-gray-300 z-0 rounded-lg"></div>
      <div class="relative z-10">
        <?php do_action('woocommerce_before_single_product_summary'); ?>
      </div>
    </div>
  </div>
</div>
