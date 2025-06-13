<?php
defined('ABSPATH') || exit;

global $product;

do_action('woocommerce_before_add_to_cart_form');
?>

<form class="variations_form cart space-y-4"
      action="<?php echo esc_url(apply_filters('woocommerce_add_to_cart_form_action', $product->get_permalink())); ?>"
      method="post" enctype='multipart/form-data'
      data-product_id="<?php echo absint($product->get_id()); ?>"
      data-product_variations="<?php echo wc_esc_json(wp_json_encode($product->get_available_variations())); ?>">

    <?php do_action('woocommerce_before_variations_form'); ?>

    <?php if (empty($product->get_available_variations()) && false !== $product->get_available_variations()) : ?>
        <p class="stock out-of-stock text-red-600 font-semibold">
            <?php echo esc_html(apply_filters('woocommerce_out_of_stock_message', __('This product is currently out of stock and unavailable.', 'woocommerce'))); ?>
        </p>
    <?php else : ?>
        <?php
        $template_path = get_template_directory() . '/components/add-to-cart-variable.php';
        if (file_exists($template_path)) {
            include $template_path;
        }
        ?>
    <?php endif; ?>

    <?php do_action('woocommerce_after_variations_form'); ?>
</form>

<?php do_action('woocommerce_after_add_to_cart_form'); ?>
