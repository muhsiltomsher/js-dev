<?php

use Product_Gallery_Sldier\Product;

if ( ! function_exists( 'cix_get_wp_image_sizes' ) ) {
	/**
	 * Get all registered image sizes with details.
	 *
	 * @return array
	 */
	function cix_get_wp_image_sizes() {
		global $_wp_additional_image_sizes;
		$sizes = array();

		foreach ( get_intermediate_image_sizes() as $_size ) {
			if ( in_array( $_size, array( 'thumbnail', 'medium', 'medium_large', 'large' ), true ) ) {
				$width           = get_option( "{$_size}_size_w" );
				$height          = get_option( "{$_size}_size_h" );
				$crop            = (bool) get_option( "{$_size}_crop" ) ? 'hard' : 'soft';
				$sizes[ $_size ] = ucfirst( "{$_size} - $crop:{$width}x{$height}" );
			} elseif ( isset( $_wp_additional_image_sizes[ $_size ] ) ) {
				$width           = $_wp_additional_image_sizes[ $_size ]['width'];
				$height          = $_wp_additional_image_sizes[ $_size ]['height'];
				$crop            = $_wp_additional_image_sizes[ $_size ]['crop'] ? 'hard' : 'soft';
				$sizes[ $_size ] = ucfirst( "{$_size} - $crop:{$width}X{$height}" );
			}
		}
		return $sizes;
	}
}

if ( ! function_exists( 'wpgs_get_option' ) ) {
	/**
	 * Get plugin option from settings.
	 *
	 * @param string $option
	 * @param mixed  $default
	 * @return mixed
	 */
	function wpgs_get_option( $option, $default = '' ) {
		$options = get_option( 'wpgs_form' );
		if ( isset( $options[ $option ] ) ) {
			return $options[ $option ];
		}
		return $default;
	}
}



add_filter( 'plugin_row_meta', 'wpgs_plugin_meta_links', 10, 2 );
/**
 * Add links to plugin's description in plugins table.
 *
 * @param array  $links Initial list of links.
 * @param string $file  Basename of current plugin.
 * @return array
 */
function wpgs_plugin_meta_links( $links, $file ) {
	if ( defined( 'WPGS_PLUGIN_BASE' ) && WPGS_PLUGIN_BASE === $file ) {
		$support_link = '<a style="color:red;" target="_blank" href="https://codeixer.com/contact-us/" title="' . esc_attr__( 'Get help', 'woo-product-gallery-slider' ) . '">' . esc_html__( 'Help & Support', 'woo-product-gallery-slider' ) . '</a>';
		$rate_twist   = '<a target="_blank" href="https://wordpress.org/support/plugin/woo-product-gallery-slider/reviews/?filter=5">' . esc_html__( 'Rate this plugin »', 'woo-product-gallery-slider' ) . '</a>';
		$links[]      = $support_link;
		$links[]      = $rate_twist;
	}
	return $links;
}

add_filter( 'wc_get_template', 'wpgs_get_template', 10, 5 );

if ( ! function_exists( 'wpgs_get_template' ) ) {
	/**
	 * Override WooCommerce template location.
	 *
	 * @param string $located
	 * @param string $template_name
	 * @param array  $args
	 * @param string $template_path
	 * @param string $default_path
	 * @return string
	 */
	function wpgs_get_template( $located, $template_name, $args, $template_path, $default_path ) {
		if ( 'single-product/product-image.php' === $template_name && defined( 'WPGS_INC' ) ) {
			$located = WPGS_INC . 'product-image.php';
		}
		return $located;
	}
}

