<?php
// Increase maximum execution time
set_time_limit(300); // 300 seconds = 5 minutes

// Increase memory limit
ini_set('memory_limit', '256M'); // Adjust to your needs



function mytheme_enqueue_scripts() {
    wp_enqueue_script('jquery');
    if (!is_admin()) {
        wp_localize_script('jquery', 'ajax_params', [
            'ajaxurl' => admin_url('admin-ajax.php')
        ]);
    }
}
add_action('wp_enqueue_scripts', 'mytheme_enqueue_scripts');
// Enqueue Tailwind CSS and other styles
function junior_salon_enqueue_styles() {
    wp_enqueue_style('tailwindcss', get_template_directory_uri() . '/dist/styles.css', [], null);
}
add_action('wp_enqueue_scripts', 'junior_salon_enqueue_styles');

add_action('admin_menu', function() {
    add_menu_page(
        'Homepage',          // Page title
        'Homepage',          // Menu title
        'manage_options',    // Capability
        'homepage-main-menu',// Menu slug
        '__return_null',     // No page content needed
        'dashicons-admin-home', // Icon
        3                    // Position (after Dashboard)
    );
});

register_post_type('home-features-banner', array(
    'label' => 'Home Features Banner',
    'public' => true,
    'show_in_menu' => 'homepage-main-menu', // <- This is the key part!
    'supports' => array('title', 'editor', 'thumbnail'),
    // other arguments...
));



 function mytheme_enqueue_scripts_font() {

    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css');
}
 add_action('wp_enqueue_scripts', 'mytheme_enqueue_scripts_font');


function junior_salon_custom_logo_setup() {
    add_theme_support('custom-logo', array(
        'height'      => 100,  // Adjust the height as needed
        'width'       => 300,  // Adjust the width as needed
        'flex-width'  => true, // Allow flexibility in the width
        'flex-height' => true, // Allow flexibility in the height
    ));
}
add_action('after_setup_theme', 'junior_salon_custom_logo_setup');
function juniorsalon_customize_register($wp_customize) {
    // Logo Width
    $wp_customize->add_setting('logo_width', array(
        'default' => '150',
        'sanitize_callback' => 'absint',
    ));

    $wp_customize->add_control('logo_width', array(
        'label' => __('Logo Width (px)', 'juniorsalon'),
        'section' => 'title_tagline',
        'type' => 'number',
        'input_attrs' => array(
            'min' => 50,
            'max' => 600,
            'step' => 1,
        ),
    ));

    // Logo Height
    $wp_customize->add_setting('logo_height', array(
        'default' => 'auto',
        'sanitize_callback' => function($value) {
            return $value === 'auto' ? 'auto' : absint($value);
        },
    ));

    $wp_customize->add_control('logo_height', array(
        'label' => __('Logo Height (px or "auto")', 'juniorsalon'),
        'section' => 'title_tagline',
        'type' => 'text',
        'description' => __('Use "auto" to keep original ratio.'),
    ));
}
add_action('customize_register', 'juniorsalon_customize_register');


// Register Menu Locations
function junior_salon_register_menus() {
    register_nav_menus(array(
        'primary' => __('Primary Menu', 'junior-salon'), // Primary menu location
        'footer1' => __('Footer Menu 1', 'junior-salon'),   // Footer menu 1
        'footer2' => __('Footer Menu 2', 'junior-salon'), // Footer menu 2
		        'footer3' => __('Footer Menu 3', 'junior-salon'), // Footer menu 3

    ));
}



add_action('after_setup_theme', 'junior_salon_register_menus');
function junior_salon_customize_register($wp_customize) {
    // Add a new section
    $wp_customize->add_section('junior_salon_footer_section', array(
        'title' => __('Footer Settings', 'junior-salon'),
        'priority' => 130,
    ));

    // Add a setting
    $wp_customize->add_setting('junior_salon_footer_copyright', array(
        'default' => '© ' . date('Y') . ' Junior Salon. All rights reserved.',
        'sanitize_callback' => 'sanitize_text_field',
    ));

    // Add a control for the setting
    $wp_customize->add_control('junior_salon_footer_copyright', array(
        'label' => __('Footer Copyright Text', 'junior-salon'),
        'section' => 'junior_salon_footer_section',
        'type' => 'text',
    ));
}
add_action('customize_register', 'junior_salon_customize_register');

function mytheme_customize_register($wp_customize) {
    // Add social media section
    $wp_customize->add_section('social_media_section', array(
        'title'    => __('Social Media', 'mytheme'),
        'priority' => 30,
    ));

    // Facebook URL setting and control
    $wp_customize->add_setting('facebook_url', array(
        'default'   => '',
        'transport' => 'refresh',
    ));
    $wp_customize->add_control('facebook_url', array(
        'label'    => __('Facebook URL', 'mytheme'),
        'section'  => 'social_media_section',
        'type'     => 'url',
    ));

    // Twitter URL setting and control
    $wp_customize->add_setting('twitter_url', array(
        'default'   => '',
        'transport' => 'refresh',
    ));
    $wp_customize->add_control('twitter_url', array(
        'label'    => __('Twitter URL', 'mytheme'),
        'section'  => 'social_media_section',
        'type'     => 'url',
    ));

    // Instagram URL setting and control
    $wp_customize->add_setting('instagram_url', array(
        'default'   => '',
        'transport' => 'refresh',
    ));
    $wp_customize->add_control('instagram_url', array(
        'label'    => __('Instagram URL', 'mytheme'),
        'section'  => 'social_media_section',
        'type'     => 'url',
    ));
}

add_action('customize_register', 'mytheme_customize_register');



function mytheme_customize_register_top_bar($wp_customize) {
    // Add a section for the top bar settings
    $wp_customize->add_section('top_bar_section', array(
        'title'    => __('Top Bar Settings', 'mytheme'),
        'priority' => 30,
    ));

    // Tagline setting
    $wp_customize->add_setting('top_bar_tagline', array(
        'default'   => 'Super Save',
        'transport' => 'refresh',
    ));
    $wp_customize->add_control('top_bar_tagline', array(
        'label'    => __('Tagline', 'mytheme'),
        'section'  => 'top_bar_section',
        'type'     => 'text',
    ));

    // Shop Now Button URL setting
    $wp_customize->add_setting('top_bar_shop_url', array(
        'default'   => home_url('/shop'),
        'transport' => 'refresh',
    ));
    $wp_customize->add_control('top_bar_shop_url', array(
        'label'    => __('Shop Now Button URL', 'mytheme'),
        'section'  => 'top_bar_section',
        'type'     => 'url',
    ));

   // Add a section for the top bar timer settings
    $wp_customize->add_section('top_bar_section', array(
        'title'       => __('Top Bar Settings', 'mytheme'),
        'description' => __('Customize the top bar settings, including the timer.', 'mytheme'),
        'priority'    => 30,
    ));
   // Add a section for the top bar timer settings
    $wp_customize->add_section('top_bar_section', array(
        'title'       => __('Top Bar Settings', 'mytheme'),
        'description' => __('Customize the top bar settings, including the timer.', 'mytheme'),
        'priority'    => 30,
    ));

    // Add a setting for the deal end date and time
    $wp_customize->add_setting('deal_end_datetime', array(
        'default' => '',
        'sanitize_callback' => 'sanitize_text_field',
    ));

    // Add the control for the datetime field (for Deal End Date)
    $wp_customize->add_control('deal_end_datetime', array(
        'label'   => __('Deal End Date and Time', 'mytheme'),
        'section' => 'top_bar_section',
        'type'    => 'datetime-local',  // DateTime input field type
    ));
}

add_action('customize_register', 'mytheme_customize_register_top_bar');



function register_testimonial_post_type() {
    register_post_type('testimonial', array(
        'labels' => array(
            'name' => 'Testimonials',
            'singular_name' => 'Testimonial',
        ),
        'public' => true,
        'has_archive' => false,
        'supports' => ['title', 'editor', 'thumbnail'],
        'show_in_rest' => true,
    ));
}
add_action('init', 'register_testimonial_post_type');

add_action('wp_ajax_load_testimonials', 'load_testimonials_ajax');
add_action('wp_ajax_nopriv_load_testimonials', 'load_testimonials_ajax');

