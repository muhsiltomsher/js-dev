<?php
/* Template Name: Custom My Orders Page */
defined('ABSPATH') || exit;

include get_template_directory() . '/layouts/header.php';

if (!is_user_logged_in()) {
    wp_redirect(wc_get_page_permalink('myaccount'));
    exit;
}

$current_user = wp_get_current_user();

$range = isset($_GET['range']) ? sanitize_text_field($_GET['range']) : 'all';

$args = [
    'customer_id' => $current_user->ID,
    'limit'       => -1,
    'orderby'     => 'date',
    'order'       => 'DESC',
    'status'      => 'any',
];

if (in_array($range, ['1', '6', '12'])) {
    $months = intval($range);
    $cutoff_date = (new DateTime())->modify("-{$months} months")->format('Y-m-d H:i:s');
    $args['date_created'] = '>=' . $cutoff_date;
}

$customer_orders = wc_get_orders($args);


?>

<div class="max-w-6xl mx-auto px-4 py-10">
    <h2 class="text-xl font-semibold text-gray-800 mb-6">Orders</h2>

    <div class="flex justify-end mb-4">
        <form method="get">
            <select name="range" onchange="this.form.submit()" class="border border-gray-300 px-3 py-2 rounded text-sm">
           <option value="all" <?php selected($range, 'all'); ?>>All Orders</option>
        <option value="1" <?php selected($range, '1'); ?>>Last 1 Month</option>
        <option value="6" <?php selected($range, '6'); ?>>Last 6 Months</option>
        <option value="12" <?php selected($range, '12'); ?>>Last 12 Months</option>
 
            </select>
        </form>
    </div>

    <div class="space-y-6">
        <?php if ($customer_orders) : ?>
            <?php foreach ($customer_orders as $order) :
                $status = $order->get_status();
                $items = $order->get_items();
                $date = $order->get_date_created()->date_i18n('l, jS M, h:i A');
                $order_number = $order->get_order_number();
                $total = $order->get_formatted_order_total();

                $badge_classes = match ($status) {
                    'completed' => 'bg-green-100 text-green-700',
                    'processing' => 'bg-blue-100 text-blue-700',
                    'cancelled' => 'bg-red-100 text-red-700',
                    default => 'bg-yellow-100 text-yellow-700',
                };
            ?>
                <div class="border border-gray-200 rounded-md overflow-hidden shadow-sm">
                    <div class="flex justify-between items-center px-4 py-3 border-b bg-gray-50">
                        <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4">
                            <span class="text-sm font-medium">Order: #<?php echo esc_html($order_number); ?></span>
                            <span class="text-xs text-gray-500">Placed on: <?php echo esc_html($date); ?></span>
                        </div>
                        <span class="text-xs font-semibold px-3 py-1 rounded-full <?php echo esc_attr($badge_classes); ?>">
                            <?php echo ucfirst($status); ?>
                        </span>
                    </div>

                    <div class="p-4 grid grid-cols-1 sm:grid-cols-2 gap-4 items-center">
                        <div class="flex items-center gap-4 overflow-x-auto">
                            <?php foreach ($items as $item) :
                                $product = $item->get_product();
                                if ($product && $product->get_image_id()) {
                                    echo '<img src="' . esc_url(wp_get_attachment_image_url($product->get_image_id(), 'thumbnail')) . '" alt="" class="w-16 h-16 object-cover rounded border">';
                                }
                            endforeach; ?>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-gray-700 mb-2">Total: <span class="font-medium"><?php echo $total; ?></span></p>
                         <a href="<?php echo esc_url(site_url('/order-details') . '?order_id=' . $order->get_id()); ?>"

                               class="inline-block px-4 py-1.5 bg-black text-white text-xs uppercase rounded hover:bg-gray-800 transition">
                                View order details
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else : ?>
            <p class="text-sm text-gray-600">No orders found in this period.</p>
        <?php endif; ?>
    </div>
</div>

<?php include get_template_directory() . '/layouts/footer.php'; ?>
