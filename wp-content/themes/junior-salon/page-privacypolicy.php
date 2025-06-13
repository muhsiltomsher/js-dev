<?php
/**
 * Template Name: PRIVACY Page
 */
include get_template_directory() . '/layouts/header.php';

?>
<main>
    <div class="container mx-auto py-12">
  <h1 class="text-3xl font-bold mb-6"><?php echo apply_filters( 'wpml_translate_single_string', 'Privacy Policy', 'Auth Texts', 'Privacy Policy' ); ?>
 </h1>

 <?php
  if (have_posts()) :
    while (have_posts()) : the_post();
      the_content();
    endwhile;
  endif;
  ?>

    </div> </main>

<?php
 include get_template_directory() . '/layouts/footer.php'; ?>