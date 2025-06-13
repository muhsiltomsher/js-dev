<?php
defined('ABSPATH') || exit;

ob_start();
?>

<?php echo do_shortcode('[fibosearch]'); ?>

<?php
$content = ob_get_clean();

get_template_part('components/drawers/drawer', null, [
  'id' => 'drawer-search',
  'title' => 'Search',
  'content' => $content,
]);