function load_testimonials_ajax() {
    $paged = isset($_GET['page']) ? intval($_GET['page']) : 1;

    $query = new WP_Query([
        'post_type' => 'testimonial',
        'posts_per_page' => 3,
        'paged' => $paged
    ]);

    if ($query->have_posts()) :
        while ($query->have_posts()) : $query->the_post();
            $rating = get_field('rating');
            ?>
            <div class="bg-white shadow rounded-lg p-6">
                <?php if (has_post_thumbnail()) : ?>
               <img src="<?php the_post_thumbnail_url('medium'); ?>" class="w-full h-48 object-cover rounded-md mb-4" alt="">

                <?php endif; ?>
                <h3 class="text-lg font-semibold mb-1"><?php the_title(); ?></h3>
                <div class="text-yellow-400 mb-2">
                    <?php for ($i = 0; $i < 5; $i++) : ?>
                        <span><?php echo $i < $rating ? '★' : '☆'; ?></span>
                    <?php endfor; ?>
                </div>
                <p class="text-gray-700 text-sm"><?php the_content(); ?></p>
            </div>
            <?php
        endwhile;
    endif;

    wp_reset_postdata();
    wp_die();
}
function load_products_by_category($cat, $lang = null) {
    $lang = $lang ?: apply_filters('wpml_current_language', null);

    $args = [
        'post_type'        => 'product',
        'posts_per_page'   => 10,
        'post_status'      => 'publish',
        'suppress_filters' => false,
        'lang'             => $lang,
    ];

    if ($cat === 'sale') {
        $args['meta_query'] = [
            [
                'key'     => '_sale_price',
                'value'   => 0,
                'compare' => '>',
                'type'    => 'NUMERIC',
            ],
        ];
    } else {
        $term = get_term_by('slug', $cat, 'product_cat');

        if ($term && function_exists('icl_object_id')) {
            $translated_term_id = icl_object_id($term->term_id, 'product_cat', false, $lang);

            if ($translated_term_id) {
                $args['tax_query'] = [
                    [
                        'taxonomy' => 'product_cat',
                        'field'    => 'term_id',
                        'terms'    => $translated_term_id,
                    ],
                ];
            }
        }
    }

    $query = new WP_Query($args);

    $product_ids = [];
    while ($query->have_posts()) {
        $query->the_post();
        $product_ids[] = get_the_ID();
    }

    wp_reset_postdata();
    return $product_ids;
}

function load_tab_products() {
    $cat = sanitize_text_field($_GET['cat'] ?? '');
   // $lang = apply_filters('wpml_current_language', null);
$lang = sanitize_text_field($_GET['lang'] ?? apply_filters('wpml_current_language', null));

    $slug_map = [
        'whats-hot' => 'whats-hot',
        'best-seller' => 'best-seller',
        'sale' => 'sale',
        'ما-هو-ساخن' => 'ما-هو-ساخن',
        'الأكثر-مبيعاً' => 'الأكثر-مبيعاً',
        'أُوكَازيُون' => 'sale',
    ];

    $resolved_cat = $slug_map[$cat] ?? '';
    if (!$resolved_cat) {
        wp_send_json_error('Invalid category');
    }

    $products = load_products_by_category($resolved_cat, $lang);
    include get_template_directory() . '/components/products/product-grid.php';
    wp_die();
}
add_action('wp_ajax_load_tab_products', 'load_tab_products');
add_action('wp_ajax_nopriv_load_tab_products', 'load_tab_products');












 
  add_action('wp_enqueue_scripts', function () {
    if (is_page() || is_singular()) {
      wp_enqueue_script('wc-add-to-cart-variation');
    }
  });
  
function product_page_template_dropdown($templates) {
    $templates['page-products.php'] = 'Products Page Template';
    return $templates;
}
add_filter('theme_page_templates', 'product_page_template_dropdown');


function load_more_products_ajax_handler() {
    $paged = isset($_POST['page']) ? intval($_POST['page']) : 1;

    $args = array(
        'post_type' => 'product',
        'posts_per_page' => 15,
        'paged' => $paged,
    );

    $loop = new WP_Query($args);

    if ($loop->have_posts()) :
        ob_start();
        while ($loop->have_posts()) : $loop->the_post(); global $product;
            ?>
            <div class="bg-white shadow-md rounded-lg overflow-hidden p-4 flex flex-col">
                <a href="<?php the_permalink(); ?>">
                    <?php if (has_post_thumbnail()) : ?>
                        <?php the_post_thumbnail('medium', ['class' => 'w-full h-48 object-cover mb-4']); ?>
                    <?php else : ?>
                        <img src="https://via.placeholder.com/300x300" alt="<?php the_title(); ?>" class="w-full h-48 object-cover mb-4">
                    <?php endif; ?>
                </a>

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
            </div>
            <?php
        endwhile;
        wp_reset_postdata();
        echo ob_get_clean();
    else :
        echo '';
    endif;

    wp_die();
}
add_action('wp_ajax_load_more_products', 'load_more_products_ajax_handler');
add_action('wp_ajax_nopriv_load_more_products', 'load_more_products_ajax_handler');


add_action('wp_ajax_fetch_sorted_products', 'fetch_sorted_products');
add_action('wp_ajax_nopriv_fetch_sorted_products', 'fetch_sorted_products');

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



add_filter( 'template_include', 'load_custom_product_template', 99 );
function load_custom_product_template( $template ) {
    if ( is_singular('product') ) {
        $custom = locate_template( 'woocommerce/single-product.php' );
        if ( $custom ) {
            return $custom;
        }
    }
    return $template;
}


remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );


//add_action( 'woocommerce_after_single_product', 'load_custom_popular_picks_template', 15 );

//function load_custom_popular_picks_template() {
   // get_template_part( 'components/products/popular-picks' );
//}



// Shortcode to display product categories as checkboxes
function product_categories_checkbox_shortcode( $atts ) {
    // Default attributes for the shortcode
    $atts = shortcode_atts( array(
        'include' => '', // Comma-separated category IDs
    ), $atts, 'product_categories_checkbox' );

    // Convert the 'include' attribute into an array
    $include_ids = array_map('intval', explode(',', $atts['include']));

    // Function to fetch and display product categories as checkboxes
    ob_start(); // Start output buffering

    $args = array(
        'taxonomy'   => 'product_cat',
        'hide_empty' => false,
        'include'    => $include_ids, // Only include specified categories by ID
    );

    // Fetch the categories
    $product_categories = get_terms( $args );

    if ( ! empty( $product_categories ) && ! is_wp_error( $product_categories ) ) {
     
        foreach ( $product_categories as $category ) {
            $cat_id = esc_attr( $category->term_id );
            $cat_name = esc_html( $category->name );
            echo "<label><input type='checkbox' name='product_cat[]' value='{$cat_id}'> {$cat_name}</label><br>";
        }
    
      
    } else {
        echo '<p>No categories available.</p>';
    }

    return ob_get_clean(); // Return the content and stop buffering
}

// Register the shortcode
add_shortcode( 'product_categories_checkbox', 'product_categories_checkbox_shortcode' );



function age_subcategories_checkboxes_shortcode() {
    // Get the parent category term object
    $parent = get_term_by('name', 'Age', 'product_cat'); // Or use slug: 'age'

    if ( ! $parent || is_wp_error($parent) ) {
        return '<p>Parent category not found.</p>';
    }

    // Fetch child terms
    $subcategories = get_terms(array(
        'taxonomy'   => 'product_cat',
        'parent'     => $parent->term_id,
        'hide_empty' => false,
        'orderby'    => 'name',
        'order'      => 'ASC',
    ));

    if (empty($subcategories)) {
        return '<p>No subcategories found.</p>';
    }

    // Build checkbox list with Tailwind styling
    $output = '<div class="space-y-2">';
    foreach ($subcategories as $subcategory) {
        $output .= '<label class="flex items-center space-x-2 text-gray-700">
            <input type="checkbox" name="age_product_cat[]" value="' . esc_attr($subcategory->term_id) . '" class="accent-blue-500">
            <span>' . esc_html($subcategory->name) . '</span>
        </label>';
    }
    $output .= '</div>';

    return $output;
}
add_shortcode('age_category_checkboxes', 'age_subcategories_checkboxes_shortcode');



function woocommerce_brand_checkboxes_shortcode() {
    $brands = get_terms(array(
        'taxonomy'   => 'product_brand', // Change this if your brand taxonomy differs
        'hide_empty' => false,
        'orderby'    => 'name',
        'order'      => 'ASC',
    ));

    if (empty($brands) || is_wp_error($brands)) {
        return '<p>No brands found.</p>';
    }

    // Output checkboxes with Tailwind styling
    $output = '<div class="space-y-2">';
    foreach ($brands as $brand) {
        $output .= '<label class="flex items-center space-x-2 text-gray-700">
            <input type="checkbox" name="product_brand[]" value="' . esc_attr($brand->term_id) . '" class="accent-blue-500">
            <span>' . esc_html($brand->name) . '</span>
        </label>';
    }
    $output .= '</div>';

    return $output;
}
add_shortcode('brand_checkboxes', 'woocommerce_brand_checkboxes_shortcode');




