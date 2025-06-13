<?php
$current_user = wp_get_current_user();
$user_id = $current_user->ID;
$is_logged_in = is_user_logged_in();
$fields = ['first_name', 'last_name', 'company', 'address_1', 'address_2', 'city', 'state', 'postcode', 'country', 'phone'];

function get_all_shipping_addresses($user_id) {
    $addresses = [];
    $default_index = get_user_meta($user_id, 'default_shipping_index', true);

    $primary = [];
    foreach ($GLOBALS['fields'] as $f) {
        $primary[$f] = get_user_meta($user_id, 'shipping_' . $f, true);
    }
    if (!empty($primary['address_1'])) {
        $addresses[] = [
            'label' => 'Primary Address',
            'value' => 'default',
            'data' => $primary,
            'is_default' => ($default_index === 'default')
        ];
    }

    $additional = get_user_meta($user_id, 'additional_shipping_addresses', true);
    if (is_array($additional)) {
        foreach ($additional as $i => $addr) {
            $addresses[] = [
                'label' => 'Address #' . ($i + 1),
                'value' => (string)$i,
                'data' => $addr,
                'is_default' => ($default_index != 'default' && $default_index == $i)
            ];
        }
    }

    return $addresses;
}

$shipping_addresses = $is_logged_in ? get_all_shipping_addresses($user_id) : [];
?>

<div class="bg-white border border-gray-200 p-6 rounded shadow-sm">
  <div class="flex justify-between items-center mb-2">
    <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-800">Deliver To</h2>
  </div>
  <hr class="mb-4">

  <?php if ($is_logged_in): ?>
    <!-- Dropdown to choose address -->
    <div class="mb-4">
      <label class="text-sm font-medium text-gray-700">Select Delivery Address</label>
      <select id="shipping_selector" class="mt-1 w-full border px-3 py-2 rounded text-sm" onchange="updateShippingForm(this)">
        <?php foreach ($shipping_addresses as $addr): ?>
        <?php
$addr_data = $addr['data'];
$formatted = $addr_data['first_name'] . ' ' . $addr_data['last_name'] . ', ';
if (!empty($addr_data['company'])) {
    $formatted .= $addr_data['company'] . ', ';
}
$formatted .= $addr_data['address_1'];
if (!empty($addr_data['address_2'])) {
    $formatted .= ', ' . $addr_data['address_2'];
}
$formatted .= ', ' . $addr_data['city'] . ', ' . $addr_data['state'];
$formatted .= ', ' . $addr_data['country'];
?>
<option value="<?= esc_attr($addr['value']) ?>" <?= $addr['is_default'] ? 'selected' : '' ?>>
  <?= esc_html($formatted) ?>
</option>

        <?php endforeach; ?>
      </select>
    </div>

    <!-- Shipping Address Preview -->
    <div id="shipping-display" class="text-sm text-gray-800 leading-relaxed mb-3"></div>

    <!-- Hidden Form (auto-filled by JS) -->
    <form id="shippingEditBox" class="space-y-3 hidden">
      <?php foreach ($fields as $f): ?>
        <input type="hidden" name="shipping_<?= esc_attr($f) ?>" id="field_<?= esc_attr($f) ?>">
      <?php endforeach; ?>
      <input type="hidden" name="action" value="save_shipping_address">
    </form>

    <script>
    const shippingAddresses = <?php echo json_encode(array_column($shipping_addresses, 'data', 'value')); ?>;

    function updateShippingForm(select) {
      const selected = select.value;
      const data = shippingAddresses[selected];
      if (!data) return;

      let preview = `
        ${data.first_name} ${data.last_name}<br>
        ${data.address_1}${data.address_2 ? ', ' + data.address_2 : ''}<br>
        ${data.city}, ${data.state}<br>
        ${data.country}<br>
        ${data.phone}
      `;
      document.getElementById('shipping-display').innerHTML = preview;

      <?php foreach ($fields as $f): ?>
        document.getElementById('field_<?= $f ?>').value = data['<?= $f ?>'] || '';
      <?php endforeach; ?>

      // Update default in DB
      fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: new URLSearchParams({
          action: 'set_default_shipping_address',
          index: selected
        })
      });
    }

    document.addEventListener('DOMContentLoaded', () => {
      updateShippingForm(document.getElementById('shipping_selector'));
    });
    </script>

  <?php else: ?>
    <!-- Guest Checkout: Show full address form -->
    <form id="shippingEditBox" class="mt-6 space-y-4" onsubmit="return saveShippingAddress(event)">
      <div id="shipping-msg" class="text-sm"></div>

      <div class="grid grid-cols-2 gap-3">
        <input name="shipping_first_name" class="border px-3 py-2 text-sm rounded w-full" placeholder="First Name" required>
        <input name="shipping_last_name" class="border px-3 py-2 text-sm rounded w-full" placeholder="Last Name" required>
      </div>

      <input name="shipping_address_1" class="border px-3 py-2 text-sm rounded w-full" placeholder="Address Line 1" required>
      <input name="shipping_address_2" class="border px-3 py-2 text-sm rounded w-full" placeholder="Address Line 2">
      <input name="shipping_city" class="border px-3 py-2 text-sm rounded w-full" placeholder="City" required>

      <div class="grid grid-cols-2 gap-3">
        <input name="shipping_state" class="border px-3 py-2 text-sm rounded w-full" placeholder="State">
        <input name="shipping_postcode" class="border px-3 py-2 text-sm rounded w-full" placeholder="Postcode">
      </div>

      <input name="shipping_country" class="border px-3 py-2 text-sm rounded w-full" placeholder="Country" required>
      <input name="shipping_phone" class="border px-3 py-2 text-sm rounded w-full" placeholder="Phone">

      <div class="flex justify-end gap-3 mt-4">
        <button type="submit" class="bg-blue-600 text-white text-sm px-4 py-2 rounded hover:bg-blue-700">Save</button>
      </div>
    </form>   <?php endif; ?>
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
          msgBox.innerText = "Address saved!";
        } else {
          msgBox.className = "text-red-600";
          msgBox.innerText = data.message || "Failed to save.";
        }
      });
      return false;
    }
    </script>
