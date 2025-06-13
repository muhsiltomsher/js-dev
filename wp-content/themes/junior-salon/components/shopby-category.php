<?php
$current_lang = defined('ICL_LANGUAGE_CODE') ? ICL_LANGUAGE_CODE : 'en';



if ($current_lang === 'ar') {

$exclude_uncategorized = get_option('default_product_cat');
$parent_home_category = get_term_by('slug', 'الفئة-الرئيسية', 'product_cat');
} else {
  $exclude_uncategorized = get_option('default_product_cat');
$parent_home_category = get_term_by('slug', 'home-category', 'product_cat');
  
}

$all_home_categories = [];

if ($parent_home_category && !is_wp_error($parent_home_category)) {
    $all_home_categories = get_terms([
        'taxonomy'   => 'product_cat',
        'hide_empty' => false,
        'parent'     => $parent_home_category->term_id,
        'exclude'    => [$exclude_uncategorized],
    ]);
}

$categories_row_1 = array_slice($all_home_categories, 0, 4);
$categories_row_2 = array_slice($all_home_categories, 4, 5);
?>

<div class="mx-auto pt-[30px] px-[15px]">
    <div class="flex items-center justify-between border-b border-yellow-200 pb-2 mb-6">
         <?php echo apply_filters( 'wpml_translate_single_string', 'Shop by Category', 'junior-salon', 'Shop by Category' ); ?>

     <a href="<?php echo esc_url(site_url($current_lang === 'ar' ? '/ar/المنتجات/' : '/products/')); ?>">
  <?php echo apply_filters('wpml_translate_single_string', 'Shop All Products', 'junior-salon', 'Shop All Products'); ?>
</a>

    </div>

    <!-- First Row (Grid) -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <?php foreach ($categories_row_1 as $cat): ?>
            <?php
            $thumbnail_id = get_term_meta($cat->term_id, 'thumbnail_id', true);
            $image_url = wp_get_attachment_url($thumbnail_id);
            ?>
            <a href="<?php echo get_term_link($cat); ?>" class="block group w-full">
                <div class="w-full aspect-[4/5] overflow-hidden relative">
                    <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($cat->name); ?>"
                         loading="lazy"
                         class="w-full h-full object-cover transition-opacity duration-300 ease-in-out group-hover:opacity-80" />
                </div>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Skeleton for Row 2 -->
    <div id="category-row2-skeleton" class="grid grid-cols-2 sm:grid-cols-5 gap-4 sm:hidden mb-6">
        <?php for ($i = 0; $i < 5; $i++): ?>
            <div class="animate-pulse bg-white border border-black/10 rounded-lg p-2 aspect-[4/5]">
                <div class="w-full h-full bg-gray-200 rounded"></div>
            </div>
        <?php endfor; ?>
    </div>

    <!-- Swiper on Mobile -->
    <div class="sm:hidden relative hidden" id="category-swiper-wrapper">
        <div class="swiper categorySwiper px-[15px]">
            <div class="swiper-wrapper">
                <?php foreach ($categories_row_2 as $cat): ?>
                    <?php
                    $thumbnail_id = get_term_meta($cat->term_id, 'thumbnail_id', true);
                    $image_url = wp_get_attachment_url($thumbnail_id);
                    ?>
                    <div class="swiper-slide shrink-0 w-36">
                        <a href="<?php echo get_term_link($cat); ?>" class="block group">
                            <div class="w-full aspect-[4/5] overflow-hidden">
                                <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($cat->name); ?>"
                                     loading="lazy"
                                     class="w-full h-full object-cover transition-opacity duration-300 ease-in-out group-hover:opacity-80" />
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="absolute bottom-0 left-[15px] right-[15px] h-[2px] bg-yellow-300 opacity-50 rounded-full mt-2"></div>
    </div>

    <!-- Desktop Grid -->
    <div class="hidden sm:grid grid-cols-2 sm:grid-cols-5 gap-4">
        <?php foreach ($categories_row_2 as $cat): ?>
            <?php
            $thumbnail_id = get_term_meta($cat->term_id, 'thumbnail_id', true);
            $image_url = wp_get_attachment_url($thumbnail_id);
            ?>
            <a href="<?php echo get_term_link($cat); ?>" class="block group w-full">
                <div class="w-full aspect-[4/5] overflow-hidden relative">
                    <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($cat->name); ?>"
                         loading="lazy"
                         class="w-full h-full object-cover transition-opacity duration-300 ease-in-out group-hover:opacity-80" />
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const skeleton = document.getElementById('category-row2-skeleton');
    const swiperWrapper = document.getElementById('category-swiper-wrapper');

    setTimeout(() => {
        skeleton.style.display = 'none';
        swiperWrapper.classList.remove('hidden');
    }, 500); // simulate loading

    new Swiper('.categorySwiper', {
        slidesPerView: 2,
        spaceBetween: 16,
        loop: false,
        grabCursor: true,
        breakpoints: {
            640: { slidesPerView: 4 },
            768: { slidesPerView: 5 },
            1024: { slidesPerView: 6 },
        }
    });
});
</script>
