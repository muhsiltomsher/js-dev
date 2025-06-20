<?php defined('ABSPATH') || exit;

$id = $args['id'] ?? 'drawer';
$title = $args['title'] ?? '';
$content = $args['content'] ?? '';
?>

<div id="<?php echo esc_attr($id); ?>" class="drawer-overlay fixed inset-0 bg-black/50 z-50 hidden" data-drawer-id="<?php echo esc_attr($id); ?>">
  <div class="drawer-content absolute right-0 top-0 h-full w-[300px] bg-white p-6 overflow-y-auto shadow-xl transform translate-x-full transition-transform duration-300 ease-in-out" onclick="event.stopPropagation();">
    <div class="flex justify-between items-center border-b pb-3 mb-4">
      <h2 class="text-lg font-semibold"><?php echo esc_html($title); ?></h2>
      <button onclick="closeDrawer('<?php echo esc_attr($id); ?>')" class="text-gray-600 border border-gray-300 rounded px-2 py-1 hover:text-black hover:border-black bg-transparent focus:outline-none text-lg">
        ✕
      </button>
    </div>
    <?php echo $content; ?>
  </div>
</div>
