<?php
defined('ABSPATH') || exit;

$current_user = wp_get_current_user();

ob_start();
?>

<div class="bg-gray-100 text-sm text-center px-4 py-3 mb-5 rounded">
  Welcome to your account, <?php echo esc_html($current_user->display_name); ?>
</div>

<div class="grid grid-cols-2 gap-3 text-center text-sm font-medium">
  <a href="<?php echo esc_url(wc_get_account_endpoint_url('orders')); ?>" class="bg-gray-50 p-4 text-black no-underline rounded hover:bg-gray-100 transition flex flex-col items-center gap-2">
    <img src="<?php echo esc_url(get_stylesheet_directory_uri() . '/assets/images/icons/orders.svg'); ?>" alt="Orders" class="w-7 h-7">
    My orders
  </a>
  <a href="<?php echo esc_url(wc_get_account_endpoint_url('wishlist')); ?>" class="bg-gray-50 p-4 text-black no-underline rounded hover:bg-gray-100 transition flex flex-col items-center gap-2">
    <img src="<?php echo esc_url(get_stylesheet_directory_uri() . '/assets/images/icons/wishlist.svg'); ?>" alt="Wishlist" class="w-7 h-7">
    My wishlist
  </a>
  <a href="<?php echo esc_url(wc_get_account_endpoint_url('edit-address')); ?>" class="bg-gray-50 p-4 text-black no-underline rounded hover:bg-gray-100 transition flex flex-col items-center gap-2">
    <img src="<?php echo esc_url(get_stylesheet_directory_uri() . '/assets/images/icons/address.svg'); ?>" alt="Address Book" class="w-7 h-7">
    Address book
  </a>
  <a href="<?php echo esc_url(wc_get_account_endpoint_url('edit-account')); ?>" class="bg-gray-50 p-4 text-black no-underline rounded hover:bg-gray-100 transition flex flex-col items-center gap-2">
    <img src="<?php echo esc_url(get_stylesheet_directory_uri() . '/assets/images/icons/user-account.svg'); ?>" alt="Account Details" class="w-7 h-7">
    Account details
  </a>
</div>

<div class="border-t mt-6 pt-4">
  <a href="<?php echo esc_url(wc_get_account_endpoint_url('customer-logout')); ?>" class="w-auto flex items-center justify-between text-black no-underline bg-gray-100 px-4 py-3 rounded hover:bg-gray-200 transition text-sm">
    <span class="flex items-center gap-2">
      <img src="<?php echo esc_url(get_stylesheet_directory_uri() . '/assets/images/icons/log-out.svg'); ?>" alt="Logout" class="w-5 h-5">
      Logout
    </span>
  </a>
</div>

<?php
$content = ob_get_clean();

get_template_part('components/drawers/drawer', null, [
  'id' => 'drawer-myaccount',
  'title' => 'MY ACCOUNT',
  'content' => $content
]);
