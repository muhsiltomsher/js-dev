<?php
$paged = get_query_var('paged') ? get_query_var('paged') : 1;

$args = array(
    'post_type' => 'product',
    'posts_per_page' => 15,
    'paged' => $paged,
    'orderby' => 'title',
    'order' => 'ASC'
);

$query = new WP_Query($args);
?>

<section class="pt-[30px] px-[15px]">
  <div class="flex flex-col sm:flex-row justify-between items-center border-b border-yellow-300 pb-2 mb-6 gap-3">
    <div class="flex items-center gap-6">
      <button onclick="openDrawerfilter()" class="text-sm font-semibold underline underline-offset-4 text-black hover:text-black transition flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4 6h16M6 12h12M10 18h4"/></svg> Filter & Order
      </button>
    </div>
    <div class="flex items-center gap-6">
      <button onclick="openDrawer('Sort By', '<?php echo esc_url( get_template_directory_uri() . '/products/sort-drawer.php' ); ?>')" class="text-sm font-semibold underline underline-offset-4 text-black hover:text-black transition flex items-center gap-2">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M3 6h18M6 12h12M10 18h4"/></svg> Sort By
      </button>
    </div>
  </div>

  <div class="flex items-center justify-between border-b border-yellow-300 pb-2 mb-6">
    <h2 class="text-base sm:text-lg md:text-xl font-semibold uppercase tracking-wide text-gray-800">
      All Products
    </h2>
    <span class="text-sm text-gray-500"><?php echo $query->found_posts; ?> Products</span>
  </div>

  <div id="product-grid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-5 gap-4">
    <?php
    if ($query->have_posts()) :
      while ($query->have_posts()) : $query->the_post();
        get_template_part('components/products/product-card');
      endwhile;
    else :
      echo '<p class="text-center text-sm text-gray-500 col-span-full">No products found.</p>';
    endif;
    wp_reset_postdata();
    ?>
  </div>

  <?php if ($query->max_num_pages > $paged) : ?>
    <div class="flex justify-center mt-8">
      <button id="load-more" class="px-6 py-2 bg-black text-white rounded-lg hover:bg-gray-900 transition" data-page="<?php echo esc_attr($paged + 1); ?>">
        Load More
      </button>
    </div>
  <?php endif; ?>
</section>

<!-- Loader -->
<div id="ajax-loader" class="fixed top-0 left-0 w-full h-full bg-white bg-opacity-75 flex items-center justify-center z-50 hidden">
  <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-black"></div>
</div>

<!-- Drawer Filter Container -->
<div id="drawer-container-filter" class="fixed top-0 right-0 w-[300px] h-full bg-white z-50 p-6 overflow-y-auto hidden shadow-lg transition-transform duration-300 ease-in-out translate-x-full"></div>
<div id="drawer-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden" onclick="closeDrawerfilter()"></div>

<!-- Sort Drawer Container -->
<div id="drawer" class="fixed inset-0 bg-gray-900 bg-opacity-50 z-50 hidden">
  <div class="absolute top-0 right-0 bg-white w-3/4 md:w-1/2 h-full p-6 transform transition-transform duration-300 ease-in-out translate-x-full" id="drawer-panel">
    <button id="close-drawer" class="absolute top-4 right-4 text-xl text-gray-500 hover:text-gray-700">&times;</button>
    <div id="drawer-content"></div>
  </div>
</div>

<script>
function openDrawerfilter() {
  document.getElementById('drawer-overlay').classList.remove('hidden');
  const drawer = document.getElementById('drawer-container-filter');
  drawer.classList.remove('hidden');
  drawer.classList.remove('translate-x-full');
  document.getElementById('ajax-loader').classList.remove('hidden');

  fetch(ajaxurl, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'action=load_filter_drawer_content'
  })
  .then(res => res.text())
  .then(data => {
    drawer.innerHTML = data;
    document.getElementById('ajax-loader').classList.add('hidden');
  });
}

function closeDrawerfilter() {
  document.getElementById('drawer-overlay').classList.add('hidden');
  const drawer = document.getElementById('drawer-container-filter');
  drawer.classList.add('translate-x-full');
  setTimeout(() => drawer.classList.add('hidden'), 300);
}

function openDrawer(title, url) {
  const drawer = document.getElementById('drawer');
  const panel = document.getElementById('drawer-panel');
  const content = document.getElementById('drawer-content');

  drawer.classList.remove('hidden');
  setTimeout(() => panel.classList.remove('translate-x-full'), 10);
  document.getElementById('ajax-loader').classList.remove('hidden');

  fetch(url)
    .then(res => res.text())
    .then(html => {
      content.innerHTML = html;
      document.getElementById('ajax-loader').classList.add('hidden');
    });
}

function closeDrawer() {
  const drawer = document.getElementById('drawer');
  const panel = document.getElementById('drawer-panel');
  panel.classList.add('translate-x-full');
  setTimeout(() => drawer.classList.add('hidden'), 300);
}

document.getElementById('close-drawer')?.addEventListener('click', closeDrawer);
document.getElementById('drawer')?.addEventListener('click', function(e) {
  if (e.target === this) closeDrawer();
});

</script>