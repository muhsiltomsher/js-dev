<?php
$user_id = get_current_user_id();
$user = get_userdata($user_id);

$first_name = get_user_meta($user_id, 'billing_first_name', true);
$last_name  = get_user_meta($user_id, 'billing_last_name', true);
$email      = $user->user_email;
$phone      = get_user_meta($user_id, 'billing_phone', true);
?>

<h2 class="text-xl font-semibold mb-6">My Profile</h2>

<form id="update-profile-form" class="space-y-4 max-w-md">
  <div>
    <label class="block text-sm font-medium text-gray-700">First Name</label>
    <input type="text"  value="<?php echo esc_attr($first_name); ?>" class="w-full border bg-gray-100 p-2 rounded" />
  </div>

  <div>
    <label class="block text-sm font-medium text-gray-700">Last Name</label>
    <input type="text"  value="<?php echo esc_attr($last_name); ?>" class="w-full border bg-gray-100 p-2 rounded" />
  </div>

  <div>
    <label class="block text-sm font-medium text-gray-700">Email</label>
    <input type="email" disabled value="<?php echo esc_attr($email); ?>" class="w-full border bg-gray-100 p-2 rounded" />
  </div>

  <div>
    <label class="block text-sm font-medium text-gray-700">Phone</label>
    <input type="text" name="phone" value="<?php echo esc_attr($phone); ?>" class="w-full border p-2 rounded" />
  </div>

  <input type="hidden" name="action" value="update_user_profile" />

  <button type="submit" class="px-4 py-2 bg-black text-white rounded">Save</button>
  <p id="profile-msg" class="text-sm mt-2"></p>
</form>

<script>
jQuery('#update-profile-form').on('submit', function(e) {
  e.preventDefault();
  var form = jQuery(this);
  jQuery.ajax({
    url: '<?php echo admin_url('admin-ajax.php'); ?>',
    method: 'POST',
    data: form.serialize(),
    success: function(response) {
      jQuery('#profile-msg')
        .text(response.data.message)
        .removeClass()
        .addClass(response.success ? 'text-green-600' : 'text-red-600');
    }
  });
});
</script>
