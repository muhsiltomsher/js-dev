<!-- Loader -->
<div id="ajax-loader" class="fixed top-0 left-0 w-full h-full bg-white bg-opacity-75 flex items-center justify-center z-50 hidden">
  <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-black"></div>
</div>

<div class="flex justify-between items-center py-4">
  <!-- FILTER & ORDER Button -->
  <button onclick="openDrawerfilter()" class="flex items-center !border-0 gap-2 bg-transparent text-[15px] font-medium px-4 py-2 hover:text-black focus:outline-none transition-all">
    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/icons/filter.svg" alt="Filter" class="w-5 h-5" />
    <?php echo apply_filters('wpml_translate_single_string', 'FILTER & ORDER', 'junior-salon', 'FILTER & ORDER'); ?>
  </button>

  <!-- SORT BY Button -->
  <button onclick="openDrawer('Sort By', '<?php echo esc_url(home_url('/wp-content/themes/junior-salon/products/sort-drawer.php')); ?>')" class="flex items-center !border-0 gap-2 bg-transparent  text-[15px] font-medium px-4 py-2 hover:text-black focus:outline-none transition-all">
    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/icons/sort.svg" alt="Sort" class="w-5 h-5" />
    <?php echo apply_filters('wpml_translate_single_string', 'SORT BY', 'junior-salon', 'SORT BY'); ?>
  </button>
</div>

<!-- Drawer Filter Container -->
<div id="drawer-container-filter" class="fixed top-0 right-0 w-80 h-full bg-white shadow-lg z-50 p-6 overflow-y-auto hidden transition-transform duration-300 ease-in-out"></div>
<div id="drawer-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden" onclick="closeDrawerfilter()"></div>

<!-- Sort Drawer Container -->
<div id="drawer" class="fixed inset-0 bg-gray-900 bg-opacity-50 z-50 hidden">
  <div class="absolute top-0 right-0 bg-white w-3/4 md:w-1/2 h-full p-6 transform transition-transform duration-300 ease-in-out translate-x-full" id="drawer-panel">
    <button id="close-drawer" class="absolute top-4 right-4 text-xl text-gray-500 hover:text-gray-700">&times;</button>
    <div id="drawer-content"></div>
  </div>
</div>
