

<h2 class="text-3xl font-bold mb-6">NEW FOR KIDS</h2>

<?php
 get_template_part('/products/drawerbuttons');
$paged = 1;
$args = array(
    'post_type' => 'product',
    'posts_per_page' => 15,
    'paged' => $paged,
);
$loop = new WP_Query($args);
?>

<?php if ($loop->have_posts()) : ?>
<div id="product-grid" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3">
    <?php while ($loop->have_posts()) : $loop->the_post();
        global $product;
        setup_postdata($post);
        wc_setup_product_data($post);
        get_template_part('components/products/product-card');
    endwhile; ?>
</div>

<?php if ($loop->max_num_pages > $paged) : ?>
<div class="flex justify-center mt-8">
    <button id="load-more" class="px-6 py-2 bg-black text-white rounded-lg hover:bg-gray-900 transition" data-page="<?php echo esc_attr($paged + 1); ?>">
        Load More
    </button>
</div>
<?php endif; ?>
<?php else : ?><p>No products found.</p><?php endif; ?>
<?php wp_reset_postdata(); ?>

<!-- Add AJAX URL -->
<script>
    var ajaxurl = '<?php echo esc_js(admin_url('admin-ajax.php')); ?>';
</script>

<!-- Your JavaScript -->
<script>jQuery(document).ready(function($) {
    let page = 1;
    let currentSort = '';

    // Open filter drawer
    function openDrawerfilter() {
        $('#drawer-overlay').removeClass('hidden');
        $('#drawer-container-filter').removeClass('hidden');
        $('#ajax-loader').removeClass('hidden');
        $.ajax({
            url: ajaxurl,
            method: 'POST',
            data: {
                action: 'load_filter_drawer_content'
            },
            success: function(response) {
                $('#drawer-container-filter').html(response);
                $('#ajax-loader').addClass('hidden');
            },
            error: function(xhr, status, error) {
                console.error('Error loading filter drawer:', xhr.responseText);
                $('#drawer-container-filter').html('<p class="text-red-500">Failed to load filters.</p>');
                $('#ajax-loader').addClass('hidden');
            }
        });
    }

    // Close filter drawer
    function closeDrawerfilter() {
        $('#drawer-overlay').addClass('hidden');
        $('#drawer-container-filter').addClass('hidden');
    }

    // Collect filter values
    function getFilterData() {
        return {
            action: 'filter_products',
            categories: $('input[name="product_cat[]"]:checked').map(function() { return $(this).val(); }).get(),
            brands: $('input[name="product_brand[]"]:checked').map(function() { return $(this).val(); }).get(),
            age: $('input[name="age_product_cat[]"]:checked').map(function() { return $(this).val(); }).get(),
            sizes: $('input[name="pa_size[]"]:checked').map(function() { return $(this).val(); }).get(),
            colors: $('input[name="pa_color[]"]:checked').map(function() { return $(this).val(); }).get(),
            min_price: $('input[name="min_price"]').val(),
            max_price: $('input[name="max_price"]').val(),
            page: page
        };
    }

    // Load products with filter
    function loadProducts(pageNum = 1, append = false, filterData = {}) {
        $('#ajax-loader').removeClass('hidden');

        filterData.page = pageNum; // update page number in case it changes

        $.ajax({
            url: ajaxurl,
            method: 'POST',
            data: filterData,
            success: function(response) {
                if (append) {
                    const tempDiv = $('<div>').html(response);
                    $('#product-grid').append(tempDiv.hide().fadeIn(400));
                } else {
                    $('#product-grid').html(response);
                }
                $('#ajax-loader').addClass('hidden');
            },
            error: function(xhr, status, error) {
                console.error('Error loading products:', xhr.responseText);
                $('#product-grid').html('<p class="text-red-500">Failed to load products.</p>');
                $('#ajax-loader').addClass('hidden');
            }
        });
    }

    // Handle filter button click
    $(document).on('click', '#filter-button', function(e) {
        e.preventDefault();
        const filters = getFilterData();
        console.log('Applied Filters:', filters);

        $('#drawer-overlay').trigger('click');
        $('#load-more').hide();
        page = 1;
        loadProducts(page, false, filters);
        closeDrawerfilter();
    });

    // Handle load more button
    $(document).on('click', '#load-more', function() {
        page++;
        const filters = getFilterData();
        loadProducts(page, true, filters);
    });

    // Handle sort change
    $('#drawer').on('change', 'input[name="sort"]', function() {
        currentSort = $(this).val();
        page = 1;
        fetchSortedProducts(currentSort, page);
        closeDrawer();
    });

    function fetchSortedProducts(sort, page) {
        $('#ajax-loader').removeClass('hidden');
        $.ajax({
            url: ajaxurl,
            method: 'GET',
            data: {
                action: 'fetch_sorted_products',
                sort: sort,
                page: page
            },
            success: function(response) {
                if (page === 1) {
                    $('#product-grid').html(response);
                } else {
                    const tempDiv = $('<div>').html(response);
                    $('#product-grid').append(tempDiv.hide().fadeIn(400));
                }
                $('#ajax-loader').addClass('hidden');
            },
            error: function(xhr, status, error) {
                console.error('Error fetching sorted products:', xhr.responseText);
                $('#ajax-loader').addClass('hidden');
            }
        });
    }

    // Global drawer functions
    window.openDrawerfilter = openDrawerfilter;
    window.closeDrawerfilter = closeDrawerfilter;

    window.openDrawer = function(title, url) {
        const drawer = document.getElementById('drawer');
        const drawerPanel = document.getElementById('drawer-panel');
        const drawerContent = document.getElementById('drawer-content');
        drawer.classList.remove('hidden');
        setTimeout(() => drawerPanel.classList.remove('translate-x-full'), 10);

        $('#ajax-loader').removeClass('hidden');
        fetch(url)
            .then(response => response.text())
            .then(html => {
                drawerContent.innerHTML = html;
                $('#ajax-loader').addClass('hidden');
            })
            .catch(error => {
                console.error('Error loading sort drawer:', error);
                drawerContent.innerHTML = '<p class="text-red-500">Failed to load content.</p>';
                $('#ajax-loader').addClass('hidden');
            });
    };

    window.closeDrawer = function() {
        const drawer = document.getElementById('drawer');
        const drawerPanel = document.getElementById('drawer-panel');
        drawerPanel.classList.add('translate-x-full');
        setTimeout(() => drawer.classList.add('hidden'), 300);
    };

    document.getElementById('close-drawer').addEventListener('click', window.closeDrawer);
    document.getElementById('drawer').addEventListener('click', function(e) {
        if (e.target === this) window.closeDrawer();
    });
});

</script>