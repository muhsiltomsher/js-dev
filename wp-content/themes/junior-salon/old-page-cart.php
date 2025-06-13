<?php
/**
 * Template Name: Custom Cart Page
 */
defined('ABSPATH') || exit;
include get_template_directory() . '/layouts/header.php';
?>

<div class="custom-cart-container bg-gray-50 min-h-screen">
    <form class="woocommerce-cart-form" action="<?php echo esc_url(wc_get_cart_url()); ?>" method="post">
        <div class="container mx-auto px-4 py-8 grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Left: Cart Products -->
            <div class="lg:col-span-2 bg-white py-5 px-5">
                <div class="border-b border-gray-200 py-2 mb-4">
                    <div class="mx-auto flex items-center justify-between">
                        <h2 class="text-base sm:text-lg my-1 font-semibold text-black tracking-wide">
                            SHOPPING BAG (<span
                                class="cart-count"><?php echo WC()->cart->get_cart_contents_count(); ?></span> ITEMS)
                        </h2>
                        <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>"
                            class="text-sm text-black !no-underline hover:underline flex items-center space-x-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                            </svg>
                            <span>Continue shopping</span>
                        </a>
                    </div>
                </div>

                <?php do_action('woocommerce_before_cart_table'); ?>

                <div id="cart-items">
                    <?php
                    if (!WC()->cart->is_empty()) {
                        foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
                            $_product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
                            $product_id = $cart_item['product_id'];

                            if ($_product && $_product->exists() && $cart_item['quantity'] > 0) {
                                $product_permalink = $_product->is_visible() ? $_product->get_permalink($cart_item) : '';
                                $brand_terms = get_the_terms($_product->get_id(), 'product_brand');
                                $brand = !empty($brand_terms) && !is_wp_error($brand_terms) ? esc_html($brand_terms[0]->name) : 'Brand';
                                $attributes = $cart_item['variation'] ?? [];
                                $color = isset($attributes['attribute_pa_color']) ? wc_clean($attributes['attribute_pa_color']) : '';
                                $size = isset($attributes['attribute_pa_size']) ? wc_clean($attributes['attribute_pa_size']) : '';
                    ?>
                    <div class="cart-item group transition-all duration-300 bg-white p-0 overflow-hidden mb-5"
                        data-cart-item-key="<?php echo esc_attr($cart_item_key); ?>">
                        <div class="flex gap-0 relative">
                            <!-- Skeleton Loader -->
                            <div class="cart-skeleton hidden absolute inset-0 bg-gray-200 animate-pulse z-10">
                                <div class="w-full max-w-[250px] h-full bg-gray-300"></div>
                                <div class="flex-1 ml-2 p-4 space-y-4">
                                    <div class="h-4 bg-gray-300 rounded w-3/4"></div>
                                    <div class="h-4 bg-gray-300 rounded w-1/2"></div>
                                    <div class="h-4 bg-gray-300 rounded w-1/4"></div>
                                </div>
                            </div>

                            <!-- Left: Product Image -->
                            <div class="w-full max-w-[250px] h-full p-0 m-0 overflow-hidden">
                                <?php echo $_product->get_image('woocommerce_thumbnail', ['class' => 'w-full h-full object-cover', 'loading' => 'lazy']); ?>
                            </div>

                            <!-- Right: Product Info -->
                            <div
                                class="flex-1 ml-2 text-sm flex flex-col justify-between border border-gray-300 transition-opacity duration-300">
                                <!-- Title -->
                                <div class="px-4 pt-2">
                                    <p class="text-[13px] font-semibold text-black leading-tight my-1">
                                        <?php echo $brand; ?></p>
                                    <p class="text-[18px] text-black mb-2 leading-snug my-1">
                                        <?php echo $_product->get_name(); ?></p>
                                </div>

                                <!-- Attributes -->
                                <div
                                    class="px-4 flex justify-between py-2 text-[13px] border-t border-gray-300 text-black">
                                    <div class="space-y-1">
                                        <p class="my-1">Size</p>
                                        <p class="my-1">Color</p>
                                    </div>
                                    <div class="text-right space-y-1 font-medium">
                                        <p class="my-1"><?php echo esc_html($size); ?></p>
                                        <p class="my-1"><?php echo esc_html($color); ?></p>
                                    </div>
                                </div>

                                <!-- Quantity + Price -->
                                <div class="px-4 flex justify-between items-center">
                                    <!-- Quantity -->
                                    <div class="flex divide-x w-fit text-[13px] overflow-hidden">
                                        <button type="button"
                                            class="px-3 py-1 text-[15px] border-y border-l border-r-0 border-black bg-white hover:bg-black hover:text-white transition-all duration-150 disabled:opacity-50 disabled:cursor-not-allowed"
                                            data-action="decrease" data-key="<?php echo esc_attr($cart_item_key); ?>"
                                            aria-label="Decrease quantity">
                                            −
                                        </button>
                                        <span class="px-4 py-1 text-center !border-y !border-x-0"
                                            data-quantity><?php echo esc_html($cart_item['quantity']); ?></span>
                                        <button type="button"
                                            class="px-3 py-1 text-[15px] border-y border-l-0 border-r border-black bg-white hover:bg-black hover:text-white transition-all duration-150 disabled:opacity-50 disabled:cursor-not-allowed"
                                            data-action="increase" data-key="<?php echo esc_attr($cart_item_key); ?>"
                                            aria-label="Increase quantity">
                                            +
                                        </button>
                                    </div>

                                    <!-- Price -->
                                    <div class="text-right font-semibold">
                                        <span
                                            class="text-red-500 text-[15px]"><?php echo wc_price($_product->get_price() * $cart_item['quantity']); ?></span>
                                        <?php if ($_product->is_on_sale()) : ?>
                                        <span
                                            class="text-gray-400 text-[13px] line-through ml-2"><?php echo wc_price($_product->get_regular_price()); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="px-4 py-2 flex gap-2 mt-2 border-t border-gray-300">
                                    <button type="button"
                                        class="remove-btn flex items-center bg-white gap-2 border border-black text-black text-[14px] px-4 py-2 transition-all hover:bg-yellow-100 disabled:opacity-50 disabled:cursor-not-allowed"
                                        data-action="remove" data-key="<?php echo esc_attr($cart_item_key); ?>">
                                        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/icons/remove-icon.svg"
                                            alt="Remove" class="w-4 h-4" />
                                        Remove
                                    </button>
                                    <button type="button"
                                        class="wishlist-toggle flex items-center bg-white gap-2 border border-black text-black text-[14px] px-4 py-2 transition-all hover:bg-yellow-100 disabled:opacity-50 disabled:cursor-not-allowed"
                                        data-product-id="<?php echo esc_attr($product_id); ?>"
                                        data-cart-item-key="<?php echo esc_attr($cart_item_key); ?>">
                                        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/icons/heart-icon.svg"
                                            alt="Wishlist" class="w-4 h-4" />
                                        Move To Wishlist
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                            }
                        }
                    } else {
                        echo '<p class="text-center text-gray-500 py-6 text-sm">Your cart is empty.</p>';
                    }
                    ?>
                </div>

                <?php do_action('woocommerce_after_cart_table'); ?>
            </div>

            <!-- Right: Order Summary -->
            <div class="lg:col-span-1 border border-gray-200 p-6 bg-white">
              
            
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

                <div class="text-sm space-y-2">
                    <div class="flex justify-between">
                        <span class="text-gray-600">SUBTOTAL</span>
                        <span class="font-semibold subtotal"> <?php wc_cart_totals_subtotal_html(); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">DELIVERY</span>
                        <span class="font-semibold"><?php wc_cart_totals_order_total_html(); ?></span>
                    </div>
                </div>

                <!-- Expandable Coupon Section -->
                <!-- Expandable Coupon Section -->
                <div x-data="{
    open: false,
    loading: false,
    coupons: [],
    init() {
      fetch('<?php echo site_url('/wp-json/custom/v1/coupons'); ?>')
        .then(res => res.json())
        .then(data => {
          this.coupons = data;
        });
    },
    applyCoupon(code) {
      const trimmed = code.trim().toLowerCase();
      this.$refs.manualCoupon.value = trimmed;

      const msgBox = document.getElementById('coupon-message');
      const formData = new FormData();
      formData.append('action', 'apply_coupon_ajax');
      formData.append('coupon_code', trimmed);
      formData.append('security', '<?php echo wp_create_nonce("apply_coupon_nonce"); ?>');

      this.loading = true;
      msgBox.classList.add('hidden');

      fetch('<?php echo admin_url("admin-ajax.php"); ?>', {
        method: 'POST',
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        this.loading = false;
        msgBox.classList.remove('hidden');
        msgBox.textContent = data.data.message;
        msgBox.className = `text-sm p-2 rounded ${data.success ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'}`;

        if (data.success) {
          document.querySelector('.subtotal').innerHTML = data.data.subtotal;
          document.querySelector('.total').innerHTML = data.data.total;
        }
      })
      .catch(err => {
        this.loading = false;
        msgBox.classList.remove('hidden');
        msgBox.textContent = 'Unexpected error.';
        msgBox.className = 'text-sm p-2 rounded bg-red-100 text-red-700';
      });
    }
  }" x-init="init" class="mt-4 border-t border-gray-200 pt-4">



                    <!-- Toggle Header -->
                    <div class="flex justify-between items-center cursor-pointer bg-gray-100 px-4 py-2 hover:bg-gray-200"
                        @click="open = !open">
                        <div class="flex items-center gap-2">
                            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/icons/coupon-icon.svg"
                                alt="Coupon" class="w-6 h-6" />
                            <h3 class="text-sm font-semibold text-black">Apply Coupon</h3>
                        </div>
                        <div class="ml-2 text-gray-600 text-sm">
                            <i x-show="!open" class="fi fi-rr-plus"></i>
                            <i x-show="open" class="fi fi-rr-minus"></i>
                        </div>
                    </div>

                    <!-- Expandable Content -->
                    <div x-show="open" x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 transform scale-y-0"
                        x-transition:enter-end="opacity-100 transform scale-y-100"
                        x-transition:leave="transition ease-in duration-200"
                        x-transition:leave-start="opacity-100 transform scale-y-100"
                        x-transition:leave-end="opacity-0 transform scale-y-0" class="mt-4 space-y-3">
                        <!-- Feedback Message -->
                        <div id="coupon-message" class="hidden text-sm p-2 rounded mt-2"></div>

                        <!-- Suggested Coupons -->
                        <div class="flex flex-wrap gap-2 mt-2">
                            <template x-for="coupon in coupons" :key="coupon.code">
                                <button type="button" @click="applyCoupon(coupon.code)"
                                    class="text-xs font-semibold text-green-600 px-4 py-1 border border-dashed border-green-500 rounded-full bg-green-50 disabled:opacity-50 disabled:cursor-not-allowed"
                                    :disabled="loading" x-text="coupon.code">
                                </button>
                            </template>
                        </div>



                        <!-- Manual Entry -->
                        <div class="flex gap-2 items-center mt-3">
                            <input type="text" x-ref="manualCoupon" placeholder="Enter coupon code"
                                class="w-full px-3 py-2 text-sm border border-black outline-none" :disabled="loading" />
                            <button type="button"
                                class="bg-white px-4 py-2 text-sm border font-semibold border-black text-black hover:bg-black hover:text-white transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                                :disabled="loading" @click="applyCoupon($refs.manualCoupon.value)">
                                <span x-show="!loading">APPLY</span>
                                <svg x-show="loading" class="animate-spin h-4 w-4 mx-auto"
                                    xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                            </button>
                        </div>





                    </div>
                </div>




                <!-- Subtotal -->
                <div class="flex justify-between text-sm mb-2">
                    <span class="text-gray-600">SUBTOTAL</span>
                    <span class="font-semibold subtotal"><?php wc_cart_totals_subtotal_html(); ?></span>
                </div>

                <!-- Shipping -->
                <div class="flex justify-between text-sm mb-2">
                    <span class="text-gray-600">DELIVERY</span>
                    <span class="font-semibold shipping"><?php wc_cart_totals_shipping_html(); ?></span>
                </div>

                <!-- Discount (if coupon applied) -->
                <?php if (WC()->cart->get_coupon_discount_totals()) : ?>
                <div class="flex justify-between text-sm mb-2 text-green-700">
                    <span>DISCOUNT</span>
                    <span class="font-semibold discount">
                        <?php
        foreach (WC()->cart->get_coupons() as $code => $coupon) {
            echo wc_price(WC()->cart->get_coupon_discount_amount($code));
        }
        ?>
                    </span>
                </div>
                <?php endif; ?>

                <!-- FREE shipping notice -->
                <div class="bg-green-100 text-green-800 text-xs px-3 py-2 rounded my-3 flex items-center gap-2">
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/icons/check-done-icon.svg"
                        alt="Check" class="w-4 h-4" />
                    Your order qualifies for FREE shipping
                </div>

                <!-- Total -->
                <div class="flex justify-between text-lg font-bold mt-4 text-gray-800">
                    <span>TOTAL</span>
                    <span class="total"><?php wc_cart_totals_order_total_html(); ?></span>
                </div>





                <div class="border-t mt-6 pt-4 text-xs text-gray-600">
                    <p class="text-green-600 font-medium flex items-center gap-2 mb-2">
                        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/icons/shield-icon.svg"
                            alt="Security" class="w-4 h-4" />
                        JuniorSalon Protects Your Payment Information
                    </p>
                    <ul class="list-disc pl-5 space-y-1">
                        <li class="flex items-start gap-2">
                            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/icons/tik-icon.svg"
                                alt="Tick" class="w-4 h-4 mt-0.5" />
                            We Do Not Store Your Payment Cards CVV, Ensuring Your Privacy
                        </li>
                        <li class="flex items-start gap-2">
                            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/icons/tik-icon.svg"
                                alt="Tick" class="w-4 h-4 mt-0.5" />
                            Every Transaction Is Secure And Encrypted
                        </li>
                    </ul>
                    <div class="flex gap-2 mt-3">
                        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/icons/payments-icon.svg"
                            alt="Payment Icons" class="h-5" />
                    </div>
                </div>






            </div>
        </div>
    </form>
