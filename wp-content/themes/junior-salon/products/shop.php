<?php
$paged = get_query_var('paged') ? get_query_var('paged') : 1;

$args = array(
    'post_type' => 'product',
    'posts_per_page' => 15,
    'paged' => $paged,
    'orderby' => 'title',
    'order' => 'ASC'
);

$query = new WP_Query($args);
?>

<section class="pt-[30px] px-[0px]">


  <div class="flex items-center justify-between border-b border-yellow-300 pb-2 mb-6">
    <h2 class="text-base sm:text-lg md:text-xl font-semibold uppercase tracking-wide text-gray-800">
      All Products
    </h2>
    <span class="text-sm text-gray-500"><?php echo $query->found_posts; ?> Products</span>
  </div>

  <div id="product-grid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-5 gap-4">
    <?php
    if ($query->have_posts()) :
      while ($query->have_posts()) : $query->the_post();
        get_template_part('components/products/product-card');
      endwhile;
    else :
      echo '<p class="text-center text-sm text-gray-500 col-span-full">No products found.</p>';
    endif;
    wp_reset_postdata();
    ?>
  </div>

  <?php if ($query->max_num_pages > $paged) : ?>
    <div class="flex justify-center mt-8">
      <button id="load-more" class="px-6 py-2 bg-black text-white rounded-lg hover:bg-gray-900 transition" data-page="<?php echo esc_attr($paged + 1); ?>">
        Load More
      </button>
    </div>
  <?php endif; ?>
</section>
