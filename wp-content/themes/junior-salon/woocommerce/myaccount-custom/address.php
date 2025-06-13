<?php
$user_id = get_current_user_id();
$billing = [];
$shipping = [];

$fields = ['first_name', 'last_name', 'company', 'address_1', 'address_2', 'city', 'state', 'postcode', 'country', 'phone'];

foreach ($fields as $field) {
    $billing[$field] = get_user_meta($user_id, 'billing_' . $field, true);
    $shipping[$field] = get_user_meta($user_id, 'shipping_' . $field, true);
}

function format_address($data) {
    return esc_html(trim("{$data['first_name']} {$data['last_name']}, {$data['address_1']}, {$data['address_2']}, {$data['city']}, {$data['state']} {$data['postcode']}, {$data['country']} - {$data['phone']}"));
}
?>

<h2 class="text-2xl font-bold mb-6">My Address Book</h2>
<div class="bg-white p-6 rounded shadow max-w-4xl mx-auto mt-10">


<!-- BILLING ADDRESS SECTION -->
  <div class="border border-gray-300 rounded p-5 mb-6 relative">
  <div class="flex justify-between items-center mb-4">
    <h2 class="text-xl font-bold text-gray-800">Billing Address</h2>
    <button type="button" onclick="toggleAddressEdit('billing')" class="text-sm text-blue-600 hover:underline">Edit</button>
  </div>
  <div id="billing-address-text" class="text-sm text-gray-800 leading-relaxed">
    <?= $billing['address_1'] ? format_address($billing) : '<span class="text-gray-500">No billing address added.</span>' ?>
  </div>
  <form id="billing-address-form" class="space-y-3 mt-4 hidden">
    <?php foreach ($billing as $key => $value): ?>
      <input type="text" name="<?= esc_attr($key) ?>" value="<?= esc_attr($value) ?>" class="w-full text-sm border px-3 py-2 rounded" placeholder="<?= ucfirst(str_replace('_', ' ', $key)) ?>">
    <?php endforeach; ?>
    <input type="hidden" name="action" value="update_billing_address">
    <div class="flex justify-end gap-2">
      <button type="submit" class="px-4 py-2 bg-black text-white text-sm rounded">Save</button>
      <button type="button" onclick="toggleAddressEdit('billing')" class="text-sm px-4 py-2 border rounded">Cancel</button>
    </div>
    <p id="billing-msg" class="text-sm mt-2"></p>
  </form>
</div>
</div>
<?php
$all_shipping_addresses = [];
$additional_addresses = get_user_meta($user_id, 'additional_shipping_addresses', true);
$default_index = get_user_meta($user_id, 'default_shipping_index', true);

// Add WooCommerce's default shipping address
if (!empty($shipping['address_1'])) {
    $all_shipping_addresses[] = [
        'is_default' => ($default_index === 'default'),
        'data' => $shipping,
        'original_index' => 'default' // we use string 'default'
    ];
}

// Add additional shipping addresses
if (is_array($additional_addresses)) {
    foreach ($additional_addresses as $real_index => $addr) {
        $all_shipping_addresses[] = [
            'is_default' => ($default_index !== 'default' && $default_index == $real_index),
            'data' => $addr,
            'original_index' => $real_index
        ];
    }
}


?>

<div class="mt-10">
  <h2 class="text-xl font-bold mb-4 text-gray-800">Shipping Addresses</h2>

  <?php if (!empty($all_shipping_addresses)): ?>
   <?php
foreach ($all_shipping_addresses as $entry):
  $is_default = $entry['is_default'];
  $address = $entry['data'];
  $uid = $entry['original_index'] === 'default' ? 'default' : 'additional-' . $entry['original_index'];
?>
<div class="border border-gray-300 rounded p-5 mb-4 relative bg-white shadow-sm">

  <!-- Default Radio -->
  <label class="inline-flex items-center text-xs text-gray-600">
    <input
      type="radio"
      name="default_shipping"
      value="<?= esc_attr($entry['original_index']) ?>"
      <?= $is_default ? 'checked' : '' ?>
      onchange="setDefaultShipping(this.value)"
      class="mr-2"
    >
    Set as default
  </label>

  <div class="flex justify-between items-start">
    <div class="text-sm text-gray-800 leading-relaxed">
      <?= format_address($address); ?>
      <?php if ($is_default): ?>
        <div class="text-xs text-green-600 mt-1 font-medium">Default Shipping Address</div>
      <?php endif; ?>
    </div>
    <button type="button" onclick="toggleShippingEdit('<?= $uid ?>')" class="text-sm text-blue-600 hover:underline">Edit</button>
  </div>

  <!-- EDIT FORM -->
  <form id="shipping-address-form-<?= $uid ?>" class="mt-4 space-y-3 hidden">
    <?php foreach ($fields as $field): ?>
      <input 
        type="text"
        name="<?= esc_attr($field) ?>"
        value="<?= esc_attr($address[$field] ?? '') ?>"
        class="w-full text-sm border px-3 py-2 rounded"
        placeholder="<?= ucfirst(str_replace('_', ' ', $field)) ?>"
      >
    <?php endforeach; ?>
    <input type="hidden" name="action" value="<?= $entry['original_index'] === 'default' ? 'update_shipping_address' : 'edit_additional_shipping_address' ?>">
    <input type="hidden" name="index" value="<?= $entry['original_index'] ?>">
    <div class="flex justify-end gap-2">
      <button type="submit" class="px-4 py-2 bg-black text-white text-sm rounded">Save</button>
      <button type="button" onclick="cancelEdit('<?= $uid ?>')" class="text-sm px-4 py-2 border rounded">Cancel</button>
    </div>
    <p id="msg-<?= $uid ?>" class="text-sm mt-2"></p>
  </form>
