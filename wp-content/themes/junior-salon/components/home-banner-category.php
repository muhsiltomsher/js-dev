<?php
$exclude_uncategorized = get_option('default_product_cat');
$parent_banner_cat = get_term_by('slug', 'home-banner-category', 'product_cat');

$banner_categories = [];

if ($parent_banner_cat && !is_wp_error($parent_banner_cat)) {
    $banner_categories = get_terms([
        'taxonomy'   => 'product_cat',
        'hide_empty' => false,
        'parent'     => $parent_banner_cat->term_id,
        'exclude'    => [$exclude_uncategorized],
        'number'     => 3,
    ]);
}
?>

<?php if (!empty($banner_categories) && count($banner_categories) === 3): ?>
  <?php
    $image_urls = array_map(function($cat) {
        $id = get_term_meta($cat->term_id, 'thumbnail_id', true);
        return wp_get_attachment_url($id);
    }, $banner_categories);
  ?>

  <style>
    .bg-fade {
      transition: opacity 0.6s ease-in-out;
      position: absolute;
      inset: 0;
      background-size: cover;
      background-position: center;
      z-index: 0;
      opacity: 0;
    }
    .bg-visible {
      opacity: 1;
    }
    .bg-overlay {
      position: absolute;
      inset: 0;
      background-color: rgb(0 0 0 / 25%);
      z-index: 1;
    }

    .hover-underline::after {
      content: "";
      display: block;
      height: 2px;
      width: 0;
      background: white;
      transition: width 0.3s ease;
    }
    .hover-underline:hover::after {
      width: 100%;
    }
  </style>

  <section class="pt-[30px] px-[0px]">
    <div 
      class="relative w-full h-auto md:h-[480px] grid grid-cols-1 md:grid-cols-3 text-white text-center uppercase" 
      x-data="{ active: 0 }" 
      @mouseenter="$el.querySelectorAll('[data-index]').forEach(el => el.addEventListener('mouseenter', e => active = e.target.dataset.index))"
      x-init="$nextTick(() => active = 0)"
    >

      <!-- Background layers -->
      <?php foreach ($image_urls as $i => $url): ?>
        <div 
          class="bg-fade" 
          :class="{ 'bg-visible': active == <?php echo $i; ?> }" 
          style="background-image: url('<?php echo esc_url($url); ?>');"
          loading="lazy"
        ></div>
      <?php endforeach; ?>

      <!-- Overlay -->
      <div class="bg-overlay"></div>

      <!-- Sections -->
      <?php foreach ($banner_categories as $index => $cat): 
        $border_class = ($index === 0 || $index === 1) ? 'md:border-r md:border-white/30' : '';
      ?>
        <div 
          class="relative z-10 group flex items-center justify-center px-4 sm:px-6 py-10 md:py-0 <?php echo $border_class; ?>" 
          data-index="<?php echo $index; ?>"
        >
          <div class="w-full">
            <p class="text-sm tracking-wider mb-1 text-white">
              <?php echo strtoupper(esc_html($cat->description)); ?>
            </p>
            <h2 class="text-2xl font-bold mb-2 mt-1 text-white">
              <?php echo strtoupper(esc_html($cat->name)); ?>
            </h2>
            <a 
              href="<?php echo get_term_link($cat); ?>" 
              class="inline-block text-sm text-white underline-offset-4 transition-all duration-300 hover-underline"
            >
  <?php echo apply_filters('wpml_translate_single_string', 'SHOP NOW', 'junior-salon', 'SHOP NOW'); ?>
              </a>
          </div>
        </div>
      <?php endforeach; ?>

    </div>
  </section>
<?php endif; ?>
