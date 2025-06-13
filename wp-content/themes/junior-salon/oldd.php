
function fetch_sorted_products() {
    $sort = isset($_GET['sort']) ? sanitize_text_field($_GET['sort']) : '';
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;

    // Define the number of products per page
    $posts_per_page = 15;

    // WP_Query arguments
    $args = array(
        'post_type'      => 'product',
        'posts_per_page' => $posts_per_page,
        'paged'          => $page,
    );

    // Sorting logic based on selected option
    switch ($sort) {
        case 'popular':
        case 'best-selling':
            $args['meta_key'] = 'total_sales';
            $args['orderby']  = 'meta_value_num';
            $args['order']    = 'DESC';
            break;
        case 'a-z':
            $args['orderby'] = 'title';
            $args['order']   = 'ASC';
            break;
        case 'z-a':
            $args['orderby'] = 'title';
            $args['order']   = 'DESC';
            break;
        case 'low-high':
            $args['meta_key'] = '_price';
            $args['orderby']  = 'meta_value_num';
            $args['order']    = 'ASC';
            break;
        case 'high-low':
            $args['meta_key'] = '_price';
            $args['orderby']  = 'meta_value_num';
            $args['order']    = 'DESC';
            break;
        case 'old-new':
            $args['orderby'] = 'date';
            $args['order']   = 'ASC';
            break;
        case 'new-old':
            $args['orderby'] = 'date';
            $args['order']   = 'DESC';
            break;
    }

    // Run the query
    $loop = new WP_Query($args);

    ob_start();

    if ($loop->have_posts()) :
        while ($loop->have_posts()) : $loop->the_post();
            global $product;
            ?>
            <div class="bg-white shadow-md rounded-lg overflow-hidden p-4 flex flex-col">
                <a href="<?php the_permalink(); ?>">
                    <?php /*if (has_post_thumbnail()) {
                        the_post_thumbnail('medium', ['class' => 'w-full h-48 object-cover mb-4']);
                    } else {
                        echo '<img src="https://via.placeholder.com/300x300" class="w-full h-48 object-cover mb-4">';
                    } */?>
                </a>


                <?php
$attachment_ids = $product->get_gallery_image_ids();
$hover_image_id = $attachment_ids[0] ?? null;
?>
<div class="relative group w-full aspect-square overflow-hidden">
  <img 
    src="<?php echo get_the_post_thumbnail_url(get_the_ID(), 'medium'); ?>" 
    class="w-full h-full object-cover transition-opacity duration-300 group-hover:opacity-0" 
    alt="<?php the_title_attribute(); ?>" 
  />
  <?php if ($hover_image_id): ?>
    <img 
      src="<?php echo wp_get_attachment_image_url($hover_image_id, 'medium'); ?>" 
      class="w-full h-full object-cover absolute inset-0 opacity-0 transition-opacity duration-300 group-hover:opacity-100" 
      alt="<?php the_title_attribute(); ?>" 
    />
  <?php endif; ?>
</div>


<?php echo do_shortcode('[yith_wcwl_add_to_wishlist]'); ?>



                <?php
                $brands = wp_get_post_terms(get_the_ID(), 'product_brand');
                if (!empty($brands) && !is_wp_error($brands)) {
                    echo '<div class="text-sm text-gray-500 mb-1">' . esc_html($brands[0]->name) . '</div>';
                }
                ?>

                <h2 class="text-md font-semibold mb-2">
                    <a href="<?php the_permalink(); ?>" class="hover:underline"><?php the_title(); ?></a>
                </h2>

                <div class="mt-auto text-lg font-bold text-gray-800">
                    <?php echo $product->get_price_html(); ?>
                </div>


                <?php
            if ($product->is_type('simple')) {
                echo '<div class="woocommerce">';
                woocommerce_simple_add_to_cart();
                echo '</div>';
            } elseif ($product->is_type('variable')) {
               echo '<div class="woocommerce">';
            woocommerce_variable_add_to_cart();
              echo '</div>';

       
            }
            ?>
            </div>

            </div>
            <?php
        endwhile;
    else :
        echo '';
    endif;

    wp_reset_postdata();

    echo ob_get_clean();
    wp_die(); // End AJAX request properly
}









