<?php
/**
 * Theme Functions for Junior Salon
 *
 * This file contains all custom theme functionality including:
 * - Performance optimizations
 * - Asset enqueuing and dequeuing
 * - Custom post types
 * - WooCommerce customizations
 * - AJAX handlers
 * - Theme settings and customizer options
 * - Shortcodes for product filtering
 * - Mega menu support
 *
 * @package JuniorSalon
 */

/**
 * Performance Optimizations
 *
 * Optimizes WordPress performance by setting execution limits, removing emojis,
 * enabling lazy loading, and deferring non-essential scripts.
 */
function junior_salon_performance_optimizations() {
    // Set execution limits
    set_time_limit(300); // 5 minutes
    ini_set('memory_limit', '256M');

    // Remove WP emojis
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('wp_print_styles', 'print_emoji_styles');
    remove_action('admin_print_scripts', 'print_emoji_detection_script');
    remove_action('admin_print_styles', 'print_emoji_styles');

    // Enable lazy loading for images
    add_filter('wp_lazy_loading_enabled', '__return_true');
}
add_action('init', 'junior_salon_performance_optimizations');

/**
 * Defer Non-Essential Scripts
 *
 * Adds defer attribute to non-jQuery JavaScript files to improve page load time.
 *
 * @param string $tag    The script tag.
 * @param string $handle The script handle.
 * @return string Modified script tag.
 */
function junior_salon_defer_scripts($tag, $handle) {
    if (!is_admin() && strpos($tag, '.js') !== false && !str_contains($tag, 'jquery')) {
        return str_replace(' src', ' defer src', $tag);
    }
    return $tag;
}
add_filter('script_loader_tag', 'junior_salon_defer_scripts', 10, 2);

/**
 * Enqueue Scripts and Styles
 *
 * Enqueues all necessary styles and scripts for the theme, including Tailwind CSS,
 * Font Awesome, SwiperJS, AlpineJS, and WooCommerce scripts.
 */
function junior_salon_enqueue_assets() {
    // Enqueue Tailwind CSS
    wp_enqueue_style('tailwindcss', get_template_directory_uri() . '/dist/styles.css', [], null);

    // Enqueue main CSS
    wp_enqueue_style('main-css', get_template_directory_uri() . '/assets/css/main.css', [], null);

    // Enqueue Font Awesome
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css', [], '6.0.0-beta3');

    // Enqueue SwiperJS
    wp_enqueue_style('swiper-css', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css', [], '11');
    wp_enqueue_script('swiper-js', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js', [], '11', true);

    // Enqueue AlpineJS for cart
    wp_enqueue_script('alpinejs', 'https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js', [], '3', true);

    // Enqueue WooCommerce variation script on product pages
    if (is_page() || is_singular()) {
        wp_enqueue_script('wc-add-to-cart-variation');
    }
}
add_action('wp_enqueue_scripts', 'junior_salon_enqueue_assets');

/**
 * Dequeue Unnecessary Assets
 *
 * Removes unnecessary scripts and styles to reduce page load time.
 */
function junior_salon_dequeue_assets() {
    if (!is_admin()) {
        // Dequeue specific scripts/styles on non-specific pages
        if (!is_page('specific-page')) {
            wp_dequeue_script('n2-ss-front');
            wp_dequeue_style('smartslider-frontend');
        }
        wp_dequeue_script('yith-wcwl');
        wp_dequeue_style('yith-wcwl-main');
        wp_dequeue_style('wc-block-style');
        if (!is_user_logged_in()) {
            wp_dequeue_style('dashicons');
        }
    }
}
add_action('wp_enqueue_scripts', 'junior_salon_dequeue_assets', 100);

/**
 * Theme Setup
 *
 * Configures theme support for WooCommerce, custom logo, and menu locations.
 */
function junior_salon_theme_setup() {
    // Add WooCommerce support
    add_theme_support('woocommerce');

    // Custom logo support
    add_theme_support('custom-logo', [
        'height'      => 100,
        'width'       => 300,
        'flex-width'  => true,
        'flex-height' => true,
    ]);

    // Register menu locations
    register_nav_menus([
        'primary'  => __('Primary Menu', 'junior-salon'),
        'footer1'  => __('Footer Menu 1', 'junior-salon'),
        'footer2'  => __('Footer Menu 2', 'junior-salon'),
        'footer3'  => __('Footer Menu 3', 'junior-salon'),
    ]);
}
add_action('after_setup_theme', 'junior_salon_theme_setup');

/**
 * Custom Post Types
 *
 * Registers custom post types for home features banner and testimonials.
 */
function junior_salon_register_post_types() {
    // Home Features Banner
    register_post_type('home-features-banner', [
        'label'        => __('Home Features Banner', 'junior-salon'),
        'public'       => true,
        'show_in_menu' => 'homepage-main-menu',
        'supports'     => ['title', 'editor', 'thumbnail'],
        'show_in_rest' => true,
    ]);

    // Testimonials
    register_post_type('testimonial', [
        'labels'       => [
            'name'          => __('Testimonials', 'junior-salon'),
            'singular_name' => __('Testimonial', 'junior-salon'),
        ],
        'public'       => true,
        'has_archive'  => false,
        'supports'     => ['title', 'editor', 'thumbnail'],
        'show_in_rest' => true,
    ]);
}
add_action('init', 'junior_salon_register_post_types');

/**
 * Admin Menu
 *
 * Adds a custom admin menu page for homepage settings.
 */
function junior_salon_admin_menu() {
    add_menu_page(
        __('Homepage', 'junior-salon'),
        __('Homepage', 'junior-salon'),
        'manage_options',
        'homepage-main-menu',
        '__return_null',
        'dashicons-admin-home',
        3
    );
}
add_action('admin_menu', 'junior_salon_admin_menu');

/**
 * Customizer Settings
 *
 * Adds customizer settings for logo, footer, social media, and top bar.
 *
 * @param WP_Customize_Manager $wp_customize Customizer object.
 */
function junior_salon_customizer_settings($wp_customize) {
    // Logo Settings
    $wp_customize->add_setting('logo_width', [
        'default'           => '150',
        'sanitize_callback' => 'absint',
    ]);
    $wp_customize->add_control('logo_width', [
        'label'       => __('Logo Width (px)', 'junior-salon'),
        'section'     => 'title_tagline',
        'type'        => 'number',
        'input_attrs' => [
            'min'  => 50,
            'max'  => 600,
            'step' => 1,
        ],
    ]);

    $wp_customize->add_setting('logo_height', [
        'default'           => 'auto',
        'sanitize_callback' => function($value) {
            return $value === 'auto' ? 'auto' : absint($value);
        },
    ]);
    $wp_customize->add_control('logo_height', [
        'label'       => __('Logo Height (px or "auto")', 'junior-salon'),
        'section'     => 'title_tagline',
        'type'        => 'text',
        'description' => __('Use "auto" to keep original ratio.', 'junior-salon'),
    ]);

    // Footer Settings
    $wp_customize->add_section('junior_salon_footer_section', [
        'title'    => __('Footer Settings', 'junior-salon'),
        'priority' => 130,
    ]);
    $wp_customize->add_setting('junior_salon_footer_copyright', [
        'default'           => '© ' . date('Y') . ' Junior Salon. All rights reserved.',
        'sanitize_callback' => 'sanitize_text_field',
    ]);
    $wp_customize->add_control('junior_salon_footer_copyright', [
        'label'   => __('Footer Copyright Text', 'junior-salon'),
        'section' => 'junior_salon_footer_section',
        'type'    => 'text',
    ]);

    // Social Media Settings
    $wp_customize->add_section('social_media_section', [
        'title'    => __('Social Media', 'junior-salon'),
        'priority' => 30,
    ]);
    $social_media = ['facebook_url', 'twitter_url', 'instagram_url'];
    foreach ($social_media as $social) {
        $wp_customize->add_setting($social, [
            'default'   => '',
            'transport' => 'refresh',
        ]);
        $wp_customize->add_control($social, [
            'label'   => __(ucfirst(str_replace('_url', ''), $social) . ' URL', 'junior-salon'),
            'section' => 'social_media_section',
            'type'    => 'url',
        ]);
    }

    // Top Bar Settings
    $wp_customize->add_section('top_bar_section', [
        'title'    => __('Top Bar Settings', 'junior-salon'),
        'priority' => 30,
    ]);
    $wp_customize->add_setting('top_bar_tagline', [
        'default'   => 'Super Save',
        'transport' => 'refresh',
    ]);
    $wp_customize->add_control('top_bar_tagline', [
        'label'   => __('Tagline', 'junior-salon'),
        'section' => 'top_bar_section',
        'type'    => 'text',
    ]);
    $wp_customize->add_setting('top_bar_shop_url', [
        'default'   => home_url('/shop'),
        'transport' => 'refresh',
    ]);
    $wp_customize->add_control('top_bar_shop_url', [
        'label'   => __('Shop Now Button URL', 'junior-salon'),
        'section' => 'top_bar_section',
        'type'    => 'url',
    ]);
    $wp_customize->add_setting('top_bar_timer_duration', [
        'default'   => 10,
        'transport' => 'refresh',
    ]);
    $wp_customize->add_control('top_bar_timer_duration', [
        'label'       => __('Timer Duration (Hours)', 'junior-salon'),
        'section'     => 'top_bar_section',
        'type'        => 'number',
        'input_attrs' => [
            'min' => 1,
            'max' => 24,
        ],
    ]);
}
add_action('customize_register', 'junior_salon_customizer_settings');

/**
 * WooCommerce Customizations
 *
 * Customizes WooCommerce by removing default actions and adding custom ones.
 */
function junior_salon_woocommerce_customizations() {
    // Remove default WooCommerce actions
    remove_action('woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20);
    remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20);
    remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40);

    // Remove additional information tab
    add_filter('woocommerce_product_tabs', function($tabs) {
        unset($tabs['additional_information']);
        return $tabs;
    }, 98);

    // Add custom actions
    add_action('woocommerce_single_product_summary', 'show_woocommerce_brand_above_title', 4);
    add_action('woocommerce_single_product_summary', 'add_duties_notice_under_price', 11);
    add_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30);
    add_action('woocommerce_before_add_to_cart_button', 'custom_separator_before_cart', 15);
    add_action('woocommerce_after_single_product', 'add_divider_after_buy_now_button', 15);
}
add_action('init', 'junior_salon_woocommerce_customizations');

