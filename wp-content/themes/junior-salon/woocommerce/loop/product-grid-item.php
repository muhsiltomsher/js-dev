<?php
global $product;

$attachment_ids = $product->get_gallery_image_ids();
$hover_image_id = $attachment_ids[0] ?? null;

$brands = wp_get_post_terms(get_the_ID(), 'product_brand');
$brand_name = (!empty($brands) && !is_wp_error($brands)) ? esc_html($brands[0]->name) : 'No brand';
?>

<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3">
    <a href="<?php the_permalink(); ?>" class="block">
        <div class="relative group w-full aspect-square overflow-hidden">
            <img 
                src="<?php echo get_the_post_thumbnail_url(get_the_ID(), 'medium'); ?>" 
                class="w-full h-full object-cover transition-opacity duration-300 group-hover:opacity-0" 
                alt="<?php the_title_attribute(); ?>" 
            />
            <?php if ($hover_image_id): ?>
                <img 
                    src="<?php echo wp_get_attachment_image_url($hover_image_id, 'medium'); ?>" 
                    class="w-full h-full object-cover absolute inset-0 opacity-0 transition-opacity duration-300 group-hover:opacity-100" 
                    alt="<?php the_title_attribute(); ?>" 
                />
            <?php endif; ?>
        </div>
    </a>

    <?php echo do_shortcode('[yith_wcwl_add_to_wishlist]'); ?>

    <p class="text-sm text-gray-500"><?php echo $brand_name; ?></p>
    <h3 class="text-base font-semibold text-gray-800"><?php the_title(); ?></h3>
    <p class="text-lg text-gray-700 mt-1"><?php echo wc_price($product->get_price()); ?></p>

    <div class="woocommerce mt-2">
        <?php woocommerce_template_loop_add_to_cart(); ?>
    </div>
</div>
