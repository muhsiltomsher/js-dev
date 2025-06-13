
<?php
function filter_products_callback() {
    error_log('Starting filter_products_callback');
    $categories = isset($_POST['categories']) ? array_map('intval', (array)$_POST['categories']) : [];
    $brands     = isset($_POST['brands']) ? array_map('intval', (array)$_POST['brands']) : [];
    $age        = isset($_POST['age']) ? array_map('intval', (array)$_POST['age']) : [];
    $sizes      = isset($_POST['sizes']) ? array_map('intval', (array)$_POST['sizes']) : [];
    $colors     = isset($_POST['colors']) ? array_map('intval', (array)$_POST['colors']) : [];
    $min_price  = floatval($_POST['min_price'] ?? 0);
    $max_price  = floatval($_POST['max_price'] ?? 0);
    $paged      = intval($_POST['page'] ?? 1);

    error_log('Filter POST data: ' . print_r($_POST, true));

    $args = [
        'post_type'      => 'product',
        'posts_per_page' => 50,
        'paged'          => $paged,
    ];

    $tax_query = ['relation' => 'AND'];

    if (!empty($categories)) {
        $tax_query[] = [
            'taxonomy' => 'product_cat',
            'field'    => 'term_id',
            'terms'    => $categories,
            'operator' => 'IN',
        ];
    }

    if (!empty($brands)) {
        $tax_query[] = [
            'taxonomy' => 'product_brand',
            'field'    => 'term_id',
            'terms'    => $brands,
            'operator' => 'IN',
        ];
    }

    if (!empty($age)) {
        $tax_query[] = [
            'taxonomy' => 'product_cat',
            'field'    => 'term_id',
            'terms'    => $age,
            'operator' => 'IN',
        ];
    }

    if (!empty($sizes)) {
        $tax_query[] = [
            'taxonomy' => 'pa_size',
            'field'    => 'term_id',
            'terms'    => $sizes,
            'operator' => 'IN',
        ];
    }

    if (!empty($colors)) {
        $tax_query[] = [
            'taxonomy' => 'pa_color',
            'field'    => 'term_id',
            'terms'    => $colors,
            'operator' => 'IN',
        ];
    }

    if (count($tax_query) > 1) {
        $args['tax_query'] = $tax_query;
    }

    $meta_query = [];

    if ($min_price || $max_price) {
        $meta_query[] = [
            'key'     => '_price',
            'value'   => [$min_price ?: 0, $max_price ?: 999999],
            'compare' => 'BETWEEN',
            'type'    => 'NUMERIC',
        ];
    }

    if (!empty($meta_query)) {
        $args['meta_query'] = $meta_query;
    }

    error_log('WP_Query args: ' . print_r($args, true));

    $query = new WP_Query($args);

    ob_start();

    if ($query->have_posts()) :
        error_log('Found posts: ' . $query->found_posts);
        while ($query->have_posts()) : $query->the_post();
            global $product; 
               setup_postdata($post);
        wc_setup_product_data($post);
        get_template_part('components/products/product-card'); 
         
        endwhile;

        if ($query->max_num_pages > $paged) : ?>
            <div class="text-center mt-6">
                <button 
                    class="load-more-btn bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
                    data-next-page="<?php echo esc_attr($paged + 1); ?>">
                    Load More
                </button>
            </div>
        <?php endif;
    else :
        echo '<p>No products found for the selected filters.</p>';
    endif;

    wp_reset_postdata();
    $output = ob_get_clean();
    error_log('Filter output length: ' . strlen($output));
    echo $output;
    wp_die();
}

add_action('wp_ajax_filter_products', 'filter_products_callback');
add_action('wp_ajax_nopriv_filter_products', 'filter_products_callback');
?>