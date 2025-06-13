<footer class="bg-[#1f1f1f] text-white font-sans text-sm [&_*]:text-white [&_a]:no-underline [&_a:hover]:no-underline">
    <div class="mx-auto pl-[200px] ">

        <!-- Top Grid: Logo + Menus + Newsletter -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-10">

            <!-- LEFT: Logo + Menus -->
            <div class="flex  flex-col  justify-between">

                  <div class="logo flex items-center pt-[70px]">
        <a href="<?php echo esc_url(home_url('/')); ?>" class="block">
          <?php 
            if (has_custom_logo()) {
              $logo_id = get_theme_mod('custom_logo');
              $logo_url = wp_get_attachment_image_src($logo_id, 'large')[0];
              $webp_url = str_replace(['.jpg', '.png'], '.webp', $logo_url);
              ?>
              <picture>
                <source srcset="<?php echo esc_url($logo_url); ?>" type="image/webp">
                <img src="<?php echo esc_url($logo_url); ?>" 
                     alt="<?php echo esc_attr(get_bloginfo('name')); ?>" 
                     class="w-auto max-w-[180px] h-auto" 
                     loading="eager">
              </picture>
              <?php
            } else {
              ?>
              <h1 class="text-2xl font-bold text-white"><?php echo esc_html(get_bloginfo('name')); ?></h1>
              <?php
            }
          ?>
        </a>
      </div>
      
            


                <div class="grid grid-cols-2 sm:grid-cols-3 gap-6">
                    <div>
                        <h4 class="uppep_ese font-semibold text-xs mb-3">SHOP</h4>
                        <?php wp_nav_menu([
      'theme_location' => 'footer1',
      'container' => false,
      'menu_class' => 'space-y-2 list-none text-sm pl-0',
      'depth' => 1,
      'fallback_cb' => false,
      'link_class' => 'hover:text-yellow-400 transition-colors no-underline',
    ]); ?>
                    </div>
                    <div>
                        <h4 class="uppercase font-semibold text-xs mb-3">INFORMATION</h4>
                        <?php wp_nav_menu([
      'theme_location' => 'footer2',
      'container' => false,
      'menu_class' => 'space-y-2 list-none text-sm pl-0',
      'depth' => 1,
      'fallback_cb' => false,
      'link_class' => 'hover:text-yellow-400 transition-colors no-underline',
    ]); ?>
                    </div>
                    <div>
                        <h4 class="uppercase font-semibold text-xs mb-3">CUSTOMER CARE</h4>
                        <?php wp_nav_menu([
      'theme_location' => 'footer3',
      'container' => false,
      'menu_class' => 'space-y-2 list-none text-sm pl-0',
      'depth' => 1,
      'fallback_cb' => false,
      'link_class' => 'hover:text-yellow-400 transition-colors no-underline',
    ]); ?>
                    </div>
                </div>




                <div class="flex items-center gap-4 justify-between border-t border-gray-700 pt-8 pb-8 mt-10">
                    <span class="text-sm font-semibold">STAY IN TOUCH</span>
                    <div class="flex gap-4 text-2xl">
                        <?php
            $social_links = [
              'facebook_url' => ['icon' => 'fab fa-facebook', 'hover' => 'hover:text-blue-600'],
              'instagram_url' => ['icon' => 'fab fa-instagram', 'hover' => 'hover:text-pink-500'],
              'youtube_url' => ['icon' => 'fab fa-youtube', 'hover' => 'hover:text-red-600'],
            ];
            foreach ($social_links as $key => $val) {
              $url = get_theme_mod($key);
              if ($url) {
                echo '<a href="' . esc_url($url) . '" class="' . esc_attr($val['hover']) . ' transition-all transform hover:scale-110" target="_blank"><i class="' . esc_attr($val['icon']) . '"></i></a>';
              }
            }
          ?>
                    </div>
                </div>

            </div>

            <!-- RIGHT: Promo Image + Newsletter -->
            <div class="overflow-hidden">
                <!-- Top Image -->
                <img src="<?php echo get_template_directory_uri(); ?>/assets/images/footer-r.jpg"
                    alt="Newsletter Kids Image" class="w-full h-auto object-cover" loading="lazy">

                <!-- Newsletter Content -->
                <div class="bg-[#424242] text-white px-6 sm:px-10 py-10">
                    <p class="text-sm tracking-wide mb-2 uppercase">Keep me updated</p>
                    <h3 class="text-2xl font-bold mb-2 mt-2">Newsletter</h3>
                    <p class="text-base text-gray-300 mb-6">
                        Subscribe to get notified about product launches, special offers and company news.
                    </p>

                    <!-- Form -->
                    <form action="#" method="post" class="max-w-xl w-full">
                        <div class="flex items-center">
                            <input type="email" name="newsletter_email" required placeholder="your-email@example.com"
                                class="w-full bg-transparent border-0 border-b border-white placeholder-gray-400 text-white focus:outline-none focus:border-b focus:border-yellow-400 py-4 transition-colors" />

                            <button type="submit"
                                class="text-white rela cursor-pointer hover:text-yellow-400 hover:bg-black transition-colors py-[11.5px] focus:outline-none border-0 border-b border-white bg-transparent">
                                <span class="text-base">→</span>
                            </button>
                        </div>
                    </form>




                </div>
            </div>






        </div>


    </div>

    <!-- Bottom Row: Social + Payment -->
    <div class="bg-[#191614]">
        <div class="container m-auto px-5 py-2 md:px-8 flex flex-col md:flex-row justify-between items-center gap-4">
            <!-- Copyright -->
            <div class="text-xs py-4 text-left w-full md:w-auto text-white">
                © 2025 Juniorsalon. All rights reserved | Designed by
                <a href="https://tomsher.co" target="_blank" class="underline">Tomsher</a>
            </div>

            <!-- Payment Icons -->
            <div class="flex items-center gap-2 justify-end w-full md:w-auto">
                <img src="<?php echo get_template_directory_uri(); ?>/assets/images/payment.png" alt="Payment Icons"
                    class="h-6 w-auto" loading="lazy">
            </div>
        </div>
    </div>


    <!-- Back to Top -->
    <a href="#top" class="fixed bottom-4 right-4 bg-black text-white p-2 rounded hover:bg-yellow-400 transition-all"
        title="Back to top">
        <i class="fas fa-arrow-up"></i>
    </a>


</footer>







<script>
document.addEventListener('DOMContentLoaded', function() {
    const overrideLabels = () => {
        document.querySelectorAll('.yith-wcwl-add-to-wishlist-button__label').forEach(label => {
            const current = label.textContent.trim().toLowerCase();
            if (current === 'add to wishlist') {
                label.textContent = 'Move to Wishlist';
            } else if (current === 'product added!') {
                label.textContent = 'Added to Wishlist';
            } else if (current === 'browse wishlist') {
                label.textContent = 'Already in Wishlist';
            }
        });
    };

    // Initial override
    overrideLabels();

    // Observe future changes
    const observer = new MutationObserver(overrideLabels);
    observer.observe(document.body, {
        childList: true,
        subtree: true
    });
});
</script>


<?php wp_footer(); ?>


<?php echo '<!-- Page generated in ' . round(microtime(true) - $start, 3) . ' seconds -->'; ?>