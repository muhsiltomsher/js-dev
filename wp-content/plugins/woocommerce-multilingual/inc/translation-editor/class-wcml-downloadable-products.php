<?php

use WCML\StandAlone\NullSitePress;
use WCML\Utilities\DB;
use WPML\Core\ISitePress;
use WPML\FP\Obj;

class WCML_Downloadable_Products {

	const DOWNLOADABLE_META       = '_downloadable';
	const DOWNLOADABLE_FILES_META = '_downloadable_files';
	const SYNC_MODE_META          = 'wcml_sync_files';
	const SYNC_MODE_META_SELF     = 'self';
	const SYNC_MODE_META_AUTO     = 'auto';
	const SYNC_MODE_SETTING_KEY   = 'file_path_sync';

	const CACHE_GROUP = 'WCML_Downloadable_Products';

	/** @var woocommerce_wpml */
	private $woocommerce_wpml;
	/** @var SitePress|NullSitePress */
	private $sitepress;
	/** @var wpdb */
	private $wpdb;
	/** @var WPML_WP_Cache */
	private $wpml_cache;

	/**
	 * WCML_Downloadable_Products constructor.
	 *
	 * @param woocommerce_wpml        $woocommerce_wpml
	 * @param SitePress|NullSitePress $sitepress
	 * @param wpdb                    $wpdb
	 * @param WPML_WP_Cache           $wpml_cache
	 */
	public function __construct(
		woocommerce_wpml $woocommerce_wpml,
		ISitePress $sitepress,
		wpdb $wpdb,
		WPML_WP_Cache $wpml_cache = null
	) {
		$this->woocommerce_wpml = $woocommerce_wpml;
		$this->sitepress        = $sitepress;
		$this->wpdb             = $wpdb;
		$this->wpml_cache       = $wpml_cache ?: new WPML_WP_Cache( self::CACHE_GROUP );
	}

	public function add_hooks() {
		// downloadable files custom.
		add_action( 'woocommerce_product_options_downloads', [ $this, 'product_options_downloads_custom_option' ] );
		add_action( 'woocommerce_variation_options_download', [ $this, 'product_options_downloads_custom_option' ], 10, 3 );
	}

	/**
	 * @param bool         $loop
	 * @param bool         $variation_data
	 * @param bool|WP_Post $variation
	 */
	public function product_options_downloads_custom_option( $loop = false, $variation_data = false, $variation = false ) {
		global $pagenow;

		$product_id   = false;
		$is_variation = false;
		/* phpcs:disable WordPress.VIP.SuperGlobalInputUsage.AccessDetected */
		if ( 'post.php' === $pagenow && isset( $_GET['post'] ) ) {
			$product_id = (int) $_GET['post'];
		} elseif ( isset( $_POST['product_id'] ) ) {
			$product_id = (int) $_POST['product_id'];
		}

		$native_editor_translation_product_page = isset( $_GET['post_type'] ) && 'product' === $_GET['post_type'] && isset( $_GET['source_lang'] );
		/* phpcs:enable WordPress.VIP.SuperGlobalInputUsage.AccessDetected */
		if ( $native_editor_translation_product_page ) {
			return false;
		}

		$is_not_original_product = $product_id && ( 'product' !== get_post_type( $product_id ) || ! $this->woocommerce_wpml->products->is_original_product( $product_id ) );
		if ( $is_not_original_product ) {
			return false;
		}

		$this->load_custom_files_js_css();

		if ( $variation ) {
			$product_id   = $variation->ID;
			$is_variation = true;
		}

		$download_options = new WCML_Custom_Files_UI( $this->woocommerce_wpml, $product_id, $is_variation );
		$download_options->show();

	}

	public function load_custom_files_js_css() {
		static $assetsEnqueued;
		if ( $assetsEnqueued ) {
			return;
		}

		$wcml_plugin_url = $this->sitepress->get_wp_api()->constant( 'WCML_PLUGIN_URL' );
		$wcml_version    = $this->sitepress->get_wp_api()->constant( 'WCML_VERSION' );
		$wcml_js_min     = $this->sitepress->get_wp_api()->constant( 'WCML_JS_MIN' );

		wp_register_style( 'wpml-wcml-files', $wcml_plugin_url . '/res/css/wcml-files.css', null, $wcml_version );
		wp_register_script( 'wcml-scripts-files', $wcml_plugin_url . '/res/js/files' . $wcml_js_min . '.js', [ 'jquery' ], $wcml_version, true );

		wp_enqueue_style( 'wpml-wcml-files' );
		wp_enqueue_script( 'wcml-scripts-files' );

		$assetsEnqueued = true;
	}

