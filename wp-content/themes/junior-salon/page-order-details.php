<?php
/**
 * Template Name: Order Details Page
 */
defined('ABSPATH') || exit;
include get_template_directory() . '/layouts/header.php';
$order_id = absint($_GET['order_id'] ?? 0);
$order    = wc_get_order($order_id);

if (!$order) {
    echo '<div class="text-red-600 text-center p-8">Order not found.</div>';
    get_footer();
    exit;
}

if (is_user_logged_in()) {
    if ($order->get_user_id() !== get_current_user_id()) {
        echo '<div class="text-red-600 text-center p-8">Access denied.</div>';
        get_footer();
        exit;
    }
} else {
    $provided_key = sanitize_text_field($_GET['key'] ?? '');
    if ($order->get_order_key() !== $provided_key) {
        echo '<div class="text-red-600 text-center p-8">Invalid order key.</div>';
        get_footer();
        exit;
    }
}
?>

<div class="max-w-4xl mx-auto px-6 py-10 bg-white border border-gray-200 rounded-xl shadow-md">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
        <div>
            <h1 class="text-lg font-semibold text-gray-900"><?php echo apply_filters( 'wpml_translate_single_string', 'Order', 'Order Texts', 'Order' ); ?>
 #<?php echo $order->get_order_number(); ?></h1>
            <p class="text-xs text-gray-500"><?php echo apply_filters( 'wpml_translate_single_string', 'Placed on', 'Order Texts', 'Placed on' ); ?>
 : <?php echo wc_format_datetime($order->get_date_created()); ?></p>
        </div>
        <div class="mt-3 sm:mt-0">
            <span class="text-xs font-semibold px-3 py-1 rounded-full
                <?php echo match ($order->get_status()) {
                    'completed' => 'bg-green-100 text-green-700',
                    'processing' => 'bg-blue-100 text-blue-700',
                    'cancelled' => 'bg-red-100 text-red-700',
                    default => 'bg-yellow-100 text-yellow-700',
                }; ?>">
                <?php echo ucfirst($order->get_status()); ?>
            </span>
        </div>
    </div>

     <div class="bg-gray-50 p-4 rounded-md mb-6">
        <h2 class="font-semibold text-sm text-gray-700 mb-3"><?php echo apply_filters( 'wpml_translate_single_string', 'Items', 'Order Texts', 'Items' ); ?>
</h2>
        <div class="space-y-2">
            <?php foreach ($order->get_items() as $item): 
                $product = $item->get_product();
                $thumbnail = $product && $product->get_image_id()
                    ? wp_get_attachment_image($product->get_image_id(), 'thumbnail', false, ['class' => 'w-16 h-16 object-cover rounded border'])
                    : '<div class="w-16 h-16 bg-gray-100 border rounded flex items-center justify-center text-gray-400 text-xs">No Image</div>';
            ?>
                <div class="flex items-center gap-4 border rounded p-3 bg-white">
                    <?php echo $thumbnail; ?>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-800"><?php echo esc_html($item->get_name()); ?></p>
                        <p class="text-xs text-gray-500">Qty: <?php echo $item->get_quantity(); ?></p>
                    </div>
                    <div class="text-sm font-semibold text-gray-700">
                        <?php echo wc_price($item->get_total()); ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>


    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
        <div>
            <h3 class="font-semibold mb-2 text-gray-800"><?php echo apply_filters( 'wpml_translate_single_string', 'Billing Address', 'Order Texts', 'Billing Address' ); ?>
 </h3>
            <div class="text-gray-600">
                <?php echo nl2br($order->get_formatted_billing_address()); ?><br>
                <?php echo esc_html($order->get_billing_email()); ?><br>
                <?php echo esc_html($order->get_billing_phone()); ?>
            </div>
        </div>
        <div>
            <h3 class="font-semibold mb-2 text-gray-800"><?php echo apply_filters( 'wpml_translate_single_string', 'Shipping Address', 'Order Texts', 'Shipping Address' ); ?>
 </h3>
            <div class="text-gray-600">
                <?php echo nl2br($order->get_formatted_shipping_address()); ?>
            </div>
        </div>
    </div>

    <div class="mt-8 border-t pt-4 text-right">
        <p class="text-sm text-gray-600 mb-1"><?php echo apply_filters( 'wpml_translate_single_string', 'Payment Method', 'Order Texts', 'Payment Method' ); ?>
 : <span class="font-medium text-gray-800"><?php echo esc_html($order->get_payment_method_title()); ?></span></p>
        <p class="text-lg font-bold text-gray-900"><?php echo apply_filters( 'wpml_translate_single_string', 'Total', 'Order Texts', 'Total' ); ?>
: <?php echo $order->get_formatted_order_total(); ?></p>
    </div>

    <div class="mt-6 text-right">
        <a href="<?php echo esc_url(site_url('/orders')); ?>" class="inline-block text-sm px-4 py-2 border border-gray-300 rounded hover:bg-gray-100">Back to Orders</a>
    </div>
</div>

<?php include get_template_directory() . '/layouts/footer.php'; ?>