function woocommerce_attribute_checkboxes_shortcode($atts) {
    $atts = shortcode_atts([
        'attribute' => '', // Expected: 'pa_color' or 'pa_size'
    ], $atts);

    $taxonomy = sanitize_title($atts['attribute']);

    if (empty($taxonomy) || !taxonomy_exists($taxonomy)) {
        return '<p>Invalid or missing attribute taxonomy.</p>';
    }

    $terms = get_terms([
        'taxonomy'   => $taxonomy,
        'hide_empty' => false,
        'orderby'    => 'name',
        'order'      => 'ASC',
    ]);

    if (empty($terms) || is_wp_error($terms)) {
        return '<p>No terms found for this attribute.</p>';
    }

    // Output checkboxes with Tailwind styling
    $output = '<div class="space-y-2">';
    foreach ($terms as $term) {
        $output .= '<label class="flex items-center space-x-2 text-gray-700">
            <input type="checkbox" name="' . esc_attr($taxonomy) . '[]" value="' . esc_attr($term->term_id) . '" class="accent-blue-500">
            <span>' . esc_html($term->name) . '</span>
        </label>';
    }
    $output .= '</div>';

    return $output;
}
add_shortcode('attribute_checkboxes', 'woocommerce_attribute_checkboxes_shortcode');



function woocommerce_price_filter_shortcode() {
    ob_start();
    ?>

    <div class="space-y-4">
        <label class="block text-sm font-medium text-gray-700">Price Range</label>
        <div class="flex items-center space-x-3">
            <input type="number" name="min_price" id="min_price" placeholder="Min" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            <span>-</span>
            <input type="number" name="max_price" id="max_price" placeholder="Max" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
        </div>
    </div>

    <?php
    return ob_get_clean();
}
add_shortcode('price_filter', 'woocommerce_price_filter_shortcode');


add_action('wp_ajax_load_sort_drawer_content', 'load_sort_drawer_content');
add_action('wp_ajax_nopriv_load_sort_drawer_content', 'load_sort_drawer_content');

function load_sort_drawer_content() {
    ob_start();
    get_template_part('products/sort-drawer');
    echo ob_get_clean();
    wp_die();
}











add_action('wp_ajax_load_filter_drawer_content', 'load_filter_drawer_content');
add_action('wp_ajax_nopriv_load_filter_drawer_content', 'load_filter_drawer_content');

function load_filter_drawer_content() {
    // Output filter content
    get_template_part('products/filter-drawer');
    wp_die(); // always call wp_die() at the end of AJAX handlers
}
add_action('wp_footer', function () {
    if (!is_admin()) {
        $ajax_url = admin_url('admin-ajax.php');
        echo '<script>var ajaxurl = "' . esc_url($ajax_url) . '";</script>';
    }
});


function filter_products_callback() {
 //   error_log('Starting filter_products_callback');
    $categories = isset($_POST['categories']) ? array_map('intval', (array)$_POST['categories']) : [];
    $brands     = isset($_POST['brands']) ? array_map('intval', (array)$_POST['brands']) : [];
    $age        = isset($_POST['age']) ? array_map('intval', (array)$_POST['age']) : [];
    $sizes      = isset($_POST['sizes']) ? array_map('intval', (array)$_POST['sizes']) : [];
    $colors     = isset($_POST['colors']) ? array_map('intval', (array)$_POST['colors']) : [];
    $min_price  = floatval($_POST['min_price'] ?? 0);
    $max_price  = floatval($_POST['max_price'] ?? 0);
    $paged      = intval($_POST['page'] ?? 1);
    $pagetype = isset($_POST['pagetype']) ? sanitize_text_field($_POST['pagetype']) : '';
  //  error_log('Filter POST data: ' . print_r($_POST, true));

    if($pagetype=="sale")
    {
$args = array(
    'post_type' => 'product',
    'posts_per_page' => 15,
    'paged' => $paged,
    'orderby' => 'title',
    'order' => 'ASC',
    'meta_query' => array(
        array(
            'key'     => '_sale_price',  // Check for sale price
            'value'   => 0,
            'compare' => '>',
            'type'    => 'NUMERIC',
        ),
    ),
);

    }
    else
    {
   $args = [
        'post_type'      => 'product',
        'posts_per_page' => 50,
        'paged'          => $paged,
    ];
    }
 

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

  //  error_log('WP_Query args: ' . print_r($args, true));

    $query = new WP_Query($args);

    ob_start();

    if ($query->have_posts()) :
     //   error_log('Found posts: ' . $query->found_posts);
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
  //  error_log('Filter output length: ' . strlen($output));
    echo $output;
    wp_die();
}

add_action('wp_ajax_filter_products', 'filter_products_callback');
add_action('wp_ajax_nopriv_filter_products', 'filter_products_callback');

function filter_products_callback_old() {
    $categories = isset($_POST['categories']) ? array_map('intval', (array)$_POST['categories']) : [];
    $brands     = isset($_POST['brands']) ? array_map('intval', (array)$_POST['brands']) : [];
    $age        = isset($_POST['age']) ? array_map('intval', (array)$_POST['age']) : [];
    $sizes      = isset($_POST['sizes']) ? array_map('intval', (array)$_POST['sizes']) : [];
    $colors     = isset($_POST['colors']) ? array_map('intval', (array)$_POST['colors']) : [];
    $min_price  = floatval($_POST['min_price'] ?? 0);
    $max_price  = floatval($_POST['max_price'] ?? 0);
    $paged      = intval($_POST['page'] ?? 1);

    // Log incoming data for debugging
 //   error_log('Filter POST data: ' . print_r($_POST, true));

    $args = [
        'post_type'      => 'product',
        'posts_per_page' => 15,
        'paged'          => $paged,
    ];

    $tax_query = ['relation' => 'AND'];

    if (!empty($categories)) {
        $tax_query[] = [
            'taxonomy' => 'product_cat',
            'field'    => 'term_id', // Use term_id
            'terms'    => $categories,
        ];
    }

    if (!empty($brands)) {
        $tax_query[] = [
            'taxonomy' => 'product_brand',
            'field'    => 'term_id', // Use term_id
            'terms'    => $brands,
        ];
    }

    if (!empty($age)) {
        $tax_query[] = [
            'taxonomy' => 'product_cat',
            'field'    => 'term_id', // Use term_id
            'terms'    => $age,
        ];
    }

    if (!empty($sizes)) {
        $tax_query[] = [
            'taxonomy' => 'pa_size',
            'field'    => 'term_id', // Changed from slug to term_id
            'terms'    => $sizes,
        ];
    }

    if (!empty($colors)) {
        $tax_query[] = [
            'taxonomy' => 'pa_color',
            'field'    => 'term_id', // Changed from slug to term_id
            'terms'    => $colors,
        ];
    }

    if (!empty($tax_query) && count($tax_query) > 1) {
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

    $query = new WP_Query($args);

    ob_start();

    if ($query->have_posts()) :
        while ($query->have_posts()) : $query->the_post();
            global $product;
            ?>
            <div class="product-card bg-white shadow-md rounded-lg overflow-hidden p-4 flex flex-col">
                <div class="relative group w-full aspect-square overflow-hidden">
                    <img 
                        src="<?php echo get_the_post_thumbnail_url(get_the_ID(), 'medium'); ?>" 
                        class="w-full h-full object-cover transition-opacity duration-300 group-hover:opacity-0" 
                        alt="<?php the_title_attribute(); ?>" 
                    />
                    <?php
                    $attachment_ids = $product->get_gallery_image_ids();
                    $hover_image_id = $attachment_ids[0] ?? null;
                    if ($hover_image_id) : ?>
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
            <?php
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
    echo ob_get_clean();
    wp_die();
}

add_filter('posts_request', function($sql) {
  //  error_log($sql);
    return $sql;
});

add_filter( 'woocommerce_product_tabs', 'remove_additional_information_tab', 98 );
function remove_additional_information_tab( $tabs ) {
    unset( $tabs['additional_information'] );
    return $tabs;
}
add_action( 'woocommerce_single_product_summary', 'show_woocommerce_brand_above_title', 4 );
function show_woocommerce_brand_above_title() {
    $terms = get_the_terms( get_the_ID(), 'product_brand' );

    if ( $terms && ! is_wp_error( $terms ) ) {
        $brand = $terms[0]; // First assigned brand
        echo '<div class="product-brand" style="font-weight: bold; margin-bottom: 10px;">';
        echo '<a href="' . esc_url( get_term_link( $brand ) ) . '">' . esc_html( $brand->name ) . '</a>';
        echo '</div>';
    }
}

add_action( 'woocommerce_single_product_summary', 'add_duties_notice_under_price', 11 );
function add_duties_notice_under_price() {
    echo '
        <div class="mt-2">
            <p class="text-sm text-gray-600 italic">(Duties and Tax included)</p>
            <hr class="mt-2 border-t border-gray-300" />
        </div>
    ';
}

remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );


remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );

add_action('woocommerce_before_add_to_cart_button', 'custom_separator_before_cart', 15);
function custom_separator_before_cart() {
    echo '<hr class="my-4 border-t border-gray-300" />';
}


function add_divider_after_buy_now_button() {
    if (is_product()) {
        echo '<div style="margin-top: 20px; border-top: 1px solid #ddd; padding-top: 20px;"></div>';
    }
}
add_action('woocommerce_after_single_product', 'add_divider_after_buy_now_button', 15);