if ( ! function_exists( 'wpgs_get_image_gallery_html' ) ) {
	/**
	 * Get gallery image HTML.
	 *
	 * @param int  $attachment_id
	 * @param bool $main_image
	 * @return string
	 */
	function wpgs_get_image_gallery_html( $attachment_id, $main_image = false ) {
		$size                      = apply_filters( 'wpgs_new_main_img_size', Product::option( 'slider_image_size' ) );
		$lightbox                  = ( Product::option( 'lightbox_picker' ) == 1 ) ? 'true' : 'false';
		$lightbox_img_alt          = ( Product::option( 'lightbox_alt_text' ) == 1 ) ? 'true' : 'false';
		$img_caption               = ( Product::option( 'slider_caption' ) === 'caption' ) ? wp_get_attachment_caption( $attachment_id ) : get_the_title( $attachment_id );
		$img_caption               = ( 'true' === $lightbox_img_alt ) ? $img_caption : '';
		$zoom_image_size           = Product::option( 'zoom_image_size', 'large' );
		$lightbox_animation        = Product::option( 'lightbox_oc_effect' );
		$lightbox_slides_animation = Product::option( 'lightbox_slide_effect' );
		$lightbox_img_count        = ( Product::option( 'lightbox_img_count' ) == 1 ) ? 'true' : 'false';
		$img_has_video             = get_post_meta( $attachment_id, 'twist_video_url', true );
		$gallery_first_item_class  = 'woocommerce-product-gallery__image';
		$video_class               = $img_has_video ? 'wpgs-video' : '';
		$gallery__image            = $main_image ? 'class="' . esc_attr( $gallery_first_item_class ) . ' wpgs_image"' : 'class="wpgs_image"';
		$img_lightbox_url          = $img_has_video ? esc_url( $img_has_video ) : esc_url( wp_get_attachment_image_url( $attachment_id, apply_filters( 'gallery_slider_lightbox_image_size', 'full' ) ) );
		$caption_html              = ( Product::option( 'slider_alt_text' ) == 1 ) ? '<span class="wpgs-gallery-caption">' . esc_html( $img_caption ) . '</span>' : '';
		$image                     = wp_get_attachment_image(
			$attachment_id,
			$size,
			false,
			array(
				'alt'              => trim( wp_strip_all_tags( get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) ) ),
				'class'            => esc_attr( $main_image ? 'wp-post-image img-attr ' . apply_filters( 'wpgs_add_img_class', '' ) : 'img-attr ' . apply_filters( 'wpgs_add_img_class', '' ) ),
				'src'              => esc_url( apply_filters( 'wpgs_lazyload_src', wp_get_attachment_image_url( $attachment_id, $size ) ) ),
				'data-lazy'        => esc_url( wp_get_attachment_image_url( $attachment_id, $size ) ),
				'data-o_img'       => esc_url( wp_get_attachment_image_url( $attachment_id, $size ) ),
				'data-large_image' => esc_url( wp_get_attachment_image_url( $attachment_id, apply_filters( 'gallery_slider_zoom_image_size', $zoom_image_size ) ) ),
				'data-zoom-image'  => esc_url( wp_get_attachment_image_url( $attachment_id, apply_filters( 'gallery_slider_zoom_image_size', $zoom_image_size ) ) ),
				'data-caption'     => esc_attr( $img_caption ),
			),
			$attachment_id,
			$main_image
		);

		if ( 'true' === $lightbox ) {
			$markup = '<div ' . $gallery__image . ' data-attachment-id="' . esc_attr( $attachment_id ) . '"><a aria-label="' . esc_attr__( 'Zoom Icon', 'woo-product-gallery-slider' ) . '" class="' . esc_attr( $video_class ) . '" href="' . $img_lightbox_url . '" data-elementor-open-lightbox="no" data-caption="' . esc_attr( $img_caption ) . '" data-thumb="' . esc_url( wp_get_attachment_image_url( $attachment_id, apply_filters( 'wpgs_new_thumb_img_size', 'woocommerce_gallery_thumbnail' ) ) ) . '" data-fancybox="wpgs" data-large_image="' . esc_url( wp_get_attachment_image_url( $attachment_id, apply_filters( 'gallery_slider_zoom_image_size', $zoom_image_size ) ) ) . '" data-animation-effect="' . esc_attr( $lightbox_animation ) . '" data-transition-effect="' . esc_attr( $lightbox_slides_animation ) . '" data-infobar="' . esc_attr( $lightbox_img_count ) . '" data-loop="true" data-hash="false" data-click-slide="close" data-options=\'{"buttons": ["zoom","slideShow","fullScreen","thumbs","close"] }\'>' . $image . '</a>' . $caption_html . '</div>';
			return $markup;
		} else {
			$markup = '<div ' . $gallery__image . ' data-attachment-id="' . esc_attr( $attachment_id ) . '">' . $image . $caption_html . '</div>';
			return $markup;
		}
	}
}

