<?php defined('ABSPATH') || exit; ?>


<!-- Summary Header -->

<div class="border-b border-gray-200 py-2 mb-4">
    <div class="mx-auto flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
        <h2 class="text-base sm:text-lg my-1 font-semibold text-black tracking-wide">ORDER SUMMARY</h2>
        <div class="flex items-center gap-3">
            <button type="submit" name="update_cart"
                class="text-sm border border-black px-4 py-1 hover:bg-black hover:text-white transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                Update Cart
            </button>
        </div>
    </div>
</div>
<!-- Pricing Overview -->
<div class="text-sm space-y-2">
    <div class="flex justify-between">
        <span class="text-gray-600">SUBTOTAL</span>
        <span class="font-semibold subtotal"><?php wc_cart_totals_subtotal_html(); ?></span>
    </div>
    <div class="flex justify-between">
        <span class="text-gray-600">DELIVERY</span>
        <span class="font-semibold shipping"><?php wc_cart_totals_shipping_html(); ?></span>
    </div>
    <?php if (WC()->cart->get_coupon_discount_totals()) : ?>
    <div class="flex justify-between text-green-700">
        <span>DISCOUNT</span>
        <span class="font-semibold discount">
            <?php foreach (WC()->cart->get_coupons() as $code => $coupon) {
                    echo wc_price(WC()->cart->get_coupon_discount_amount($code));
                } ?>
        </span>
    </div>
    <?php endif; ?>
</div>

<!-- Include Coupon Section -->
<?php include get_template_directory() . '/components/cart/cart-coupon.php'; ?>

<!-- Free Shipping -->
<div class="bg-green-100 text-green-800 text-xs px-3 py-2 rounded my-3 flex items-center gap-2">
    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/icons/check-done-icon.svg" alt="Check"
        class="w-4 h-4" />
    Your order qualifies for FREE shipping
</div>

<!-- Total -->
<div class="flex justify-between text-lg font-bold mt-4 text-gray-800">
    <span>TOTAL</span>
    <span class="total"><?php wc_cart_totals_order_total_html(); ?></span>
</div>

<!-- Checkout Button -->
<a href="<?php echo esc_url(wc_get_checkout_url()); ?>"
    class="mt-6 w-full block text-center bg-black text-white text-sm py-2 rounded hover:bg-gray-800 transition">
    Secure Checkout
</a>

<!-- Payment Info -->
<div class="border-t mt-6 pt-4 text-xs text-gray-600">
    <p class="text-green-600 font-medium flex items-center gap-2 mb-2">
        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/icons/shield-icon.svg" alt="Security"
            class="w-4 h-4" />
        JuniorSalon Protects Your Payment Information
    </p>
    <ul class="list-disc pl-5 space-y-1">
        <li class="flex items-start gap-2">
            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/icons/tik-icon.svg" alt="Tick"
                class="w-4 h-4 mt-0.5" />
            We Do Not Store Your Payment Cards CVV, Ensuring Your Privacy
        </li>
        <li class="flex items-start gap-2">
            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/icons/tik-icon.svg" alt="Tick"
                class="w-4 h-4 mt-0.5" />
            Every Transaction Is Secure And Encrypted
        </li>
    </ul>
    <div class="flex gap-2 mt-3">
        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/icons/payments-icon.svg" alt="Payment Icons"
            class="h-5" />
    </div>
</div>