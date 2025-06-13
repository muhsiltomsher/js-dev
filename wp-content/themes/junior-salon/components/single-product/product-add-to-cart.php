    <?php
    defined('ABSPATH') || exit;

    global $product;

    if ($product->is_type('variable')) {
    $available_variations = $product->get_available_variations();
    $attributes = $product->get_variation_attributes();
    ?>

    <?php if (!empty($available_variations)) : ?>
    <form class="variations_form cart space-y-6" method="post" enctype="multipart/form-data"
        action="<?php echo esc_url(apply_filters('woocommerce_add_to_cart_form_action', $product->get_permalink())); ?>"
        data-product_id="<?php echo esc_attr($product->get_id()); ?>"
        data-product_variations='<?php echo wc_esc_json(wp_json_encode($available_variations)); ?>'>

        <!-- COLOR Swatches -->
        <?php if (!empty($attributes['pa_color'])) : ?>
        <div>
            <p class="text-sm font-medium uppercase mb-2">Colour</p>
            <div class="flex flex-wrap gap-2" data-attribute-name="pa_color">
                <?php foreach ($attributes['pa_color'] as $color) : ?>
                <label
                    class="swatch-option border rounded-full px-3 py-1 text-xs uppercase cursor-pointer transition border-gray-300 hover:border-black"
                    data-value="<?php echo esc_attr($color); ?>">
                    <?php echo esc_html($color); ?>
                </label>
                <?php endforeach; ?>
            </div>
            <select name="attribute_pa_color" class="hidden attribute-select" data-attribute-name="pa_color">
                <option value=""><?php esc_html_e('Choose an option', 'woocommerce'); ?></option>
                <?php foreach ($attributes['pa_color'] as $color) : ?>
                <option value="<?php echo esc_attr($color); ?>"><?php echo esc_html($color); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>

        <!-- SIZE Options with Price/Stock -->
        <?php if (!empty($attributes['pa_size'])) : ?>
        <div class="max-h-[300px] overflow-y-auto">
            <p class="text-sm font-medium uppercase mb-2 flex justify-between">
                Select Size
                <a href="#" class="text-xs underline text-gray-500">Size Guide</a>
            </p>
            <div class="space-y-2" data-attribute-name="pa_size">
                <?php foreach ($available_variations as $variation) :
                $size  = $variation['attributes']['attribute_pa_size'] ?? '';
                $stock = $variation['max_qty'];
                $price = wc_price($variation['display_price']);
                $variation_id = $variation['variation_id'];
                $low_stock = $stock > 0 && $stock <= 2;
                ?>
                <label class="size-option flex items-center justify-between border rounded-lg p-3 cursor-pointer transition-all duration-200
                                border-gray-300 hover:border-black" data-value="<?php echo esc_attr($size); ?>">
                    <span class="text-sm font-medium uppercase"><?php echo esc_html($size); ?></span>
                    <?php if ($low_stock): ?>
                    <span class="text-xs text-red-500">⚠ Only <?php echo $stock; ?> left!</span>
                    <?php endif; ?>
                    <span class="text-sm font-semibold text-green-600"><?php echo $price; ?></span>
                </label>
                <?php endforeach; ?>
            </div>
            <select name="attribute_pa_size" class="hidden attribute-select" data-attribute-name="pa_size">
                <option value=""><?php esc_html_e('Choose an option', 'woocommerce'); ?></option>
                <?php foreach ($attributes['pa_size'] as $size) : ?>
                <option value="<?php echo esc_attr($size); ?>"><?php echo esc_html($size); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>

        <!-- Quantity -->
        <div>
            <label for="product-qty" class="block text-sm font-medium text-gray-700 uppercase mb-1">Quantity</label>
            <input type="number" id="product-qty" name="quantity" min="1" value="1"
                class="w-24 border border-gray-300 rounded px-3 py-1 text-sm" />
        </div>

        <!-- Hidden Required Fields -->
        <input type="hidden" name="add-to-cart" value="<?php echo esc_attr($product->get_id()); ?>">
        <input type="hidden" name="product_id" value="<?php echo esc_attr($product->get_id()); ?>">
        <input type="hidden" name="variation_id" value="" class="variation-id-field">

        <!-- Buttons -->
        <div class="flex gap-2 mt-4">
            <button type="submit"
                class="flex-1 text-sm uppercase px-6 py-3 bg-black text-white border border-black hover:bg-transparent hover:text-black transition">
                Add To Cart
            </button>
            <button type="button"
                class="wishlist-toggle w-12 h-12 flex items-center justify-center bg-black text-yellow-400 hover:text-pink-500 transition"
                aria-label="Add to wishlist">
                <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                    <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 
                        2 5.42 4.42 3 7.5 3c1.74 0 3.41 0.81 
                        4.5 2.09C13.09 3.81 14.76 3 16.5 3 
                        19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 
                        11.54L12 21.35z" />
                </svg>
            </button>
        </div>

        <div>
            <button type="submit" name="buy-now"
                class="w-full text-sm uppercase px-6 py-3 bg-white text-black border border-black hover:bg-black hover:text-white transition">
                Buy Now
            </button>
        </div>
    </form>

    <script>
document.addEventListener("DOMContentLoaded", function () {
  const form = document.querySelector(".variations_form");
  const variationInput = form.querySelector(".variation-id-field");
  const qtyInput = form.querySelector("#product-qty");
  const variations = JSON.parse(form.dataset.product_variations);

  const updateQtyLimit = () => {
    const selected = getSelectedAttributes();
    const match = findMatchingVariation(selected);
    if (match) {
      qtyInput.max = match.max_qty;
      if (parseInt(qtyInput.value) > match.max_qty) {
        qtyInput.value = match.max_qty;
      }
    } else {
      qtyInput.removeAttribute("max");
    }
  };

  const findMatchingVariation = (selected) => {
    return variations.find(v =>
      Object.entries(v.attributes).every(([k, val]) => selected[k] === val)
    );
  };

  const getSelectedAttributes = () => {
    const selected = {};
    form.querySelectorAll("select.attribute-select").forEach(select => {
      if (select.value) selected[select.name] = select.value;
    });
    return selected;
  };

  const updateVariationId = () => {
    const selected = getSelectedAttributes();
    const match = findMatchingVariation(selected);
    variationInput.value = match ? match.variation_id : "";
    updateQtyLimit();
  };

  const updateAvailabilityUI = () => {
    const selected = getSelectedAttributes();

    form.querySelectorAll("[data-attribute-name]").forEach(group => {
      const attrName = group.dataset.attributeName;
      const selectName = `attribute_${attrName}`;
      const swatches = group.querySelectorAll(".swatch-option, .size-option");

      swatches.forEach(swatch => {
        const value = swatch.dataset.value;

        const isAvailable = variations.some(v => {
          if (!v.is_in_stock) return false;
          const attrs = v.attributes;
          return Object.entries(selected).every(([key, val]) => {
            if (key === selectName) return true;
            return attrs[key] === val;
          }) && attrs[selectName] === value;
        });

        swatch.classList.toggle("opacity-50", !isAvailable);
        swatch.classList.toggle("cursor-not-allowed", !isAvailable);
        swatch.disabled = !isAvailable;
      });
    });
  };

  const resetAttribute = (attr) => {
    const select = form.querySelector(`select[name="attribute_${attr}"]`);
    select.value = "";

    const swatches = form.querySelectorAll(`[data-attribute-name="${attr}"] .swatch-option, .size-option`);
    swatches.forEach(s => s.classList.remove("border-black", "bg-black", "text-white"));
  };

  // Attach click handlers
  form.querySelectorAll("[data-attribute-name]").forEach(group => {
    const attrName = group.dataset.attributeName;
    const select = form.querySelector(`select[name="attribute_${attrName}"]`);
    const swatches = group.querySelectorAll(".swatch-option, .size-option");

    swatches.forEach(swatch => {
      swatch.addEventListener("click", () => {
        if (swatch.classList.contains("opacity-50")) return;

        // If changing color, reset size
        if (attrName === "pa_color") {
          resetAttribute("pa_size");
        }

        // Set select value
        select.value = swatch.dataset.value;

        // Mark active visually
        swatches.forEach(s => s.classList.remove("border-black", "bg-black", "text-white"));
        swatch.classList.add("border-black", "bg-black", "text-white");

        // Trigger change and update
        select.dispatchEvent(new Event("change", { bubbles: true }));
        updateVariationId();
        updateAvailabilityUI();
      });
    });
  });

  // Validate form before submit
  form.addEventListener("submit", function (e) {
    const missing = Array.from(form.querySelectorAll("select.attribute-select")).some(select => !select.value.trim());
    if (missing || !variationInput.value) {
      e.preventDefault();
      alert("Please select all required options.");
    }
  });

  // Init
  updateAvailabilityUI();
});
</script>




    <?php else : ?>
    <div class="border border-yellow-400 bg-yellow-50 text-yellow-800 px-4 py-3 rounded text-sm">
        <?php esc_html_e('Sorry, this product is currently out of stock or unavailable in any variation.', 'woocommerce'); ?>
    </div>
    <?php endif; ?>
    <?php } elseif ($product->is_type('simple')) {
    ?>

    <p class="text-lg font-bold text-gray-800"><?php echo $product->get_price_html(); ?></p>

    <form class="cart space-y-6" method="post" enctype="multipart/form-data"
        action="<?php echo esc_url(apply_filters('woocommerce_add_to_cart_form_action', $product->get_permalink())); ?>">

        <div>
            <label for="product-qty" class="block text-sm font-medium text-gray-700 uppercase mb-1">Quantity</label>
            <input type="number" id="product-qty" name="quantity" min="1" value="1"
                class="w-24 border border-gray-300 rounded px-3 py-1 text-sm" />
        </div>

        <input type="hidden" name="add-to-cart" value="<?php echo esc_attr($product->get_id()); ?>" />

        <button type="submit"
            class="w-full text-sm uppercase px-6 py-3 bg-black text-white border border-black hover:bg-transparent hover:text-black transition">
            <?php echo esc_html($product->single_add_to_cart_text()); ?>
        </button>
    </form>

    <?php
}
?>