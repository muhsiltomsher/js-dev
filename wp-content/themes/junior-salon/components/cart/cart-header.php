<h2 class="text-2xl font-bold mb-4">SHOPPING CART</h2>
<p class="text-sm text-gray-500" x-data x-init="
  document.addEventListener('refresh-cart-totals', () => {
    fetch('<?php echo esc_url(wc_get_cart_url()); ?>?count_only=1')
      .then(res => res.text())
      .then(count => $el.textContent = `Showing ${count} products you added`);
  });
">
  Showing <?php echo WC()->cart->get_cart_contents_count(); ?> products you added 
</p>
<?php do_action('woocommerce_before_cart_table'); ?>