/**
 * WooCommerce Template Overrides
 *
 * Overrides default WooCommerce templates with custom ones.
 *
 * @param string $template The default template path.
 * @return string The custom template path.
 */
function junior_salon_woocommerce_templates($template) {
    if (is_singular('product')) {
        $custom = locate_template('woocommerce/single-product.php');
        if ($custom) {
            return $custom;
        }
    }
    return $template;
}
add_filter('template_include', 'junior_salon_woocommerce_templates', 99);

/**
 * Add Products Page Template
 *
 * Adds a custom page template for products.
 *
 * @param array $templates The list of page templates.
 * @return array Modified list of page templates.
 */
function junior_salon_page_templates($templates) {
    $templates['page-products.php'] = 'Products Page Template';
    return $templates;
}
add_filter('theme_page_templates', 'junior_salon_page_templates');

/**
 * Load Products by Category
 *
 * Retrieves product IDs for a given category or sale status.
 *
 * @param string $cat The category slug or 'sale'.
 * @return array List of product IDs.
 */
function load_products_by_category($cat) {
    $args = [
        'post_type'      => 'product',
        'posts_per_page' => 10,
        'post_status'    => 'publish',
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
    } elseif (!empty($cat)) {
        $args['tax_query'] = [
            [
                'taxonomy' => 'product_cat',
                'field'    => 'slug',
                'terms'    => $cat,
            ],
        ];
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

/**
 * AJAX Handlers
 */

/**
 * Load Tab Products
 *
 * AJAX handler to load products for a specific category tab.
 */
function load_tab_products() {
    $cat = sanitize_text_field($_GET['cat'] ?? '');
    if (empty($cat)) {
        wp_send_json_error('Invalid category.');
    }

    $products = load_products_by_category($cat);
    include get_template_directory() . '/components/products/product-grid.php';
    wp_die();
}
add_action('wp_ajax_load_tab_products', 'load_tab_products');
add_action('wp_ajax_nopriv_load_tab_products', 'load_tab_products');

/**
 * Load More Products
 *
 * AJAX handler to load additional products with pagination.
 */
function load_more_products_ajax_handler() {
    $paged = isset($_POST['page']) ? intval($_POST['page']) : 1;
    $args = [
        'post_type'      => 'product',
        'posts_per_page' => 15,
        'paged'          => $paged,
    ];

    $loop = new WP_Query($args);
    ob_start();

    if ($loop->have_posts()) :
        while ($loop->have_posts()) : $loop->the_post();
            global $product;
            $attachment_ids = $product->get_gallery_image_ids();
            $hover_image_id = $attachment_ids[0] ?? null;
            $thumbnail_url = get_the_post_thumbnail_url(get_the_ID(), 'medium') ?: 'https://via.placeholder.com/300x300';
            $webp_url = str_replace(['.jpg', '.png'], '.webp', $thumbnail_url);
            ?>
            <div class="bg-white shadow-md rounded-lg overflow-hidden p-4 flex flex-col">
                <div class="relative group w-full aspect-square overflow-hidden">
                    <picture>
                        <source srcset="<?php echo esc_url($webp_url); ?>" type="image/webp">
                        <img src="<?php echo esc_url($thumbnail_url); ?>" 
                             class="w-full h-full object-cover transition-opacity duration-300 group-hover:opacity-0" 
                             alt="<?php the_title_attribute(); ?>" 
                             loading="lazy">
                    </picture>
                    <?php if ($hover_image_id) : 
                        $hover_url = wp_get_attachment_image_url($hover_image_id, 'medium');
                        $hover_webp_url = str_replace(['.jpg', '.png'], '.webp', $hover_url);
                    ?>
                        <picture class="absolute inset-0 opacity-0 group-hover:opacity-100">
                            <source srcset="<?php echo esc_url($hover_webp_url); ?>" type="image/webp">
                            <img src="<?php echo esc_url($hover_url); ?>" 
                                 class="w-full h-full object-cover transition-opacity duration-300" 
                                 alt="<?php the_title_attribute(); ?>" 
                                 loading="lazy">
                        </picture>
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

                <div class="woocommerce mt-4">
                    <?php
                    if ($product->is_type('simple')) {
                        woocommerce_simple_add_to_cart();
                    } elseif ($product->is_type('variable')) {
                        woocommerce_variable_add_to_cart();
                    }
                    ?>
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

/**
 * Fetch Sorted Products
 *
 * AJAX handler to fetch products sorted by various criteria.
 */
function fetch_sorted_products() {
    $sort = isset($_GET['sort']) ? sanitize_text_field($_GET['sort']) : '';
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $posts_per_page = 12; // Optimized for performance

    $cache_key = 'sorted_products_' . md5($sort . $page);
    $cached = get_transient($cache_key);

    if (false !== $cached) {
        echo $cached;
        wp_die();
    }

    $args = [
        'post_type'      => 'product',
        'posts_per_page' => $posts_per_page,
        'paged'          => $page,
    ];

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
        default:
            $args['orderby'] = 'menu_order date';
            $args['order']   = 'DESC';
    }

    $loop = new WP_Query($args);
    $product_ids = wp_list_pluck($loop->posts, 'ID');
    $brand_terms = wp_get_object_terms($product_ids, 'product_brand', ['fields' => 'all_with_object_id']);
    $brands_by_product = [];
    foreach ($brand_terms as $term) {
        $brands_by_product[$term->object_id] = $term;
    }

    ob_start();

    if ($loop->have_posts()) :
        while ($loop->have_posts()) : $loop->the_post();
            global $product;
            $attachment_ids = $product->get_gallery_image_ids();
            $hover_image_id = $attachment_ids[0] ?? null;
            $thumbnail_url = get_the_post_thumbnail_url(get_the_ID(), 'medium') ?: 'https://via.placeholder.com/300x300';
            $webp_url = str_replace(['.jpg', '.png'], '.webp', $thumbnail_url);
            ?>
            <div class="bg-white shadow-md rounded-lg overflow-hidden p-4 flex flex-col">
                <div class="relative group w-full aspect-square overflow-hidden">
                    <picture>
                        <source srcset="<?php echo esc_url($webp_url); ?>" type="image/webp">
                        <img src="<?php echo esc_url($thumbnail_url); ?>" 
                             class="w-full h-full object-cover transition-opacity duration-300 group-hover:opacity-0" 
                             alt="<?php the_title_attribute(); ?>" 
                             loading="lazy">
                    </picture>
                    <?php if ($hover_image_id) : 
                        $hover_url = wp_get_attachment_image_url($hover_image_id, 'medium');
                        $hover_webp_url = str_replace(['.jpg', '.png'], '.webp', $hover_url);
                    ?>
                        <picture class="absolute inset-0 opacity-0 group-hover:opacity-100">
                            <source srcset="<?php echo esc_url($hover_webp_url); ?>" type="image/webp">
                            <img src="<?php echo esc_url($hover_url); ?>" 
                                 class="w-full h-full object-cover transition-opacity duration-300" 
                                 alt="<?php the_title_attribute(); ?>" 
                                 loading="lazy">
                        </picture>
                    <?php endif; ?>
                </div>
                <?php echo do_shortcode('[yith_wcwl_add_to_wishlist]'); ?>

                <?php
                $brand = $brands_by_product[get_the_ID()] ?? null;
                if ($brand) :
                ?>
                    <div class="text-sm text-gray-500 mb-1"><?php echo esc_html($brand->name); ?></div>
                <?php endif; ?>

                <h2 class="text-md font-semibold mb-2">
                    <a href="<?php the_permalink(); ?>" class="hover:underline"><?php the_title(); ?></a>
                </h2>

                <div class="mt-auto text-lg font-bold text-gray-800">
                    <?php echo $product->get_price_html(); ?>
                </div>

                <div class="woocommerce mt-4">
                    <?php
                    if ($product->is_type('simple')) {
                        woocommerce_simple_add_to_cart();
                    } elseif ($product->is_type('variable')) {
                        woocommerce_variable_add_to_cart();
                    }
                    ?>
                </div>
            </div>
            <?php
        endwhile;

        if ($loop->max_num_pages > $page) : ?>
            <div class="text-center mt-6">
                <button class="load-more-btn bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
                        data-next-page="<?php echo esc_attr($page + 1); ?>">
                    Load More
                </button>
            </div>
        <?php endif;
    else :
        echo '<p class="text-center text-gray-600">No products found.</p>';
    endif;

    wp_reset_postdata();
    $output = ob_get_clean();

    set_transient($cache_key, $output, HOUR_IN_SECONDS);
    echo $output;
    wp_die();
}
add_action('wp_ajax_fetch_sorted_products', 'fetch_sorted_products');
add_action('wp_ajax_nopriv_fetch_sorted_products', 'fetch_sorted_products');

/**
 * Fetch Sorted Products by Category
 *
 * AJAX handler to fetch products by category with sorting and pagination.
 */
function fetch_sorted_products_bycategory() {
    $taxonomy = isset($_GET['taxonomy']) ? sanitize_text_field($_GET['taxonomy']) : 'product_cat';
    $sort = isset($_GET['sort']) ? sanitize_text_field($_GET['sort']) : '';
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $term_id = isset($_GET['term_id']) ? intval($_GET['term_id']) : 0;
    $posts_per_page = 12;

    if (!taxonomy_exists($taxonomy)) {
        wp_send_json_error(['message' => 'Invalid taxonomy']);
    }

    $cache_key = 'sorted_products_bycategory_' . md5($taxonomy . $term_id . $sort . $page);
    $cached = get_transient($cache_key);

    if (false !== $cached) {
        echo $cached;
        wp_die();
    }

    $args = [
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'posts_per_page' => $posts_per_page,
        'paged'          => $page,
        'tax_query'      => [
            [
                'taxonomy' => $taxonomy,
                'field'    => 'term_id',
                'terms'    => $term_id,
                'operator' => 'IN',
            ],
        ],
    ];

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
        default:
            $args['orderby'] = 'menu_order date';
            $args['order']   = 'DESC';
    }

    $loop = new WP_Query($args);
    $product_ids = wp_list_pluck($loop->posts, 'ID');
    $brand_terms = wp_get_object_terms($product_ids, 'product_brand', ['fields' => 'all_with_object_id']);
    $brands_by_product = [];
    foreach ($brand_terms as $term) {
        $brands_by_product[$term->object_id] = $term;
    }

    ob_start();

    if ($loop->have_posts()) :
        while ($loop->have_posts()) : $loop->the_post();
            global $product;
            $attachment_ids = $product->get_gallery_image_ids();
            $hover_image_id = $attachment_ids[0] ?? null;
            $thumbnail_url = get_the_post_thumbnail_url(get_the_ID(), 'medium') ?: 'https://via.placeholder.com/300x300';
            $webp_url = str_replace(['.jpg', '.png'], '.webp', $thumbnail_url);
            ?>
            <div class="bg-white shadow-md rounded-lg overflow-hidden p-4 flex flex-col">
                <div class="relative group w-full aspect-square overflow-hidden">
                    <picture>
                        <source srcset="<?php echo esc_url($webp_url); ?>" type="image/webp">
                        <img src="<?php echo esc_url($thumbnail_url); ?>" 
                             class="w-full h-full object-cover transition-opacity duration-300 group-hover:opacity-0" 
                             alt="<?php the_title_attribute(); ?>" 
                             loading="lazy">
                    </picture>
                    <?php if ($hover_image_id) : 
                        $hover_url = wp_get_attachment_image_url($hover_image_id, 'medium');
                        $hover_webp_url = str_replace(['.jpg', '.png'], '.webp', $hover_url);
                    ?>
                        <picture class="absolute inset-0 opacity-0 group-hover:opacity-100">
                            <source srcset="<?php echo esc_url($hover_webp_url); ?>" type="image/webp">
                            <img src="<?php echo esc_url($hover_url); ?>" 
                                 class="w-full h-full object-cover transition-opacity duration-300" 
                                 alt="<?php the_title_attribute(); ?>" 
                                 loading="lazy">
                        </picture>
                    <?php endif; ?>
                </div>
                <?php echo do_shortcode('[yith_wcwl_add_to_wishlist]'); ?>

                <?php
                $brand = $brands_by_product[get_the_ID()] ?? null;
                if ($brand) :
                ?>
                    <div class="text-sm text-gray-500 mb-1"><?php echo esc_html($brand->name); ?></div>
                <?php endif; ?>

                <h2 class="text-md font-semibold mb-2">
                    <a href="<?php the_permalink(); ?>" class="hover:underline"><?php the_title(); ?></a>
                </h2>

                <div class="mt-auto text-lg font-bold text-gray-800">
                    <?php echo $product->get_price_html(); ?>
                </div>

                <div class="woocommerce mt-4">
                    <?php
                    if ($product->is_type('simple')) {
                        woocommerce_simple_add_to_cart();
                    } elseif ($product->is_type('variable')) {
                        woocommerce_variable_add_to_cart();
                    }
                    ?>
                </div>
            </div>
            <?php
        endwhile;

        if ($loop->max_num_pages > $page) : ?>
            <div class="text-center mt-6">
                <button class="load-more-btn bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
                        data-next-page="<?php echo esc_attr($page + 1); ?>">
                    Load More
                </button>
            </div>
        <?php endif;
    else :
        echo '<p class="text-center text-gray-600">No products found.</p>';
    endif;

    wp_reset_postdata();
    $output = ob_get_clean();

    set_transient($cache_key, $output, HOUR_IN_SECONDS);
    echo $output;
    wp_die();
}
add_action('wp_ajax_fetch_sorted_products_bycategory', 'fetch_sorted_products_bycategory');
add_action('wp_ajax_nopriv_fetch_sorted_products_bycategory', 'fetch_sorted_products_bycategory');

/**
 * Filter Products
 *
 * AJAX handler to filter products based on categories, brands, age, sizes, colors, and price range.
 */
function filter_products_callback() {
    $categories = isset($_POST['categories']) ? array_map('intval', $_POST['categories']) : [];
    $brands = isset($_POST['brands']) ? array_map('intval', $_POST['brands']) : [];
    $age = isset($_POST['age']) ? array_map('intval', $_POST['age']) : [];
    $sizes = isset($_POST['sizes']) ? array_map('intval', $_POST['sizes']) : [];
    $colors = isset($_POST['colors']) ? array_map('intval', $_POST['colors']) : [];
    $min_price = isset($_POST['min_price']) ? floatval($_POST['min_price']) : 0;
    $max_price = isset($_POST['max_price']) ? floatval($_POST['max_price']) : 0;
    $paged = isset($_POST['page']) ? intval($_POST['page']) : 1;

    $args = [
        'post_type'      => 'product',
        'posts_per_page' => 12,
        'paged'          => $paged,
    ];

    $tax_query = [];
    if (!empty($categories)) {
        $tax_query[] = [
            'taxonomy' => 'product_cat',
            'field'    => 'id',
            'terms'    => $categories,
            'operator' => 'IN',
        ];
    }
    if (!empty($brands)) {
        $tax_query[] = [
            'taxonomy' => 'product_brand',
            'field'    => 'id',
            'terms'    => $brands,
            'operator' => 'IN',
        ];
    }
    if (!empty($age)) {
        $tax_query[] = [
            'taxonomy' => 'product_cat',
            'field'    => 'id',
            'terms'    => $age,
            'operator' => 'IN',
        ];
    }
    if (!empty($sizes)) {
        $tax_query[] = [
            'taxonomy' => 'pa_size',
            'field'    => 'id',
            'terms'    => $sizes,
            'operator' => 'IN',
        ];
    }
    if (!empty($colors)) {
        $tax_query[] = [
            'taxonomy' => 'pa_color',
            'field'    => 'id',
            'terms'    => $colors,
            'operator' => 'IN',
        ];
    }

    $meta_query = [];
    if ($min_price > 0 || $max_price > 0) {
        $price_range = [
            'key'     => '_price',
            'value'   => [$min_price > 0 ? $min_price : 0, $max_price > 0 ? $max_price : 999999],
            'compare' => 'BETWEEN',
            'type'    => 'NUMERIC',
        ];
        $meta_query[] = $price_range;
    }

    if (!empty($tax_query)) {
        $args['tax_query'] = ['relation' => 'AND'] + $tax_query;
    }
    if (!empty($meta_query)) {
        $args['meta_query'] = $meta_query;
    }

    $query = new WP_Query($args);
    $product_ids = wp_list_pluck($query->posts, 'ID');
    $brand_terms = wp_get_object_terms($product_ids, 'product_brand', ['fields' => 'all_with_object_id']);
    $brands_by_product = [];
    foreach ($brand_terms as $term) {
        $brands_by_product[$term->object_id] = $term;
    }

    ob_start();

    if ($query->have_posts()) :
        while ($query->have_posts()) : $query->the_post();
            global $product;
            $attachment_ids = $product->get_gallery_image_ids();
            $hover_image_id = $attachment_ids[0] ?? null;
            $thumbnail_url = get_the_post_thumbnail_url(get_the_ID(), 'medium') ?: 'https://via.placeholder.com/300x300';
            $webp_url = str_replace(['.jpg', '.png'], '.webp', $thumbnail_url);
            ?>
            <div class="product-card bg-white shadow-md rounded-lg overflow-hidden p-4 flex flex-col">
                <div class="relative group w-full aspect-square overflow-hidden">
                    <picture>
                        <source srcset="<?php echo esc_url($webp_url); ?>" type="image/webp">
                        <img src="<?php echo esc_url($thumbnail_url); ?>" 
                             class="w-full h-full object-cover transition-opacity duration-300 group-hover:opacity-0" 
                             alt="<?php the_title_attribute(); ?>" 
                             loading="lazy">
                    </picture>
                    <?php if ($hover_image_id) : 
                        $hover_url = wp_get_attachment_image_url($hover_image_id, 'medium');
                        $hover_webp_url = str_replace(['.jpg', '.png'], '.webp', $hover_url);
                    ?>
                        <picture class="absolute inset-0 opacity-0 group-hover:opacity-100">
                            <source srcset="<?php echo esc_url($hover_webp_url); ?>" type="image/webp">
                            <img src="<?php echo esc_url($hover_url); ?>" 
                                 class="w-full h-full object-cover transition-opacity duration-300" 
                                 alt="<?php the_title_attribute(); ?>" 
                                 loading="lazy">
                        </picture>
                    <?php endif; ?>
                </div>
                <?php echo do_shortcode('[yith_wcwl_add_to_wishlist]'); ?>

                <?php
                $brand = $brands_by_product[get_the_ID()] ?? null;
                if ($brand) :
                ?>
                    <div class="text-sm text-gray-500 mb-1"><?php echo esc_html($brand->name); ?></div>
                <?php endif; ?>

                <h2 class="text-md font-semibold mb-2">
                    <a href="<?php the_permalink(); ?>" class="hover:underline"><?php the_title(); ?></a>
                </h2>

                <div class="mt-auto text-lg font-bold text-gray-800">
                    <?php echo $product->get_price_html(); ?>
                </div>

                <div class="woocommerce mt-4">
                    <?php
                    if ($product->is_type('simple')) {
                        woocommerce_simple_add_to_cart();
                    } elseif ($product->is_type('variable')) {
                        woocommerce_variable_add_to_cart();
                    }
                    ?>
                </div>
            </div>
            <?php
        endwhile;

        if ($query->max_num_pages > $paged) : ?>
            <div class="text-center mt-6">
                <button class="load-more-btn bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
                        data-next-page="<?php echo esc_attr($paged + 1); ?>">
                    Load More
                </button>
            </div>
        <?php endif;
    else :
        echo '<p class="text-center text-gray-600">No products found.</p>';
    endif;

    wp_reset_postdata();
    $output = ob_get_clean();

    set_transient($cache_key, $output, HOUR_IN_SECONDS);
    echo $output;
    wp_die();
}
add_action('wp_ajax_filter_products', 'filter_products_callback');
add_action('wp_ajax_nopriv_filter_products', 'filter_products_callback');

/**
 * Filter Products with Term
 *
 * AJAX handler to filter products by a specific taxonomy term and additional filters.
 */
function filter_products_with_term() {
    $categories = isset($_POST['categories']) ? array_map('intval', $_POST['categories']) : [];
    $brands = isset($_POST['brands']) ? array_map('intval', $_POST['brands']) : [];
    $age = isset($_POST['age']) ? array_map('intval', $_POST['age']) : [];
    $term_id = isset($_POST['term_id']) ? intval($_POST['term_id']) : 0;
    $paged = isset($_POST['page']) ? intval($_POST['page']) : 1;
    $taxonomy = isset($_POST['taxonomy']) ? sanitize_text_field($_POST['taxonomy']) : 'product_cat';

    $args = [
        'post_type'      => 'product',
        'posts_per_page' => 12,
        'paged'          => $paged,
    ];

    $tax_query = ['relation' => 'AND'];
    if (!empty($term_id)) {
        $tax_query[] = [
            'taxonomy' => $taxonomy,
            'field'    => 'term_id',
            'terms'    => $term_id,
        ];
    }
    if (!empty($categories)) {
        $tax_query[] = [
            'taxonomy' => 'product_cat',
            'field'    => 'id',
            'terms'    => $categories,
            'operator' => 'IN',
        ];
    }
    if (!empty($brands)) {
        $tax_query[] = [
            'taxonomy' => 'product_brand',
            'field'    => 'id',
            'terms'    => $brands,
            'operator' => 'IN',
        ];
    }
    if (!empty($age)) {
        $tax_query[] = [
            'taxonomy' => 'product_cat',
            'field'    => 'id',
            'terms'    => $age,
            'operator' => 'IN',
        ];
    }

    if (count($tax_query) > 1) {
        $args['tax_query'] = $tax_query;
    }

    $query = new WP_Query($args);
    $product_ids = wp_list_pluck($query->posts, 'ID');
    $brand_terms = wp_get_object_terms($product_ids, 'product_brand', ['fields' => 'all_with_object_id']);
    $brands_by_product = [];
    foreach ($brand_terms as $term) {
        $brands_by_product[$term->object_id] = $term;
    }

    ob_start();

    if ($query->have_posts()) :
        while ($query->have_posts()) : $query->the_post();
            global $product;
            $attachment_ids = $product->get_gallery_image_ids();
            $hover_image_id = $attachment_ids[0] ?? null;
            $thumbnail_url = get_the_post_thumbnail_url(get_the_ID(), 'medium') ?: 'https://via.placeholder.com/300x300';
            $webp_url = str_replace(['.jpg', '.png'], '.webp', $thumbnail_url);
            ?>
            <div class="product-card bg-white shadow-md rounded-lg overflow-hidden p-4 flex flex-col">
                <div class="relative group w-full aspect-square overflow-hidden">
                    <picture>
                        <source srcset="<?php echo esc_url($webp_url); ?>" type="image/webp">
                        <img src="<?php echo esc_url($thumbnail_url); ?>" 
                             class="w-full h-full object-cover transition-opacity duration-300 group-hover:opacity-0" 
                             alt="<?php the_title_attribute(); ?>" 
                             loading="lazy">
                    </picture>
                    <?php if ($hover_image_id) : 
                        $hover_url = wp_get_attachment_image_url($hover_image_id, 'medium');
                        $hover_webp_url = str_replace(['.jpg', '.png'], '.webp', $hover_url);
                    ?>
                        <picture class="absolute inset-0 opacity-0 group-hover:opacity-100">
                            <source srcset="<?php echo esc_url($hover_webp_url); ?>" type="image/webp">
                            <img src="<?php echo esc_url($hover_url); ?>" 
                                 class="w-full h-full object-cover transition-opacity duration-300" 
                                 alt="<?php the_title_attribute(); ?>" 
                                 loading="lazy">
                        </picture>
                    <?php endif; ?>
                </div>
                <?php echo do_shortcode('[yith_wcwl_add_to_wishlist]'); ?>

                <?php
                $brand = $brands_by_product[get_the_ID()] ?? null;
                if ($brand) :
                ?>
                    <div class="text-sm text-gray-500 mb-1"><?php echo esc_html($brand->name); ?></div>
                <?php endif; ?>

                <h2 class="text-md font-semibold mb-2">
                    <a href="<?php the_permalink(); ?>" class="hover:underline"><?php the_title(); ?></a>
                </h2>

                <div class="mt-auto text-lg font-bold text-gray-800">
                    <?php echo $product->get_price_html(); ?>
                </div>

                <div class="woocommerce mt-4">
                    <?php
                    if ($product->is_type('simple')) {
                        woocommerce_simple_add_to_cart();
                    } elseif ($product->is_type('variable')) {
                        woocommerce_variable_add_to_cart();
                    }
                    ?>
                </div>
            </div>
            <?php
        endwhile;

        if ($query->max_num_pages > $paged) : ?>
            <div class="text-center mt-6">
                <button class="load-more-btn bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
                        data-next-page="<?php echo esc_attr($paged + 1); ?>">
                    Load More
                </button>
            </div>
        <?php endif;
    else :
        echo '<p class="text-center text-gray-600">No products found.</p>';
    endif;

    wp_reset_postdata();
    $output = ob_get_clean();

    set_transient($cache_key, $output, HOUR_IN_SECONDS);
    echo $output;
    wp_die();
}
add_action('wp_ajax_filter_products_with_term', 'filter_products_with_term');
add_action('wp_ajax_nopriv_filter_products_with_term', 'filter_products_with_term');

/**
 * Load Testimonials
 *
 * AJAX handler to load testimonials with pagination.
 */
function load_testimonials_ajax() {
    $paged = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $query = new WP_Query([
        'post_type'      => 'testimonial',
        'posts_per_page' => 3,
        'paged'          => $paged,
    ]);

    ob_start();
    if ($query->have_posts()) :
        while ($query->have_posts()) : $query->the_post();
            $rating = get_field('rating');
            ?>
            <div class="bg-white shadow rounded-lg p-6">
                <?php if (has_post_thumbnail()) : ?>
                    <img src="<? downloadable_image($the_post_thumbnail_url('medium')); ?>" 
                         class="w-full h-48 object-cover rounded-md mb-4" 
                         alt="<?php the_title_attribute(); ?>" 
                         loading="lazy">
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
    echo ob_get_clean();
    wp_die();
}
add_action('wp_ajax_load_testimonials', 'load_testimonials_ajax');
add_action('wp_ajax_nopriv_load_testimonials', 'load_testimonials_ajax');

/**
 * Lazy Load Template Section
 *
 * AJAX handler to load template parts dynamically.
 */
function lazy_load_template_section() {
    $template = sanitize_text_field($_GET['template'] ?? '');
    if (!$template || !preg_match('/^[a-zA-Z0-9\-\/]+$/', $template)) {
        wp_send_json_error('Invalid template name');
    }

    ob_start();
    get_template_part('components/' . $template);
    echo ob_get_clean();
    wp_die();
}
add_action('wp_ajax_load_section', 'lazy_load_template_section');
add_action('wp_ajax_nopriv_load_section', 'lazy_load_template_section');

/**
 * Load Filter Drawer Content
 *
 * AJAX handler to load the filter drawer content.
 */
function load_filter_drawer_content() {
    ob_start();
    get_template_part('products/filter-drawer');
    echo ob_get_clean();
    wp_die();
}
add_action('wp_ajax_load_filter_drawer_content', 'load_filter_drawer_content');
add_action('wp_ajax_nopriv_load_filter_drawer_content', 'load_filter_drawer_content');

/**
 * Shortcodes
 */

/**
 * Product Categories Checkboxes
 *
 * Shortcode to display product categories as checkboxes.
 *
 * @param array $atts Shortcode attributes.
 * @return string HTML output.
 */
function product_categories_checkbox_shortcode($atts) {
    $atts = shortcode_atts(['include' => ''], $atts, 'product_categories_checkbox');
    $include_ids = array_map('intval', explode(',', $atts['include']));

    ob_start();
    $args = [
        'taxonomy'   => 'product_cat',
        'hide_empty' => false,
        'include'    => $include_ids,
    ];

    $product_categories = get_terms($args);
    if (!empty($product_categories) && !is_wp_error($product_categories)) {
        echo '<div class="space-y-2">';
        foreach ($product_categories as $category) {
            $cat_id = esc_attr($category->term_id);
            $cat_name = esc_html($category->name);
            echo "<label class='flex items-center space-x-2 text-gray-700'>
                    <input type='checkbox' name='product_cat[]' value='{$cat_id}' class='accent-blue-500'>
                    <span>{$cat_name}</span>
                  </label>";
        }
        echo '</div>';
    } else {
        echo '<p>No categories available.</p>';
    }

    return ob_get_clean();
}
add_shortcode('product_categories_checkbox', 'product_categories_checkbox_shortcode');

/**
 * Age Subcategories Checkboxes
 *
 * Shortcode to display subcategories of the 'Age' product category as checkboxes.
 *
 * @return string HTML output.
 */
function age_subcategories_checkboxes_shortcode() {
    $parent = get_term_by('name', 'Age', 'product_cat');
    if (!$parent || is_wp_error($parent)) {
        return '<p>Parent category not found.</p>';
    }

    $subcategories = get_terms([
        'taxonomy'   => 'product_cat',
        'parent'     => $parent->term_id,
        'hide_empty' => false,
        'orderby'    => 'name',
        'order'      => 'ASC',
    ]);

    if (empty($subcategories)) {
        return '<p>No subcategories found.</p>';
    }

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

/**
 * Brand Checkboxes
 *
 * Shortcode to display product brands as checkboxes.
 *
 * @return string HTML output.
 */
function woocommerce_brand_checkboxes_shortcode() {
    $brands = get_terms([
        'taxonomy'   => 'product_brand',
        'hide_empty' => false,
        'orderby'    => 'name',
        'order'      => 'ASC',
    ]);

    if (empty($brands) || is_wp_error($brands)) {
        return '<p>No brands found.</p>';
    }

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

/**
 * Attribute Checkboxes
 *
 * Shortcode to display WooCommerce product attributes as checkboxes.
 *
 * @param array $atts Shortcode attributes.
 * @return string HTML output.
 */
function woocommerce_attribute_checkboxes_shortcode($atts) {
    $atts = shortcode_atts(['attribute' => ''], $atts);
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

/**
 * Price Filter
 *
 * Shortcode to display a price range filter input.
 *
 * @return string HTML output.
 */
function woocommerce_price_filter_shortcode() {
    ob_start();
    ?>
    <div class="space-y-4">
        <label class="block text-sm font-medium text-gray-700">Price Range</label>
        <div class="flex items-center space-x-3">
            <input type="number" name="min_price" id="min_price" placeholder="Min" 
                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            <span>-</span>
            <input type="number" name="max_price" id="max_price" placeholder="Max" 
                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('price_filter', 'woocommerce_price_filter_shortcode');

/**
 * WooCommerce Single Product Customizations
 */

/**
 * Show Brand Above Title
 *
 * Displays the product brand above the product title on single product pages.
 */
function show_woocommerce_brand_above_title() {
    $terms = get_the_terms(get_the_ID(), 'product_brand');
    if ($terms && !is_wp_difference($terms)) {
        $brand = $terms[0];
        echo '<div class="product-brand" style="font-weight: bold; margin-bottom: 10px;">';
        echo '<a href="' . esc_url(get_term_link($brand)) . '">' . esc_html($brand->name) . '</a>';
        echo '</div>';
    }
}

/**
 * Add Duties Notice Under Price
 *
 * Adds a notice about duties and taxes under the product price.
 */
function add_duties_notice_under_price() {
    echo '<div class="mt-2">
            <p class="text-sm text-gray-600 italic">(Duties and Tax included)</p>
            <hr class="mt-2 border-t border-gray-300" />
          </div>';
}

/**
 * Add Separator Before Cart Button
 *
 * Adds a horizontal rule before the add-to-cart button.
 */
function custom_separator_before_cart() {
    echo '<hr class="my-4 border-t border-gray-300" />';
}

/**
 * Add Divider After Buy Now Button
 *
 * Adds a divider after the buy now button on single product pages.
 */
function add_divider_after_buy_now_button() {
    if (is_product()) {
        echo '<div style="margin-top: 20px; border-top: 1px solid #ddd; padding-top: 20px;"></div>';
    }
}

/**
 * User Account Management
 */

/**
 * Validate Registration
 *
 * Validates password confirmation during WooCommerce registration.
 *
 * @param string   $username         The username.
 * @param string   $email            The email.
 * @param WP_Error $validation_errors The validation errors object.
 */
function junior_salon_validate_registration($username, $email, $validation_errors) {
    if ($_POST['password'] !== $_POST['password2']) {
        $validation_errors->add('password_mismatch', 'Passwords do not match.');
    }
}
add_action('woocommerce_register_post', 'junior_salon_validate_registration', 10, 3);

/**
 * Save User Data
 *
 * Saves additional user data (first name, last name, phone) during registration.
 *
 * @param int $customer_id The customer ID.
 */
function junior_salon_save_user_data($customer_id) {
    if (!empty($_POST['first_name'])) {
        update_user_meta($customer_id, 'first_name', sanitize_text_field($_POST['first_name']));
    }
    if (!empty($_POST['last_name'])) {
        update_user_meta($customer_id, 'last_name', sanitize_text_field($_POST['last_name']));
    }
    if (!empty($_POST['billing_phone'])) {
        update_user_meta($customer_id, 'billing_phone', sanitize_text_field($_POST['billing_phone']));
    }
}
add_action('woocommerce_created_customer', 'junior_salon_save_user_data');

/**
 * Address Management
 */

/**
 * Save Shipping Address
 *
 * AJAX handler to save shipping address for logged-in users.
 */
function ajax_save_shipping_address() {
    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'Not logged in']);
    }

    $user_id = get_current_user_id();
    $fields = ['first_name', 'last_name', 'company', 'address_1', 'address_2', 'city', 'state', 'postcode', 'country', 'phone'];

    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            update_user_meta($user_id, 'shipping_' . $field, sanitize_text_field($_POST[$field]));
        }
    }

    wp_send_json_success();
}
add_action('wp_ajax_save_shipping_address', 'ajax_save_shipping_address');

