<?php defined('ABSPATH') || exit; ?>

<div id="drawer-search" class="drawer-overlay fixed inset-0 bg-black/50 z-50 hidden">
  <div class="drawer-content absolute right-0 top-0 h-full w-[300px] bg-white p-3 overflow-y-auto shadow-xl transform translate-x-full transition-transform duration-300 ease-in-out">
    <div class="flex justify-between items-center border-b pb-3 mb-4">
      <h2 class="text-lg font-semibold">Search</h2>
      <button onclick="closeDrawer('drawer-search')" class="text-gray-600 hover:text-black text-lg">✕</button>
    </div>
    <?php echo do_shortcode('[fibosearch]'); ?>
  </div>
</div>
