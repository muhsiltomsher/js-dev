<?php
defined('ABSPATH') || exit;

global $product;
?>

<form class="cart mt-4"
      action="<?php echo esc_url(apply_filters('woocommerce_add_to_cart_form_action', $product->get_permalink())); ?>"
      method="post" enctype='multipart/form-data'>
    
    <?php do_action('woocommerce_before_add_to_cart_button'); ?>

    <button type="submit"
            name="add-to-cart"
            value="<?php echo esc_attr($product->get_id()); ?>"
            class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
        <?php echo esc_html($product->single_add_to_cart_text()); ?>
    </button>

    <?php do_action('woocommerce_after_add_to_cart_button'); ?>
</form>
