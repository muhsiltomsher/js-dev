<?php
/**
 * Payment Method Section for Custom Checkout
 */

if (!function_exists('wc')) return;

$available_gateways = WC()->payment_gateways->get_available_payment_gateways();
$chosen_gateway     = WC()->session->get('chosen_payment_method');
?>

<div class="bg-white border border-gray-200 p-6 rounded shadow-sm">
  <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-800 mb-4">Payment Methods</h2>

  <form id="payment-methods" class="space-y-4">
    <?php foreach ($available_gateways as $gateway): ?>
      <label class="flex items-center border px-4 py-3 rounded cursor-pointer hover:border-blue-500 transition <?php echo $gateway->id === $chosen_gateway ? 'border-blue-600 bg-blue-50' : 'border-gray-300'; ?>">
        <input type="radio" name="payment_method" value="<?php echo esc_attr($gateway->id); ?>" class="mr-3"
               <?php checked($gateway->id, $chosen_gateway); ?>>
        <div class="flex flex-col text-sm text-gray-700">
          <span class="font-medium"><?php echo esc_html($gateway->get_title()); ?></span>
          <?php if ($gateway->get_description()): ?>
            <span class="text-xs text-gray-500 mt-1"><?php echo wp_kses_post($gateway->get_description()); ?></span>
          <?php endif; ?>
        </div>
        <?php if ($gateway->id === 'tabby'): ?>
          <img src="/wp-content/uploads/tabby-logo.svg" alt="Tabby" class="ml-auto h-6">
        <?php endif; ?>
        <?php if ($gateway->id === 'cod'): ?>
          <span class="ml-auto text-xs text-gray-400">AED 10 fee applies</span>
        <?php endif; ?>
      </label>
    <?php endforeach; ?>
  </form>
</div>

<script>
document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
  radio.addEventListener('change', function () {
    fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      credentials: 'same-origin',
      body: new URLSearchParams({
        action: 'woocommerce_update_payment_method',
        payment_method: this.value
      })
    });
  });
});
</script>