add_action('wp_ajax_fetch_sorted_products', 'fetch_sorted_products_bycategory');
add_action('wp_ajax_nopriv_fetch_sorted_products', 'fetch_sorted_products_bycategory');

function fetch_sorted_products() {
    $sort = isset($_GET['sort']) ? sanitize_text_field($_GET['sort']) : '';
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;

    // Define the number of products per page
    $posts_per_page = 15;

    // WP_Query arguments
    $args = array(
        'post_type'      => 'product',
        'posts_per_page' => $posts_per_page,
        'paged'          => $page,
    );

    // Sorting logic based on selected option
    switch ($sort) {
        case 'popular':
        case 'best-selling':
            $args['meta_key'] = 'total_sales';
            $args['orderby']  = 'meta_value_num';
            $args['order']    = 'DESC';
            break;
        case 'a-z':
            $args['orderby'] = 'title';
            $args['order']   = 'ASC';
            break;
        case 'z-a':
            $args['orderby'] = 'title';
            $args['order']   = 'DESC';
            break;
        case 'low-high':
            $args['meta_key'] = '_price';
            $args['orderby']  = 'meta_value_num';
            $args['order']    = 'ASC';
            break;
        case 'high-low':
            $args['meta_key'] = '_price';
            $args['orderby']  = 'meta_value_num';
            $args['order']    = 'DESC';
            break;
        case 'old-new':
            $args['orderby'] = 'date';
            $args['order']   = 'ASC';
            break;
        case 'new-old':
            $args['orderby'] = 'date';
            $args['order']   = 'DESC';
            break;
    }

    // Run the query
    $loop = new WP_Query($args);

    ob_start();

    if ($loop->have_posts()) :
        while ($loop->have_posts()) : $loop->the_post();
            global $product;
            ?>
            <div class="bg-white shadow-md rounded-lg overflow-hidden p-4 flex flex-col">
                <a href="<?php the_permalink(); ?>">
                    <?php /*if (has_post_thumbnail()) {
                        the_post_thumbnail('medium', ['class' => 'w-full h-48 object-cover mb-4']);
                    } else {
                        echo '<img src="https://via.placeholder.com/300x300" class="w-full h-48 object-cover mb-4">';
                    } */?>
                </a>


                <?php
$attachment_ids = $product->get_gallery_image_ids();
$hover_image_id = $attachment_ids[0] ?? null;
?>
<div class="relative group w-full aspect-square overflow-hidden">
  <img 
    src="<?php echo get_the_post_thumbnail_url(get_the_ID(), 'medium'); ?>" 
    class="w-full h-full object-cover transition-opacity duration-300 group-hover:opacity-0" 
    alt="<?php the_title_attribute(); ?>" 
  />
  <?php if ($hover_image_id): ?>
    <img 
      src="<?php echo wp_get_attachment_image_url($hover_image_id, 'medium'); ?>" 
      class="w-full h-full object-cover absolute inset-0 opacity-0 transition-opacity duration-300 group-hover:opacity-100" 
      alt="<?php the_title_attribute(); ?>" 
    />
  <?php endif; ?>
</div>


<?php echo do_shortcode('[yith_wcwl_add_to_wishlist]'); ?>



                <?php
                $brands = wp_get_post_terms(get_the_ID(), 'product_brand');
                if (!empty($brands) && !is_wp_error($brands)) {
                    echo '<div class="text-sm text-gray-500 mb-1">' . esc_html($brands[0]->name) . '</div>';
                }
                ?>

                <h2 class="text-md font-semibold mb-2">
                    <a href="<?php the_permalink(); ?>" class="hover:underline"><?php the_title(); ?></a>
                </h2>

                <div class="mt-auto text-lg font-bold text-gray-800">
                    <?php echo $product->get_price_html(); ?>
                </div>


                <?php
            if ($product->is_type('simple')) {
                echo '<div class="woocommerce">';
                woocommerce_simple_add_to_cart();
                echo '</div>';
            } elseif ($product->is_type('variable')) {
               echo '<div class="woocommerce">';
            woocommerce_variable_add_to_cart();
              echo '</div>';

       
            }
            ?>
            </div>

            </div>
            <?php
        endwhile;
    else :
        echo '';
    endif;

    wp_reset_postdata();

    echo ob_get_clean();
    wp_die(); // End AJAX request properly
}