	/**
	 * @param string|int $original_id
	 * @param string|int $trnsl_id
	 * @param array|null $data
	 */
	public function sync_files_to_translations( $original_id, $trnsl_id, $data = null ) {
		$general_product_sync = $this->woocommerce_wpml->settings[ self::SYNC_MODE_SETTING_KEY ] ;
		$custom_product_sync  = get_post_meta( $original_id, self::SYNC_MODE_META, true );

		if ( ( $custom_product_sync && $custom_product_sync === self::SYNC_MODE_META_SELF ) || ( ! $custom_product_sync && ! $general_product_sync) ) {
			if ( $data ) {
				$orig_var_files   = $this->get_files_data( $original_id );
				$file_paths_array = [];
				foreach ( $orig_var_files as $key => $var_file ) {
					$key_file                              = md5( $data[ md5( 'file-url' . $key . $original_id ) ] . $data[ md5( 'file-name' . $key . $original_id ) ] );
					$file_paths_array[ $key_file ]['name'] = $data[ md5( 'file-name' . $key . $original_id ) ];
					$file_paths_array[ $key_file ]['file'] = $data[ md5( 'file-url' . $key . $original_id ) ];
				}
				update_post_meta( $trnsl_id, self::DOWNLOADABLE_FILES_META, $file_paths_array );
			}
		} elseif ( ( $custom_product_sync && $custom_product_sync === self::SYNC_MODE_META_AUTO ) || $general_product_sync ) {
			$orig_file_path = maybe_unserialize( get_post_meta( $original_id, self::DOWNLOADABLE_FILES_META, true ) );
			update_post_meta( $trnsl_id, self::DOWNLOADABLE_FILES_META, $orig_file_path );
		}
	}

