<?php
$user_id = get_current_user_id();
$bank_name = get_user_meta($user_id, 'bank_name', true);
$account_number = get_user_meta($user_id, 'account_number', true);
$iban = get_user_meta($user_id, 'iban_number', true);
?>

<form id="bank-details-form" class="space-y-4">
  <input type="text" name="bank_name" class="w-full border p-2" placeholder="Bank Name" value="<?php echo esc_attr($bank_name); ?>">
  <input type="text" name="account_number" class="w-full border p-2" placeholder="Account Number" value="<?php echo esc_attr($account_number); ?>">
  <input type="text" name="iban_number" class="w-full border p-2" placeholder="IBAN Number" value="<?php echo esc_attr($iban); ?>">
  <input type="hidden" name="action" value="update_bank_details">
  <button type="submit" class="px-4 py-2 bg-black text-white">Save</button>
  <p id="bank-msg" class="text-sm mt-2"></p>
</form>

<script>
jQuery('#bank-details-form').on('submit', function(e) {
  e.preventDefault();
  const form = jQuery(this);
  jQuery.ajax({
    url: '<?php echo admin_url('admin-ajax.php'); ?>',
    method: 'POST',
    data: form.serialize(),
    success: function(response) {
      jQuery('#bank-msg').text(response.data.message).removeClass().addClass(response.success ? 'text-green-600' : 'text-red-600');
    }
  });
});
</script>