/**
 * Save Billing Address
 *
 * AJAX handler to save billing address for logged-in users.
 */
function ajax_save_billing_address() {
    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'Not logged in']);
    }

    $user_id = get_current_user_id();
    $fields = ['first_name', 'last_name', 'company', 'address_1', 'address_2', 'city', 'state', 'postcode', 'country', 'phone'];

    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            update_user_meta($user_id, 'billing_' . $field, sanitize_text_field($_POST[$field]));
        }
    }

    wp_send_json_success();
}
add_action('wp_ajax_save_billing_address', 'ajax_save_billing_address');

/**
 * Get Shipping Address
 *
 * AJAX handler to retrieve shipping address for logged-in users.
 */
function ajax_get_shipping_address() {
    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'Not logged in']);
    }

    $user_id = get_current_user_id();
    $fields = ['first_name', 'last_name', 'company', 'address_1', 'address_2', 'city', 'state', 'postcode', 'country', 'phone'];
    $data = [];

    foreach ($fields as $field) {
        $data[$field] = get_user_meta($user_id, 'shipping_' . $field, true);
    }

    wp_send_json_success($data);
}
add_action('wp_ajax_get_shipping_address', 'ajax_get_shipping_address');










/**
 * =============================
 * Junior Salon: WooCommerce AJAX & Wishlist Management
 * =============================
 */