</div>
<?php endforeach; ?>

  <?php else: ?>
    <p class="text-gray-500 text-sm">No shipping addresses saved yet.</p>
  <?php endif; ?>
</div>

<!-- ADD DELIVERY ADDRESS BUTTON -->
<!-- ADD SHIPPING ADDRESS BUTTON -->
<div class="text-right max-w-4xl mx-auto mb-6">
  <button onclick="openAddressModal()" class="px-4 py-2 bg-green-600 text-white rounded text-sm">+ Add Shipping Address</button>
</div>
<!-- SHIPPING ADDRESS MODAL -->
<div id="address-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center">
  <div class="bg-white rounded-lg p-6 w-full max-w-lg relative">
    <h2 class="text-lg font-semibold mb-4">Add New Shipping Address</h2>
    <form id="new-shipping-address-form" class="space-y-3">
      <?php foreach ($fields as $field): ?>
        <input type="text" name="<?= esc_attr($field) ?>" class="w-full text-sm border px-3 py-2 rounded" placeholder="<?= ucfirst(str_replace('_', ' ', $field)) ?>">
      <?php endforeach; ?>
      <input type="hidden" name="action" value="add_shipping_address">
      <div class="flex justify-end gap-2">
        <button type="submit" class="px-4 py-2 bg-black text-white text-sm rounded">Save</button>
        <button type="button" onclick="closeAddressModal()" class="text-sm px-4 py-2 border rounded">Cancel</button>
      </div>
      <p id="shipping-msg" class="text-sm mt-2"></p>
    </form>
  </div>
</div>


<script>
function openAddressModal() {
  document.getElementById('address-modal').classList.remove('hidden');
}

function closeAddressModal() {
  document.getElementById('address-modal').classList.add('hidden');
}

jQuery('#new-shipping-address-form').on('submit', function(e) {
  e.preventDefault();
  const form = jQuery(this);
  jQuery.ajax({
    url: '<?php echo admin_url('admin-ajax.php'); ?>',
    method: 'POST',
    data: form.serialize(),
    success: function(response) {
      const msg = jQuery('#delivery-msg');
      msg.text(response.data.message).removeClass().addClass(response.success ? 'text-green-600' : 'text-red-600');
      if (response.success) {
        closeAddressModal();
        location.reload();
      }
    }
  });
});
</script>



<script>
function toggleAddressEdit(type) {
  const form = document.getElementById(`${type}-address-form`);
  const text = document.getElementById(`${type}-address-text`);
  form.classList.toggle('hidden');
  text.classList.toggle('hidden');
}

jQuery('#billing-address-form').on('submit', function(e) {
  e.preventDefault();
  const form = jQuery(this);
  jQuery.ajax({
    url: '<?php echo admin_url('admin-ajax.php'); ?>',
    method: 'POST',
    data: form.serialize(),
    success: function(response) {
      const msg = jQuery('#billing-msg');
      msg.text(response.data.message).removeClass().addClass(response.success ? 'text-green-600' : 'text-red-600');
      if (response.success) {
        toggleAddressEdit('billing');
        location.reload();
      }
    }
  });
});

jQuery('#shipping-address-form').on('submit', function(e) {
  e.preventDefault();
  const form = jQuery(this);
  jQuery.ajax({
    url: '<?php echo admin_url('admin-ajax.php'); ?>',
    method: 'POST',
    data: form.serialize(),
    success: function(response) {
      const msg = jQuery('#shipping-msg');
      msg.text(response.data.message).removeClass().addClass(response.success ? 'text-green-600' : 'text-red-600');
      if (response.success) {
        toggleAddressEdit('shipping');
        location.reload();
      }
    }
  });
});
</script>



<script>
function toggleShippingEdit(uid) {
  document.getElementById(`shipping-address-form-${uid}`).classList.toggle('hidden');
}

function cancelEdit(uid) {
  document.getElementById(`shipping-address-form-${uid}`).classList.add('hidden');
}
// AJAX submit each form

// Update listener to handle dynamic ID-based submission
document.querySelectorAll('form[id^="shipping-address-form-"]').forEach((formEl) => {
  formEl.addEventListener('submit', function(e) {
    e.preventDefault();
    const form = new FormData(this);
    const uid = this.id.replace('shipping-address-form-', '');
    const msg = document.getElementById(`msg-${uid}`);

    fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
      method: 'POST',
      body: form
    })
    .then(res => res.json())
    .then(response => {
      msg.textContent = response.data.message;
      msg.className = response.success ? 'text-green-600 text-sm mt-2' : 'text-red-600 text-sm mt-2';
      if (response.success) {
        toggleShippingEdit(uid);
        setTimeout(() => location.reload(), 500);
      }
    });
  });
});
</script>
<script>
function setDefaultShipping(index) {
  fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded'
    },
    body: new URLSearchParams({
      action: 'set_default_shipping_address',
      index: index
    })
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      location.reload();
    } else {
      alert(data.data.message);
    }
  });
}
</script>