</div>

<script>
jQuery(document).ready(function($) {
    // Cache selectors
    const $cartItems = $('#cart-items');
    const $couponMessage = $('#coupon-message');

    // Debounce function
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // Show loading state
    function showLoading($element, show) {
        const $skeleton = $element.find('.cart-skeleton');
        const $content = $element.children().not('.cart-skeleton');
        if (show) {
            $skeleton.removeClass('hidden');
            $content.addClass('opacity-0');
            $element.find('button').prop('disabled', true);
        } else {
            $skeleton.addClass('hidden');
            $content.removeClass('opacity-0');
            $element.find('button').prop('disabled', false);
        }
    }

    // Handle AJAX requests for cart operations
    function handleAjax(action, data, $item = null) {
        if ($item) showLoading($item, true);

        return $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: $.extend({
                action: action,
                security: '<?php echo wp_create_nonce('woocommerce-cart'); ?>'
            }, data),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('.cart-count').text(response.data.cart_count);
                    $('.subtotal').html(response.data.subtotal);
                    $('.total').html(response.data.total);
                    if ($item) {
                        if (response.data.cart_count === 0) {
                            $cartItems.html(
                                '<p class="text-center text-gray-500 py-6 text-sm">Your cart is empty.</p>'
                            );
                        } else if (action !== 'update_cart_item') {
                            $item.fadeOut(300, function() {
                                $(this).remove();
                            });
                        }
                    }
                } else {
                    alert(response.data.message || 'An error occurred. Please try again.');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', status, error, xhr.responseText);
                alert('A network error occurred. Please try again.');
            },
            complete: function() {
                if ($item) showLoading($item, false);
            }
        });
    }

    // Coupon application function
    function applyCoupon(code) {
        const messageBox = document.getElementById('coupon-message');
        const formData = new FormData();
        formData.append('action', 'apply_coupon_ajax');
        formData.append('coupon_code', code.trim().toLowerCase());
        formData.append('security', '<?php echo wp_create_nonce("apply_coupon_nonce"); ?>');

        document.querySelector('[x-data]').__x.$data.loading = true;
        messageBox.classList.add('hidden');

        fetch('<?php echo admin_url("admin-ajax.php"); ?>', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                document.querySelector('[x-data]').__x.$data.loading = false;
                messageBox.classList.remove('hidden');
                messageBox.textContent = data.data.message;
                messageBox.className =
                    `text-sm p-2 rounded ${data.success ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'}`;

                if (data.success) {
                    document.querySelector('.subtotal').innerHTML = data.data.subtotal;
                    document.querySelector('.total').innerHTML = data.data.total;
                    document.querySelector('[x-ref="manualCoupon"]').value = '';
                }
            })
            .catch(err => {
                console.error('Coupon AJAX Error:', err);
                document.querySelector('[x-data]').__x.$data.loading = false;
                messageBox.classList.remove('hidden');
                messageBox.textContent = 'Unexpected error. Please try again.';
                messageBox.className = 'text-sm p-2 rounded bg-red-100 text-red-700';
            });
    }


    // Expose applyCoupon globally
    window.applyCoupon = applyCoupon;

    // Quantity buttons
    $cartItems.on('click', 'button[data-action="decrease"], button[data-action="increase"]', debounce(function(
        e) {
        e.preventDefault();
        const $button = $(this);
        const $item = $button.closest('.cart-item');
        const action = $button.data('action');
        const cartItemKey = $button.data('key');
        const $quantitySpan = $button.siblings('span[data-quantity]');
        let quantity = parseInt($quantitySpan.text());

        if (action === 'decrease' && quantity <= 1) return;
        quantity = action === 'decrease' ? quantity - 1 : quantity + 1;

        handleAjax('update_cart_item', {
            cart_item_key: cartItemKey,
            quantity: quantity
        }, $item).done(function(response) {
            if (response.success) $quantitySpan.text(quantity);
        });
    }, 300));

    // Remove button
    $cartItems.on('click', '.remove-btn', function(e) {
        e.preventDefault();
        const $button = $(this);
        const $item = $button.closest('.cart-item');
        const cartItemKey = $button.data('key');
        handleAjax('remove_cart_item', {
            cart_item_key: cartItemKey
        }, $item);
    });

    // Wishlist button
    $cartItems.on('click', '.wishlist-toggle', function(e) {
        e.preventDefault();
        const $button = $(this);
        const $item = $button.closest('.cart-item');
        const cartItemKey = $button.data('cart-item-key');
        const productId = $button.data('product-id');
        handleAjax('move_to_wishlist', {
            cart_item_key: cartItemKey,
            product_id: productId
        }, $item);
    });
});
</script>

<?php include get_template_directory() . '/layouts/footer.php'; ?>