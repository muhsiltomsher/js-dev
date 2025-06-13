<div class="mx-auto pt-[30px] px-[15px]">
  <div class="flex items-center justify-between border-b border-yellow-200 pb-2 mb-6">
    <h2 class="text-lg sm:text-xl md:text-2xl font-semibold uppercase tracking-wide">
      
    
     <?php
    echo apply_filters( 'wpml_translate_single_string', 'Shop by Brands', 'junior-salon', 'Shop by Brands' );
  ?>
  
  
  </h2>
    <div class="flex items-center gap-2">
      <button class="brand-swiper-prev w-8 h-8 rounded-full border border-gray-300 flex items-center justify-center transition hover:bg-gray-200 hover:border-gray-400">
        <svg class="w-4 h-4 text-black" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path d="M15 19l-7-7 7-7" />
        </svg>
      </button>
      <button class="brand-swiper-next w-8 h-8 rounded-full border border-gray-300 flex items-center justify-center transition hover:bg-gray-200 hover:border-gray-400">
        <svg class="w-4 h-4 text-black" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path d="M9 5l7 7-7 7" />
        </svg>
      </button>
    </div>
  </div>

  <div class="swiper brandSwiper px-[15px]">
    <div class="swiper-wrapper">
      <?php
      $exclude_uncategorized = get_option('default_product_cat');
      $brands = get_terms([
        'taxonomy'   => 'product_brand',
        'number'     => 12,
        'hide_empty' => false,
        'exclude'    => [$exclude_uncategorized],
      ]);

      foreach ($brands as $brand) :
        $thumbnail_id = get_term_meta($brand->term_id, 'thumbnail_id', true);
        $image_url = wp_get_attachment_url($thumbnail_id);
      ?>
        <div class="swiper-slide w-full">
          <a href="<?php echo get_term_link($brand); ?>" class="block group w-full">
            <div class="w-full aspect-[4/5] overflow-hidden relative">

              <!-- Skeleton Placeholder -->
              <div class="skeleton absolute inset-0 bg-gray-200 animate-pulse rounded z-0"></div>

              <!-- Image -->
              <img
                src="<?php echo esc_url($image_url); ?>"
                alt="<?php echo esc_attr($brand->name); ?>"
                loading="lazy"
                class="brand-img w-full h-full object-cover opacity-0 transition-opacity duration-300 ease-in-out group-hover:opacity-80 relative z-10"
                onload="this.classList.add('opacity-100'); this.previousElementSibling.style.display='none';"
              />

            </div>
          </a>
        </div>
      <?php endforeach; ?>
    </div>
    <div class="swiper-pagination mt-6"></div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    new Swiper('.brandSwiper', {
      loop: true,
      grabCursor: true,
      autoplay: false,
      navigation: {
        nextEl: '.brand-swiper-next',
        prevEl: '.brand-swiper-prev',
      },
      slidesPerView: 2,
      spaceBetween: 12,
      breakpoints: {
        640: { slidesPerView: 3, spaceBetween: 16 },
        768: { slidesPerView: 4, spaceBetween: 20 },
        1024: { slidesPerView: 5, spaceBetween: 24 },
        1280: { slidesPerView: 7, spaceBetween: 24 },
      }
    });
  });
</script>