	/**
	 * @param string|int $post_id
	 */
	public function save_files_option( $post_id ) {
		$nonce = filter_input( INPUT_POST, 'wcml_save_files_option_nonce', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

		if ( isset( $_POST['wcml_file_path_option'], $nonce ) && wp_verify_nonce( $nonce, 'wcml_save_files_option' ) ) {
			if ( isset( $_POST['wcml_file_path_option'][ $post_id ] ) ) {
				if ( $_POST['wcml_file_path_option'][ $post_id ] === 'on' ) {
					update_post_meta( $post_id, self::SYNC_MODE_META, $_POST['wcml_file_path_sync'][ $post_id ] );
				} else {
					delete_post_meta( $post_id, self::SYNC_MODE_META );
				}
			}
		}
	}

	/**
	 * @param \WC_Product $product
	 *
	 * @return bool
	 */
	public function isDownloadableProduct( $product ) {
		$productId = $product->get_id();
		$cacheKey  = 'is_downloadable_product_' . $productId;

		$found   = false;
		$inCache = $this->wpml_cache->get( $cacheKey, $found );

		if ( $found ) {
			return $inCache;
		}

		if ( $product->is_downloadable() ) {
			$this->wpml_cache->set( $cacheKey, true );
			return true;
		}

		if ( $this->hasDownloadableVariation( $product ) ) {
			$this->wpml_cache->set( $cacheKey, true );
			return true;
		}

		$this->wpml_cache->set( $cacheKey, false );
		return false;
	}

	/**
	 * @param \WC_Product $product
	 *
	 * @return bool
	 */
	private function hasDownloadableVariation( $product ) {
		if ( ! $this->woocommerce_wpml->products->isVariableProductObject( $product ) ) {
			return false;
		}

		$variationIds = $product->get_children();
		if ( empty( $variationIds ) ) {
			return false;
		}

		$hasDownloadableVariations = $this->wpdb->get_results(
			$this->wpdb->prepare(
				"SELECT post_id FROM {$this->wpdb->postmeta}
				WHERE meta_key = %s
				AND meta_value = %s
				AND post_id IN (" . DB::prepareIn( $variationIds, '%d' ) . ")
				LIMIT 1",
				self::DOWNLOADABLE_META,
				wc_bool_to_string( true )
			)
		);
		return ! is_null( $hasDownloadableVariations );
	}

	/**
	 * @param \WC_Product $product
	 * @param bool        $translatableOnly Get only downloadable files that should be translated
	 *
	 * @return array
	 * @test When variable without downloadable variations
	 * @test When variable with downloadable variations and not translatable only
	 * @test When variable with downloadable variations and translatable only
	 */
	public function getDownloadableFiles( $product, $translatableOnly = true ) {
		if ( $this->woocommerce_wpml->products->isVariableProductObject( $product ) ) {
			return $this->getVariationsDownloadableFiles( $product, $translatableOnly );
		}

		$productId = $product->get_id();
		if ( ! $translatableOnly ) {
			return [ $productId => $this->get_files_data( $productId ) ];
		}

		$generalSyncEnabled  = $this->woocommerce_wpml->settings[ self::SYNC_MODE_SETTING_KEY ];
		$productSyncSettings = get_post_meta( $productId, self::SYNC_MODE_META, true );
		if (
			$productSyncSettings === self::SYNC_MODE_META_SELF
			|| ( ! $productSyncSettings && ! $generalSyncEnabled )
		) {
			return [ $productId => $this->get_files_data( $productId ) ];
		}

		return [];
	}

	/**
	 * @param \WC_Product $product
	 * @param bool        $translatableOnly Get only downloadable files that should be translated
	 *
	 * @return array
	 */
	private function getVariationsDownloadableFiles( $product, $translatableOnly = true ) {
		$variationIds = $product->get_children();
		if ( empty( $variationIds ) ) {
			return [];
		}

		$variationsDownloadableRawData = $this->wpdb->get_results(
			$this->wpdb->prepare(
				"SELECT post_id, meta_key, meta_value FROM {$this->wpdb->postmeta}
				WHERE (
				meta_key = %s OR meta_key = %s OR meta_key = %s
				) AND post_id IN (" . DB::prepareIn( $variationIds, '%d' ) . ")
				LIMIT %d",
				self::DOWNLOADABLE_META,
				self::SYNC_MODE_META,
				self::DOWNLOADABLE_FILES_META,
				3 * count( $variationIds )
			)
		);

		if ( empty( $variationsDownloadableRawData ) ) {
			return [];
		}

		$downloadableData = [];
		// Group meta values by variation ID.
		foreach ( $variationsDownloadableRawData as $rawData ) {
			if ( ! array_key_exists( $rawData->post_id, $downloadableData ) ) {
				$downloadableData[ $rawData->post_id ] = [
					self::DOWNLOADABLE_META       => wc_bool_to_string( false ),
					self::SYNC_MODE_META          => null,
					self::DOWNLOADABLE_FILES_META => '',
				];
			}
			$downloadableData[ $rawData->post_id ][ $rawData->meta_key ] = $rawData->meta_value;
		}

		$generalSyncEnabled       = (bool) $this->woocommerce_wpml->settings[ self::SYNC_MODE_SETTING_KEY ];
		$downloadableDataFiltered = array_filter( $downloadableData, function( $data ) use ( $translatableOnly, $generalSyncEnabled ) {
			if ( wc_bool_to_string( true ) !== Obj::prop( self::DOWNLOADABLE_META, $data ) ) {
				// Not downloadable.
				return false;
			}
			if ( false === (bool) Obj::prop( self::DOWNLOADABLE_FILES_META, $data ) ) {
				// Has no downloadable files.
				return false;
			}
			if ( ! $translatableOnly ) {
				// Do not check on files translatability settings.
				return true;
			}
			if ( self::SYNC_MODE_META_SELF === Obj::prop( self::SYNC_MODE_META, $data ) ) {
				// Variation is set to add separate download files for translations.
				return true;
			}
			if ( ! $generalSyncEnabled && null === Obj::prop( self::SYNC_MODE_META, $data ) ) {
				// General setting is to add separate download files for translations and variation has no custom setting.
				return true;
			}
			return false;
		} );

		$files = [];
		foreach ( $downloadableDataFiltered as $variationId => $variationData ) {
			$variationFiles = maybe_unserialize( Obj::prop( self::DOWNLOADABLE_FILES_META, $variationData ) );
			if ( ! $variationFiles ) {
				continue;
			}
			$files[ $variationId ] = [];
			foreach ( $variationFiles as $variationFile ) {
				$value = Obj::prop( 'file', $variationFile );
				$label = Obj::prop( 'name', $variationFile );
				if ( $value && $label ) {
					$files[ $variationId ][] = [
						'value' => $value,
						'label' => $label,
					];
				}
			}
		}

		return $files;
	}

	/**
	 * @param int $product_id
	 *
	 * @return array
	 */
	public function get_files_data( $product_id ) {
		$files      = maybe_unserialize( get_post_meta( $product_id, self::DOWNLOADABLE_FILES_META, true ) );
		$files_data = [];
		if ( $files ) {
			foreach ( $files as $file ) {
				$files_info          = [];
				$files_info['value'] = $file['file'];
				$files_info['label'] = $file['name'];
				$files_data[]        = $files_info;
			}
		}

		return $files_data;
	}
}