/**
 * Cart Management - Optimized Functions with Debugging
 */

/**
 * Apply Coupon
 */
function ajax_apply_coupon() {
    check_ajax_referer('woocommerce-cart', 'security') || wp_send_json_error(['message' => __('Security check failed.', 'woocommerce')]);

    $coupon_code = sanitize_text_field($_POST['coupon'] ?? '');
    if (empty($coupon_code)) {
        wp_send_json_error(['message' => __('Coupon code is missing.', 'woocommerce')]);
    }

    try {
        $applied = WC()->cart->apply_coupon($coupon_code);
        if (!$applied) {
            wc_clear_notices();
            $notice = wc_get_notices('error') ? wc_print_notices(true) : __('Invalid or expired coupon code.', 'woocommerce');
            wp_send_json_error(['message' => $notice]);
        }

        WC()->cart->calculate_totals();
        wc_clear_notices();

        wp_send_json_success([
            'subtotal' => wc_cart_totals_subtotal_html(),
            'shipping' => wc_cart_totals_shipping_html(),
            'total' => wc_cart_totals_order_total_html(),
            'message' => __('Coupon applied successfully.', 'woocommerce'),
        ]);
    } catch (Exception $e) {
        error_log('Coupon Error: ' . $e->getMessage());
        wp_send_json_error(['message' => __('Error applying coupon: ' . $e->getMessage(), 'woocommerce')]);
    }

    wp_die();
}
add_action('wp_ajax_apply_coupon', 'ajax_apply_coupon');
add_action('wp_ajax_nopriv_apply_coupon', 'ajax_apply_coupon');

