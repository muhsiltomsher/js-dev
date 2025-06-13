<?php
/**
 * The Template for displaying all single products
 * 
 * @package WooCommerce\Templates
 * @version 1.6.4
 */

include get_template_directory() . '/layouts/header.php'; ?>

<main>
  <div class="container mx-auto py-12">

    <?php
      // Optional: show breadcrumb
      // do_action('woocommerce_breadcrumb');
    ?>

    <?php while (have_posts()) : the_post(); ?>

      <?php
        // Load your modular single product template
        wc_get_template_part('content', 'single-product');
      ?>

    <?php endwhile; ?>

  </div>
</main>

<?php
// Popular picks section after product
// get_template_part('components/products/popular-picks');


// Footer
include get_template_directory() . '/layouts/footer.php';
