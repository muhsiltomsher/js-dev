<?php
if (!WC()->cart->is_empty()) {
    foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
        $_product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
        $product_id = $cart_item['product_id'];

        if ($_product && $_product->exists() && $cart_item['quantity'] > 0) {
            $brand_terms = get_the_terms($_product->get_id(), 'product_brand');
            $brand = !empty($brand_terms) && !is_wp_error($brand_terms) ? esc_html($brand_terms[0]->name) : 'Brand';
            $attributes = $cart_item['variation'] ?? [];
            $color = $attributes['attribute_pa_color'] ?? '';
            $size = $attributes['attribute_pa_size'] ?? '';
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
        <div class="flex-1 ml-2 text-sm flex flex-col justify-between border border-gray-300 transition-opacity duration-300">
            <div class="px-4 pt-2">
                <p class="text-[13px] font-semibold text-black leading-tight my-1"><?php echo $brand; ?></p>
                <p class="text-[18px] text-black mb-2 leading-snug my-1"><?php echo $_product->get_name(); ?></p>
            </div>

            <div class="px-4 flex justify-between py-2 text-[13px] border-t border-gray-300 text-black">
                <div class="space-y-1">
                    <p class="my-1">Size</p>
                    <p class="my-1">Color</p>
                </div>
                <div class="text-right space-y-1 font-medium">
                    <p class="my-1"><?php echo esc_html($size); ?></p>
                    <p class="my-1"><?php echo esc_html($color); ?></p>
                </div>
            </div>

            <div class="px-4 flex justify-between items-center">
                <div class="flex divide-x w-fit text-[13px] overflow-hidden">
                    <button type="button"
                            class="px-3 py-1 text-[15px] border-y border-l border-r-0 border-black bg-white hover:bg-black hover:text-white transition-all duration-150 disabled:opacity-50 disabled:cursor-not-allowed"
                            data-action="decrease" data-key="<?php echo esc_attr($cart_item_key); ?>"
                            aria-label="Decrease quantity">−</button>
                    <span class="px-4 py-1 text-center !border-y !border-x-0" data-quantity><?php echo esc_html($cart_item['quantity']); ?></span>
                    <button type="button"
                            class="px-3 py-1 text-[15px] border-y border-l-0 border-r border-black bg-white hover:bg-black hover:text-white transition-all duration-150 disabled:opacity-50 disabled:cursor-not-allowed"
                            data-action="increase" data-key="<?php echo esc_attr($cart_item_key); ?>"
                            aria-label="Increase quantity">+</button>
                </div>

                <div class="text-right font-semibold">
                    <span class="text-red-500 text-[15px]"><?php echo wc_price($_product->get_price() * $cart_item['quantity']); ?></span>
                    <?php if ($_product->is_on_sale()) : ?>
                        <span class="text-gray-400 text-[13px] line-through ml-2"><?php echo wc_price($_product->get_regular_price()); ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="px-4 py-2 flex gap-2 mt-2 border-t border-gray-300">
                <button type="button"
                        class="remove-btn flex items-center bg-white gap-2 border border-black text-black text-[14px] px-4 py-2 transition-all hover:bg-yellow-100 disabled:opacity-50 disabled:cursor-not-allowed"
                        data-action="remove" data-key="<?php echo esc_attr($cart_item_key); ?>">
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/icons/remove-icon.svg" alt="Remove" class="w-4 h-4" />
                    Remove
                </button>
<button type="button"
        class="wishlist-toggle flex items-center bg-white gap-2 border border-black text-black text-[14px] px-4 py-2 transition-all hover:bg-yellow-100 disabled:opacity-50 disabled:cursor-not-allowed"
        data-product-id="<?php echo esc_attr($product_id); ?>"
        data-cart-item-key="<?php echo esc_attr($cart_item_key); ?>">
    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/icons/heart-icon.svg" alt="Wishlist" class="w-4 h-4" />
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
<script>
var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
var cart_nonce = '<?php echo wp_create_nonce('cart_nonce'); ?>';
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const $cartItems = document.querySelector('#cart-items');
    if (!$cartItems) return;

    $cartItems.addEventListener('click', function (e) {
        const target = e.target.closest('button');
        if (!target) return;

        const key = target.dataset.key;
        const item = target.closest('.cart-item');

        // Move to wishlist
        if (target.classList.contains('wishlist-toggle')) {
            const productId = target.dataset.productId;
            handleAjax('move_to_wishlist', { cart_item_key: key, product_id: productId }, (response) => {
                if (response.success) {
                    item.remove(); // Remove the item from the cart UI
                } else {
                    alert('Failed to move item to wishlist.');
                }
            });
        }

        // Update quantity (increase/decrease)
        if (target.dataset.action === 'increase' || target.dataset.action === 'decrease') {
            let qty = parseInt(item.querySelector('[data-quantity]').innerText);
            if (target.dataset.action === 'increase') qty++;
            if (target.dataset.action === 'decrease' && qty > 1) qty--;
            
            handleAjax('update_cart_item', { cart_item_key: key, quantity: qty }, (response) => {
                if (response.success) {
                    item.querySelector('[data-quantity]').innerText = qty;
                } else {
                    alert('Failed to update quantity.');
                }
            });
        }

        // Remove item from cart
        if (target.dataset.action === 'remove') {
            handleAjax('remove_cart_item', { cart_item_key: key }, (response) => {
                if (response.success) {
                    item.remove();
                } else {
                    alert('Failed to remove item.');
                }
            });
        }
    });
});

function handleAjax(action, data, onSuccess) {
    fetch(ajaxurl, {
        method: 'POST',
        body: new URLSearchParams({
            ...data,
            action: action,
            security: cart_nonce // This nonce should be generated and passed correctly
        }),
    })
    .then(res => res.json())
    .then(response => {
        if (response.success && onSuccess) onSuccess(response);
    })
    .catch(error => {
        console.error('AJAX request failed:', error);
    });
}

</script>