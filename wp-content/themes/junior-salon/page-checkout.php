<?php
/**
 * Template Name: Custom Checkout Page
 */
include get_template_directory() . '/layouts/header.php';

// Render thank you page if order-received and key are present
$order_id = absint(get_query_var('order-received'));
$order_key = isset($_GET['key']) ? wc_clean(wp_unslash($_GET['key'])) : '';
if ($order_id && $order_key) {
    wc_get_template('checkout/thankyou.php', ['order' => wc_get_order($order_id)]);
    return;
}
?>

<div class="container mx-auto py-12 grid grid-cols-1 lg:grid-cols-3 gap-8">
  <!-- LEFT Column (2/3 width) -->
  <div class="lg:col-span-2 space-y-8">
    <?php include get_template_directory() . '/components/checkout/section-login.php'; ?>
    <?php include get_template_directory() . '/components/checkout/section-shipping.php'; ?>
    <?php include get_template_directory() . '/components/checkout/section-billing.php'; ?>
    <?php include get_template_directory() . '/components/checkout/section-payment.php'; ?>
  </div>

  <!-- RIGHT Column (1/3 width) -->
  <div class="lg:col-span-1 space-y-8">
    <?php include get_template_directory() . '/components/checkout/section-cart-items.php'; ?>
    <?php include get_template_directory() . '/components/checkout/section-order-summary.php'; ?>
  </div>
</div>

<?php include get_template_directory() . '/layouts/footer.php'; ?>