function mytheme_add_woocommerce_support() {
    add_theme_support('woocommerce');
}
add_action('after_setup_theme', 'mytheme_add_woocommerce_support');

add_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30);



function fetch_sorted_products_bycategory() {
    $taxonomy = isset($_GET['taxonomy']) ? sanitize_text_field($_GET['taxonomy']) : 'product_cat';

    $sort = isset($_GET['sort']) ? sanitize_text_field($_GET['sort']) : '';
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $term_id = isset($_GET['term_id']) ? intval($_GET['term_id']) : 0; // Category ID

    $posts_per_page = 15;

    $args = array(
        'post_type'      => 'product',
        'posts_per_page' => $posts_per_page,
        'paged'          => $page,
        'tax_query'      => array(
            array(
                'taxonomy' => $taxonomy,
                'field'    => 'term_id',
                'terms'    => $term_id,
            ),
        ),
    );

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
        echo '<p>No products found.</p>';
    endif;

    wp_reset_postdata();

    echo ob_get_clean();
    wp_die();
}
add_action('wp_ajax_fetch_sorted_products_bycategory', 'fetch_sorted_products_bycategory');
add_action('wp_ajax_nopriv_fetch_sorted_products_bycategory', 'fetch_sorted_products_bycategory');



add_action('wp_ajax_filter_products_with_term', 'filter_products_with_term');
add_action('wp_ajax_nopriv_filter_products_with_term', 'filter_products_with_term');

