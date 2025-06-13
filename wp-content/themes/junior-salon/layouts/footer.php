<footer class="bg-[#1f1f1f] text-white font-sans text-sm [&_*]:text-white [&_a]:no-underline [&_a:hover]:no-underline">
  <div class="container mx-auto px-4">

    <!-- Top Grid: Logo + Menus + Newsletter -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
      
      <!-- LEFT: Logo + Menus -->
      <div>
        <div class="logo flex items-center mb-4">
          <a href="<?php echo esc_url(home_url('/')); ?>" class="block">
            <?php 
              $footer_logo = get_theme_mod('junior_salon_footer_logo');
              if ($footer_logo) {
                echo '<img src="' . esc_url($footer_logo) . '" alt="Footer Logo" class="w-[150px] h-auto block" loading="eager">';
              } else {
                echo '<h1 class="text-2xl font-bold">' . esc_html(get_bloginfo('name')) . '</h1>';
              }
            ?>
          </a>
        </div>

        <p class="text-xs uppercase tracking-widest mb-6">Luxury Kidswear</p>

        <div class="grid grid-cols-2 sm:grid-cols-3 gap-6">
          <div>
            <h4 class="uppercase font-semibold text-xs mb-3">SHOP</h4>
            <?php wp_nav_menu([
              'theme_location' => 'footer1',
              'container' => false,
              'menu_class' => 'space-y-2 list-none text-sm',
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
              'menu_class' => 'space-y-2 list-none text-sm',
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
              'menu_class' => 'space-y-2 list-none text-sm',
              'depth' => 1,
              'fallback_cb' => false,
              'link_class' => 'hover:text-yellow-400 transition-colors no-underline',
            ]); ?>
          </div>
        </div>
      </div>

      <!-- RIGHT: Promo Image + Newsletter -->
      <div>
        <?php 
          $promo_image = get_theme_mod('junior_salon_newsletter_image');
          if (!empty($promo_image)) {
              $webp_promo = preg_replace('/\.(jpg|jpeg|png)$/i', '.webp', $promo_image);
              $webp_exists = @getimagesize($webp_promo);
        ?>
<div class="max-w-full overflow-hidden">
  <picture>
    <?php if ($webp_exists): ?>
      <source srcset="<?php echo esc_url($webp_promo); ?>" type="image/webp">
    <?php endif; ?>
    <img src="<?php echo esc_url($promo_image); ?>" alt="Newsletter Image" class="rounded-xl max-w-full w-full mb-4">
  </picture>
</div>

        <?php } ?>
        
        <p class="text-xs tracking-wide mb-1">KEEP ME UPDATED</p>
        <h3 class="text-xl font-semibold mb-2">Newsletter</h3>
        <p class="text-sm mb-4">Subscribe to get notified about product launches, special offers and company news.</p>

        <?php echo do_shortcode('[wpforms id="573"]'); ?>
      </div>

    </div>

    <!-- Bottom Row: Social + Payment -->
    <div class="px-6 md:px-8 mt-10 pt-6 border-t border-gray-700 flex flex-col md:flex-row justify-between items-center gap-4">
      <div class="flex items-center gap-4">
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

      <div class="flex items-center gap-2">
        <?php
          $payment_image = get_theme_mod('junior_salon_payment_icons');
          if ($payment_image) {
            echo '<img src="' . esc_url($payment_image) . '" alt="Payment Icons" class="h-6 w-auto" loading="lazy">';
          }
        ?>
      </div>
    </div>

    <!-- Copyright -->
    <div class="text-center text-xs mt-4 mb-4 px-6">
      <?php 
        echo wp_kses_post(get_theme_mod('junior_salon_footer_copyright', '© ' . date('Y') . ' Junior Salon. All rights reserved. | Designed by <a href="https://tomsher.co" target="_blank">Tomsher</a>'));
      ?>
    </div>

  </div>

  <!-- Back to Top -->
  <a href="#top" class="fixed bottom-4 right-4 bg-black text-white p-2 rounded hover:bg-yellow-400 transition-all" title="Back to top">
    <i class="fas fa-arrow-up"></i>
  </a>
</footer>



<script>
document.addEventListener('DOMContentLoaded', function () {
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
  observer.observe(document.body, { childList: true, subtree: true });
});
</script>


<?php wp_footer(); ?>


<?php //echo '<!-- Page generated in ' . round(microtime(true) - $start, 3) . ' seconds -->'; ?>
