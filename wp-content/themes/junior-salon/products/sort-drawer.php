<h2 class="text-lg font-semibold mb-4"><?php echo apply_filters('wpml_translate_single_string', 'SORT BY', 'junior-salon', 'SORT BY'); ?>
 </h2>

<div class="flex flex-col gap-3">
  <?php
  $options = [
    'popular'      => 'Most Popular',
    'best-selling' => 'Best Selling',
    'a-z'          => 'Alphabetically, A-Z',
    'z-a'          => 'Alphabetically, Z-A',
    'low-high'     => 'Price, Low to High',
    'high-low'     => 'Price, High to Low',
    'old-new'      => 'Date, Old to New',
    'new-old'      => 'Date, New to Old',
  ];

  foreach ($options as $value => $label) :
  ?>
    <label class="flex items-center gap-2 text-sm">
      <input type="radio" name="sort" value="<?php echo esc_attr($value); ?>" class="form-radio text-black" />
      <?php echo esc_html($label); ?>
    </label>
  <?php endforeach; ?>
</div>
