<?php
/**
 * Product Accordion using Alpine.js (Smooth + Custom UI)
 *
 * @package junior-salon
 */

defined('ABSPATH') || exit;

$sections = [
  'Description' => 'short_description',
  'Material' => 'material',
  'Care instruction' => 'care_instruction',
  'Shipping information' => 'shippinginfo',
];

$index = 0;
$has_data = false;
?>

<div class="border-t border-b divide-y divide-gray-200" x-data="{ open: null }">
  <?php foreach ($sections as $label => $field_key) :
    $content = ($field_key === 'short_description') ? get_the_excerpt() : get_field($field_key);
    $id = 'accordion_' . $index;
    $index++;

    if ($content) :
      $has_data = true;
  ?>
      <div>
        <button @click="open === '<?php echo $id; ?>' ? open = null : open = '<?php echo $id; ?>'"
                class="w-full flex justify-between items-center py-4 text-left text-sm font-medium text-gray-900 hover:text-black transition">
          <span><?= esc_html($label) ?></span>
          <svg class="w-4 h-4 transform transition-transform duration-300"
               :class="{ 'rotate-45': open === '<?php echo $id; ?>' }"
               fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
        </button>
        <div x-show="open === '<?php echo $id; ?>'"
             x-collapse
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 max-h-0"
             x-transition:enter-end="opacity-100 max-h-screen"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 max-h-screen"
             x-transition:leave-end="opacity-0 max-h-0"
             class="overflow-hidden text-sm text-gray-600 leading-relaxed pb-4"
             x-cloak>
          <?= wp_kses_post(wpautop($content)) ?>
        </div>
      </div>
    <?php endif; ?>
  <?php endforeach; ?>

  <?php if (!$has_data) : ?>
    <div class="py-6 text-sm text-center text-gray-500">No data found.</div>
  <?php endif; ?>
</div>
