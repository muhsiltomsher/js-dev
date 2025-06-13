<?php
defined('ABSPATH') || exit;

global $product;
if (!isset($product) || !is_a($product, 'WC_Product')) {
    $product = wc_get_product(get_the_ID());
}

$attachment_ids = $product->get_gallery_image_ids();
$hover_image_id = $attachment_ids[0] ?? null;

$is_variable = $product->is_type('variable');
$variations = $is_variable ? $product->get_available_variations() : [];
$attributes = $is_variable ? $product->get_variation_attributes() : [];
?>

<div class="overflow-hidden flex flex-col relative group border border-gray-200">
  <!-- Image Block -->
  <div class="relative block w-full aspect-[4/6] overflow-hidden">
    <!-- Skeleton -->
    <div class="absolute inset-0 bg-gray-200 animate-pulse z-0"></div>

    <!-- Main Image -->
    <img
      src="<?php echo esc_url(get_the_post_thumbnail_url(get_the_ID(), 'medium')); ?>"
      alt="<?php the_title_attribute(); ?>"
      loading="lazy"
      class="w-full h-full object-cover relative z-10 opacity-0 transition-opacity duration-300 group-hover:opacity-0"
      onload="this.classList.add('opacity-100'); this.previousElementSibling.style.display='none';"
    />

    <!-- Hover Image -->
    <?php if ($hover_image_id): ?>
      <img
        src="<?php echo esc_url(wp_get_attachment_image_url($hover_image_id, 'medium')); ?>"
        alt="<?php the_title_attribute(); ?>"
        loading="lazy"
        class="w-full h-full object-cover absolute inset-0 opacity-0 transition-opacity duration-300 group-hover:opacity-100"
      />
    <?php endif; ?>

    <!-- Wishlist -->
    <button class="wishlist-toggle absolute top-3 right-3 z-10 bg-white border border-gray-300 rounded-full shadow-md p-2 w-[40px] h-[40px] flex items-center justify-center transition-all" aria-label="Add to wishlist">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
        class="w-[25px] h-[25px] fill-current text-transparent stroke-black stroke-2 transition-all duration-300 transform">
        <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 
                 4.42 3 7.5 3c1.74 0 3.41 0.81 4.5 
                 2.09C13.09 3.81 14.76 3 16.5 
                 3 19.58 3 22 5.42 22 
                 8.5c0 3.78-3.4 6.86-8.55 
                 11.54L12 21.35z" />
      </svg>
    </button>

    <!-- Hover Content -->
    <div class="absolute shadow bottom-0 left-0 right-0 bg-white/95 text-black py-3 px-[10px] opacity-0 translate-y-full group-hover:translate-y-0 group-hover:opacity-100 transition-all duration-300 z-20">
      <?php if ($is_variable && !empty($attributes)): ?>
        <form class="variation-cart-form flex flex-col w-full max-w-[300px] mx-auto mt-4 gap-2" 
              method="post" 
              action="<?php echo esc_url(apply_filters('woocommerce_add_to_cart_form_action', $product->get_permalink())); ?>"
              data-product_id="<?php echo esc_attr($product->get_id()); ?>"
              data-product_variations='<?php echo wc_esc_json(wp_json_encode($variations)); ?>'>
          
          <!-- Attributes -->
          <?php foreach ($attributes as $attr_key => $options): ?>
            <div class="mb-1 text-[10px] font-semibold uppercase"><?php echo wc_attribute_label($attr_key); ?></div>
            <div class="flex flex-wrap justify-center gap-[8px] variation-hover-form mb-[10px]"
                 data-attribute-name="<?php echo esc_attr($attr_key); ?>">
              <?php foreach ($options as $opt): ?>
                <label class="cursor-pointer transition">
                  <input type="radio" name="attribute_<?php echo esc_attr($attr_key); ?>"
                         value="<?php echo esc_attr($opt); ?>" 
                         class="sr-only peer">
                  <span class="px-[10px] py-1 border border-gray-400 rounded-full text-[10px] uppercase text-black peer-checked:bg-[#DEAF27] peer-checked:text-white peer-checked:border-[#DEAF27] transition">
                    <?php echo esc_html($opt); ?>
                  </span>
                </label>
              <?php endforeach; ?>
            </div>
          <?php endforeach; ?>

          <!-- Hidden Fields -->
          <input type="hidden" name="add-to-cart" value="<?php echo esc_attr($product->get_id()); ?>">
          <input type="hidden" name="product_id" value="<?php echo esc_attr($product->get_id()); ?>">
          <input type="hidden" name="variation_id" value="" class="variation-id-field">
          <input type="hidden" name="quantity" value="1">

          <!-- Buttons -->
          <div class="flex gap-2">
         <button type="button" 
        class="flex-1 text-xs uppercase px-4 py-1.5 bg-black text-white border border-black hover:bg-transparent hover:text-black transition add-to-cart-btn"
        data-type="add">Add to Cart</button>
            <button type="submit"
                    formaction="<?php echo esc_url(wc_get_checkout_url()); ?>"
                    class="flex-1 text-xs uppercase px-4 py-1.5 bg-black text-white border border-black hover:bg-transparent hover:text-black transition add-to-cart-btn"
                    data-type="buy">Buy Now</button>
          </div>
        </form>
      <?php elseif ($product->is_type('simple')): ?>
        <form class="cart flex w-full max-w-[300px] mx-auto mt-2 gap-2" method="post">
          <input type="hidden" name="add-to-cart" value="<?php echo esc_attr($product->get_id()); ?>" />
          <input type="hidden" name="quantity" value="1">
          <button type="submit"
                  class="flex-1 text-xs uppercase px-4 py-1.5 bg-black text-white border border-black hover:bg-transparent hover:text-black transition">
            Add to Cart
          </button>
          <button type="submit"
                  formaction="<?php echo esc_url(wc_get_checkout_url()); ?>"
                  class="flex-1 text-xs uppercase px-4 py-1.5 bg-black text-white border border-black hover:bg-transparent hover:text-black transition">
            Buy Now
          </button>
        </form>
      <?php endif; ?>
    </div>
  </div>

  <!-- Brand -->
  <?php
  $brands = wp_get_post_terms(get_the_ID(), 'product_brand');
  if (!empty($brands) && !is_wp_error($brands)) {
      echo '<div class="text-xs text-center text-gray-500 mt-2">' . esc_html($brands[0]->name) . '</div>';
  }
  ?>

  <!-- Title -->
  <h2 class="text-xs text-center font-medium mt-1 text-gray-800">
    <a href="<?php the_permalink(); ?>" class="text-gray-800 no-underline hover:underline transition-colors duration-200">
      <?php the_title(); ?>
    </a>
  </h2>

  <!-- Price -->
  <div class="text-sm text-center font-bold text-gray-900 mb-2">
    <?php echo $product->get_price_html(); ?>
  </div>
