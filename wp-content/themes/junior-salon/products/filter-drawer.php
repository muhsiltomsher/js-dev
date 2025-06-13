<h1 class="text-lg font-semibold mb-4"> 
  
<?php echo __('FILTER & ORDER', 'junior-salon'); ?>
</h1>

<!-- Category -->
<h2 class="text-sm font-semibold uppercase mb-3">
  
<?php echo __('Category', 'junior-salon'); ?>

</h2>
<?php echo do_shortcode('[product_categories_checkbox include="27, 22, 30, 24, 23, 26, 25, 28, 29"]'); ?>

<!-- Color -->
<h2 class="text-sm font-semibold uppercase mt-6 mb-3">  
  
<?php echo __('Color', 'junior-salon'); ?>


</h2>
<?php echo do_shortcode('[attribute_checkboxes attribute="pa_color"]'); ?>

<!-- Size -->
<h2 class="text-sm font-semibold uppercase mt-6 mb-3"> 
  <?php echo __('Size', 'junior-salon'); ?>


</h2>
<?php echo do_shortcode('[attribute_checkboxes attribute="pa_size"]'); ?>

<!-- Age -->
<h2 class="text-sm font-semibold uppercase mt-6 mb-3">  
    <?php echo __('Age', 'junior-salon'); ?>

</h2>
<?php echo do_shortcode('[age_category_checkboxes]'); ?>



<!-- Brand -->
<h2 class="text-sm font-semibold uppercase mt-6 mb-3" id="filter-brand">
  
    <?php echo __('Brand', 'junior-salon'); ?>

</h2>
<?php echo do_shortcode('[brand_checkboxes]'); ?>

<!-- Price -->
<h2 class="text-sm font-semibold uppercase mt-6 mb-3">
      <?php echo __('Price', 'junior-salon'); ?>

</h2>
<?php echo do_shortcode('[price_filter]'); ?>

<!-- Apply Filters -->
<div class="mt-6">
  <button id="filter-button" class="w-full bg-black text-white px-4 py-2 text-sm font-medium uppercase hover:bg-gray-800 transition">
  <?php echo apply_filters('wpml_translate_single_string', 'Apply Filters', 'junior-salon', 'Apply Filters'); ?>
  </button>
</div>
