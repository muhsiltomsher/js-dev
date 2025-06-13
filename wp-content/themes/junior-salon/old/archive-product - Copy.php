<?php
/**
 * The Template for displaying product archives, including the main shop page which is a post type archive
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/archive-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 8.6.0
 */

 include get_template_directory() . '/layouts/header.php'; 

/**
 * Hook: woocommerce_before_main_content.
 *
 * @hooked woocommerce_output_content_wrapper - 10 (outputs opening divs for the content)
 * @hooked woocommerce_breadcrumb - 20
 * @hooked WC_Structured_Data::generate_website_data() - 30
 */
do_action( 'woocommerce_before_main_content' );

/**
 * Hook: woocommerce_shop_loop_header.
 *
 * @since 8.6.0
 *
 * @hooked woocommerce_product_taxonomy_archive_header - 10
 */
do_action( 'woocommerce_shop_loop_header' );

if ( woocommerce_product_loop() ) {

	/**
	 * Hook: woocommerce_before_shop_loop.
	 *
	 * @hooked woocommerce_output_all_notices - 10
	 * @hooked woocommerce_result_count - 20
	 * @hooked woocommerce_catalog_ordering - 30
	 */
	//do_action( 'woocommerce_before_shop_loop' );
?>
	<h2 class="text-3xl font-bold mb-6"><?php //single_term_title(); ?></h2>




<!-- Trigger Button -->
<button onclick="openDrawerfilter()" class="flex items-center bg-transparent group m-0 text-lg font-semibold px-4 py-2 hover:text-blue-600">
    FILTER & ORDER
</button>

<!-- Drawer Container -->
<div id="drawer-container-filter" class="fixed top-0 right-0 w-80 h-full bg-white shadow-lg z-50 p-6 overflow-y-auto hidden transition-transform duration-300 ease-in-out">
    <!-- AJAX content will be injected here -->
</div>

<!-- Optional Overlay -->
<div id="drawer-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden" onclick="closeDrawerfilter()"></div>


<script>
function openDrawerfilter() {
    // Show overlay and drawer
    document.getElementById('drawer-overlay').classList.remove('hidden');
    document.getElementById('drawer-container-filter').classList.remove('hidden');

    // Fetch drawer content
    fetch(ajaxurl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=load_filter_drawer_content'
    })
    .then(res => res.text())
    .then(data => {
        document.getElementById('drawer-container-filter').innerHTML = data;
    });
}

function closeDrawerfilter() {
    document.getElementById('drawer-overlay').classList.add('hidden');
    document.getElementById('drawer-container-filter').classList.add('hidden');
}
</script>



<button onclick="openDrawer('Sort By', '<?php echo esc_url( home_url('/wp-content/themes/junior-salon/products/sort-drawer.php') ); ?>')" 
        class="flex items-center bg-transparent group m-0">
    <span class="text-lg">Sort By</span>
</button>

<!-- Drawer Container -->
<div id="drawer" class="fixed inset-0 bg-gray-900 bg-opacity-50 z-50 hidden">
    <div class="absolute top-0 right-0 bg-white w-3/4 md:w-1/2 h-full p-6 transform transition-transform duration-300 ease-in-out translate-x-full" id="drawer-panel">
        <!-- Close Button -->
        <button id="close-drawer" class="absolute top-4 right-4 text-xl text-gray-500 hover:text-gray-700">&times;</button>
        
        <!-- Dynamic Content -->
        <div id="drawer-content">
            <!-- Loaded via JS -->
        </div>
    </div>
</div>

<!-- JavaScript -->
<script>
    
function openDrawer(title, url) {
    const drawer = document.getElementById('drawer');
    const drawerPanel = document.getElementById('drawer-panel');
    const drawerContent = document.getElementById('drawer-content');

    // Show overlay and drawer
    drawer.classList.remove('hidden');
    setTimeout(() => drawerPanel.classList.remove('translate-x-full'), 10);

    // Load content
    fetch(url)
        .then(response => response.text())
        .then(html => {
            drawerContent.innerHTML = html;
        })
        .catch(err => {
            drawerContent.innerHTML = '<p class="text-red-500">Failed to load content.</p>';
        });
}

