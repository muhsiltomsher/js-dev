<?php include get_template_directory() . '/layouts/header.php'; ?>

<main>
  <div class="container mx-auto py-12">

<?php
$term = get_queried_object(); // current category term

// Get the category image
$category_image_id = get_term_meta($term->term_id, 'thumbnail_id', true);
$category_image_url = $category_image_id ? wp_get_attachment_url($category_image_id) : 'https://via.placeholder.com/300x300?text=Category';

// Optional: get category description
$category_description = term_description($term);
?>

<!-- Category Banner Section (Left Image, Right Content) -->
<div class="w-full bg-white shadow-sm rounded-xl overflow-hidden mb-10">
  <div class="flex flex-col md:flex-row items-center md:items-stretch">

    <!-- Image Section -->
    <div class="w-full md:w-1/3">
      <img src="<?php echo esc_url($category_image_url); ?>" alt="<?php echo esc_attr($term->name); ?>"
           class="w-full h-full object-cover aspect-square" />
    </div>

    <!-- Content Section -->
    <div class="w-full md:w-2/3 p-6 flex flex-col justify-center">
      <h1 class="text-2xl md:text-3xl font-bold text-gray-900 mb-2"><?php echo esc_html($term->name); ?></h1>
      <?php if ($category_description): ?>
        <div class="text-sm text-gray-700 leading-relaxed"><?php echo wp_kses_post($category_description); ?></div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php get_template_part('products/all-products-categorywise'); ?>

  </div>
</main>

<?php include get_template_directory() . '/layouts/footer.php'; ?>