function filter_products_with_term() {
    $categories = isset($_POST['categories']) ? $_POST['categories'] : [];
    $brands     = isset($_POST['brands']) ? $_POST['brands'] : [];
    $age        = isset($_POST['age']) ? $_POST['age'] : [];
    $term_id    = isset($_POST['term_id']) ? intval($_POST['term_id']) : 0;
    $paged      = isset($_POST['page']) ? intval($_POST['page']) : 1;
    $taxonomy = isset($_POST['taxonomy']) ? sanitize_text_field($_POST['taxonomy']) : 'product_cat';
 $sizes      = isset($_POST['sizes']) ? array_map('intval', (array)$_POST['sizes']) : [];
    $colors     = isset($_POST['colors']) ? array_map('intval', (array)$_POST['colors']) : [];
   $min_price  = floatval($_POST['min_price'] ?? 0);
    $max_price  = floatval($_POST['max_price'] ?? 0);


   

if( $taxonomy=="product_brand")
{
$args = [
    'post_type'      => 'product',
    'posts_per_page' => 15,
    'paged'          => $paged,
    'tax_query'      => [
        [
            'taxonomy' => 'product_brand',
            'field'    => 'term_id',
            'terms'    => $term_id,
        ],
    ],
];
} else
{
     $args = array(
        'post_type'      => 'product',
        'posts_per_page' => 15,
        'paged'          => $paged,
    );
}

    $tax_query = array('relation' => 'AND');

    if (!empty($term_id)) {
        $tax_query[] = array(
            'taxonomy' => $taxonomy,
            'field'    => 'term_id',
            'terms'    => $term_id,
        );
    }

    if (!empty($categories)) {
        $tax_query[] = array(
            'taxonomy' => 'product_cat',
            'field'    => 'id',
            'terms'    => $categories,
            'operator' => 'IN',
        );
    }

    if (!empty($brands)) {
        $tax_query[] = array(
            'taxonomy' => 'product_brand',
            'field'    => 'id',
            'terms'    => $brands,
            'operator' => 'IN',
        );
    }

    if (!empty($age)) {
        $tax_query[] = array(
            'taxonomy' => 'product_cat', // Change this if "age" has its own taxonomy
            'field'    => 'id',
            'terms'    => $age,
            'operator' => 'IN',
        );
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
    $query = new WP_Query($args);

    ob_start();

    if ($query->have_posts()) :
        while ($query->have_posts()) : $query->the_post();
            global $product;         setup_postdata($post);
        wc_setup_product_data($post);
        get_template_part('components/products/product-card'); 
    
          
        endwhile;?>
        </div>
       <?php if ($query->max_num_pages > $paged) {
            ?>
            <div class="text-center mt-6">
                <button 
                    class="load-more-btn bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
                    data-next-page="<?php echo esc_attr($paged + 1); ?>">
                    Load More
                </button>
            </div>
            <?php
        }
   
    endif;

    wp_reset_postdata();
    echo ob_get_clean();
    wp_die();
}

function mytheme_enqueue_ajax_script() {
    wp_enqueue_script('jquery');
    ?>
    <script type="text/javascript">
        var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
    </script>
    <?php
}
add_action('wp_footer', 'mytheme_enqueue_ajax_script');

// Save first name, last name, phone number, and validate password confirmation
add_action('woocommerce_register_post', function ($username, $email, $validation_errors) {
    if ($_POST['password'] !== $_POST['password2']) {
        $validation_errors->add('password_mismatch', 'Passwords do not match.');
    }
}, 10, 3);

add_action('woocommerce_created_customer', function ($customer_id) {
    if (!empty($_POST['first_name'])) {
        update_user_meta($customer_id, 'first_name', sanitize_text_field($_POST['first_name']));
    }
    if (!empty($_POST['last_name'])) {
        update_user_meta($customer_id, 'last_name', sanitize_text_field($_POST['last_name']));
    }
    if (!empty($_POST['billing_phone'])) {
        update_user_meta($customer_id, 'billing_phone', sanitize_text_field($_POST['billing_phone']));
    }
});


add_action('wp_ajax_save_shipping_address', 'ajax_save_shipping_address');
add_action('wp_ajax_nopriv_save_shipping_address', 'ajax_save_shipping_address');

function ajax_save_shipping_address() {
  $fields = [
    'shipping_first_name',
    'shipping_last_name',
    'shipping_company',
    'shipping_address_1',
    'shipping_address_2',
    'shipping_city',
    'shipping_state',
    'shipping_postcode',
    'shipping_country',
    'shipping_phone'
  ];

  $updated = false;

  foreach ($fields as $field) {
    if (!isset($_POST[$field])) continue;

    $value = sanitize_text_field($_POST[$field]);
    
if (is_user_logged_in()) {
    update_user_meta(get_current_user_id(), 'default_shipping_index', 'default');
}
    // Store in WooCommerce session for guest checkout
    $key = str_replace('shipping_', '', $field);
    WC()->customer->{"set_shipping_{$key}"}($value);

    $updated = true;
  }

  if ($updated) {
    wp_send_json_success(['message' => 'Shipping address saved']);
  } else {
    wp_send_json_error(['message' => 'No valid fields to save']);
  }
}
add_action('wp_ajax_save_billing_address', 'ajax_save_billing_address');
add_action('wp_ajax_nopriv_save_billing_address', 'ajax_save_billing_address');

function ajax_save_billing_address() {
  $fields = [
    'billing_first_name',
    'billing_last_name',
    'billing_company',
    'billing_address_1',
    'billing_address_2',
    'billing_city',
    'billing_state',
    'billing_postcode',
    'billing_country',
    'billing_phone',
    'billing_email'
  ];

  $updated = false;

  foreach ($fields as $field) {
    if (!isset($_POST[$field])) continue;

    $value = ($field === 'billing_email')
      ? sanitize_email($_POST[$field])
      : sanitize_text_field($_POST[$field]);

    $short_key = str_replace('billing_', '', $field);

    // Always set Woo session (this is what WC validates)
    WC()->customer->{"set_billing_{$short_key}"}($value);

    // Save to user meta if logged in
    if (is_user_logged_in()) {
      update_user_meta(get_current_user_id(), $field, $value);
    }

    $updated = true;
  }

  if ($updated) {
    wp_send_json_success(['message' => 'Billing address updated']);
  } else {
    wp_send_json_error(['message' => 'No valid fields received']);
  }
}


add_action('wp_ajax_get_shipping_address', 'ajax_get_shipping_address');
add_action('wp_ajax_nopriv_get_shipping_address', 'ajax_get_shipping_address');

function ajax_get_shipping_address() {
  $fields = ['first_name','last_name','company','address_1','address_2','city','state','postcode','country','phone'];
  $data = [];

  if (is_user_logged_in()) {
    $user_id = get_current_user_id();
    foreach ($fields as $field) {
      $data[$field] = get_user_meta($user_id, 'shipping_' . $field, true);
    }
  } else {
    // Guest fallback: use WooCommerce session data
    foreach ($fields as $field) {
      $method = "get_shipping_{$field}";
      $data[$field] = WC()->customer->$method();
    }
  }

  wp_send_json_success($data);
}


add_action('wp_ajax_woocommerce_update_payment_method', function () {
  if (isset($_POST['payment_method'])) {
    WC()->session->set('chosen_payment_method', sanitize_text_field($_POST['payment_method']));
  }
  wp_die();
});

add_action('wp_ajax_apply_coupon', 'ajax_apply_coupon');
add_action('wp_ajax_nopriv_apply_coupon', 'ajax_apply_coupon');

function ajax_apply_coupon() {
    if (!isset($_POST['coupon'])) {
        wp_send_json_error(['message' => 'Coupon code is missing.']);
    }

    // Ensure cart is initialized
    if (!WC()->cart) {
        wc_load_cart();
    }

    $coupon_code = sanitize_text_field($_POST['coupon']);
    $applied = WC()->cart->apply_coupon($coupon_code);

    // If apply_coupon returns false, show reason
    if (!$applied) {
        wc_clear_notices();
        WC()->cart->calculate_totals();
        ob_start();
        wc_print_notices();
        $notice = ob_get_clean();

        wp_send_json_error([
            'message' => $notice ?: 'Invalid or expired coupon code.'
        ]);
    }

    WC()->cart->calculate_totals();
    wc_clear_notices(); // remove default Woo notices

    ob_start();
    wc_cart_totals_subtotal_html();
    $subtotal = ob_get_clean();

    ob_start();
    wc_cart_totals_shipping_html();
    $shipping = ob_get_clean();

    ob_start();
    wc_cart_totals_order_total_html();
    $total = ob_get_clean();

    wp_send_json_success([
        'subtotal' => $subtotal,
        'shipping' => $shipping,
        'total'    => $total
    ]);
}


function enqueue_alpine_for_cart() {
  wp_enqueue_script('alpinejs', 'https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js', [], null, true);
}
add_action('wp_enqueue_scripts', 'enqueue_alpine_for_cart');
add_action('wp_ajax_update_cart_item', 'custom_ajax_update_cart_item');
add_action('wp_ajax_nopriv_update_cart_item', 'custom_ajax_update_cart_item');

function custom_ajax_update_cart_item() {
  $cart_item_key = sanitize_text_field($_POST['cart_item_key']);
  $quantity = max(0, intval($_POST['quantity']));

  if (isset(WC()->cart->cart_contents[$cart_item_key])) {
    WC()->cart->set_quantity($cart_item_key, $quantity, true);
  }

  WC()->cart->calculate_totals();
  wp_send_json_success();
}
add_action('wp_ajax_remove_cart_item', 'custom_ajax_remove_cart_item');
add_action('wp_ajax_nopriv_remove_cart_item', 'custom_ajax_remove_cart_item');

function custom_ajax_remove_cart_item() {
  $cart_item_key = sanitize_text_field($_POST['cart_item_key']);
  WC()->cart->remove_cart_item($cart_item_key);
  WC()->cart->calculate_totals();
  wp_send_json_success();
}
add_action('template_redirect', function() {
  if (isset($_GET['count_only']) && $_GET['count_only'] == '1') {
    echo WC()->cart->get_cart_contents_count();
    exit;
  }
});


// MUHSIL 


// Enqueue SwiperJS assets
function enqueue_swiper_assets() {
    wp_enqueue_style('swiper-css', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css', [], null);
    wp_enqueue_script('swiper-js', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js', [], null, true);
}
add_action('wp_enqueue_scripts', 'enqueue_swiper_assets');

// AJAX lazy loader endpoint
add_action('wp_ajax_load_section', 'lazy_load_template_section');
add_action('wp_ajax_nopriv_load_section', 'lazy_load_template_section');

function lazy_load_template_section() {
    $template = sanitize_text_field($_GET['template'] ?? '');
    if (!$template) {
        wp_send_json_error('Template not provided');
    }

    // Prevent directory traversal
    if (!preg_match('/^[a-zA-Z0-9\-\/]+$/', $template)) {
        wp_send_json_error('Invalid template name');
    }

    ob_start();
    get_template_part('components/' . $template);
    echo ob_get_clean();
    wp_die();
}

add_action('wp_ajax_update_billing_address', function () {
    $user_id = get_current_user_id();
    if (!$user_id) wp_send_json_error(['message' => 'Unauthorized']);

    $fields = ['first_name', 'last_name', 'company', 'address_1', 'address_2', 'city', 'state', 'postcode', 'country', 'phone'];
    foreach ($fields as $field) {
        update_user_meta($user_id, 'billing_' . $field, sanitize_text_field($_POST[$field] ?? ''));
    }

    wp_send_json_success(['message' => 'Billing address updated successfully.']);
});

add_action('wp_ajax_update_shipping_address', function () {
    $user_id = get_current_user_id();
    if (!$user_id) wp_send_json_error(['message' => 'Unauthorized']);

    $fields = ['first_name', 'last_name', 'company', 'address_1', 'address_2', 'city', 'state', 'postcode', 'country', 'phone'];
    foreach ($fields as $field) {
        update_user_meta($user_id, 'shipping_' . $field, sanitize_text_field($_POST[$field] ?? ''));
    }

    wp_send_json_success(['message' => 'Shipping address updated successfully.']);
});

add_action('wp_ajax_update_bank_details', function () {
    $user_id = get_current_user_id();
    if (!$user_id) {
        wp_send_json_error(['message' => 'Not logged in.']);
    }

    update_user_meta($user_id, 'bank_name', sanitize_text_field($_POST['bank_name']));
    update_user_meta($user_id, 'account_number', sanitize_text_field($_POST['account_number']));
    update_user_meta($user_id, 'iban_number', sanitize_text_field($_POST['iban_number']));

    wp_send_json_success(['message' => 'Bank details updated successfully.']);
});
add_action('wp_ajax_update_account_password', function () {
    $user_id = get_current_user_id();
    if (!$user_id) {
        wp_send_json_error(['message' => 'Not logged in.']);
    }

    $current_password = $_POST['current_password'];
    $new_password     = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (!$current_password || !$new_password || !$confirm_password) {
        wp_send_json_error(['message' => 'All fields are required.']);
    }

    $user = get_user_by('id', $user_id);

    if (!wp_check_password($current_password, $user->user_pass, $user_id)) {
        wp_send_json_error(['message' => 'Current password is incorrect.']);
    }

    if ($new_password !== $confirm_password) {
        wp_send_json_error(['message' => 'New passwords do not match.']);
    }

    wp_set_password($new_password, $user_id);

    wp_send_json_success(['message' => 'Password changed successfully. Please log in again.']);
});
function load_font_awesome_for_account_icons() {
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css');
}
add_action('wp_enqueue_scripts', 'load_font_awesome_for_account_icons');
add_action('wp_ajax_update_user_profile', function () {
    $user_id = get_current_user_id();
    if (!$user_id) {
        wp_send_json_error(['message' => 'You are not logged in.']);
    }

    $phone = sanitize_text_field($_POST['phone'] ?? '');

    update_user_meta($user_id, 'billing_phone', $phone);
    update_user_meta($user_id, 'shipping_phone', $phone); // Optional sync

    wp_send_json_success(['message' => 'Profile updated successfully.']);
});


/**
 * ===========================================
 * Force custom template for product_cat archive pages
 * ===========================================
 */
add_filter('template_include', function ($template) {
    if (is_tax('product_cat')) {
        $custom_template = get_theme_file_path('taxonomy-product_cat.php');
        if (file_exists($custom_template)) {
            return $custom_template;
        }
    }
    return $template;
}, 99);



/**
 * Register REST API to fetch all available WooCommerce coupons
 */
add_action('rest_api_init', function () {
    register_rest_route('custom/v1', '/coupons', [
        'methods'  => 'GET',
        'callback' => 'get_available_coupons',
        'permission_callback' => '__return_true',
    ]);
});



/**
 * ===========================================
 * Force custom template for product_brand archive pages
 * ===========================================
 */
add_filter('template_include', function ($template) {
    if (is_tax('product_brand')) {
        $custom_template = get_theme_file_path('taxonomy-product_brand.php');
        if (file_exists($custom_template)) {
            return $custom_template;
        }
    }
    return $template;
}, 99);



function get_available_coupons() {
    $args = [
        'post_type'      => 'shop_coupon',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
    ];

    $coupons = get_posts($args);
    $data = [];

    foreach ($coupons as $post) {
        $code       = strtoupper(get_the_title($post));
        $amount     = get_post_meta($post->ID, 'coupon_amount', true);
        $type       = get_post_meta($post->ID, 'discount_type', true);
        $free_ship  = get_post_meta($post->ID, 'free_shipping', true);
        $desc       = $post->post_excerpt ?: $post->post_content;

        $data[] = [
            'code'          => $code,
            'amount'        => $amount,
            'type'          => $type,
            'description'   => $desc,
            'free_shipping' => $free_ship === 'yes',
        ];
    }

    return rest_ensure_response($data);
}




/**
 * AJAX handler for applying coupon to WooCommerce cart
 */
add_action('wp_ajax_apply_coupon_ajax', 'apply_coupon_ajax_handler');
add_action('wp_ajax_nopriv_apply_coupon_ajax', 'apply_coupon_ajax_handler');

function apply_coupon_ajax_handler() {
    // Security check
    if (!check_ajax_referer('apply_coupon_nonce', 'security', false)) {
        wp_send_json_error(['message' => 'Security check failed.']);
    }

    // Ensure WooCommerce cart is initialized
    if (!WC()->cart) {
        wp_send_json_error(['message' => 'Cart not initialized.']);
    }

    // Sanitize and validate coupon code
    $coupon_code = sanitize_text_field($_POST['coupon_code'] ?? '');
    if (empty($coupon_code)) {
        wp_send_json_error(['message' => 'Please enter a coupon code.']);
    }

    try {
        $coupon_code = wc_format_coupon_code($coupon_code);

        // Remove any previously applied coupons
        foreach (WC()->cart->get_applied_coupons() as $applied_code) {
            WC()->cart->remove_coupon($applied_code);
        }

        // Try to apply the new coupon
        $applied = WC()->cart->apply_coupon($coupon_code);

        if (is_wp_error($applied)) {
            wp_send_json_error(['message' => $applied->get_error_message()]);
        }

        if (!$applied) {
            $notices = wc_get_notices('error');
            wc_clear_notices();
            $message = !empty($notices) ? wp_strip_all_tags($notices[0]['notice']) : 'Invalid or expired coupon.';
            wp_send_json_error(['message' => $message]);
        }

        // Update cart totals after applying the coupon
        WC()->cart->calculate_totals();
        wc_clear_notices();

        // Get HTML fragments for updated totals
        ob_start();
        wc_cart_totals_subtotal_html();
        $subtotal_html = ob_get_clean();

        ob_start();
        wc_cart_totals_shipping_html();
        $shipping_html = ob_get_clean();

        ob_start();
        wc_cart_totals_order_total_html();
        $total_html = ob_get_clean();

        ob_start();
        foreach (WC()->cart->get_coupons() as $code => $coupon) {
            echo wc_price(WC()->cart->get_coupon_discount_amount($code));
        }
        $discount_html = ob_get_clean();

        // Return success response with updated totals
        wp_send_json_success([
            'message'   => 'Coupon applied successfully!',
            'subtotal'  => $subtotal_html,
            'shipping'  => $shipping_html,
            'discount'  => $discount_html,
            'total'     => $total_html,
        ]);

    } catch (Exception $e) {
     //   error_log('Coupon Error: ' . $e->getMessage());
        wp_send_json_error(['message' => 'An error occurred while applying the coupon.']);
    }
}



/**
 * Output global AJAX vars for frontend JS
 */
add_action('wp_footer', function () {
    if (!is_admin()) {
        ?>
        <script>
            var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
            var apply_coupon_nonce = "<?php echo wp_create_nonce('apply_coupon_nonce'); ?>";
        </script>
        <?php
    }
});






/**
 * Register REST API to Fetch Available Coupons
 */
add_action('rest_api_init', function () {
    register_rest_route('custom/v1', '/coupons', [
        'methods'  => 'GET',
        'callback' => 'get_available_coupons',
        'permission_callback' => '__return_true',
    ]);
});

if (!function_exists('get_available_coupons')) {
    function get_available_coupons() {
        $args = [
            'post_type'      => 'shop_coupon',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
        ];

        $coupons = get_posts($args);
        $data = [];

        foreach ($coupons as $post) {
            $code       = strtoupper(get_the_title($post));
            $amount     = get_post_meta($post->ID, 'coupon_amount', true);
            $type       = get_post_meta($post->ID, 'discount_type', true);
            $free_ship  = get_post_meta($post->ID, 'free_shipping', true);
            $desc       = $post->post_excerpt ?: $post->post_content;

            $data[] = [
                'code'          => $code,
                'amount'        => $amount,
                'type'          => $type,
                'description'   => $desc,
                'free_shipping' => $free_ship === 'yes',
            ];
        }

        return rest_ensure_response($data);
    }
}

add_action('wp_ajax_custom_lost_password', 'custom_lost_password_handler');
add_action('wp_ajax_nopriv_custom_lost_password', 'custom_lost_password_handler');

function custom_lost_password_handler() {
    check_ajax_referer('lost_password', 'woocommerce-lost-password-nonce');

    $email = sanitize_email($_POST['user_login']);
    $user = get_user_by('email', $email);

    if ($user) {
        // Native WordPress function that triggers email
        $result = retrieve_password($user->user_login);

        if ($result === true) {
            wp_send_json_success('A reset link has been sent to your email.');
        } else {
            wp_send_json_error('Could not send reset email. Please try again.');
        }
    } else {
        wp_send_json_error('Invalid email address.');
    }
}
add_filter('retrieve_password_message', 'custom_reset_password_email_link', 10, 4);

function custom_reset_password_email_link($message, $key, $user_login, $user_data) {
    $default_url = network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user_login), 'login');
    $custom_url  = site_url('/reset-password/') . '?key=' . urlencode($key) . '&login=' . urlencode($user_login);

    // Replace the default URL with your custom one
    $message = str_replace($default_url, $custom_url, $message);

    return $message;
}
add_filter('retrieve_password_title', function($title, $user_login, $user_data) {
    return 'Reset Your Password for Junior Salon';
}, 10, 3);
add_filter('retrieve_password_message', function($message, $key, $user_login, $user_data) {
    $reset_url = site_url('/reset-password/') . '?key=' . urlencode($key) . '&login=' . urlencode($user_login);

    $site_name = get_bloginfo('name');
    $user_email = $user_data->user_email;

    $custom_message = <<<EOT
Hi {$user_login},

We received a request to reset your password for your account at {$site_name}.

Click the link below to set a new password:
{$reset_url}

If you didn’t request this change, you can safely ignore this email — your password will remain the same.

This request was made for the account associated with:
Email: {$user_email}
Site: {$site_name}

Thank you,  
The {$site_name} Team
EOT;

    return $custom_message;
}, 10, 4);

add_action('wp_ajax_add_shipping_address', 'save_additional_shipping_address');
function save_additional_shipping_address() {
    $user_id = get_current_user_id();
    if (!$user_id) wp_send_json_error(['message' => 'You must be logged in.']);

    $fields = ['first_name', 'last_name', 'company', 'address_1', 'address_2', 'city', 'state', 'postcode', 'country', 'phone'];
    $address_data = [];

    foreach ($fields as $field) {
        $address_data[$field] = sanitize_text_field($_POST[$field] ?? '');
    }

    // Save to a meta field like "additional_shipping_addresses"
    $existing = get_user_meta($user_id, 'additional_shipping_addresses', true);
    if (!is_array($existing)) $existing = [];

    $existing[] = $address_data;
    update_user_meta($user_id, 'additional_shipping_addresses', $existing);

    wp_send_json_success(['message' => 'Shipping address added successfully.']);
}
add_action('wp_ajax_edit_additional_shipping_address', 'edit_additional_shipping_address');
function edit_additional_shipping_address() {
    $user_id = get_current_user_id();
    $index = isset($_POST['index']) ? intval($_POST['index']) : -1;

    if (!$user_id || $index < 0) {
        wp_send_json_error(['message' => 'Invalid request.']);
    }

    $addresses = get_user_meta($user_id, 'additional_shipping_addresses', true);
    if (!is_array($addresses) || !array_key_exists($index, $addresses)) {
        wp_send_json_error(['message' => 'Address not found.']);
    }

    $fields = ['first_name', 'last_name', 'company', 'address_1', 'address_2', 'city', 'state', 'postcode', 'country', 'phone'];
    foreach ($fields as $field) {
        $addresses[$index][$field] = sanitize_text_field($_POST[$field] ?? '');
    }

    update_user_meta($user_id, 'additional_shipping_addresses', $addresses);
    wp_send_json_success(['message' => 'Shipping address updated successfully.']);
}

add_action('wp_ajax_set_default_shipping_address', 'set_default_shipping_address');
function set_default_shipping_address() {
    $user_id = get_current_user_id();
    if (!$user_id) {
        wp_send_json_error(['message' => 'You must be logged in.']);
    }

    $index = sanitize_text_field($_POST['index'] ?? '');
    // Validate: either "default" or numeric index
    if ($index !== 'default' && !is_numeric($index)) {
        wp_send_json_error(['message' => 'Invalid address index.']);
    }

    update_user_meta($user_id, 'default_shipping_index', $index);
    wp_send_json_success(['message' => 'Default shipping address updated.']);
}

// Remove item from cart
add_action('wp_ajax_remove_cart_item', 'remove_cart_item_handler');
add_action('wp_ajax_nopriv_remove_cart_item', 'remove_cart_item_handler');

function remove_cart_item_handler() {
    if (isset($_POST['cart_item_key'])) {
        $cart_item_key = sanitize_text_field($_POST['cart_item_key']);
        WC()->cart->remove_cart_item($cart_item_key);
        wp_send_json_success(); // Send success response
    } else {
        wp_send_json_error(array('message' => 'Failed to remove item.'));
    }
}


// Update Cart Item Quantity
add_action('wp_ajax_update_cart_item', 'update_cart_item_handler');
add_action('wp_ajax_nopriv_update_cart_item', 'update_cart_item_handler');

function update_cart_item_handler() {
    if (isset($_POST['cart_item_key']) && isset($_POST['quantity'])) {
        $cart_item_key = sanitize_text_field($_POST['cart_item_key']);
        $quantity = absint($_POST['quantity']);
        WC()->cart->set_quantity($cart_item_key, $quantity);
        wp_send_json_success(); // Send success response
    } else {
        wp_send_json_error(array('message' => 'Failed to update item.'));
    }
}
class Custom_Mega_Menu_Walker extends Walker_Nav_Menu {
    function start_lvl( &$output, $depth = 0, $args = null ) {
        $indent = str_repeat("\t", $depth);

        if ( $depth === 0 ) {
            // Full-width dropdown
            $output .= "\n$indent<div class=\"absolute left-0 top-full w-screen bg-white shadow-xl z-50 hidden group-hover:block\">\n";
            $output .= "<div class=\"max-w-[1300px] mx-auto grid grid-cols-4 gap-8 p-8\">\n";
        } else {
            $output .= "\n$indent<ul class=\"mt-2 space-y-2\">\n";
        }
    }

    function end_lvl( &$output, $depth = 0, $args = null ) {
        if ( $depth === 0 ) {
            $output .= "</div></div>\n"; // close grid and outer div
        } else {
            $output .= "</ul>\n";
        }
    }

    function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
        $classes = implode(' ', $item->classes ?? []);
        $is_active = strpos($classes, 'current-menu-item') !== false;

        if ( $depth === 0 ) {
            $output .= '<li class="relative group">';
            $output .= '<a href="' . esc_url($item->url) . '" class="inline-block px-4 py-2 text-sm font-semibold text-white hover:text-yellow-400 transition">';
            $output .= esc_html($item->title);
            $output .= '</a>';
        } else {
            $output .= '<div>';
            $output .= '<a href="' . esc_url($item->url) . '" class="block text-sm text-gray-700 hover:text-yellow-600 font-medium">';
            $output .= esc_html($item->title);
            $output .= '</a>';
            $output .= '</div>';
        }

        if ( $depth === 0 ) {
            $output .= '</li>';
        }
    }
}
add_action('wp_ajax_woocommerce_ajax_add_to_cart', 'handle_ajax_add_to_cart');
add_action('wp_ajax_nopriv_woocommerce_ajax_add_to_cart', 'handle_ajax_add_to_cart');

