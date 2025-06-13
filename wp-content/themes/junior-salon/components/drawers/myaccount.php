<?php defined('ABSPATH') || exit;

$current_user = wp_get_current_user();
?>

<div id="drawer-myaccount" class="drawer-overlay fixed inset-0 bg-black/50 z-50 hidden">
  <div class="drawer-content absolute right-0 top-0 h-full w-[320px] bg-white shadow-xl transform translate-x-full transition-transform duration-300 ease-in-out flex flex-col">

    <!-- Header -->
    <div class="flex justify-between items-center border-b p-6">
      <h2 class="text-xl font-semibold tracking-wide">MY ACCOUNT</h2>
      <button onclick="closeDrawer('drawer-myaccount')" class="text-gray-600 hover:text-black text-lg">✕</button>
    </div>

    <!-- Content area -->
    <div class="flex-1 overflow-y-auto p-6 flex flex-col justify-start">
      <div class="bg-gray-100 text-sm text-center px-4 py-3 mb-5 rounded">
        Welcome to your account, <?php echo esc_html($current_user->display_name); ?>
      </div>

      <div class="grid grid-cols-2 gap-3 text-center text-sm font-medium">
        <a href="<?php echo esc_url(wc_get_account_endpoint_url('orders')); ?>" class="bg-gray-50 p-4 rounded hover:bg-gray-100 transition flex flex-col items-center gap-2">
          <img src="http://localhost/junior-salon/wp-content/uploads/2025/05/Orders.svg" alt="Orders" class="w-5 h-5">
          My orders
        </a>
        <a href="<?php echo esc_url(wc_get_account_endpoint_url('wishlist')); ?>" class="bg-gray-50 p-4 rounded hover:bg-gray-100 transition flex flex-col items-center gap-2">
          <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/icons/wishlist.svg" alt="Wishlist" class="w-5 h-5">
          My wishlist
        </a>
        <a href="<?php echo esc_url(wc_get_account_endpoint_url('edit-address')); ?>" class="bg-gray-50 p-4 rounded hover:bg-gray-100 transition flex flex-col items-center gap-2">
          <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/icons/address.svg" alt="Address Book" class="w-5 h-5">
          Address book
        </a>
        <a href="<?php echo esc_url(wc_get_account_endpoint_url('edit-account')); ?>" class="bg-gray-50 p-4 rounded hover:bg-gray-100 transition flex flex-col items-center gap-2">
          <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/icons/account.svg" alt="Account Details" class="w-5 h-5">
          Account details
        </a>
      </div>
    </div>

    <!-- Fixed bottom logout -->
    <div class="border-t px-6 py-4">
      <a href="<?php echo esc_url(wc_get_account_endpoint_url('customer-logout')); ?>" class="w-auto flex items-center justify-between bg-gray-100 px-4 py-3 rounded hover:bg-gray-200 transition text-sm">
        <span class="flex items-center gap-2">
          <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/icons/logout.svg" alt="Logout" class="w-4 h-4">
          Logout
        </span>
      </a>
    </div>

  </div>
</div>