// Close drawer handler
document.getElementById('close-drawer').addEventListener('click', closeDrawer);
document.getElementById('drawer').addEventListener('click', function(e) {
    if (e.target === this) closeDrawer();
});

function closeDrawer() {
    const drawer = document.getElementById('drawer');
    const drawerPanel = document.getElementById('drawer-panel');

    drawerPanel.classList.add('translate-x-full');
    setTimeout(() => drawer.classList.add('hidden'), 300); // Match transition duration
}
</script>



<div id="product-grid"  class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6">
    <?php
    $term = get_queried_object(); // gets current category object
	$paged = 1; // Set current page
    $args = array(
        'post_type' => 'product',
        'posts_per_page' => 1,
		'paged' => $paged,
        'tax_query' => array(
            array(
                'taxonomy' => 'product_cat',
                'field'    => 'term_id',
                'terms'    => $term->term_id,
            ),
        ),
    );

    $query = new WP_Query($args);

    if ($query->have_posts()) :
        while ($query->have_posts()) : $query->the_post();
            global $product;

            $brand_terms = get_the_term_list(get_the_ID(), 'product_brand', '', ', ');
            $brand = (!is_wp_error($brand_terms) && $brand_terms) ? $brand_terms : 'No brand';
            ?>
            <div class="product-card bg-white p-4 rounded-lg shadow hover:shadow-lg transition">
                <a href="<?php the_permalink(); ?>" class="block">

                    <?php
                    $attachment_ids = $product->get_gallery_image_ids();
                    $hover_image_id = $attachment_ids[0] ?? null;
                    ?>
                    <div class="relative group w-full aspect-square overflow-hidden">
                        <img 
                            src="<?php echo get_the_post_thumbnail_url(get_the_ID(), 'medium'); ?>" 
                            class="w-full h-full object-cover transition-opacity duration-300 group-hover:opacity-0" 
                            alt="<?php the_title_attribute(); ?>" 
                        />
                        <?php if ($hover_image_id): ?>
                            <img 
                                src="<?php echo wp_get_attachment_image_url($hover_image_id, 'medium'); ?>" 
                                class="w-full h-full object-cover absolute inset-0 opacity-0 transition-opacity duration-300 group-hover:opacity-100" 
                                alt="<?php the_title_attribute(); ?>" 
                            />
                        <?php endif; ?>
                    </div>

                    <?php echo do_shortcode('[yith_wcwl_add_to_wishlist]'); ?>

                    <p class="text-sm text-gray-500"><?php echo $brand; ?></p>
                    <h3 class="text-base font-semibold text-gray-800"><?php the_title(); ?></h3>
                    <p class="text-lg text-gray-700 mt-1"><?php echo wc_price($product->get_price()); ?></p>
                </a>
			
                <?php
                echo '<div class="woocommerce">';
                if ($product->is_type('simple')) {
					woocommerce_template_loop_add_to_cart();
                } elseif ($product->is_type('variable')) {
                    woocommerce_variable_add_to_cart();
                }
                echo '</div>';
                ?>
            </div>
            <?php
        endwhile; endif; 
        wp_reset_postdata();
  ?>

        </div> 
        
        
        <?php
if ($query->max_num_pages > $paged) : ?>
        <!-- Load More Button -->
        <div class="flex justify-center mt-8">
            <button id="load-more" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
                data-page="<?php echo esc_attr($paged + 1); ?>">
                Load More
            </button>
        </div>
    <?php 

endif;

    ?>

<?php
	/**
	 * Hook: woocommerce_after_shop_loop.
	 *
	 * @hooked woocommerce_pagination - 10
	 */
	//do_action( 'woocommerce_after_shop_loop' );
} else {
	/**
	 * Hook: woocommerce_no_products_found.
	 *
	 * @hooked wc_no_products_found - 10
	 */
	do_action( 'woocommerce_no_products_found' );
}
?>

</div>
<?php
/**
 * Hook: woocommerce_after_main_content.
 *
 * @hooked woocommerce_output_content_wrapper_end - 10 (outputs closing divs for the content)
 */
do_action( 'woocommerce_after_main_content' );

