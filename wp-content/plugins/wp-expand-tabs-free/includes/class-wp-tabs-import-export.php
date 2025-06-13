<?php
/**
 * Custom import export.
 *
 * @link http://shapedplugin.com
 * @since 2.0.0
 *
 * @package wp-expand-tabs-free
 * @subpackage wp-expand-tabs-free/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Custom import export.
 */
class Wp_Tabs_Import_Export {
	/**
	 * Export
	 *
	 * @param  mixed $shortcode_ids Export shortcode ids.
	 * @return object
	 */
	public function export( $shortcode_ids ) {
		$export = array();
		if ( ! empty( $shortcode_ids ) ) {
			$post_in = 'all_shortcodes' === $shortcode_ids ? '' : $shortcode_ids;

			$args       = array(
				'post_type'        => 'sp_wp_tabs',
				'post_status'      => array( 'inherit', 'publish' ),
				'orderby'          => 'modified',
				'suppress_filters' => 1, // wpml, ignore language filter.
				'posts_per_page'   => -1,
				'post__in'         => $post_in,
			);
			$shortcodes = get_posts( $args );
			if ( ! empty( $shortcodes ) ) {
				foreach ( $shortcodes as $shortcode ) {
					$accordion_export = array(
						'title'       => $shortcode->post_title,
						'original_id' => $shortcode->ID,
						'meta'        => array(),
					);
					foreach ( get_post_meta( $shortcode->ID ) as $metakey => $value ) {
						$accordion_export['meta'][ $metakey ] = $value[0];
					}
					$export['shortcode'][] = $accordion_export;

					unset( $accordion_export );
				}
				$export['metadata'] = array(
					'version' => WP_TABS_VERSION,
					'date'    => gmdate( 'Y/m/d' ),
				);
			}
			return $export;
		}
	}
	/**
	 * Retrieve all field IDs and their sanitize callbacks from a given metabox.
	 *
	 * @param string $metabox_id The ID of the metabox.
	 * @return array List of field ID and sanitize callback pairs.
	 */
	public function sp_get_metabox_field_ids_with_sanitizers( $metabox_id ) {
		if ( ! class_exists( 'SP_WP_TABS' ) ) {
			return array();
		}
		$sections = SP_WP_TABS::$args['sections'][ $metabox_id ] ?? null;
		if ( empty( $sections ) || ! is_array( $sections ) ) {
			return array();
		}
		$field_data = array();
		foreach ( $sections as $section ) {
			if ( empty( $section['fields'] ) || ! is_array( $section['fields'] ) ) {
				continue;
			}
			foreach ( $section['fields'] as $field ) {
				if ( isset( $field['id'] ) ) {
					$field_data[] = array(
						'id'       => $field['id'],
						'sanitize' => $field['sanitize'] ?? null,
					);
				}
			}
		}
		return $field_data;
	}
	/**
	 * Export tabs by ajax.
	 *
	 * @return void
	 */
	public function export_shortcode() {
		$nonce = ( ! empty( $_POST['nonce'] ) ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'wptabspro_options_nonce' ) ) {
			die();
		}

		$shortcode_ids = '';
		if ( isset( $_POST['tab_ids'] ) ) {
			$shortcode_ids = is_array( $_POST['tab_ids'] ) ? wp_unslash( array_map( 'absint', $_POST['tab_ids'] ) ) : sanitize_text_field( wp_unslash( $_POST['tab_ids'] ) );
		}
		$export = $this->export( $shortcode_ids );

		if ( is_wp_error( $export ) ) {
			wp_send_json_error(
				array(
					'message' => $export->get_error_message(),
				),
				400
			);
		}

