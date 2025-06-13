<?php
/**
 * Billing Section for Custom Checkout
 */

$current_user = wp_get_current_user();
$user_id = $current_user->ID;
$is_logged_in = is_user_logged_in();

$billing_fields = array(
  'first_name' => get_user_meta($user_id, 'billing_first_name', true),
  'last_name'  => get_user_meta($user_id, 'billing_last_name', true),
  'company'    => get_user_meta($user_id, 'billing_company', true),
  'address_1'  => get_user_meta($user_id, 'billing_address_1', true),
  'address_2'  => get_user_meta($user_id, 'billing_address_2', true),
  'city'       => get_user_meta($user_id, 'billing_city', true),
  'state'      => get_user_meta($user_id, 'billing_state', true),
  'postcode'   => get_user_meta($user_id, 'billing_postcode', true),
  'country'    => get_user_meta($user_id, 'billing_country', true),
  'phone'      => get_user_meta($user_id, 'billing_phone', true),
);
?>

<div class="bg-white border border-gray-200 p-6 rounded shadow-sm">
  <div class="flex justify-between items-center mb-2">
    <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-800">Billing Address</h2>
    <button onclick="document.getElementById('billingEditBox').classList.toggle('hidden')" class="text-xs underline text-gray-600 hover:text-black">Edit/Change</button>
  </div>

  <hr class="mb-4">

  <label class="block mb-4 text-sm">
    <input type="checkbox" id="sameAsShipping" class="mr-2"> Billing address is same as shipping address
  </label>

  <div id="billing-display" class="text-sm text-gray-800 leading-relaxed">
    <?php if ($is_logged_in): ?>
      <?php echo esc_html($billing_fields['first_name'] . ' ' . $billing_fields['last_name']); ?><br>
      <?php echo esc_html($billing_fields['address_1']); ?><br>
      <?php echo esc_html($billing_fields['city'] . ', ' . $billing_fields['state']); ?><br>
      <?php echo esc_html($billing_fields['country']); ?><br>
      <?php echo esc_html($billing_fields['phone']); ?>
    <?php else: ?>
      <p class="italic text-gray-500">Not logged in.</p>
    <?php endif; ?>
  </div>

  <form id="billingEditBox" class="<?php echo $is_logged_in ? 'hidden' : ''; ?> mt-6 space-y-4" onsubmit="return saveBillingAddress(event)">
    <div id="billing-msg" class="text-sm"></div>

    <div class="grid grid-cols-2 gap-3">
      <input name="billing_first_name" class="border px-3 py-2 text-sm rounded w-full" value="<?php echo esc_attr($billing_fields['first_name']); ?>" placeholder="First Name" required>
      <input name="billing_last_name" class="border px-3 py-2 text-sm rounded w-full" value="<?php echo esc_attr($billing_fields['last_name']); ?>" placeholder="Last Name" required>
    </div>
    <input name="billing_address_1" class="border px-3 py-2 text-sm rounded w-full" value="<?php echo esc_attr($billing_fields['address_1']); ?>" placeholder="Address Line 1" required>
    <input name="billing_address_2" class="border px-3 py-2 text-sm rounded w-full" value="<?php echo esc_attr($billing_fields['address_2']); ?>" placeholder="Address Line 2">
    <input name="billing_city" class="border px-3 py-2 text-sm rounded w-full" value="<?php echo esc_attr($billing_fields['city']); ?>" placeholder="City" required>
    <div class="grid grid-cols-2 gap-3">
      <input name="billing_state" class="border px-3 py-2 text-sm rounded w-full" value="<?php echo esc_attr($billing_fields['state']); ?>" placeholder="State">
      <input name="billing_postcode" class="border px-3 py-2 text-sm rounded w-full" value="<?php echo esc_attr($billing_fields['postcode']); ?>" placeholder="Postcode">
    </div>
    <input name="billing_country" class="border px-3 py-2 text-sm rounded w-full" value="<?php echo esc_attr($billing_fields['country']); ?>" placeholder="Country" required>
    <input name="billing_phone" class="border px-3 py-2 text-sm rounded w-full" value="<?php echo esc_attr($billing_fields['phone']); ?>" placeholder="Phone">
   
   <?php if (!$is_logged_in): ?>
  <input name="billing_email" class="border px-3 py-2 text-sm rounded w-full" placeholder="Email" required>
<?php else: ?>
  <input type="hidden" name="billing_email" value="<?php echo esc_attr($current_user->user_email); ?>">
<?php endif; ?>

    <div class="flex justify-end gap-3 mt-4">
      <button type="submit" class="bg-blue-600 text-white text-sm px-4 py-2 rounded hover:bg-blue-700">Save</button>
      <button type="button" onclick="document.getElementById('billingEditBox').classList.add('hidden')" class="text-sm px-4 py-2 border rounded hover:bg-gray-100">Cancel</button>
    </div>
  </form>
</div>
<script>
function saveBillingAddress(e) {
  e.preventDefault();

  const form = document.getElementById('billingEditBox');
  const formData = new FormData(form);
  formData.append('action', 'save_billing_address');

  fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
    method: 'POST',
    credentials: 'same-origin',
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    const msgBox = document.getElementById('billing-msg');
    console.log('Server response:', data); // 👈 log response for debugging

    if (data.success) {
      msgBox.className = "text-green-600";
      msgBox.innerText = data.data?.message || "Billing address updated!";
      document.getElementById('billingEditBox').classList.add('hidden');
      document.getElementById('billing-display').innerHTML = `
        ${form.billing_first_name.value} ${form.billing_last_name.value}<br>
        ${form.billing_address_1.value}<br>
        ${form.billing_address_2.value}<br>
        ${form.billing_city.value}, ${form.billing_state.value}<br>
        ${form.billing_postcode.value}<br>
        ${form.billing_country.value}<br>
        ${form.billing_phone.value}
      `;
    } else {
      const errorMsg = data.data?.message || "Failed to update. Please try again.";
      msgBox.className = "text-red-600";
      msgBox.innerText = errorMsg;
      alert("Error: " + errorMsg); // 👈 show popup for clarity
    }
  })
  .catch(error => {
    console.error('AJAX error:', error);
    alert('Unexpected error. Please check console.');
  });

  return false;
}


// Copy shipping to billing if checkbox is clicked
const checkbox = document.getElementById('sameAsShipping');
checkbox?.addEventListener('change', async function () {
  const formBox = document.getElementById('billingEditBox');
  if (this.checked) {
    formBox.classList.remove('hidden');
    const response = await fetch('<?php echo admin_url('admin-ajax.php'); ?>?action=get_shipping_address', { credentials: 'same-origin' });
    const data = await response.json();
    if (data.success) {
      const form = document.getElementById('billingEditBox');
      for (const key in data.data) {
        const input = form.querySelector(`[name="${key}"]`);
        if (input) input.value = data.data[key];
      }
    }
  } else {
    formBox.classList.add('hidden');
  }
});
</script>