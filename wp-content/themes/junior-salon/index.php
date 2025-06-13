<?php //get_header(); ?>
<?php include get_template_directory() . '/layouts/header.php'; ?>

<main>
    <div class="container mx-auto">
        
        <!-- Include Hero Slider -->
        <?php get_template_part('home/hero-slider'); ?>

        <?php get_template_part('home/homepage'); ?>

       
        <!-- Your featured products loop (like WooCommerce) goes here -->
        <!-- This is a placeholder for your products -->

    </div>
</main>

<?php include get_template_directory() . '/layouts/footer.php'; ?>