<?php
/**
 * Handles free plugin user dashboard
 * 
 * @package SCFW_Size_Chart_For_Woocommerce
 * @since   2.4.3
 */

// Exit if accessed directly
if ( ! defined( 'WPINC' ) ) {
	exit;
}

// Get plugin header
require_once( plugin_dir_path( __FILE__ ) . 'header/plugin-header.php' );

// Get product details from Freemius via API
$annual_plugin_price = '';
$monthly_plugin_price = '';
$plugin_details = array(
    'product_id' => 45112,
);

$api_url = add_query_arg(wp_rand(), '', SCFW_STORE_URL . 'wp-json/dotstore-product-fs-data/v2/dotstore-product-fs-data');
$final_api_url = add_query_arg($plugin_details, $api_url);

if ( function_exists( 'vip_safe_wp_remote_get' ) ) {
    $api_response = vip_safe_wp_remote_get( $final_api_url, 3, 1, 20 );
} else {
    $api_response = wp_remote_get( $final_api_url ); // phpcs:ignore
}

if ( ( !is_wp_error($api_response)) && (200 === wp_remote_retrieve_response_code( $api_response ) ) ) {
	$api_response_body = wp_remote_retrieve_body($api_response);
	$plugin_pricing = json_decode( $api_response_body, true );

	if ( isset( $plugin_pricing ) && ! empty( $plugin_pricing ) ) {
		$first_element = reset( $plugin_pricing );
        if ( ! empty( $first_element['price_data'] ) ) {
            $first_price = reset( $first_element['price_data'] )['annual_price'];
        } else {
            $first_price = "0";
        }

        if( "0" !== $first_price ){
        	$annual_plugin_price = $first_price;
        	$monthly_plugin_price = round( intval( $first_price  ) / 12 );
        }
	}
}

