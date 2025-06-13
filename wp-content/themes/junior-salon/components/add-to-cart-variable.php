<?php
/**
 * Secure Variation Form with Locked Quantity & AJAX Add to Cart + Buy Now + Listing Page Fix
 */
defined('ABSPATH') || exit;

global $product;

$available_variations    = $product->get_available_variations();
$attributes              = $product->get_variation_attributes();
$selected_attributes     = $product->get_default_attributes();

if (!empty($available_variations)) :
?>
<form class="variations_form cart space-y-6"
      action="<?php echo esc_url(apply_filters('woocommerce_add_to_cart_form_action', $product->get_permalink())); ?>"
      method="post"
      enctype="multipart/form-data"
      data-product_id="<?php echo absint($product->get_id()); ?>"
      data-product_variations="<?php echo htmlspecialchars(wp_json_encode($available_variations), ENT_QUOTES, 'UTF-8'); ?>">

    <?php foreach ($attributes as $attribute_name => $options) :
        $sanitized_name = sanitize_title($attribute_name);
        $label = wc_attribute_label($attribute_name);
        $default_value = $selected_attributes[$attribute_name] ?? '';
    ?>
        <div>
            <p class="mb-1 text-sm font-semibold text-gray-700 uppercase"><?php echo esc_html($label); ?></p>
            <div class="flex flex-wrap gap-2" data-attribute-name="<?php echo esc_attr($sanitized_name); ?>">
                <?php foreach ($options as $option) :
                    $option_slug = sanitize_title($option);
                    $input_id = $sanitized_name . '-' . $option_slug;
                    $is_checked = ($default_value === $option) ? 'checked' : '';
                ?>
                    <label for="<?php echo esc_attr($input_id); ?>" class="cursor-pointer">
                        <input type="radio"
                               name="attribute_<?php echo esc_attr($sanitized_name); ?>"
                               value="<?php echo esc_attr($option); ?>"
                               id="<?php echo esc_attr($input_id); ?>"
                               class="hidden peer"
                               <?php echo $is_checked; ?> />
                        <div class="px-3 py-1 border border-gray-300 rounded-full text-sm text-gray-800 peer-checked:bg-black peer-checked:text-white transition-all duration-200">
                            <?php echo esc_html($option); ?>
                        </div>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>

    <!-- Hidden Fixed Quantity -->
    <input type="hidden" name="quantity" value="1">
    <input type="hidden" name="variation_id" class="variation-id-field" value="">
    <input type="hidden" name="add-to-cart" value="<?php echo absint($product->get_id()); ?>">

    <div class="mt-4">
        <button type="submit"
                class="add-to-cart-btn w-full bg-black text-white px-5 py-2.5 rounded text-sm uppercase tracking-wide hover:bg-transparent hover:text-black border border-black transition">
            <span class="btn-text"><?php echo esc_html($product->single_add_to_cart_text()); ?></span>
            <span class="btn-loading hidden">Adding...</span>
        </button>
    </div>
</form>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const form = document.querySelector(".variations_form");
    if (!form) return;

    const variationInput = form.querySelector(".variation-id-field");
    let variations;
    try {
        variations = JSON.parse(form.dataset.product_variations || "[]");
    } catch (e) {
        console.error("Invalid product_variations JSON", e);
        return;
    }

    const btn = form.querySelector(".add-to-cart-btn");
    const btnText = btn.querySelector(".btn-text");
    const btnLoading = btn.querySelector(".btn-loading");

    const getSelectedAttributes = () => {
        const selected = {};
        form.querySelectorAll("[data-attribute-name]").forEach(group => {
            const attrName = group.dataset.attributeName;
            const checked = group.querySelector("input[type=radio]:checked");
            if (checked) {
                selected[`attribute_${attrName}`] = checked.value.trim();
            }
        });
        return selected;
    };

    const findMatchingVariation = (selected) => {
        return variations.find(v => {
            return Object.entries(v.attributes).every(([k, val]) => {
                return selected[k] && selected[k].toLowerCase() === val.toLowerCase();
            });
        });
    };

    const updateVariationInput = () => {
        const selected = getSelectedAttributes();
        const match = findMatchingVariation(selected);
        variationInput.value = match ? match.variation_id : "";
    };

    form.addEventListener("change", updateVariationInput);
    form.addEventListener("input", updateVariationInput);

    form.addEventListener("submit", function (e) {
        e.preventDefault();
        const selected = getSelectedAttributes();
        const match = findMatchingVariation(selected);

        if (!match) {
            alert("Please select all required options.");
            return;
        }

        variationInput.value = match.variation_id;

        btnText.classList.add("hidden");
        btnLoading.classList.remove("hidden");

        const formData = new FormData(form);

        fetch("<?php echo esc_url(admin_url('admin-ajax.php')); ?>", {
            method: "POST",
            credentials: "same-origin",
            body: new URLSearchParams({
                action: "woocommerce_ajax_add_to_cart",
                security: "<?php echo esc_js(wc_get_nonce_value()); ?>",
                product_id: formData.get("add-to-cart"),
                variation_id: formData.get("variation_id"),
                quantity: 1,
                ...Object.fromEntries(formData.entries())
            })
        }).then(res => res.json())
        .then(response => {
            if (response.error && response.product_url) {
                window.location = response.product_url;
            } else if (response.fragments) {
                document.body.dispatchEvent(new CustomEvent("wc_fragment_refresh"));
                alert("Product added to cart!");
            }
        }).finally(() => {
            btnText.classList.remove("hidden");
            btnLoading.classList.add("hidden");
        });
    });
});
</script>


<?php else : ?>
    <div class="text-sm text-red-600">This product is currently unavailable.</div>
<?php endif; ?>
