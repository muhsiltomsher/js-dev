<?php
//include get_template_directory() . '/layouts/header.php'; 
 if ( $order ) : ?>

  <?php if ( $order->has_status( 'failed' ) ) : ?>
    <div class="max-w-2xl mx-auto mt-10 p-6 bg-red-50 border border-red-200 rounded text-red-700 text-center">
      <h2 class="text-xl font-semibold mb-2">Payment Failed</h2>
      <p class="mb-4">Unfortunately, your payment was declined. Please try again using a different method.</p>
      <a href="<?php echo esc_url( $order->get_checkout_payment_url() ); ?>"
         class="inline-block bg-red-600 text-white px-5 py-2 rounded hover:bg-red-700 transition">
        Try Again
      </a>
    </div>

  <?php else : ?>
    <section class="max-w-2xl mx-auto mt-10 bg-white shadow-md rounded p-6 text-center">
      <div class="text-green-600 text-4xl mb-3">✓</div>
      <h2 class="text-xl font-bold mb-2">Thank you! Your order has been received.</h2>
      <p class="text-gray-600 mb-6">We've emailed you an order confirmation with all the details.</p>

      <ul class="text-left space-y-2 text-sm text-gray-700">
        <li class="flex justify-between">
          <span class="font-medium">Order #:</span>
          <span><?php echo esc_html( $order->get_order_number() ); ?></span>
        </li>
        <li class="flex justify-between">
          <span class="font-medium">Date:</span>
          <span><?php echo esc_html( wc_format_datetime( $order->get_date_created() ) ); ?></span>
        </li>
        <li class="flex justify-between">
          <span class="font-medium">Total:</span>
          <span><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></span>
        </li>
        <li class="flex justify-between">
          <span class="font-medium">Payment Method:</span>
          <span><?php echo esc_html( $order->get_payment_method_title() ); ?></span>
        </li>
      </ul>

      <a href="<?php echo home_url('/products'); ?>"
         class="inline-block mt-6 bg-black text-white px-6 py-2 text-sm rounded hover:bg-gray-900 transition">
        Continue Shopping
      </a>
    </section>
  <?php endif; ?>

  <?php
   // do_action( 'woocommerce_thankyou_' . $order->get_payment_method(), $order->get_id() );
   // do_action( 'woocommerce_thankyou', $order->get_id() );
  ?>

<?php else : ?>
  <section class="max-w-2xl mx-auto mt-10 p-6 bg-white shadow rounded text-center">
    <h2 class="text-lg font-semibold text-gray-800 mb-2">Thank you. Your order has been received.</h2>
    <p class="text-gray-600">We couldn’t find specific order details, but your checkout was successful.</p>
  </section>
<?php 

 endif;   include get_template_directory() . '/layouts/footer.php';?>