if ( ! function_exists( 'wpgs_get_image_gallery_thumb_html' ) ) {
	/**
	 * Get gallery thumbnail HTML.
	 *
	 * @param int  $attachment_id
	 * @param bool $main_image
	 * @return string
	 */
	function wpgs_get_image_gallery_thumb_html( $attachment_id, $main_image = false ) {
		$size                      = apply_filters( 'wpgs_new_thumb_img_size', Product::option( 'thumbnail_image_size' ) );
		$lightbox_img_alt          = ( Product::option( 'lightbox_alt_text' ) == 1 ) ? 'true' : 'false';
		$img_caption               = empty( wp_get_attachment_caption( $attachment_id ) ) ? get_the_title( $attachment_id ) : wp_get_attachment_caption( $attachment_id );
		$img_caption               = ( 'true' === $lightbox_img_alt ) ? $img_caption : '';
		$lightbox_animation        = Product::option( 'lightbox_oc_effect' );
		$lightbox_slides_animation = Product::option( 'lightbox_slide_effect' );
		$lightbox_img_count        = ( Product::option( 'lightbox_img_count' ) == 1 ) ? 'true' : 'false';
		$img_has_video             = get_post_meta( $attachment_id, 'twist_video_url', true );
		$video_class               = $img_has_video ? 'wpgs-video' : '';
		$gallery_thumb_image       = $main_image ? 'class="gallery_thumbnail_first thumbnail_image ' . esc_attr( $video_class ) . '"' : 'class="thumbnail_image ' . esc_attr( $video_class ) . '"';
		$img_lightbox_url          = $img_has_video ? esc_url( $img_has_video ) : esc_url( wp_get_attachment_image_url( $attachment_id, apply_filters( 'gallery_slider_lightbox_image_size', 'full' ) ) );
		$image                     = wp_get_attachment_image(
			$attachment_id,
			$size,
			false,
			array(
				'alt'        => trim( wp_strip_all_tags( get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) ) ),
				'class'      => esc_attr( $main_image ? 'wp-post-image img-attr ' . apply_filters( 'wpgs_add_img_class', '' ) : 'img-attr ' . apply_filters( 'wpgs_add_img_class', '' ) ),
				'src'        => esc_url( apply_filters( 'wpgs_lazyload_src', wp_get_attachment_image_url( $attachment_id, $size ) ) ),
				'data-lazy'  => esc_url( wp_get_attachment_image_url( $attachment_id, $size ) ),
				'data-thumb' => esc_url( wp_get_attachment_image_url( $attachment_id, $size ) ),
			),
			$attachment_id,
			$main_image
		);

		if ( apply_filters( 'wpgs_carousel_mode', true ) !== true ) {
			$markup = '<a ' . $gallery_thumb_image . ' href="' . $img_lightbox_url . '" data-elementor-open-lightbox="no" data-caption="' . esc_attr( $img_caption ) . '" data-thumb="' . esc_url( wp_get_attachment_image_url( $attachment_id, $size ) ) . '" data-fancybox="wpgs" data-animation-effect="' . esc_attr( $lightbox_animation ) . '" data-transition-effect="' . esc_attr( $lightbox_slides_animation ) . '" data-infobar="' . esc_attr( $lightbox_img_count ) . '" data-loop="true" data-hash="false" data-click-slide="close" data-options=\'{"buttons": ["zoom","slideShow","fullScreen","thumbs","close"] }\'>' . $image . '</a>';
			return $markup;
		} else {
			return '<div ' . $gallery_thumb_image . '>' . $image . '</div>';
		}
	}
}

// Remove Phlox Pro "shop" plugin lightbox filter to avoid conflicts.
remove_filter( 'woocommerce_single_product_image_thumbnail_html', 'auxin_single_product_lightbox', 10, 2 );

add_action( 'woocommerce_admin_field_payment_gateways', 'bayna_plugin_banner' );

/**
 * Display a banner for Deposits for WooCommerce plugin.
 */
function bayna_plugin_banner() {
	include_once ABSPATH . 'wp-admin/includes/plugin.php';

	if ( is_plugin_active( 'deposits-for-woocommerce/deposits-for-woocommerce.php' ) ) {
		$bayna_plugin_btn        = __( 'Check Options', 'woo-product-gallery-slider' );
		$bayna_plugin_activate   = true;
		$bayna_plugin_plugin_url = admin_url( 'admin.php?page=deposits_settings' );
	} elseif ( file_exists( WP_PLUGIN_DIR . '/deposits-for-woocommerce/deposits-for-woocommerce.php' ) ) {
		$bayna_plugin_btn        = __( 'Activate Now', 'woo-product-gallery-slider' );
		$bayna_plugin_activate   = false;
		$bayna_plugin_plugin_url = wp_nonce_url( 'plugins.php?action=activate&plugin=deposits-for-woocommerce/deposits-for-woocommerce.php&plugin_status=all&paged=1', 'activate-plugin_deposits-for-woocommerce/deposits-for-woocommerce.php' );
	} else {
		$bayna_plugin_btn        = __( 'Install Now', 'woo-product-gallery-slider' );
		$bayna_plugin_activate   = false;
		$bayna_plugin_plugin_url = wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=deposits-for-woocommerce' ), 'install-plugin_deposits-for-woocommerce' );
	}

	if ( $bayna_plugin_activate ) {
		return;
	}

	echo '<div class="notice notice-info">
		<h2>' . esc_html__( 'Need Deposits or Partial Payments? Try Deposits for WooCommerce for Free!', 'woo-product-gallery-slider' ) . '</h2>
		<p>' . esc_html__( 'Want to offer deposits, partial payments, or flexible payment plans in your WooCommerce store? Deposits for WooCommerce is the ultimate solution!', 'woo-product-gallery-slider' ) . '</p>
		<p><a href="' . esc_url( $bayna_plugin_plugin_url ) . '" class="button button-primary">' . esc_html( $bayna_plugin_btn ) . '</a></p>
	</div>';
}
