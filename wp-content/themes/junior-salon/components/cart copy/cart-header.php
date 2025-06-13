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