		if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
			echo wp_json_encode( $export, JSON_PRETTY_PRINT );
			die;
		}

		wp_send_json( $export, 200 );
	}

	/**
	 * Import
	 *
	 * @param  array $shortcodes Import shortcode array.
	 * @throws \Exception Error massage.
	 * @return object
	 */
	public function import( $shortcodes ) {
		$errors = array();
		foreach ( $shortcodes as $index => $shortcode ) {
			$errors[ $index ] = array();
			$new_tabs_id      = 0;
			try {
				$new_tabs_id = wp_insert_post(
					array(
						'post_title'  => isset( $shortcode['title'] ) ? $shortcode['title'] : '',
						'post_status' => 'publish',
						'post_type'   => 'sp_wp_tabs',
					),
					true
				);

				if ( is_wp_error( $new_tabs_id ) ) {
					throw new Exception( $new_tabs_id->get_error_message() );
				}

				if ( isset( $shortcode['meta'] ) && is_array( $shortcode['meta'] ) ) {
					foreach ( $shortcode['meta'] as $key => $value ) {
						if ( 'sp_tab_source_options' === $key || 'sp_tab_shortcode_options' === $key ) {
							$value = str_replace( '{#ID#}', $new_tabs_id, $value );
							if ( is_serialized( $value ) ) {
								// If the value is serialized, we need to unserialize it.
								$value = unserialize( $value, array( 'allowed_classes' => false ) );
							}
							$sanitize_value = $this->sanitize_and_collect_metabox_data( $key, $value );

							update_post_meta(
								$new_tabs_id,
								$key,
								$sanitize_value
							);

						}
					}
				}
			} catch ( Exception $e ) {
				array_push( $errors[ $index ], $e->getMessage() );
				// If there was a failure somewhere, clean up.
				wp_trash_post( $new_tabs_id );
			}

			// If no errors, remove the index.
			if ( ! count( $errors[ $index ] ) ) {
				unset( $errors[ $index ] );
			}

			// External modules manipulate data here.
			do_action( 'sp_wp_tabs_imported', $new_tabs_id );
		}

		$errors = reset( $errors );
		return isset( $errors[0] ) ? new WP_Error( 'import_tabs_error', $errors[0] ) : $shortcodes;
	}

	/**
	 * Sanitize and process metabox form data.
	 *
	 * @param  string $metabox_key Unique metabox identifier.
	 * @param  array  $request_data Data submitted via the form ($_POST or similar).
	 * @return array Sanitized metabox data.
	 */
	public function sanitize_and_collect_metabox_data( $metabox_key, $request_data ) {
		$sanitized_data = array();

		// Retrieve the list of fields with their respective sanitization callbacks.
		$metabox_fields = $this->sp_get_metabox_field_ids_with_sanitizers( $metabox_key );

		foreach ( $metabox_fields as $field ) {
			// Ensure the field has a valid ID.
			if ( empty( $field['id'] ) ) {
				continue;
			}

			$field_id    = sanitize_key( $field['id'] );
			$field_value = isset( $request_data[ $field_id ] ) ? $request_data[ $field_id ] : '';

			// If a custom sanitizer function is provided, use it.
			if ( ! empty( $field['sanitize'] ) && is_callable( $field['sanitize'] ) ) {
				$sanitized_data[ $field_id ] = call_user_func( $field['sanitize'], $field_value );
			} elseif ( is_array( $field_value ) ) {
				$sanitized_data[ $field_id ] = wp_kses_post_deep( $field_value );
			} else {
				$sanitized_data[ $field_id ] = $field_value ? wp_kses_post( $field_value ) : null;
			}
		}

		return $sanitized_data;
	}

	/**
	 * Import Tabs by ajax.
	 *
	 * @return void
	 */
	public function import_shortcode() {
		$nonce = ( ! empty( $_POST['nonce'] ) ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'wptabspro_options_nonce' ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Error: Nonce verification has failed. Please try again.', 'wp-expand-tabs-free' ) ), 401 );
		}
		$allow_tags = isset( $_POST['unSanitize'] ) ? sanitize_text_field( wp_unslash( $_POST['unSanitize'] ) ) : '';
		// Don't worry sanitize after JSON decode below.
		$data         = isset( $_POST['shortcode'] ) ? wp_unslash( $_POST['shortcode'] ) : '';//phpcs:ignore
		$data       = json_decode( $data );
		$data       = json_decode( $data, true );
		$shortcodes = $allow_tags ? $data['shortcode'] : wp_kses_post_deep( $data['shortcode'] );
		if ( ! $data ) {
			wp_send_json_error(
				array(
					'message' => __( 'Nothing to import.', 'wp-expand-tabs-free' ),
				),
				400
			);
		}

		$status = $this->import( $shortcodes );

		if ( is_wp_error( $status ) ) {
			wp_send_json_error(
				array(
					'message' => $status->get_error_message(),
				),
				400
			);
		}

		wp_send_json_success( $status, 200 );
	}
}