function handle_ajax_add_to_cart() {
    check_ajax_referer('add-to-cart', 'security');

    $product_id = apply_filters('woocommerce_add_to_cart_product_id', absint($_POST['product_id']));
    $quantity = empty($_POST['quantity']) ? 1 : wc_stock_amount($_POST['quantity']);
    $variation_id = isset($_POST['variation_id']) ? absint($_POST['variation_id']) : '';
    $variations = [];

    foreach ($_POST as $key => $value) {
        if (strpos($key, 'attribute_') === 0) {
            $variations[$key] = wc_clean(wp_unslash($value));
        }
    }

    $passed_validation = apply_filters('woocommerce_add_to_cart_validation', true, $product_id, $quantity, $variation_id, $variations);
    $product_status = get_post_status($product_id);

    if ($passed_validation && 'publish' === $product_status) {
        WC()->cart->add_to_cart($product_id, $quantity, $variation_id, $variations);
        do_action('woocommerce_ajax_added_to_cart', $product_id);

        WC_AJAX::get_refreshed_fragments();
    } else {
        wp_send_json([
            'success' => false,
            'data' => [
                'error' => true,
                'product_url' => apply_filters('woocommerce_cart_redirect_after_error', get_permalink($product_id), $product_id),
                'message' => __('Please choose product options before adding to cart.', 'woocommerce')
            ]
        ]);
    }

    wp_die();
}

