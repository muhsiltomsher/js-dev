<form id="change-password-form" class="space-y-4">
  <input type="password" name="current_password" class="w-full border p-2" placeholder="Current Password">
  <input type="password" name="new_password" class="w-full border p-2" placeholder="New Password">
  <input type="password" name="confirm_password" class="w-full border p-2" placeholder="Confirm New Password">
  <input type="hidden" name="action" value="update_account_password">
  <button type="submit" class="px-4 py-2 bg-black text-white">Change Password</button>
  <p id="password-msg" class="text-sm mt-2"></p>
</form>

<script>
jQuery('#change-password-form').on('submit', function(e) {
  e.preventDefault();
  const form = jQuery(this);
  jQuery.ajax({
    url: '<?php echo admin_url('admin-ajax.php'); ?>',
    method: 'POST',
    data: form.serialize(),
    success: function(response) {
      jQuery('#password-msg').text(response.data.message).removeClass().addClass(response.success ? 'text-green-600' : 'text-red-600');
    }
  });
});
</script>
