<?php
/**
 * Template Name: Custom Cart Page
 */
defined('ABSPATH') || exit;

include get_template_directory() . '/layouts/header.php';
?>

<div class="custom-cart-container bg-gray-50 min-h-screen">
    <form class="woocommerce-cart-form" action="<?php echo esc_url(wc_get_cart_url()); ?>" method="post">
        <div class="container mx-auto px-4 py-8 grid grid-cols-1 lg:grid-cols-3 gap-8">

            <!-- LEFT: Cart Items (2/3 width) -->
            <div class="lg:col-span-2 bg-white py-5 px-5 space-y-6">

                <!-- Cart Header -->
                <?php include get_template_directory() . '/components/cart/cart-header.php'; ?>

                <!-- Cart Items -->
                <div id="cart-items">
                    <?php include get_template_directory() . '/components/cart/cart-items.php'; ?>
                </div>

                <!-- WooCommerce Hook -->
                <?php do_action('woocommerce_after_cart_table'); ?>
            </div>

            <!-- RIGHT: Coupon + Summary (1/3 width) -->
            <div class="lg:col-span-1 border border-gray-200 p-6 bg-white">
                <!-- Summary Section -->
                <div>
                    <?php include get_template_directory() . '/components/cart/cart-summary.php'; ?>
                </div>

            </div>
        </div>
    </form>
</div>

<?php include get_template_directory() . '/layouts/footer.php'; ?>