</div><script>
jQuery(function($) {
    $('.variation-cart-form').each(function() {
        const form = $(this);
        const variationInput = form.find('.variation-id-field');
        const variations = JSON.parse(form.attr('data-product_variations'));

        const updateVariation = () => {
           // alert('🔁 updateVariation triggered');

            const selected = {};
            const checkedInputs = form.find('input[type="radio"]:checked');

            checkedInputs.each(function() {
                const name = $(this).attr('name');
                const value = $(this).val();
                selected[name] = value;
            //    alert(`✔️ Selected: ${name} = ${value}`);
            });

         //   alert(`📦 Selected Attributes:\n${JSON.stringify(selected, null, 2)}`);

            // Match variation
            const matched = variations.find(v => {
                return Object.entries(selected).every(([key, val]) => {
                    return v.attributes[key] === val || v.attributes[key] === '';
                });
            });

            if (matched) {
                variationInput.val(matched.variation_id);
           //     alert('✅ Matched Variation ID: ' + matched.variation_id);
            } else {
                variationInput.val('');
             //   alert('❌ No matching variation found.');
            }

            // Update attribute availability
            form.find('.variation-hover-form').each(function() {
                const attrName = $(this).data('attribute-name');
                const selectName = `attribute_${attrName}`;
                const options = $(this).find('label');

                options.each(function() {
                    const val = $(this).find('input').val();
                    const isAvailable = variations.some(v => {
                        return v.is_in_stock &&
                            Object.entries(selected).every(([k, vval]) => {
                                if (k === selectName) return true;
                                return v.attributes[k] === vval || v.attributes[k] === '';
                            }) &&
                            (v.attributes[selectName] === val || v.attributes[selectName] === '');
                    });

                    $(this).toggleClass('opacity-50 cursor-not-allowed', !isAvailable);
                    $(this).find('input').prop('disabled', !isAvailable);
                });
            });
        };

        // Bind attribute change
        form.find('input[type="radio"]').on('change', function() {
            const name = $(this).attr('name');
            if (name.includes('attribute_pa_color')) {
                form.find('input[name^="attribute_pa_size"]').prop('checked', false)
                    .closest('label').removeClass('peer-checked:bg-[#DEAF27] peer-checked:text-white');
             //   alert('🎨 Color changed. Reset size.');
            }
            updateVariation();
        });

        // Handle form submission
form.find('.add-to-cart-btn[data-type="add"]').off('click').on('click', function(e) {

    e.preventDefault();
            const variationId = variationInput.val();
           // const isBuyNow = $(document.activeElement).data('type') === 'buy';
            const totalAttrs = form.find('.variation-hover-form').length;
            const selectedAttrs = form.find('input[type="radio"]:checked').length;

          //  alert(`🛒 Submitting:\nVariation ID: ${variationId}\nSelected: ${selectedAttrs}/${totalAttrs}`);

            if (!variationId || selectedAttrs < totalAttrs) {
             //   alert('⚠️ Please select all required attributes.');
                return;
            }

            const data = {
                action: 'woocommerce_ajax_add_to_cart',
                product_id: form.data('product_id'),
                variation_id: variationId,
                quantity: 1,
                security: '<?php echo wp_create_nonce("add-to-cart"); ?>'
            };

            form.find('input[type="radio"]:checked').each(function() {
                data[$(this).attr('name')] = $(this).val();
            });

          //  alert(`📤 AJAX Payload:\n${JSON.stringify(data, null, 2)}`);

            $.ajax({
                url: wc_add_to_cart_params.ajax_url,
                type: 'POST',
                data: data,
                beforeSend: function() {
                   form.find('.add-to-cart-btn[data-type="add"]').prop('disabled', true).text('Adding...');
         },
                  success: function(response) {
            if (response.success) {
                $(document.body).trigger('added_to_cart', [response.data.fragments, response.data.cart_hash]);
            }
        },
        error: function(xhr, status, error) {
            console.error(error);
        },
            
                complete: function() {
            form.find('.add-to-cart-btn[data-type="add"]').prop('disabled', false).text('Add to Cart');
        }
            });
        });

        // Init
        updateVariation();
    });

    // Wishlist toggle
    $('.wishlist-toggle').on('click', function() {
        const $svg = $(this).find('svg');
        const active = $svg.hasClass('text-pink-500');
        $svg.addClass('scale-150');
        setTimeout(() => $svg.removeClass('scale-150'), 150);

        if (active) {
            $svg.removeClass('text-pink-500 stroke-pink-500').addClass('text-transparent stroke-black');
            $(this).removeClass('border-pink-500').addClass('border-gray-300');
        } else {
            $svg.removeClass('text-transparent stroke-black').addClass('text-pink-500 stroke-pink-500');
            $(this).removeClass('border-gray-300').addClass('border-pink-500');
        }
    });
});
</script>