/**
 * Update Cart Item
 */
function custom_ajax_update_cart_item() {
    check_ajax_referer('woocommerce-cart', 'security') || wp_send_json_error(['message' => __('Security check failed.', 'woocommerce')]);

    $cart_item_key = sanitize_text_field($_POST['cart_item_key'] ?? '');
    $quantity = max(0, intval($_POST['quantity'] ?? 0));

    if (!$cart_item_key || !isset(WC()->cart->cart_contents[$cart_item_key])) {
        wp_send_json_error(['message' => __('Invalid cart item.', 'woocommerce')]);
    }

    try {
        WC()->cart->set_quantity($cart_item_key, $quantity, true);
        WC()->cart->calculate_totals();

        wp_send_json_success([
            'cart_count' => WC()->cart->get_cart_contents_count(),
            'subtotal' => wc_cart_totals_subtotal_html(),
            'total' => wc_cart_totals_order_total_html(),
            'message' => $quantity === 0 ? __('Item removed successfully.', 'woocommerce') : __('Quantity updated successfully.', 'woocommerce'),
        ]);
    } catch (Exception $e) {
        error_log('Update Cart Error: ' . $e->getMessage());
        wp_send_json_error(['message' => __('Error updating cart: ' . $e->getMessage(), 'woocommerce')]);
    }

    wp_die();
}
add_action('wp_ajax_update_cart_item', 'custom_ajax_update_cart_item');
add_action('wp_ajax_nopriv_update_cart_item', 'custom_ajax_update_cart_item');