add_action('wp_enqueue_scripts', 'enqueue_woocommerce_scripts');
function enqueue_woocommerce_scripts() {
    if (function_exists('is_woocommerce') && is_woocommerce()) {
        wp_enqueue_script('wc-add-to-cart');
        wp_enqueue_script('wc-add-to-cart-variation');
    }
}


add_filter('yith_wcwl_add_to_wishlist_label', 'custom_wishlist_label_add', 10, 2);
add_filter('yith_wcwl_added_to_wishlist_label', 'custom_wishlist_label_added', 10, 2);
add_filter('yith_wcwl_exists_in_wishlist_label', 'custom_wishlist_label_exists', 10, 2);

function custom_wishlist_label_add($label, $product_id) {
    return 'Move to Wishlist'; // Before adding
}

function custom_wishlist_label_added($label, $product_id) {
    return 'Added to Wishlist'; // After added
}

function custom_wishlist_label_exists($label, $product_id) {
    return 'Already in Wishlist'; // If item exists
}


/**
 * Junior Salon – register translatable UI strings
 * Runs after WPML finished loading.
 */
function jsalon_register_strings() {

    $strings = [
        // Hero / landing
        'Shop by Category',
        'Shop All Products',
        'Shop by Brands',
        'Explore Trending',
        'Shop by Age',
        'Popular Picks',
        'NEW FOR KIDS',

        // Filter drawer
        'FILTER & ORDER',
        'SORT BY',
        'Category',
        'Color',
        'Size',
        'Age',
        'Brand',
        'Price',
        'Apply Filters',

        // Buttons (WooCommerce domain!)
        'Add to cart',
        'Buy Now',
    ];

    foreach ( $strings as $str ) {
        /**
         * 1) Choose the correct domain:
         *    – Everything you created yourself → "junior-salon"
         *    – Core WC buttons               → "woocommerce"
         *
         * 2) Use the string *itself* as the $name and $value
         *    (or give it a shorter slug-like $name if you prefer).
         */
        $domain = in_array( $str, [ 'Add to cart', 'Buy Now' ], true )
            ? 'woocommerce'
            : 'junior-salon';

        do_action( 'wpml_register_single_string', $domain, $str, $str );
    }
}
add_action( 'wpml_loaded', 'jsalon_register_strings', 20 ); // ← runs after WPML is ready



function my_theme_load_theme_textdomain() {
    load_theme_textdomain( 'junior-salon', get_template_directory() . '/languages' );
}
add_action( 'after_setup_theme', 'my_theme_load_theme_textdomain' );



