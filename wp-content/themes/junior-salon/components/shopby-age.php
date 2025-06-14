<?php
$parent_cat = get_term_by('slug', 'age', 'product_cat');

if ($parent_cat && !is_wp_error($parent_cat)) {
    $subcategories = get_terms([
        'taxonomy'   => 'product_cat',
        'hide_empty' => false,
        'parent'     => $parent_cat->term_id,
        'number'     => 3,
    ]);

    $thumbnail_id = get_term_meta($parent_cat->term_id, 'thumbnail_id', true);
    $image_url = wp_get_attachment_url($thumbnail_id);
}
?>

<section class="pt-[30px] px-[0px]">
  <!-- Header -->
<div class="flex items-center justify-between border-b border-yellow-300 pb-2 mb-4">
  <h2 class="text-base sm:text-lg md:text-xl font-semibold uppercase tracking-wide text-gray-800">
    <?php echo apply_filters('wpml_translate_single_string', 'Shop by Age', 'junior-salon', 'Shop by Age'); ?>
  </h2>
  <a href="<?php echo esc_url(site_url((ICL_LANGUAGE_CODE === 'ar' ? '/ar/product-category/العمر-ar/' : '/product-category/age/'))); ?>" 
     class="text-sm font-semibold underline underline-offset-4 text-black hover:text-black transition">
    <?php echo apply_filters('wpml_translate_single_string', 'Shop All Products', 'junior-salon', 'Shop All Products'); ?>
  </a>
</div>


  <div class="grid grid-cols-1 lg:grid-cols-2 gap-[15px]">
    <!-- Left: Subcategory Images -->
    <div class="flex flex-col gap-[15px] h-full">
      <div class="grid grid-cols-2 gap-[15px] flex-1">
        <?php if (!empty($subcategories)): ?>
          <?php foreach (array_slice($subcategories, 0, 2) as $cat): ?>
            <?php
              $cat_thumbnail_id = get_term_meta($cat->term_id, 'thumbnail_id', true);
              $cat_image_url = wp_get_attachment_url($cat_thumbnail_id);
            ?>
            <a href="<?php echo get_term_link($cat); ?>" class="block h-full w-full group">
              <?php if ($cat_image_url): ?>
                <img src="<?php echo esc_url($cat_image_url); ?>"
                     alt="<?php echo esc_attr($cat->name); ?>"
                     loading="lazy"
                     class="w-full h-full object-cover transition-opacity duration-300 ease-in-out group-hover:opacity-80" />
              <?php endif; ?>
            </a>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

      <?php if (!empty($subcategories) && count($subcategories) > 2): ?>
        <?php $third_cat = $subcategories[2]; ?>
        <?php
          $third_cat_thumbnail_id = get_term_meta($third_cat->term_id, 'thumbnail_id', true);
          $third_cat_image_url = wp_get_attachment_url($third_cat_thumbnail_id);
        ?>
        <a href="<?php echo get_term_link($third_cat); ?>" class="block w-full h-[200px] group">
          <?php if ($third_cat_image_url): ?>
            <img src="<?php echo esc_url($third_cat_image_url); ?>"
                 alt="<?php echo esc_attr($third_cat->name); ?>"
                 loading="lazy"
                 class="w-full h-full object-cover transition-opacity duration-300 ease-in-out group-hover:opacity-80" />
          <?php endif; ?>
        </a>
      <?php endif; ?>
    </div>

    <!-- Right: Parent Image -->
    <?php if (!empty($image_url)): ?>
      <a href="<?php echo get_term_link($parent_cat); ?>" class="block w-full h-full group">
        <img src="<?php echo esc_url($image_url); ?>"
             alt="<?php echo esc_attr($parent_cat->name); ?>"
             loading="lazy"
             class="w-full h-full object-cover transition-opacity duration-300 ease-in-out group-hover:opacity-80" />
      </a>
    <?php else: ?>
      <div class="bg-gray-100 h-full flex items-center justify-center">
        <p class="text-gray-500 text-sm">No image found for the "Age" category.</p>
      </div>
    <?php endif; ?>
  </div>
</section>
