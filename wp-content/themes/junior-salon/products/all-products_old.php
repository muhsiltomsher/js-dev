<!-- Loader -->
<div id="ajax-loader"
    class="fixed top-0 left-0 w-full h-full bg-white bg-opacity-75 flex items-center justify-center z-50 hidden">
    <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-black"></div>
</div>

<!-- FILTER & ORDER Button -->
<button onclick="openDrawerfilter()"
    class="flex items-center bg-transparent group m-0 text-lg font-semibold px-4 py-2 hover:text-black">
    FILTER & ORDER
</button>

<!-- Drawer Filter Container -->
<div id="drawer-container-filter"
    class="fixed top-0 right-0 w-80 h-full bg-white shadow-lg z-50 p-6 overflow-y-auto hidden transition-transform duration-300 ease-in-out">
</div>
<div id="drawer-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden" onclick="closeDrawerfilter()"></div>

<!-- SORT BY Button -->
<button
    onclick="openDrawer('Sort By', '<?php echo esc_url( home_url('/wp-content/themes/junior-salon/products/sort-drawer.php') ); ?>')"
    class="flex items-center bg-transparent group m-0">
    <span class="text-lg">Sort By</span>
</button>

<!-- Sort Drawer Container -->
<div id="drawer" class="fixed inset-0 bg-gray-900 bg-opacity-50 z-50 hidden">
    <div class="absolute top-0 right-0 bg-white w-3/4 md:w-1/2 h-full p-6 transform transition-transform duration-300 ease-in-out translate-x-full"
        id="drawer-panel">
        <button id="close-drawer" class="absolute top-4 right-4 text-xl text-gray-500 hover:text-gray-700">&times;</button>
        <div id="drawer-content"></div>
    </div>
</div>

<h2 class="text-3xl font-bold mb-6">NEW FOR KIDS</h2>

<?php
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
    <button id="load-more" class="px-6 py-2 bg-black text-white rounded-lg hover:bg-gray-900 transition"
        data-page="<?php echo esc_attr($paged + 1); ?>">
        Load More
    </button>
</div>
<?php endif; ?>
<?php else : ?><p>No products found.</p><?php endif; ?>
<?php wp_reset_postdata(); ?>
<script>
    var ajaxurl = '<?php echo esc_js(admin_url('admin-ajax.php')); ?>';
</script>
<script>
function openDrawerfilter() {
    document.getElementById('drawer-overlay').classList.remove('hidden');
    document.getElementById('drawer-container-filter').classList.remove('hidden');
    document.getElementById('ajax-loader').classList.remove('hidden');
    fetch(ajaxurl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'action=load_filter_drawer_content'
    })
    .then(res => res.text())
    .then(data => {
        document.getElementById('drawer-container-filter').innerHTML = data;
        document.getElementById('ajax-loader').classList.add('hidden');
    });
}

function closeDrawerfilter() {
    document.getElementById('drawer-overlay').classList.add('hidden');
    document.getElementById('drawer-container-filter').classList.add('hidden');
}

function openDrawer(title, url) {
    const drawer = document.getElementById('drawer');
    const drawerPanel = document.getElementById('drawer-panel');
    const drawerContent = document.getElementById('drawer-content');
    drawer.classList.remove('hidden');
    setTimeout(() => drawerPanel.classList.remove('translate-x-full'), 10);
    document.getElementById('ajax-loader').classList.remove('hidden');
    fetch(url)
        .then(response => response.text())
        .then(html => {
            drawerContent.innerHTML = html;
            document.getElementById('ajax-loader').classList.add('hidden');
        })
        .catch(() => {
            drawerContent.innerHTML = '<p class="text-red-500">Failed to load content.</p>';
            document.getElementById('ajax-loader').classList.add('hidden');
        });
}

function closeDrawer() {
    const drawer = document.getElementById('drawer');
    const drawerPanel = document.getElementById('drawer-panel');
    drawerPanel.classList.add('translate-x-full');
    setTimeout(() => drawer.classList.add('hidden'), 300);
}
document.getElementById('close-drawer').addEventListener('click', closeDrawer);
document.getElementById('drawer').addEventListener('click', function(e) {
    if (e.target === this) closeDrawer();
});