function register_custom_wpml_strings() {
    do_action( 'wpml_register_single_string', 'Header Texts', 'About us', 'About us' );
    do_action( 'wpml_register_single_string', 'Header Texts', 'Get in Touch', 'Get in Touch' );
    do_action( 'wpml_register_single_string', 'Header Texts', 'Have a question or need help? Our team is here to assist you.', 'Have a question or need help? Our team is here to assist you.' );
    do_action( 'wpml_register_single_string', 'Header Texts', 'Frequently Asked Questions', 'Frequently Asked Questions' );
    do_action( 'wpml_register_single_string', 'Header Texts', 'Contact Us', 'Contact Us' );

    do_action( 'wpml_register_single_string', 'Header Texts', 'Customer Care', 'Customer Care' );
    do_action( 'wpml_register_single_string', 'Header Texts', 'WhatsApp', 'WhatsApp' );
    do_action( 'wpml_register_single_string', 'Header Texts', 'Phone', 'Phone' );
    do_action( 'wpml_register_single_string', 'Header Texts', 'Email', 'Email' );
 do_action( 'wpml_register_single_string', 'Header Texts', 'STAY IN TOUCH', 'STAY IN TOUCH' );

do_action( 'wpml_register_single_string', 'Header Texts', 'Reset Your Password', 'Reset Your Password' );
do_action( 'wpml_register_single_string', 'Header Texts', "Enter your email address and we'll send you a link to reset your password", "Enter your email address and we'll send you a link to reset your password" );
do_action( 'wpml_register_single_string', 'Header Texts', 'Email address', 'Email address' );
do_action( 'wpml_register_single_string', 'Header Texts', 'Send Reset Link', 'Send Reset Link' );
do_action( 'wpml_register_single_string', 'Header Texts', 'Back to Login', 'Back to Login' );
do_action( 'wpml_register_single_string', 'Header Texts', 'LOGIN TO JUNIORSALON', 'LOGIN TO JUNIORSALON' );
do_action( 'wpml_register_single_string', 'Header Texts', 'Password', 'Password' );
do_action( 'wpml_register_single_string', 'Header Texts', 'Incorrect email or password', 'Incorrect email or password' );
do_action( 'wpml_register_single_string', 'Header Texts', 'Don’t have an account?', 'Don’t have an account?' );
do_action( 'wpml_register_single_string', 'Header Texts', 'My Account', 'My Account' );
do_action( 'wpml_register_single_string', 'Header Texts', 'My Orders', 'My Orders' );
do_action( 'wpml_register_single_string', 'Header Texts', 'My Profile', 'My Profile' );
do_action( 'wpml_register_single_string', 'Header Texts', 'My Address Book', 'My Address Book' );
do_action( 'wpml_register_single_string', 'Header Texts', 'Bank Details', 'Bank Details' );
do_action( 'wpml_register_single_string', 'Header Texts', 'Change Password', 'Change Password' );
do_action( 'wpml_register_single_string', 'Header Texts', 'Logout', 'Logout' );
do_action( 'wpml_register_single_string', 'Header Texts', 'Invalid section', 'Invalid section' );
do_action( 'wpml_register_single_string', 'Header Texts', 'Sign in', 'Sign in' );
do_action( 'wpml_register_single_string', 'Order Texts', 'Placed on', 'Placed on' );
do_action( 'wpml_register_single_string', 'Order Texts', 'Order', 'Order' );
do_action( 'wpml_register_single_string', 'Order Texts', 'Items', 'Items' );
do_action( 'wpml_register_single_string', 'Order Texts', 'Billing Address', 'Billing Address' );
do_action( 'wpml_register_single_string', 'Order Texts', 'Shipping Address', 'Shipping Address' );
do_action( 'wpml_register_single_string', 'Order Texts', 'Payment Method', 'Payment Method' );
do_action( 'wpml_register_single_string', 'Order Texts', 'Total', 'Total' );
do_action( 'wpml_register_single_string', 'Order Texts', 'Orders', 'Orders' );
do_action( 'wpml_register_single_string', 'Order Texts', 'All Orders', 'All Orders' );
do_action( 'wpml_register_single_string', 'Order Texts', 'Last 1 Month', 'Last 1 Month' );
do_action( 'wpml_register_single_string', 'Order Texts', 'Last 6 Months', 'Last 6 Months' );
do_action( 'wpml_register_single_string', 'Order Texts', 'Last 12 Months', 'Last 12 Months' );
do_action( 'wpml_register_single_string', 'Order Texts', 'View order details', 'View order details' );
do_action( 'wpml_register_single_string', 'Order Texts', 'No orders found in this period.', 'No orders found in this period.' );
do_action( 'wpml_register_single_string', 'Auth Texts', 'Privacy Policy', 'Privacy Policy' );
do_action( 'wpml_register_single_string', 'Auth Texts', 'CREATE YOUR ACCOUNT', 'CREATE YOUR ACCOUNT' );
do_action( 'wpml_register_single_string', 'Auth Texts', 'First name', 'First name' );
do_action( 'wpml_register_single_string', 'Auth Texts', 'Last name', 'Last name' );
do_action( 'wpml_register_single_string', 'Auth Texts', 'Email address', 'Email address' );
do_action( 'wpml_register_single_string', 'Auth Texts', 'Phone number', 'Phone number' );
do_action( 'wpml_register_single_string', 'Auth Texts', 'Password', 'Password' );
do_action( 'wpml_register_single_string', 'Auth Texts', 'Confirm password', 'Confirm password' );
do_action( 'wpml_register_single_string', 'Auth Texts', 'Create account', 'Create account' );
do_action( 'wpml_register_single_string', 'Auth Texts', 'By creating an account, you’re accepting our', 'By creating an account, you’re accepting our' );
do_action( 'wpml_register_single_string', 'Auth Texts', 'Terms & Conditions', 'Terms & Conditions' );
do_action( 'wpml_register_single_string', 'Auth Texts', 'and', 'and' );
do_action( 'wpml_register_single_string', 'Auth Texts', 'Already have an account?', 'Already have an account?' );
do_action( 'wpml_register_single_string', 'Auth Texts', "You're already logged in.", "You're already logged in." );
do_action( 'wpml_register_single_string', 'Auth Texts', 'Go to My Account', 'Go to My Account' );
do_action( 'wpml_register_single_string', 'Auth Texts', 'Sign in', 'Sign in' );
do_action( 'wpml_register_single_string', 'Auth Texts', 'Set a New Password', 'Set a New Password' );
do_action( 'wpml_register_single_string', 'Auth Texts', 'New Password', 'New Password' );
do_action( 'wpml_register_single_string', 'Auth Texts', 'Confirm New Password', 'Confirm New Password' );
do_action( 'wpml_register_single_string', 'Auth Texts', 'Reset Password', 'Reset Password' );
do_action( 'wpml_register_single_string', 'Auth Texts', 'Return Policy', 'Return Policy' );
do_action( 'wpml_register_single_string', 'Auth Texts', 'Terms of Service', 'Terms of Service' );
do_action( 'wpml_register_single_string', 'Auth Texts', 'MY WISHLIST', 'MY WISHLIST' );
do_action( 'wpml_register_single_string', 'Auth Texts', 'ITEMS', 'ITEMS' );
do_action( 'wpml_register_single_string', 'Auth Texts', 'Continue shopping', 'Continue shopping' );
do_action( 'wpml_register_single_string', 'Cart Texts', 'Apply Coupon', 'Apply Coupon' );
do_action( 'wpml_register_single_string', 'Cart Texts', 'Remove', 'Remove' );
do_action( 'wpml_register_single_string', 'Cart Texts', 'SHOPPING CART', 'SHOPPING CART' );
do_action( 'wpml_register_single_string', 'Cart Texts', 'Showing', 'Showing' );
do_action( 'wpml_register_single_string', 'Cart Texts', 'products you added', 'products you added' );
do_action( 'wpml_register_single_string', 'Cart Texts', 'ORDER SUMMARY', 'ORDER SUMMARY' );
do_action( 'wpml_register_single_string', 'Cart Texts', 'Total Price (Item)', 'Total Price (Item)' );
do_action( 'wpml_register_single_string', 'Cart Texts', 'Shipping Tax & Fee', 'Shipping Tax & Fee' );
do_action( 'wpml_register_single_string', 'Cart Texts', 'Grand Total', 'Grand Total' );
do_action( 'wpml_register_single_string', 'Cart Texts', 'JuniorSalon Protects Your Payment/Information', 'JuniorSalon Protects Your Payment/Information' );
do_action( 'wpml_register_single_string', 'Cart Texts', 'Encrypted SSL secure checkout', 'Encrypted SSL secure checkout' );
do_action( 'wpml_register_single_string', 'Cart Texts', 'We respect your privacy', 'We respect your privacy' );


do_action( 'wpml_register_single_string', 'Checkout Texts', 'Shopping Bag', 'Shopping Bag' );
do_action( 'wpml_register_single_string', 'Checkout Texts', 'You can proceed as a guest. We\'ll only ask for necessary details on the next step.', 'You can proceed as a guest. We\'ll only ask for necessary details on the next step.' );
do_action( 'wpml_register_single_string', 'Checkout Texts', 'Guest Checkout', 'Guest Checkout' );
do_action( 'wpml_register_single_string', 'Checkout Texts', 'Your Email Is', 'Your Email Is' );
do_action( 'wpml_register_single_string', 'Checkout Texts', 'Remember me', 'Remember me' );
do_action( 'wpml_register_single_string', 'Checkout Texts', 'Forgot password?', 'Forgot password?' );
do_action( 'wpml_register_single_string', 'Checkout Texts', 'Modify', 'Modify' );
do_action( 'wpml_register_single_string', 'Checkout Texts', 'Color', 'Color' );

do_action( 'wpml_register_single_string', 'Checkout Texts', 'Quantity', 'Quantity' );

do_action( 'wpml_register_single_string', 'Cart Texts', 'Your cart is empty.', 'Your cart is empty.' );

do_action( 'wpml_register_single_string', 'Checkout Texts', 'Billing Address', 'Billing Address' );
do_action( 'wpml_register_single_string', 'Checkout Texts', 'Billing address is same as shipping address', 'Billing address is same as shipping address' );
do_action( 'wpml_register_single_string', 'Checkout Texts', 'Not logged in.', 'Not logged in.' );
do_action( 'wpml_register_single_string', 'Checkout Texts', 'Save', 'Save' );
do_action( 'wpml_register_single_string', 'Checkout Texts', 'Cancel', 'Cancel' );
do_action( 'wpml_register_single_string', 'Checkout Texts', 'Apply', 'Apply' );
do_action( 'wpml_register_single_string', 'Checkout Texts', 'Subtotal', 'Subtotal' );
do_action( 'wpml_register_single_string', 'Checkout Texts', 'Delivery', 'Delivery' );
do_action( 'wpml_register_single_string', 'Checkout Texts', 'Your order qualifies for FREE shipping', 'Your order qualifies for FREE shipping' );
do_action( 'wpml_register_single_string', 'Checkout Texts', 'Duties and taxes included', 'Duties and taxes included' );
do_action( 'wpml_register_single_string', 'Checkout Texts', 'Place Order', 'Place Order' );
do_action( 'wpml_register_single_string', 'Checkout Texts', 'Payment Methods', 'Payment Methods' );
do_action( 'wpml_register_single_string', 'Checkout Texts', 'AED 10 fee applies', 'AED 10 fee applies' );

}
add_action( 'init', 'register_custom_wpml_strings' );
