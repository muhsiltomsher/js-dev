<?php
/**
 * Handles free plugin user dashboard
 * 
 * @package SCFW_Size_Chart_For_Woocommerce
 * @since   1.0.0
 */
 
// Exit if accessed directly
if ( ! defined( 'WPINC' ) ) {
	exit;
}

$file_dir_path = 'header/plugin-header.php';
if ( file_exists( plugin_dir_path( __FILE__ ) . $file_dir_path ) ) {
	require_once plugin_dir_path( __FILE__ ) . $file_dir_path;
}
?>
    <div class="thedotstore-main-table res-cl">
        <div class="dots-getting-started-main">
            <div class="getting-started-content">
                <span><?php esc_html_e( 'How to Get Started', 'size-chart-for-woocommerce' ); ?></span>
                <h3><?php esc_html_e( 'Welcome to Product Size Charts Plugin', 'size-chart-for-woocommerce' ); ?></h3>
                <p><?php esc_html_e( 'Thank you for choosing our top-rated WooCommerce Product Size Charts plugin. Our user-friendly interface makes it easy to create product size charts.', 'size-chart-for-woocommerce' ); ?></p>
                <p>
                    <?php 
                    echo sprintf(
                        esc_html__('To help you get started, watch the quick tour video on the right. For more help, explore our help documents or visit our %s for detailed video tutorials.', 'size-chart-for-woocommerce'),
                        '<a href="' . esc_url('https://www.youtube.com/@DotStore16?sub_confirmation=1') . '" target="_blank">' . esc_html__('YouTube channel', 'size-chart-for-woocommerce') . '</a>',
                    );
                    ?>
                </p>
                <div class="getting-started-actions">
                    <a href="<?php echo esc_url(add_query_arg(array('post_type' => 'size-chart'), admin_url('edit.php'))); ?>" class="quick-start"><?php esc_html_e( 'Manage Size Charts', 'size-chart-for-woocommerce' ); ?><span class="dashicons dashicons-arrow-right-alt"></span></a>
                    <a href="https://docs.thedotstore.com/article/246-getting-started-with-size-charts-plugin" target="_blank" class="setup-guide"><span class="dashicons dashicons-book-alt"></span><?php esc_html_e( 'Read the Setup Guide', 'size-chart-for-woocommerce' ); ?></a>
                </div>
            </div>
            <div class="getting-started-video">
                <iframe width="960" height="600" src="<?php echo esc_url('https://www.youtube.com/embed/oqYqsLYF4Cs'); ?>" title="<?php esc_attr_e( 'Plugin Tour', 'size-chart-for-woocommerce' ); ?>" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
            </div>
        </div>
    </div>
</div>
</div>
</div>
</div>
<?php
