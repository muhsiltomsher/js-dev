<?php //get_header(); ?>
<?php include get_template_directory() . '/layouts/header.php'; ?>

<main>
    <div class="container mx-auto py-12">
        


  <?php get_template_part('components/shopby-brands'); ?>
  <?php get_template_part('products/all-products'); ?>

 
    </div>
</main>

<?php include get_template_directory() . '/layouts/footer.php'; ?>