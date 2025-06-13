
<!-- Loader -->
<div id="ajax-loader" class="fixed top-0 left-0 w-full h-full bg-white bg-opacity-75 flex items-center justify-center z-50 hidden">
  <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-black"></div>
</div>

<!-- FILTER & ORDER Button -->
<button onclick="openDrawerfilter()" class="flex items-center bg-transparent group m-0 text-lg font-semibold px-4 py-2 hover:text-black">
 <?php echo apply_filters('wpml_translate_single_string', 'FILTER & ORDER', 'junior-salon', 'FILTER & ORDER'); ?>

</button>

<!-- Drawer Filter Container -->
<div id="drawer-container-filter" class="fixed top-0 right-0 w-80 h-full bg-white shadow-lg z-50 p-6 overflow-y-auto hidden transition-transform duration-300 ease-in-out"></div>
<div id="drawer-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden" onclick="closeDrawerfilter()"></div>

<!-- SORT BY Button -->
<button onclick="openDrawer('Sort By', '<?php echo esc_url(home_url('/wp-content/themes/junior-salon/products/sort-drawer.php')); ?>')" class="flex items-center bg-transparent group m-0">
  <span class="text-lg"><?php echo apply_filters('wpml_translate_single_string', 'SORT BY', 'junior-salon', 'SORT BY'); ?>
 </span>
</button>

<!-- Sort Drawer Container -->
<div id="drawer" class="fixed inset-0 bg-gray-900 bg-opacity-50 z-50 hidden">
  <div class="absolute top-0 right-0 bg-white w-3/4 md:w-1/2 h-full p-6 transform transition-transform duration-300 ease-in-out translate-x-full" id="drawer-panel">
    <button id="close-drawer" class="absolute top-4 right-4 text-xl text-gray-500 hover:text-gray-700">&times;</button>
    <div id="drawer-content"></div>
  </div>
</div>
