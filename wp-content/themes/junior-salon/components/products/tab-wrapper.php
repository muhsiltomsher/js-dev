<?php
/**
 * Tab Product Section with 2-Row Grid (No Slider)
 */

defined('ABSPATH') || exit;
$current_lang = defined('ICL_LANGUAGE_CODE') ? ICL_LANGUAGE_CODE : 'en';

if ($current_lang === 'ar') {
    $products = load_products_by_category('ما-هو-ساخن', 'ar');
    $tabs = [
        'ما-هو-ساخن'     => "ما هو ساخن",
        'الأكثر-مبيعاً'   => "الأكثر مبيعاً",
        'sale'           => "أُوكَازيُون",
    ];
} else {
    $products = load_products_by_category('whats-hot', 'en');
    $tabs = [
        'whats-hot'     => "WHAT’S HOT",
        'best-seller'   => "BEST SELLERS",
        'sale'          => "SALE",
    ];
}

?>

<div class="bg-white pt-[30px] px-[15px]">
  <div class="mx-auto">

    <!-- Tabs Header with Arrows -->
    <div class="flex items-center justify-between border-b border-yellow-200 pb-2 mb-6">
      <h2 class="text-lg sm:text-xl md:text-2xl font-semibold uppercase tracking-wide">
         <?php
    echo apply_filters( 'wpml_translate_single_string', 'Explore Trending', 'junior-salon', 'Explore Trending' );
  ?>
      
      </h2>
     <a href="<?php echo esc_url(site_url($current_lang === 'ar' ? '/ar/المنتجات/' : '/products/')); ?>">
  <?php echo apply_filters('wpml_translate_single_string', 'Shop All Products', 'junior-salon', 'Shop All Products'); ?>
</a>
    </div>

    <!-- Tab Buttons Slider -->
    <div class="relative overflow-hidden">
      <div id="tab-slider" class="flex items-center gap-2 sm:gap-3 overflow-x-auto scroll-smooth whitespace-nowrap no-scrollbar pr-6">
        <?php
     
        foreach ($tabs as $slug => $label) {
          echo '<button class="tab-btn shrink-0 px-4 py-1.5 text-sm font-medium text-gray-600 bg-gray-100 border-0 rounded-full transition-all hover:bg-black hover:text-white whitespace-nowrap"
                  data-cat="' . esc_attr($slug) . '">' . esc_html($label) . '</button>';
        }
        ?>
      </div>
    </div>

    <!-- Product Grid: 2 Rows -->
    <div id="product-grid" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 transition-opacity duration-500 ease-in-out mt-6">
      <?php
      if (!empty($products)) {
        $limit = 5; // max 1 rows of 5 products
        $count = 0;
        foreach ($products as $product_id) {
          if ($count++ >= $limit) break;
          $product = wc_get_product($product_id);
          if (!$product) continue;

          setup_postdata($GLOBALS['post'] = get_post($product->get_id()));
          get_template_part('components/products/product-card');
        }
        wp_reset_postdata();
      }
      ?>
    </div>

    <!-- Skeleton Loader Template -->
    <template id="product-skeleton-template">
      <div class="animate-pulse bg-white border border-black/10 rounded-lg p-4 space-y-4">
        <div class="w-full h-[300px] bg-gray-200 rounded"></div>
        <div class="h-4 bg-gray-200 rounded w-3/4 mx-auto"></div>
        <div class="h-4 bg-gray-200 rounded w-1/2 mx-auto"></div>
      </div>
    </template>
  </div>
</div>

<script>
  document.addEventListener("DOMContentLoaded", function () {
    const tabs = document.querySelectorAll(".tab-btn");
    const grid = document.getElementById("product-grid");
    const skeletonTemplate = document.getElementById("product-skeleton-template");
    const tabSlider = document.getElementById("tab-slider");
let currentCat = "<?php echo (defined('ICL_LANGUAGE_CODE') && ICL_LANGUAGE_CODE === 'ar') ? 'ما-هو-ساخن' : 'whats-hot'; ?>";
//alert(currentCat);
   // let currentCat = "whats-hot";
   let currentLang = "<?php echo esc_js($current_lang); ?>";

   
   const cache = {};

    if (tabs.length > 0) {
      tabs[0].classList.add("bg-black", "text-white");
      tabs[0].classList.remove("bg-gray-100", "text-gray-700");
    }

    function showSkeletons(count = 5) {
      grid.innerHTML = "";
      grid.classList.add("opacity-50");
      for (let i = 0; i < count; i++) {
        const clone = skeletonTemplate.content.cloneNode(true);
        grid.appendChild(clone);
      }
    }

    tabs.forEach((tab) => {
      tab.addEventListener("click", () => {
        const cat = tab.getAttribute("data-cat");
        if (cat === currentCat) return;

        currentCat = cat;
//alert(cat);
        tabs.forEach((t) => {
          t.classList.remove("bg-black", "text-white");
          t.classList.add("bg-gray-100", "text-gray-700");
        });
        tab.classList.add("bg-black", "text-white");
        tab.classList.remove("bg-gray-100", "text-gray-700");

        if (cache[cat]) {
          grid.style.opacity = "0.5";
          setTimeout(() => {
            grid.innerHTML = cache[cat];
            grid.classList.remove("opacity-50");
            grid.style.opacity = "1";
          }, 100);
          return;
        }

        requestAnimationFrame(() => {
          showSkeletons();

       fetch(`<?php echo esc_url(admin_url('admin-ajax.php')); ?>?action=load_tab_products&cat=${cat}&lang=${currentLang}`)
   .then((res) => res.text())
            .then((html) => {
              cache[cat] = html;
              grid.style.opacity = "0";
              setTimeout(() => {
                grid.innerHTML = html;
                grid.classList.remove("opacity-50");
                grid.style.opacity = "1";
              }, 200);
            });
        });
      });
    });
  });
</script>

<style>
  .no-scrollbar::-webkit-scrollbar { display: none; }
  .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
