<?php
/**
 * Shipping Address Section for Custom Checkout
 */

$current_user = wp_get_current_user();
$user_id = $current_user->ID;
$is_logged_in = is_user_logged_in();

function get_default_shipping_fields($user_id) {
    $fields = ['first_name', 'last_name', 'company', 'address_1', 'address_2', 'city', 'state', 'postcode', 'country', 'phone'];
    $address = array_fill_keys($fields, '');

    $default_index = get_user_meta($user_id, 'default_shipping_index', true);

    // Fallback to Woo session if not found or user not logged in
    if (!$user_id || $default_index === '') {
        foreach ($fields as $field) {
            $method = "get_shipping_{$field}";
            $address[$field] = WC()->customer->$method();
        }
    } elseif ($default_index === 'default') {
        foreach ($fields as $field) {
            $address[$field] = get_user_meta($user_id, "shipping_{$field}", true);
        }
    } elseif (is_numeric($default_index)) {
        $saved = get_user_meta($user_id, 'additional_shipping_addresses', true);
        if (is_array($saved) && isset($saved[$default_index])) {
            foreach ($fields as $field) {
                $address[$field] = $saved[$default_index][$field] ?? '';
            }
        }
    }

    return $address;
}

$address_fields = get_default_shipping_fields($user_id);

?>

<div class="bg-white border border-gray-200 p-6 rounded shadow-sm">
  <div class="flex justify-between items-center mb-2">
    <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-800">Deliver To</h2>
    <button onclick="document.getElementById('shippingEditBox').classList.toggle('hidden')" class="text-xs underline text-gray-600 hover:text-black">Edit/Change</button>
  </div>

  <hr class="mb-4">

  <div id="shipping-display" class="text-sm text-gray-800 leading-relaxed">
    <?php if ($is_logged_in): ?>
      <?php echo esc_html($address_fields['first_name'] . ' ' . $address_fields['last_name']); ?><br>
      <?php echo esc_html($address_fields['address_1']); ?><br>
      <?php echo esc_html($address_fields['city'] . ', ' . $address_fields['state']); ?><br>
      <?php echo esc_html($address_fields['country']); ?><br>
      <?php echo esc_html($address_fields['phone']); ?>
    <?php else: ?>
      <p class="italic text-gray-500">Not logged in.</p>
    <?php endif; ?>
  </div>

  <form id="shippingEditBox" class=" <?php echo $is_logged_in ? 'hidden' : ''; ?> mt-6 space-y-4" onsubmit="return saveShippingAddress(event)">
    <div id="shipping-msg" class="text-sm"></div>

    <div class="grid grid-cols-2 gap-3">
      <input name="shipping_first_name" class="border px-3 py-2 text-sm rounded w-full" value="<?php echo esc_attr($address_fields['first_name']); ?>" placeholder="First Name" required>
      <input name="shipping_last_name" class="border px-3 py-2 text-sm rounded w-full" value="<?php echo esc_attr($address_fields['last_name']); ?>" placeholder="Last Name" required>
    </div>

    <input name="shipping_address_1" class="border px-3 py-2 text-sm rounded w-full" value="<?php echo esc_attr($address_fields['address_1']); ?>" placeholder="Address Line 1" required>
    <input name="shipping_address_2" class="border px-3 py-2 text-sm rounded w-full" value="<?php echo esc_attr($address_fields['address_2']); ?>" placeholder="Address Line 2">
    <input name="shipping_city" class="border px-3 py-2 text-sm rounded w-full" value="<?php echo esc_attr($address_fields['city']); ?>" placeholder="City" required>

    <div class="grid grid-cols-2 gap-3">
      <input name="shipping_state" class="border px-3 py-2 text-sm rounded w-full" value="<?php echo esc_attr($address_fields['state']); ?>" placeholder="State">
      <input name="shipping_postcode" class="border px-3 py-2 text-sm rounded w-full" value="<?php echo esc_attr($address_fields['postcode']); ?>" placeholder="Postcode">
    </div>

    <input name="shipping_country" class="border px-3 py-2 text-sm rounded w-full" value="<?php echo esc_attr($address_fields['country']); ?>" placeholder="Country" required>
    <input name="shipping_phone" class="border px-3 py-2 text-sm rounded w-full" value="<?php echo esc_attr($address_fields['phone']); ?>" placeholder="Phone">

    <div class="flex justify-end gap-3 mt-4">
      <button type="submit" class="bg-blue-600 text-white text-sm px-4 py-2 rounded hover:bg-blue-700">Save</button>
      <button type="button" onclick="document.getElementById('shippingEditBox').classList.add('hidden')" class="text-sm px-4 py-2 border rounded hover:bg-gray-100">Cancel</button>
    </div>
  </form>
</div>

<script>
function saveShippingAddress(e) {
  e.preventDefault();
  const form = document.getElementById('shippingEditBox');
  const formData = new FormData(form);
  formData.append('action', 'save_shipping_address');

  fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
    method: 'POST',
    credentials: 'same-origin',
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    const msgBox = document.getElementById('shipping-msg');
    if (data.success) {
      msgBox.className = "text-green-600";
      msgBox.innerText = "Address updated!";
      document.getElementById('shippingEditBox').classList.add('hidden');
      document.getElementById('shipping-display').innerHTML = `
        ${form.shipping_first_name.value} ${form.shipping_last_name.value}<br>
        ${form.shipping_address_1.value}<br>
        ${form.shipping_city.value}, ${form.shipping_state.value}<br>
        ${form.shipping_country.value}<br>
        ${form.shipping_phone.value}`;
    } else {
      msgBox.className = "text-red-600";
      msgBox.innerText = data.message || "Failed to update.";
    }
  });
  return false;
}
</script>