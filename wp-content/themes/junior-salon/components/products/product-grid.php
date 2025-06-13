<?php
/**
 * Renders grid of products using product-card.php
 * Expects: $products = array of WC_Product or post IDs
 */

defined('ABSPATH') || exit;

if (!isset($products) || !is_array($products)) return;

foreach ($products as $product) {
    if (is_numeric($product)) {
        $product = wc_get_product($product);
    }

    if (!$product || !$product->is_visible()) continue;

    setup_postdata($GLOBALS['post'] = get_post($product->get_id()));
    wc_setup_product_data($GLOBALS['post']);

    get_template_part('components/products/product-card');
}

wp_reset_postdata();
