<?php
include get_template_directory() . '/layouts/header.php';
defined( 'ABSPATH' ) || exit;

global $product;

if ( ! is_a( $product, 'WC_Product' ) ) {
    $product = wc_get_product( get_the_ID() );
}

$attachment_ids = $product->get_gallery_image_ids();
$variations = $product->get_available_variations();
$attributes = $product->get_attributes();
?>

<div class="max-w-7xl mx-auto px-4 py-10">
  <div class="grid grid-cols-1 md:grid-cols-2 gap-10">

    <!-- Product Image & Thumbnails -->
    <div class="flex gap-4">
      <div class="flex flex-col space-y-2">
        <?php foreach ( $attachment_ids as $id ) : ?>
          <img src="<?= wp_get_attachment_image_url($id, 'thumbnail'); ?>" class="w-20 h-20 object-cover rounded-md border" />
        <?php endforeach; ?>
      </div>
      <div>
        <img src="<?= wp_get_attachment_image_url( $product->get_image_id(), 'large' ); ?>" class="rounded-xl shadow-md max-w-full" />
      </div>
    </div>

    <!-- Product Details -->
    <div>
      <h1 class="text-2xl font-bold"><?= $product->get_name(); ?></h1>
      <div class="text-red-600 text-xl font-semibold"><?= wc_price($product->get_sale_price() ?: $product->get_price()); ?></div>
      <?php if ($product->get_sale_price()) : ?>
        <div class="line-through text-gray-500"><?= wc_price($product->get_regular_price()); ?></div>
      <?php endif; ?>
      <p class="text-sm text-gray-500">(Duties And Taxes Included)</p>

	  <?php 
// Assuming you're in the single product template file (single-product.php or content-single-product.php)

// Get the color attribute for the product
$product = wc_get_product(get_the_ID());
$color = $product->get_attribute('pa_color'); // 'pa_color' is the taxonomy name for the color attribute

// Check if the color attribute exists
if ($color) : 
?>
  <div class="mt-6">
    <p class="font-semibold mb-2">COLOUR: <span class="uppercase text-blue-600"><?php echo esc_html($color); ?></span></p>
    <div class="flex gap-2">
      <!-- Example: Dynamically display color buttons based on the attribute value -->
      <?php 
      $colors = explode(',', $color); // If there are multiple color options
      foreach ($colors as $color_value) :
        // For each color, you might display a button with that color
        $color_value = trim($color_value);
      ?>
      <?php //echo esc_attr($color_value); ?>
      <?php endforeach; ?>
    </div>
  </div>
<?php endif; ?>

      <!-- Size + Stock -->
      <div class="mt-6">
        <p class="font-semibold mb-2">SELECT SIZE</p>
        <div class="space-y-2">
		<?php foreach ( $variations as $variation ) :
  $attributes = $variation['attributes'];

  $size  = isset($attributes['attribute_size']) ? $attributes['attribute_size'] : 'N/A';
  $color = isset($attributes['attribute_color']) ? $attributes['attribute_color'] : 'N/A';

  $price = wc_price($variation['display_price']);
  $qty = $variation['max_qty'];
  $low_stock = $qty <= 2;
?>
  <div class="flex items-center justify-between p-3 border <?= $low_stock ? 'border-yellow-500' : 'border-gray-300' ?> rounded-lg">
    <span class="text-sm uppercase">
      <?= esc_html($size); ?>
      <?= $low_stock ? "<span class='text-red-500 text-xs ml-2'>ONLY $qty LEFT!</span>" : '' ?>
    </span>
    <span class="text-green-600 font-semibold"><?= $price ?></span>
  </div>
<?php endforeach; ?>

        </div>
      </div>

      <!-- Buttons -->
      <div class="mt-6 flex gap-3">
        <button class="bg-black text-white px-6 py-3 rounded-lg hover:bg-gray-800">Add To Cart</button>
        <button class="border rounded-lg w-12 h-12 text-yellow-500 flex items-center justify-center">❤️</button>
      </div>
      <button class="mt-4 w-full bg-gray-200 py-3 rounded-lg hover:bg-gray-300">Buy Now</button>

	  <div class="mt-10 space-y-4">
  <?php
  // Define sections
  $sections = [
    'Description' => 'short_description',
    'Features' => 'features',
    'Material' => 'material',
    'Care Instruction' => 'care_instruction',
    'Delivery Information' => 'shippinginfo',
    'Return Policy' => 'return_policy'
  ];

  // Loop through each section
  foreach ($sections as $section => $acf_field) :
    // Get the ACF field value or the short description if it's the Description section
    if ($acf_field === 'short_description') {
      $content = get_the_excerpt();  // Short Description
    } else {
      $content = get_field($acf_field); // ACF Field
    }
    
    // Only display section if there's content
    if ($content) :
  ?>
      <details class="border border-gray-300 rounded-lg p-4">
        <summary class="font-medium cursor-pointer"><?= esc_html($section) ?></summary>
        <p class="mt-2 text-sm text-gray-600"><?= esc_html($content) ?></p>
      </details>
  <?php 
    endif;
  endforeach;
  ?>
</div>



    </div>

  </div>
</div>
<?php include get_template_directory() . '/layouts/footer.php';  ?>