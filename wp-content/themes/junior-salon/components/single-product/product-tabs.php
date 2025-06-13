<?php
/**
 * Product Tabs Component
 * Displays WooCommerce's built-in product tabs (Description, Reviews, etc.)
 *
 * @package junior-salon
 */

defined('ABSPATH') || exit;

/**
 * Hook: woocommerce_after_single_product_summary
 *
 * @hooked woocommerce_output_product_data_tabs - 10
 */
?>
<div class="woocommerce-tabs-wrapper mt-8">
    <?php do_action('woocommerce_after_single_product_summary'); ?>
</div>
