<?php
/**
 * Template Name: Custom Cart Page
 */
include get_template_directory() . '/layouts/header.php';
?>

<div class="custom-cart-container">
  <form class="woocommerce-cart-form" action="<?php echo esc_url(wc_get_cart_url()); ?>" method="post">
    <div class="container mx-auto px-4 py-8 grid grid-cols-1 lg:grid-cols-3 gap-8">

      <!-- Left: Cart Items -->
      <div class="lg:col-span-2 space-y-6">
        <?php include get_template_directory() . '/components/cart/cart-header.php'; ?>
        <div id="cart-items">
          <?php include get_template_directory() . '/components/cart/cart-items.php'; ?>
        </div>
      </div>

      <!-- Right: Cart Summary -->
      <?php include get_template_directory() . '/components/cart/cart-summary.php'; ?>
    </div>
  </form>
</div>

<!-- Popular Picks Section -->
<div class="container mx-auto pb-[30px]">
    <?php get_template_part('components/products/popular-picks'); ?>
</div>


<!-- Alpine + AJAX Cart Handling -->
<script>
  document.addEventListener('alpine:init', () => {
    Alpine.data('cartItemHandler', (key, initialQty) => ({
      qty: initialQty,
      visible: true,

      updateQty(newQty) {
        this.qty = newQty < 1 ? 0 : newQty;

        fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: new URLSearchParams({
            action: 'update_cart_item',
            cart_item_key: key,
            quantity: this.qty
          })
        }).then(() => {
          document.dispatchEvent(new CustomEvent('refresh-cart-totals'));
        });
      },

      toggleItem() {
        if (this.qty > 0) {
          this.visible = false;
          this.updateQty(0);
        }
      }
    }));
  });

  document.addEventListener('refresh-cart-totals', () => {
    const wrapper = document.querySelector('#cart-totals-wrapper');
    if (wrapper && wrapper.__x) wrapper.__x.$data.loading = true;

    fetch(window.location.href)
      .then(res => res.text())
      .then(html => {
        const temp = document.createElement('div');
        temp.innerHTML = html;
        const newWrapper = temp.querySelector('#cart-totals-wrapper');
        if (newWrapper && wrapper) wrapper.innerHTML = newWrapper.innerHTML;
      });
  });
</script>

<?php include get_template_directory() . '/layouts/footer.php'; ?>