/**
 * Hook: woocommerce_sidebar.
 *
 * @hooked woocommerce_get_sidebar - 10
 */
//do_action( 'woocommerce_sidebar' );


include get_template_directory() . '/layouts/footer.php';

if (is_product_category()) {
    $term = get_queried_object();
    echo '<script>var currentTermId = ' . intval($term->term_id) . ';</script>';
} 
?>

<script>
jQuery(document).ready(function($) {
    let page = 1; // Start from page 1 for loading products
    let currentSort = ''; // Default empty sorting option

    // Event listener for sorting
    $('#drawer').on('change', 'input[name="sort"]', function() {
		alert(currentTermId);
        currentSort = $(this).val(); // Get the selected sort value
        page = 1; // Reset page number on sort change

        // Alert the selected sort option (for debugging purposes)
      //  alert('Selected sort option: ' + currentSort);

        // Fetch sorted products
        fetch_sorted_products_by_category(currentSort, page,currentTermId);

        // Close the drawer after selection (optional)
        closeDrawer();
    });

    // Load more functionality
    $('#load-more').on('click', function() {
 alert(currentSort);
 alert(page);

 alert(currentTermId);

        page++; // Increment the page number
        fetch_sorted_products_by_category(currentSort, page,currentTermId); // Fetch products for the next page
    });

    // Function to fetch sorted products
    function fetch_sorted_products_by_category(sort, page,currentTermId) {
        const ajaxUrl = '<?php echo admin_url("admin-ajax.php"); ?>';

        // Make the AJAX request
        $.ajax({
            url: ajaxUrl,
            method: 'GET',     dataType: 'html', 
            data: {
                action: 'fetch_sorted_products_by_category',
                sort: sort,
                page: page,      term_id: currentTermId
            },
            success: function(response) {
                console.log(response); // Debug
          
                // If page is 1, replace the content, else append the new products
                if (page === 1) { alert("dd");
                $('#product-grid').html(response);
            } else { alert("ww");
                $('#product-grid').html(response);
            }


  // Check if there are more pages to load
  if (page >= response.max_pages) {
                // Hide the "Load More" button if all products have been loaded
                $('#load-more').hide();
            }

            },
            error: function(err) {
                console.error('Error fetching products:', err);
            }
        });
    }
});

jQuery(document).ready(function ($) {

let currentPage = 1;

// Store filters globally so we can reuse them on "Load More"
let selectedCategories = [];
let selectedBrands = [];
let selectedAge = [];

// Function to load products
function loadProducts(page = 1, append = false) {
    $.ajax({
        url: ajaxurl,
        method: 'POST',
        data: {
            action: 'filter_products',
            categories: selectedCategories,
            brands: selectedBrands,
            age: selectedAge,
            page: page,
        },
        beforeSend: function () {
            $('.load-more-btn').remove(); // Remove old button
            if (!append) {
                $('#product-grid').html('<p>Loading...</p>');
            }
        },
        success: function (response) {
            if (append) {
                $('#product-grid').append(response);
            } else {
                $('#product-grid').html(response);
            }
            currentPage = page;
        }
    });
}

// Filter button click
$(document).on('click', '#filter-button', function (e) {
    e.preventDefault();

    selectedCategories = $('input[name="product_cat[]"]:checked').map(function () {
        return $(this).val();
    }).get();

    selectedBrands = $('input[name="product_brand[]"]:checked').map(function () {
        return $(this).val();
    }).get();

    selectedAge = $('input[name="age_product_cat[]"]:checked').map(function () {
        return $(this).val();
    }).get();

 //   alert("Applying filters...\n" +
      //  "Categories: " + selectedCategories.join(', ') + "\n" +
      //  "Brands: " + selectedBrands.join(', ') + "\n" +
      //  "Age: " + selectedAge.join(', '));

    $('#drawer-overlay').trigger('click');
    $('#load-more').hide();
    currentPage = 1;
    loadProducts(currentPage, false);

    closeDrawerFilter();
});

// Load More button click
$(document).on('click', '.load-more-btn', function () {
    const nextPage = parseInt($(this).data('next-page'));
    loadProducts(nextPage, true);
});
});


</script>