/**
 * Remove Cart Item
 */
function custom_ajax_remove_cart_item() {
    check_ajax_referer('woocommerce-cart', 'security') || wp_send_json_error(['message' => __('Security check failed.', 'woocommerce')]);

    $cart_item_key = sanitize_text_field($_POST['cart_item_key'] ?? '');

    if (!$cart_item_key || !isset(WC()->cart->cart_contents[$cart_item_key])) {
        wp_send_json_error(['message' => __('Invalid cart item.', 'woocommerce')]);
    }

    try {
        $removed = WC()->cart->remove_cart_item($cart_item_key);
        if (!$removed) {
            wp_send_json_error(['message' => __('Failed to remove item from cart.', 'woocommerce')]);
        }

        WC()->cart->calculate_totals();

        wp_send_json_success([
            'cart_count' => WC()->cart->get_cart_contents_count(),
            'subtotal' => wc_cart_totals_subtotal_html(),
            'total' => wc_cart_totals_order_total_html(),
            'message' => __('Item removed successfully.', 'woocommerce'),
        ]);
    } catch (Exception $e) {
        error_log('Remove Cart Error: ' . $e->getMessage());
        wp_send_json_error(['message' => __('Error removing item: ' . $e->getMessage(), 'woocommerce')]);
    }

    wp_die();
}
add_action('wp_ajax_remove_cart_item', 'custom_ajax_remove_cart_item');
add_action('wp_ajax_nopriv_remove_cart_item', 'custom_ajax_remove_cart_item');

