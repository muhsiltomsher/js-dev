<?php
/**
 * The file that defines the WP Tabs Pro Woo Feature
 *
 * @link http://shapedplugin.com
 * @since 2.0.2
 *
 * @package    WP_Tabs
 * @subpackage WP_Tabs/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
/**
 * Custom post class to register the Product tab.
 */
class WP_Tabs_Product_Tab {

	/**
	 * The single instance of the class.
	 *
	 * @var self
	 * @since 2.0.2
	 */
	private static $instance;

	/**
	 * Path to the file.
	 *
	 * @since 2.0.2
	 *
	 * @var string
	 */
	public $file = __FILE__;

	/**
	 * Sptpro_woo_set_tab.
	 *
	 * @var string
	 */
	public $sptpro_woo_set_tab = 'latest';

	/**
	 * Holds the base class object.
	 *
	 * @since 2.0.2
	 *
	 * @var object
	 */
	public $base;

	/**
	 *
	 * WooCommerce product tab construct function.
	 *
	 * @param string $sptpro_woo_set_tab set tab.
	 */
	public function __construct( $sptpro_woo_set_tab ) {
		$this->sptpro_woo_set_tab = $sptpro_woo_set_tab;
	}

	/**
	 *
	 * WooCommerce faq tab.
	 *
	 * @return array $tabs
	 * @param array $tabs woo tab.
	 */
	public function sptpro_woo_tab( $tabs ) {
		global $product;

		$sptpro_woo_set_tab     = $this->sptpro_woo_set_tab;
		$sptpro_display_tab_for = $sptpro_woo_set_tab['sptpro_display_tab_for'];

		$product_id = method_exists( $product, 'get_id' ) === true ? $product->get_id() : $product->ID;
		$cat_ids    = get_the_terms( $product_id, 'product_cat' );
		$cat_ids    = wp_list_pluck( $cat_ids, 'term_id' );

		if ( 'all' === $sptpro_display_tab_for ) {
			$sptpro_tab_priority          = isset( $sptpro_woo_set_tab['sptpro_woo_tab_label_priority'] ) ? $sptpro_woo_set_tab['sptpro_woo_tab_label_priority'] : '35';
			$sptpro_woo_tab_shortcode_ids = isset( $sptpro_woo_set_tab['sptpro_woo_tab_shortcode'] ) ? $sptpro_woo_set_tab['sptpro_woo_tab_shortcode'] : array();

			if ( ! empty( $sptpro_woo_tab_shortcode_ids ) ) {
				foreach ( $sptpro_woo_tab_shortcode_ids as $key => $sptpro_woo_tab_shortcode_id ) {
					$sptpro_data_src = get_post_meta( $sptpro_woo_tab_shortcode_id, 'sp_tab_source_options', true );
					$sptpro_tab_type = ( isset( $sptpro_data_src['sptpro_tab_type'] ) ? $sptpro_data_src['sptpro_tab_type'] : '' );
					$sptpro_data_src = isset( $sptpro_data_src['sptpro_content_source'] ) ? $sptpro_data_src['sptpro_content_source'] : '';

					if ( 'content-tabs' === $sptpro_tab_type && ! empty( $sptpro_data_src ) ) {
						foreach ( $sptpro_data_src as $key => $sptpro_tab ) {
							if ( ! is_array( $sptpro_tab ) ) {
								$sptpro_tab = array();
							}
							++$sptpro_tab_priority;

							// Check if WPML is active.
							if ( function_exists( 'icl_object_id' ) ) {
								// Get the language details for this product ID.
								$language_product_info   = apply_filters( 'wpml_post_language_details', null, $product_id );
								$language_shortcode_info = apply_filters( 'wpml_post_language_details', null, $sptpro_woo_tab_shortcode_id );

								$product_lang   = $language_product_info['language_code'];
								$shortcode_lang = $language_shortcode_info['language_code'];
							}

							if ( ! empty( $sptpro_tab ) && is_array( $sptpro_tab ) ) {
								if ( ! function_exists( 'icl_object_id' ) || $product_lang === $shortcode_lang ) {
									$tabs[ 'sptpro_tab_' . $sptpro_tab_priority . $key ] = array(
										'title'    => $sptpro_tab['tabs_content_title'],
										'priority' => '50',
										'callback' => array( $this, 'sptpro_product_tabs_panel_content' ),
										'content'  => $sptpro_tab['tabs_content_description'],
									);
								}
							}
						}
					}
				}
			}
		}
		return $tabs;
	}

	/**
	 * WooCommerce Tab Content.
	 *
	 * @param array $key tab key.
	 * @param array $tab woo tab content.
	 * @return void
	 */
	public function sptpro_product_tabs_panel_content( $key, $tab ) {
		$content = '';

		$sptpro_the_content_filter = apply_filters( 'sptpro_the_content_filter', true );

		if ( $sptpro_the_content_filter ) {
			$content = apply_filters( 'the_content', $tab['content'] );
		} else {
			$content = apply_filters( 'sptpro_woo_tab_content', $tab['content'] );
		}

		$tab_title_html = '<h2 class="sptpro-woo-tab-title sptpro-woo-tab-title-' . urldecode( sanitize_title( $tab['title'] ) ) . '">' . $tab['title'] . '</h2>';
		echo apply_filters( 'sptpro_repeatable_product_tabs_heading', $tab_title_html, $tab );
		echo apply_filters( 'sptpro_repeatable_product_tabs_content', $content, $tab );
	}
}
