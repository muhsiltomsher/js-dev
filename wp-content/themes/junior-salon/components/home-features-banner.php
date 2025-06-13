<?php
$current_lang = defined('ICL_LANGUAGE_CODE') ? ICL_LANGUAGE_CODE : 'en';

$page_id = ($current_lang === 'ar') ? 624 : get_option('page_on_front');

$feature_banner = get_field('feature_banner', $page_id);
$feature_banner_url = $feature_banner['url'] ?? '';
?>


<section class="mt-[30px] px-[15px] relative">
  <!-- Background Image -->
  <div class="absolute inset-0 bg-cover bg-center z-0" style="background-image: url('<?php echo esc_url($feature_banner_url); ?>');"></div>
  <!-- Dark Overlay -->
  <div class="absolute inset-0 bg-black/60 z-0"></div>

  <div class="relative z-10 max-w-screen-xl mx-auto py-20">
    <!-- Swiper Wrapper -->
    <div class="swiper featureBannerSwiper relative">
      <div class="swiper-wrapper">

        <?php
        $args = [
            'post_type' => 'home-features-banner',
            'posts_per_page' => 6,
        ];
        $banner_query = new WP_Query($args);

        if ($banner_query->have_posts()) :
            while ($banner_query->have_posts()) : $banner_query->the_post();
        ?>
          <div class="swiper-slide">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 items-center text-white">
              <!-- Left: Image -->
              <div>
                <?php if (has_post_thumbnail()) :
                  the_post_thumbnail('medium', [
                    'class' => 'w-full max-w-md h-auto shadow-md object-cover',
                    'loading' => 'lazy'
                  ]);
                endif; ?>
              </div>

              <!-- Right: Text -->
              <div class="md:pr-[50px]">
                <h2 class="text-3xl font-semibold mb-4 uppercase"><?php the_title(); ?></h2>
                <div class="text-md font-normal leading-relaxed"><?php the_content(); ?></div>
              </div>

            </div>
          </div>
        <?php
            endwhile;
        endif;
        wp_reset_postdata();
        ?>

      </div>

      <!-- Navigation Buttons -->
      <div class="swiper-button-prev !text-white !w-10 !h-10 !left-0"></div>
      <div class="swiper-button-next !text-white !w-10 !h-10 !right-0"></div>
    </div>
  </div>
</section>

<!-- Swiper Init Script -->
<script>
  document.addEventListener('DOMContentLoaded', function () {
    new Swiper('.featureBannerSwiper', {
      loop: true,
      speed: 700,
      autoplay: {
        delay: 4000,
        disableOnInteraction: false,
      },
      navigation: {
        nextEl: '.swiper-button-next',
        prevEl: '.swiper-button-prev',
      },
    });
  });
</script>