/**
 * Move Cart Item to Wishlist
 */
function custom_ajax_move_to_wishlist() {
    check_ajax_referer('woocommerce-cart', 'security') || wp_send_json_error(['message' => __('Security check failed.', 'woocommerce')]);

    $cart_item_key = sanitize_text_field($_POST['cart_item_key'] ?? '');
    $product_id = absint($_POST['product_id'] ?? 0);

    if (!$cart_item_key || !$product_id || !isset(WC()->cart->cart_contents[$cart_item_key])) {
        wp_send_json_error(['message' => __('Invalid cart item or product ID.', 'woocommerce')]);
    }

    try {
        if (function_exists('YITH_WCWL')) {
            YITH_WCWL()->add($product_id);
        }

        WC()->cart->remove_cart_item($cart_item_key);
        WC()->cart->calculate_totals();

        wp_send_json_success([
            'cart_count' => WC()->cart->get_cart_contents_count(),
            'subtotal' => wc_cart_totals_subtotal_html(),
            'total' => wc_cart_totals_order_total_html(),
            'message' => __('Item moved to wishlist successfully.', 'woocommerce'),
        ]);
    } catch (Exception $e) {
        error_log('Move to Wishlist Error: ' . $e->getMessage());
        wp_send_json_error(['message' => __('Error moving item to wishlist: ' . $e->getMessage(), 'woocommerce')]);
    }

    wp_die();
}
add_action('wp_ajax_move_to_wishlist', 'custom_ajax_move_to_wishlist');
add_action('wp_ajax_nopriv_move_to_wishlist', 'custom_ajax_move_to_wishlist');