jQuery(document).ready(function($) {
    let page = 1;
    let currentSort = '';

    $('#drawer').on('change', 'input[name="sort"]', function() {
        currentSort = $(this).val();
        page = 1;
        fetchSortedProducts(currentSort, page);
        closeDrawer();
    });

    $('#load-more').on('click', function() {
        page++;
        fetchSortedProducts(currentSort, page);
    });

    function fetchSortedProducts(sort, page) {
        const ajaxUrl = '<?php echo admin_url("admin-ajax.php"); ?>';
        $('#ajax-loader').removeClass('hidden');
        $.ajax({
            url: ajaxUrl,
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
            error: function(err) {
                $('#ajax-loader').addClass('hidden');
                console.error('Error fetching products:', err);
            }
        });
    }

    let currentPage = 1;
    let selectedCategories = [], selectedBrands = [], selectedAge = [];

function loadProducts(page = 1, append = false, sizes = [], colors = [], minPrice = '', maxPrice = '') {
alert(sizes);
        $('#ajax-loader').removeClass('hidden');
        $.ajax({
            url: ajaxurl,
            method: 'POST',
            data: {
                action: 'filter_products',
                categories: selectedCategories,
                brands: selectedBrands,
                age: selectedAge,
                sizes: sizes,
            colors: colors,
            min_price: minPrice,
            max_price: maxPrice,
                page: page,
            },
            success: function(response) {
                if (append) {
                    const tempDiv = $('<div>').html(response);
                    $('#product-grid').append(tempDiv.hide().fadeIn(400));
                } else {
                    $('#product-grid').html(response);
                }
                $('#ajax-loader').addClass('hidden');
                currentPage = page;
            },
            error: function(xhr, status, error) {
                console.error('Error loading products:', error);
                $('#product-grid').html('<p class="text-red-500">Failed to load products.</p>');
                $('#ajax-loader').addClass('hidden');
            }
        });
    }

    $(document).on('click', '#filter-button', function(e) {
        e.preventDefault();
        selectedCategories = $('input[name="product_cat[]"]:checked').map(function() { return $(this).val(); }).get();
        selectedBrands = $('input[name="product_brand[]"]:checked').map(function() { return $(this).val(); }).get();
        selectedAge = $('input[name="age_product_cat[]"]:checked').map(function() { return $(this).val(); }).get();
      
        let selectedSizes = $('input[name="pa_size[]"]:checked').map(function() {
        return $(this).val();
    }).get();

    let selectedColors = $('input[name="pa_color[]"]:checked').map(function() {
        return $(this).val();
    }).get();

    let minPrice = $('input[name="min_price"]').val();
    let maxPrice = $('input[name="max_price"]').val();

      
        $('#drawer-overlay').trigger('click');
        $('#load-more').hide();
        currentPage = 1;
        loadProducts(currentPage, false,selectedSizes, selectedColors, minPrice, maxPrice);
        closeDrawerfilter();
    });

    $(document).on('click', '.load-more-btn', function() {
        const nextPage = parseInt($(this).data('next-page'));
 // Re-read current filters
 selectedCategories = $('input[name="product_cat[]"]:checked').map(function() {
        return $(this).val();
    }).get();

    selectedBrands = $('input[name="product_brand[]"]:checked').map(function() {
        return $(this).val();
    }).get();

    selectedAge = $('input[name="age_product_cat[]"]:checked').map(function() {
        return $(this).val();
    }).get();

    let selectedSizes = $('input[name="pa_size[]"]:checked').map(function() {
        return $(this).val();
    }).get();

    let selectedColors = $('input[name="pa_color[]"]:checked').map(function() {
        return $(this).val();
    }).get();

    let minPrice = $('input[name="min_price"]').val();
    let maxPrice = $('input[name="max_price"]').val();

    loadProducts(nextPage, true, selectedSizes, selectedColors, minPrice, maxPrice);

    });
});
</script>
