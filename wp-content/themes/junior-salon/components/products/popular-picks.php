<section class="pt-[30px] px-[15px]">
  <!-- Section Title -->
<div class="flex items-center justify-between border-b border-yellow-300 pb-2 mb-4">
  <h2 class="text-base sm:text-lg md:text-xl font-semibold uppercase tracking-wide text-gray-800">
    <?php echo apply_filters('wpml_translate_single_string', 'Popular Picks', 'junior-salon', 'Popular Picks'); ?>
  </h2>
  <a href="<?php echo esc_url(site_url((ICL_LANGUAGE_CODE === 'ar' ? '/ar/product-category/اختيارات-شائعة/' : '/product-category/popular-picks/'))); ?>" 
     class="text-sm font-semibold underline underline-offset-4 text-black hover:text-black transition">
    <?php echo apply_filters('wpml_translate_single_string', 'Shop All Products', 'junior-salon', 'Shop All Products'); ?>
  </a>
</div>





  <!-- Product Grid: 2 Rows -->
  <div id="product-grid" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 transition-opacity duration-500 ease-in-out mt-6">
    <?php
    $args = [
      'post_type' => 'product',
      'posts_per_page' => 10, // ← Updated to show 2 rows
      'tax_query' => [
        [
          'taxonomy' => 'product_cat',
          'field'    => 'slug',
          'terms'    => 'popular-picks',
        ],
      ],
    ];

    $query = new WP_Query($args);

    if ($query->have_posts()) :
      while ($query->have_posts()) : $query->the_post();
        global $product;

        setup_postdata($GLOBALS['post'] = get_post($product->get_id()));
        get_template_part('components/products/product-card');
      endwhile;
      wp_reset_postdata();
    endif;
    ?>
  </div>
</section>