// Set plugin key features content
$plugin_key_features = array(
    array(
        'title' => esc_html__( 'Easy Size Chart User Access', 'size-chart-for-woocommerce' ),
        'description' => esc_html__( 'Grant specific user roles, such as shop managers, access to manage size charts by providing the necessary permissions for seamless control.', 'size-chart-for-woocommerce' ),
        'popup_image' => esc_url( SCFW_PLUGIN_URL . 'admin/images/pro-features-img/feature-box-one-img.png' ),
        'popup_content' => array(
        	esc_html__( 'Grant access to specific user roles, such as shop managers, to manage size charts and reduce the admin headache by providing them with the necessary permissions.', 'size-chart-for-woocommerce' ),
        ),
        'popup_examples' => array(
            esc_html__( 'Empower shop managers to update and customize size charts based on their specific inventory and customer needs.', 'size-chart-for-woocommerce' ),
            esc_html__( 'Enable vendors to maintain accurate and relevant size charts for their specific products.', 'size-chart-for-woocommerce' ),
        )
    ),
    array(
        'title' => esc_html__( 'One Click Import Export', 'size-chart-for-woocommerce' ),
        'description' => esc_html__( 'Easily import and export size charts for seamless management and sharing of size chart templates across your WooCommerce store.', 'size-chart-for-woocommerce' ),
        'popup_image' => esc_url( SCFW_PLUGIN_URL . 'admin/images/pro-features-img/feature-box-two-img.png' ),
        'popup_content' => array(
        	esc_html__( 'Effortlessly manage and share size chart templates across your WooCommerce store using the import and export feature with just a few clicks.', 'size-chart-for-woocommerce' ),
        ),
        'popup_examples' => array(
            esc_html__( 'Import bulk size charts effortlessly between staging to the production site in minutes once you want to make the changes live.', 'size-chart-for-woocommerce' ),
            esc_html__( 'Export all the size charts templates for backup purposes or share them with colleagues or clients.', 'size-chart-for-woocommerce' ),
        )
    ),
    array(
        'title' => esc_html__( 'Country Based Size Chart', 'size-chart-for-woocommerce' ),
        'description' => esc_html__( 'Optimize the visibility of your size charts and enhance the user experience by displaying relevant size information based on customer location.', 'size-chart-for-woocommerce' ),
        'popup_image' => esc_url( SCFW_PLUGIN_URL . 'admin/images/pro-features-img/feature-box-three-img.png' ),
        'popup_content' => array(
        	esc_html__( 'Improve size chart visibility and enhance the user experience by displaying location-based relevant size chart information.', 'size-chart-for-woocommerce' ),
        ),
        'popup_examples' => array(
            esc_html__( 'Personalized size chart recommendation for US customers to enhance user experience.', 'size-chart-for-woocommerce' ),
            esc_html__( 'Display specific size chart guidance for French shoppers with different measurements.', 'size-chart-for-woocommerce' ),
        )
    ),
    array(
        'title' => esc_html__( 'Flexible Size Chart Position', 'size-chart-for-woocommerce' ),
        'description' => esc_html__( 'Customize the position of the size chart pop-up button for optimal visibility and engagement - before the summary, after "add to cart", or after the product meta.', 'size-chart-for-woocommerce' ),
        'popup_image' => esc_url( SCFW_PLUGIN_URL . 'admin/images/pro-features-img/feature-box-four-img.png' ),
        'popup_content' => array(
        	esc_html__( 'Define the size chart link placement before the product summary, before/after the cart button, or after the product meta.', 'size-chart-for-woocommerce' ),
        ),
        'popup_examples' => array(
            esc_html__( 'Place the size chart button before the summary to aid purchase decisions.', 'size-chart-for-woocommerce' ),
            esc_html__( 'Showcase the size chart button after the “add to cart” button for easy reference.', 'size-chart-for-woocommerce' ),
        )
    ),
    array(
        'title' => esc_html__( 'Size Chart Compatibility', 'size-chart-for-woocommerce' ),
        'description' => esc_html__( 'Utilize a shortcode to seamlessly display size charts on your custom product template to make it compatible with enhancing the user experience.', 'size-chart-for-woocommerce' ),
        'popup_image' => esc_url( SCFW_PLUGIN_URL . 'admin/images/pro-features-img/feature-box-five-img.png' ),
        'popup_content' => array(
        	esc_html__( 'Enhance the user experience by seamlessly integrating size charts into your custom product templates using a shortcode.', 'size-chart-for-woocommerce' ),
        	esc_html__( 'Provide valuable sizing information on your unique product pages, boosting customer confidence and informed decision-making.', 'size-chart-for-woocommerce' )
        ),
        'popup_examples' => array(
            esc_html__( 'Integrate size charts effortlessly using the shortcode in the custom product templates of WPBakery, Elementor, or any other page builder.', 'size-chart-for-woocommerce' ),
        )
    ),
    array(
        'title' => esc_html__( 'Own Chart Table Design', 'size-chart-for-woocommerce' ),
        'description' => esc_html__( 'Effortlessly customize size chart layout, including table head color, row color, etc., to create visually stunning size charts that captivate your customers.', 'size-chart-for-woocommerce' ),
        'popup_image' => esc_url( SCFW_PLUGIN_URL . 'admin/images/pro-features-img/feature-box-six-img.png' ),
        'popup_content' => array(
        	esc_html__( 'Customize size chart tables easily to match visually with your theme, captivating customers.', 'size-chart-for-woocommerce' ),
        	esc_html__( 'You can adjust table head colors, row colors, and more to match your brand aesthetic.', 'size-chart-for-woocommerce' )
        ),
        'popup_examples' => array(
            esc_html__( 'Design attractive size charts with custom colors with different color effects, like table cell hover, etc.', 'size-chart-for-woocommerce' ),
            esc_html__( 'Create glamorous size charts with silver table heads and soft silver even rows for effective table visualization.', 'size-chart-for-woocommerce' ),
        )
    ),
);
?>
	<div class="wcpfc-section-left">
		<div class="dotstore-upgrade-dashboard">
			<div class="premium-benefits-section">
				<h2><?php esc_html_e( 'Upgrade to Unlock Premium Features', 'size-chart-for-woocommerce' ); ?></h2>
				<p><?php esc_html_e( 'Upgrade to premium for advanced features, increased customer satisfaction, and fewer returns with the right size guide!', 'size-chart-for-woocommerce' ); ?></p>
			</div>
			<div class="premium-plugin-details">
				<div class="premium-key-fetures">
					<h3><?php esc_html_e( 'Discover Our Top Key Features', 'size-chart-for-woocommerce' ) ?></h3>
					<ul>
						<?php 
						if ( isset( $plugin_key_features ) && ! empty( $plugin_key_features ) ) {
							foreach( $plugin_key_features as $key_feature ) {
								?>
								<li>
									<h4><?php echo esc_html( $key_feature['title'] ); ?><span class="premium-feature-popup"></span></h4>
									<p><?php echo esc_html( $key_feature['description'] ); ?></p>
									<div class="feature-explanation-popup-main">
										<div class="feature-explanation-popup-outer">
											<div class="feature-explanation-popup-inner">
												<div class="feature-explanation-popup">
													<span class="dashicons dashicons-no-alt popup-close-btn" title="<?php esc_attr_e('Close', 'size-chart-for-woocommerce'); ?>"></span>
													<div class="popup-body-content">
														<div class="feature-content">
															<h4><?php echo esc_html( $key_feature['title'] ); ?></h4>
															<?php 
															if ( isset( $key_feature['popup_content'] ) && ! empty( $key_feature['popup_content'] ) ) {
																foreach( $key_feature['popup_content'] as $feature_content ) {
																	?>
																	<p><?php echo esc_html( $feature_content ); ?></p>
																	<?php
																}
															}
															?>
															<ul>
																<?php 
																if ( isset( $key_feature['popup_examples'] ) && ! empty( $key_feature['popup_examples'] ) ) {
																	foreach( $key_feature['popup_examples'] as $feature_example ) {
																		?>
																		<li><?php echo esc_html( $feature_example ); ?></li>
																		<?php
																	}
																}
																?>
															</ul>
														</div>
														<div class="feature-image">
															<img src="<?php echo esc_url( $key_feature['popup_image'] ); ?>" alt="<?php echo esc_attr( $key_feature['title'] ); ?>">
														</div>
													</div>
												</div>		
											</div>
										</div>
									</div>
								</li>
								<?php
							}
						}
						?>
					</ul>
				</div>
				<div class="premium-plugin-buy">
					<div class="premium-buy-price-box">
						<div class="price-box-top">
							<div class="pricing-icon">
								<img src="<?php echo esc_url( SCFW_PLUGIN_URL . 'admin/images/premium-upgrade-img/pricing-1.svg' ); ?>" alt="<?php esc_attr_e( 'Personal Plan', 'size-chart-for-woocommerce' ); ?>">
							</div>
							<h4><?php esc_html_e( 'Personal', 'size-chart-for-woocommerce' ) ?></h4>
						</div>
						<div class="price-box-middle">
							<?php
							if ( ! empty( $annual_plugin_price ) ) {
								?>
								<div class="monthly-price-wrap"><?php echo esc_html( '$' . $monthly_plugin_price ) ?><span class="seprater">/</span><span><?php esc_html_e( 'month', 'size-chart-for-woocommerce' ) ?></span></div>
								<div class="yearly-price-wrap"><?php echo sprintf( esc_html__( 'Pay $%s today. Renews in 12 months.', 'size-chart-for-woocommerce' ), esc_html( $annual_plugin_price ) ); ?></div>
								<?php	
							}
							?>
							<span class="for-site"><?php esc_html_e( '1 site', 'size-chart-for-woocommerce' ) ?></span>
							<p class="price-desc"><?php esc_html_e( 'Great for website owners with a single WooCommerce Store', 'size-chart-for-woocommerce' ) ?></p>
						</div>
						<div class="price-box-bottom">
							<a href="javascript:void(0);" class="upgrade-now"><?php esc_html_e( 'Get The Premium Version', 'size-chart-for-woocommerce' ) ?></a>
							<p class="trusted-by"><?php esc_html_e( 'Trusted by 100,000+ store owners and WP experts!', 'size-chart-for-woocommerce' ) ?></p>
						</div>
					</div>
					<div class="premium-satisfaction-guarantee premium-satisfaction-guarantee-2">
						<div class="money-back-img">
							<img src="<?php echo esc_url(SCFW_PLUGIN_URL . 'admin/images/premium-upgrade-img/14-Days-Money-Back-Guarantee.png'); ?>" alt="<?php esc_attr_e('14-Day money-back guarantee', 'size-chart-for-woocommerce'); ?>">
						</div>
						<div class="money-back-content">
							<h2><?php esc_html_e( '14-Day Satisfaction Guarantee', 'size-chart-for-woocommerce' ) ?></h2>
							<p><?php esc_html_e( 'You are fully protected by our 100% Satisfaction Guarantee. If over the next 14 days you are unhappy with our plugin or have an issue that we are unable to resolve, we\'ll happily consider offering a 100% refund of your money.', 'size-chart-for-woocommerce' ); ?></p>
						</div>
					</div>
					<div class="plugin-customer-review">
						<h3><?php esc_html_e( 'Best Size Chart Plugin!', 'size-chart-for-woocommerce' ) ?></h3>
						<p>
							<?php echo wp_kses( __( 'This <strong>plugin significantly boosts my sales</strong> by helping customers choose the right size, ensuring a confident purchase. It <strong>also reduces returns</strong>. Thanks for creating this awesome plugin.', 'size-chart-for-woocommerce' ), array(
					                'strong' => array(),
					            ) ); 
				            ?>
			            </p>
						<div class="review-customer">
							<div class="customer-img">
								<img src="<?php echo esc_url(SCFW_PLUGIN_URL . 'admin/images/premium-upgrade-img/customer-profile-img.png'); ?>" alt="<?php esc_attr_e('Customer Profile Image', 'size-chart-for-woocommerce'); ?>">
							</div>
							<div class="customer-name">
								<span><?php esc_html_e( 'Shayle Casey', 'size-chart-for-woocommerce' ) ?></span>
								<div class="customer-rating-bottom">
									<div class="customer-ratings">
										<span class="dashicons dashicons-star-filled"></span>
										<span class="dashicons dashicons-star-filled"></span>
										<span class="dashicons dashicons-star-filled"></span>
										<span class="dashicons dashicons-star-filled"></span>
										<span class="dashicons dashicons-star-filled"></span>
									</div>
									<div class="verified-customer">
										<span class="dashicons dashicons-yes-alt"></span>
										<?php esc_html_e( 'Verified Customer', 'size-chart-for-woocommerce' ) ?>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="upgrade-to-pro-faqs">
				<h2><?php esc_html_e( 'FAQs', 'size-chart-for-woocommerce' ); ?></h2>
				<div class="upgrade-faqs-main">
					<div class="upgrade-faqs-list">
						<div class="upgrade-faqs-header">
							<h3><?php esc_html_e( 'Do you offer support for the plugin? What’s it like?', 'size-chart-for-woocommerce' ); ?></h3>
						</div>
						<div class="upgrade-faqs-body">
							<p>
							<?php 
								echo sprintf(
								    esc_html__('Yes! You can read our %1$s or submit a %2$s. We are very responsive and strive to do our best to help you.', 'size-chart-for-woocommerce'),
								    '<a href="' . esc_url('https://docs.thedotstore.com/collection/233-product-size-charts') . '" target="_blank">' . esc_html__('knowledge base', 'size-chart-for-woocommerce') . '</a>',
								    '<a href="' . esc_url('https://www.thedotstore.com/support-ticket/') . '" target="_blank">' . esc_html__('support ticket', 'size-chart-for-woocommerce') . '</a>',
								);

							?>
							</p>
						</div>
					</div>
					<div class="upgrade-faqs-list">
						<div class="upgrade-faqs-header">
							<h3><?php esc_html_e( 'What payment methods do you accept?', 'size-chart-for-woocommerce' ); ?></h3>
						</div>
						<div class="upgrade-faqs-body">
							<p><?php esc_html_e( 'You can pay with your credit card using Stripe checkout. Or your PayPal account.', 'size-chart-for-woocommerce' ) ?></p>
						</div>
					</div>
					<div class="upgrade-faqs-list">
						<div class="upgrade-faqs-header">
							<h3><?php esc_html_e( 'What’s your refund policy?', 'size-chart-for-woocommerce' ); ?></h3>
						</div>
						<div class="upgrade-faqs-body">
							<p><?php esc_html_e( 'We have a 14-day money-back guarantee.', 'size-chart-for-woocommerce' ) ?></p>
						</div>
					</div>
					<div class="upgrade-faqs-list">
						<div class="upgrade-faqs-header">
							<h3><?php esc_html_e( 'I have more questions…', 'size-chart-for-woocommerce' ); ?></h3>
						</div>
						<div class="upgrade-faqs-body">
							<p>
							<?php 
								echo sprintf(
								    esc_html__('No problem, we’re happy to help! Please reach out at %s.', 'size-chart-for-woocommerce'),
								    '<a href="' . esc_url('mailto:hello@thedotstore.com') . '" target="_blank">' . esc_html('hello@thedotstore.com') . '</a>',
								);

							?>
							</p>
						</div>
					</div>
				</div>
			</div>
			<div class="upgrade-to-premium-btn">
				<a href="javascript:void(0);" target="_blank" class="upgrade-now"><?php esc_html_e( 'Get The Premium Version', 'size-chart-for-woocommerce' ) ?><svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="crown" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512" class="svg-inline--fa fa-crown fa-w-20 fa-3x" width="22" height="20"><path fill="#000" d="M528 448H112c-8.8 0-16 7.2-16 16v32c0 8.8 7.2 16 16 16h416c8.8 0 16-7.2 16-16v-32c0-8.8-7.2-16-16-16zm64-320c-26.5 0-48 21.5-48 48 0 7.1 1.6 13.7 4.4 19.8L476 239.2c-15.4 9.2-35.3 4-44.2-11.6L350.3 85C361 76.2 368 63 368 48c0-26.5-21.5-48-48-48s-48 21.5-48 48c0 15 7 28.2 17.7 37l-81.5 142.6c-8.9 15.6-28.9 20.8-44.2 11.6l-72.3-43.4c2.7-6 4.4-12.7 4.4-19.8 0-26.5-21.5-48-48-48S0 149.5 0 176s21.5 48 48 48c2.6 0 5.2-.4 7.7-.8L128 416h384l72.3-192.8c2.5.4 5.1.8 7.7.8 26.5 0 48-21.5 48-48s-21.5-48-48-48z" class=""></path></svg></a>
			</div>
		</div>
	</div>
</div>
</div>
</div>
</div>
<?php 
