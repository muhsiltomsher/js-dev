<div x-data="{
    open: false,
    loading: false,
    coupons: [],
    activeCoupon: '',
    init() {
        fetch('<?php echo esc_url(site_url('/wp-json/custom/v1/coupons')); ?>')
            .then(res => res.json())
            .then(data => this.coupons = data);
    },
    applyCoupon(code) {
        const trimmed = code.trim().toUpperCase();
        this.$refs.manualCoupon.value = trimmed;
        this.loading = true;

        applyCouponJS(trimmed, (success) => {
            this.loading = false;
            if (success) {
                this.activeCoupon = trimmed;
            }
        });
    },
    removeCoupon() {
        this.loading = true;

        removeCouponJS((success) => {
            this.loading = false;
            if (success) {
                this.activeCoupon = '';
                this.$refs.manualCoupon.value = '';
            }
        });
    }
}" x-init="init" class="mt-6 border-t border-gray-200 pt-4">

    <!-- Toggle Header -->
    <div class="flex justify-between items-center cursor-pointer bg-gray-100 px-4 py-2 hover:bg-gray-200"
         @click="open = !open">
        <div class="flex items-center gap-2">
            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/icons/coupon-icon.svg"
                 alt="Coupon" class="w-5 h-5" />
            <h3 class="text-sm font-semibold text-black"><?php echo apply_filters( 'wpml_translate_single_string', 'Apply Coupon', 'Cart Texts', 'Apply Coupon' ); ?>
 </h3>
        </div>
        <div class="ml-2 text-gray-600 text-sm">
            <template x-if="!open"><i class="fi fi-rr-plus"></i></template>
            <template x-if="open"><i class="fi fi-rr-minus"></i></template>
        </div>
    </div>

    <!-- Expandable Content -->
    <div x-show="open"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-y-0"
         x-transition:enter-end="opacity-100 scale-y-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-y-100"
         x-transition:leave-end="opacity-0 scale-y-0"
         class="mt-4 space-y-4"
         style="display: none;">

        <!-- Feedback Message with Remove -->
        <div id="coupon-message" class="hidden text-sm p-2 rounded flex justify-between items-center gap-4">
            <span class="coupon-msg-text"></span>
            <button type="button"
                    @click="removeCoupon"
                    class="text-xs px-3 py-1 border border-red-500 text-red-600 rounded hover:bg-red-500 hover:text-white transition">
                <?php echo apply_filters( 'wpml_translate_single_string', 'Remove', 'Cart Texts', 'Remove' ); ?>

            </button>
        </div>

        <!-- Suggested Coupons -->
        <div class="flex flex-wrap gap-2">
            <template x-for="coupon in coupons" :key="coupon.code">
                <div class="relative group">
                    <button type="button"
                            @click="applyCoupon(coupon.code)"
                            class="text-xs font-semibold text-green-600 px-4 py-1 border border-dashed border-green-500 rounded-full bg-green-50 disabled:opacity-50 disabled:cursor-not-allowed"
                            :disabled="loading || activeCoupon === coupon.code"
                            :class="activeCoupon === coupon.code ? 'bg-green-100 border-solid font-bold' : ''"
                            x-text="coupon.code">
                    </button>
                    <div class="absolute z-10 hidden group-hover:block bg-white text-gray-700 text-xs border px-2 py-1 mt-1 rounded shadow max-w-xs">
                        <span x-text="coupon.description"></span>
                    </div>
                </div>
            </template>
        </div>

        <!-- Manual Entry -->
        <div class="flex gap-2 items-center">
            <input type="text"
                   x-ref="manualCoupon"
                   placeholder="Enter coupon code"
                   class="w-full px-3 py-2 text-sm border border-black outline-none uppercase"
                   :disabled="loading" />

            <button type="button"
                    class="bg-white px-4 py-2 text-sm border font-semibold border-black text-black hover:bg-black hover:text-white transition disabled:opacity-50 disabled:cursor-not-allowed"
                    :disabled="loading"
                    @click="activeCoupon ? removeCoupon() : applyCoupon($refs.manualCoupon.value)">
                <span x-show="!loading" x-text="activeCoupon ? 'REMOVE' : 'APPLY'"></span>
                <svg x-show="loading" class="animate-spin h-4 w-4 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none"
                     viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                          d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
            </button>
        </div>

    </div>
</div>




<script>


function removeCouponJS(done) {
    const msgBox = document.getElementById('coupon-message');
    const msgText = msgBox.querySelector('.coupon-msg-text') || msgBox;

    const formData = new FormData();
    formData.append('action', 'remove_coupon_ajax');
    formData.append('security', apply_coupon_nonce);

    fetch(ajaxurl, {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        msgBox.classList.remove('hidden');
        msgText.textContent = data.data.message;

        if (data.success) {
            msgBox.className = 'text-yellow-700 bg-yellow-100 border border-yellow-300 p-2 rounded';

            const subtotal = document.querySelector('.subtotal-html');
            const shipping = document.querySelector('.shipping-html');
            const discount = document.querySelector('.discount-html');
            const total = document.querySelector('.total-html');

            if (subtotal) subtotal.innerHTML = data.data.subtotal;
            if (shipping) shipping.innerHTML = data.data.shipping;
            if (discount) discount.innerHTML = data.data.discount;
            if (total) total.innerHTML = data.data.total;

            done(true);
        } else {
            msgBox.className = 'text-red-700 bg-red-100 border border-red-300 p-2 rounded';
            done(false);
        }
    })
    .catch(() => {
        msgText.textContent = 'Unexpected error removing coupon.';
        msgBox.className = 'text-red-700 bg-red-100 border border-red-300 p-2 rounded';
        msgBox.classList.remove('hidden');
        done(false);
    });
}





</script>
