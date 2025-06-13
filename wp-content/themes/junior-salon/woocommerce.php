<?php
/**
 * Template for all WooCommerce pages
 */
include get_template_directory() . '/layouts/header.php'; 
?>

<div class="container mx-auto px-4 py-8">
  <?php woocommerce_content(); ?>
</div>

<?php  include get_template_directory() . '/layouts/footer.php'; ?>