/**
 * Toggle Wishlist
 */
function custom_ajax_toggle_wishlist() {
    check_ajax_referer('woocommerce-cart', 'security') || wp_send_json_error(['message' => __('Security check failed.', 'woocommerce')]);

    $product_id = absint($_POST['product_id'] ?? 0);
    $add = (int) ($_POST['add'] ?? 0);

    if (!$product_id) {
        wp_send_json_error(['message' => __('Invalid product ID.', 'woocommerce')]);
    }

    try {
        // Optionally call YITH_WCWL()->add/remove here based on $add
        wp_send_json_success([
            'added' => $add,
            'message' => $add ? __('Added to wishlist.', 'woocommerce') : __('Removed from wishlist.', 'woocommerce'),
        ]);
    } catch (Exception $e) {
        error_log('Toggle Wishlist Error: ' . $e->getMessage());
        wp_send_json_error(['message' => __('Error toggling wishlist: ' . $e->getMessage(), 'woocommerce')]);
    }

    wp_die();
}
add_action('wp_ajax_toggle_wishlist', 'custom_ajax_toggle_wishlist');
add_action('wp_ajax_nopriv_toggle_wishlist', 'custom_ajax_toggle_wishlist');

/**
 * Remove from Wishlist
 */
function custom_ajax_remove_from_wishlist() {
    check_ajax_referer('woocommerce-cart', 'security') || wp_send_json_error(['message' => __('Security check failed.', 'woocommerce')]);

    $product_id = absint($_POST['product_id'] ?? 0);
    if (!$product_id) {
        wp_send_json_error(['message' => __('Invalid product ID.', 'woocommerce')]);
    }

    try {
        if (function_exists('YITH_WCWL')) {
            $wishlist = YITH_WCWL()->get_wishlist();
            if ($wishlist) {
                $wishlist_item = YITH_WCWL()->get_product($product_id, $wishlist->get_id());
                if ($wishlist_item) {
                    YITH_WCWL()->remove($wishlist_item->get_id());
                }
            }
        }

        wp_send_json_success(['message' => __('Item removed from wishlist successfully.', 'woocommerce')]);
    } catch (Exception $e) {
        error_log('Remove from Wishlist Error: ' . $e->getMessage());
        wp_send_json_error(['message' => __('Error removing from wishlist: ' . $e->getMessage(), 'woocommerce')]);
    }

    wp_die();
}
add_action('wp_ajax_remove_from_wishlist', 'custom_ajax_remove_from_wishlist');
add_action('wp_ajax_nopriv_remove_from_wishlist', 'custom_ajax_remove_from_wishlist');

/**
 * Custom function to get wishlist items
 */
/**
 * Custom function to get wishlist items (compatible with YITH)
 */
/**
 * Custom function to get wishlist items (YITH Compatible)
 */
/**
 * Custom function to get wishlist items (YITH Compatible)
 */
function get_custom_wishlist_items() {
    if (!class_exists('YITH_WCWL_Wishlist_Factory')) return [];

    // Get the default wishlist object
    $wishlist = YITH_WCWL_Wishlist_Factory::get_default_wishlist();

    if (!$wishlist || !method_exists($wishlist, 'get_items')) return [];

    // Fetch items
    $items = $wishlist->get_items();

    // Prepare compatible array for template
    $wishlist_items = [];
    foreach ($items as $item) {
        $wishlist_items[] = [
            'prod_id' => $item->get_product_id(),
            'wishlist_id' => $item->get_wishlist_id(),
            'ID' => $item->get_id(),
        ];
    }

    return $wishlist_items;
}




/**
 * Override YITH Wishlist default template
 */
add_filter('yith_wcwl_wishlist_template', function() {
    return 'custom-wishlist-template.php';
});

/**
 * Get Cart Count (template redirect)
 */
function junior_salon_cart_count() {
    if (isset($_GET['count_only']) && $_GET['count_only'] === '1') {
        echo WC()->cart->get_cart_contents_count();
        exit;
    }
}
add_action('template_redirect', 'junior_salon_cart_count');

/**
 * Update Payment Method
 */
function woocommerce_update_payment_method() {
    check_ajax_referer('woocommerce-cart', 'security') || wp_send_json_error(['message' => __('Security check failed.', 'woocommerce')]);

    $payment_method = sanitize_text_field($_POST['payment_method'] ?? '');
    if ($payment_method) {
        WC()->session->set('chosen_payment_method', $payment_method);
        wp_send_json_success(['message' => __('Payment method updated.', 'woocommerce')]);
    } else {
        wp_send_json_error(['message' => __('Payment method not provided.', 'woocommerce')]);
    }

    wp_die();
}
add_action('wp_ajax_woocommerce_update_payment_method', 'woocommerce_update_payment_method');
add_action('wp_ajax_nopriv_woocommerce_update_payment_method', 'woocommerce_update_payment_method');

/**
 * Debug SQL Queries
 */
function junior_salon_log_queries($sql) {
    error_log($sql);
    return $sql;
}
add_filter('posts_request', 'junior_salon_log_queries');

/**
 * Custom Mega Menu Walker
 */
class Custom_Mega_Menu_Walker extends Walker_Nav_Menu {
    public function start_lvl(&$output, $depth = 0, $args = null) {
        if ($depth === 0) {
            $output .= '<div class="mega-menu hidden absolute left-0 top-full w-screen bg-white shadow-lg z-50 py-8">';
            $output .= '<div class="max-w-7xl mx-auto grid grid-cols-4 gap-6 px-6">';
        }
    }

    public function end_lvl(&$output, $depth = 0, $args = null) {
        if ($depth === 0) {
            $output .= '</div></div>';
        }
    }

    public function start_el(&$output, $item, $depth = 0, $args = null, $id = 0) {
        $classes = !empty($item->classes) ? (array) $item->classes : [];
        $is_mega = in_array('mega-menu', $classes);

        if ($depth === 0) {
            $output .= '<li class="relative group">';
            $output .= '<a href="' . esc_url($item->url) . '" class="inline-block px-3 py-2 text-sm font-medium text-gray-800 hover:text-red-600" ' . ($is_mega ? 'data-toggle="mega"' : '') . '>';
            $output .= esc_html($item->title);
            $output .= '</a>';
        } else {
            $output .= '<div><a href="' . esc_url($item->url) . '" class="block text-sm text-gray-700 hover:text-red-600">' . esc_html($item->title) . '</a></div>';
        }
    }

    public function end_el(&$output, $item, $depth = 0, $args = null) {
        if ($depth === 0) {
            $output .= '</li>';
        }
    }
}

/**
 * Output AJAX URL in Frontend
 */
add_action('wp_footer', function () {
    if (!is_admin()) {
        echo '<script>var ajaxurl = "' . admin_url('admin-ajax.php') . '";</script>';
    }
});
