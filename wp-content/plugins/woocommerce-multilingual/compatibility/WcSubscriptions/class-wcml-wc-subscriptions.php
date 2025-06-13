<?php

class WCML_WC_Subscriptions implements \IWPML_Action {

	const TRANSLATION_DOMAIN = 'woocommerce_subscriptions';

	/** @var woocommerce_wpml */
	private $woocommerce_wpml;

	/** @var wpdb */
	private $wpdb;

	public function __construct( woocommerce_wpml $woocommerce_wpml, wpdb $wpdb ) {
		$this->woocommerce_wpml = $woocommerce_wpml;
		$this->wpdb             = $wpdb;
	}

	public function add_hooks() {
		add_action( 'init', [ $this, 'init' ], 9 );
		add_filter( 'wcml_variation_term_taxonomy_ids', [ $this, 'wcml_variation_term_taxonomy_ids' ] );
		add_filter( 'woocommerce_subscription_lengths', [ $this, 'woocommerce_subscription_lengths' ], 10, 2 );

		add_filter( 'wcml_register_endpoints_query_vars', [ $this, 'register_endpoint' ], 10, 3 );
		add_filter( 'wcml_endpoint_permalink_filter', [ $this, 'endpoint_permalink_filter' ], 10, 2 );

		add_action( 'woocommerce_subscriptions_product_options_pricing', [ $this, 'show_pointer_info' ] );
		add_action( 'woocommerce_variable_subscription_pricing', [ $this, 'show_pointer_info' ] );

		add_filter( 'wcml_xliff_allowed_variations_types', [ $this, 'set_allowed_variations_types_in_xliff' ] );

		// Add language links to email settings.
		add_filter( 'wcml_emails_options_to_translate', [ $this, 'translate_email_options' ] );
		add_filter( 'wcml_emails_section_name_prefix', [ $this, 'email_option_section_prefix' ], 10, 2 );
	}

	public function init() {
		if ( ! is_admin() ) {
			add_filter( 'wcml_should_translate_order_items', [ $this, 'translateSubscriptionProductItems' ], 10, 3 );
		}

		// Translate emails.
		add_filter( 'woocommerce_generated_manual_renewal_order_renewal_notification', [ $this, 'translate_renewal_notification' ], 9 );
		add_filter( 'woocommerce_order_status_failed_renewal_notification', [ $this, 'translate_renewal_notification' ], 9 );
	}

	public function wcml_variation_term_taxonomy_ids( $get_variation_term_taxonomy_ids ) {

		// phpcs:ignore WordPress.WP.PreparedSQL.NotPrepared
		$get_variation_term_taxonomy_id = $this->wpdb->get_var( "SELECT tt.term_taxonomy_id FROM {$this->wpdb->terms} AS t LEFT JOIN {$this->wpdb->term_taxonomy} AS tt ON t.term_id = tt.term_id WHERE t.slug = 'variable-subscription'" );

		if ( ! empty( $get_variation_term_taxonomy_id ) ) {
			$get_variation_term_taxonomy_ids[] = $get_variation_term_taxonomy_id;
		}

		return $get_variation_term_taxonomy_ids;
	}

	public function woocommerce_subscription_lengths( $subscription_ranges, $subscription_period ) {

		if ( is_array( $subscription_ranges ) ) {
			foreach ( $subscription_ranges as $period => $ranges ) {
				if ( is_array( $ranges ) ) {
					foreach ( $ranges as $range ) {
						if ( '9 months' === $range ) {
							$breakpoint = true;
						}
						$new_subscription_ranges[ $period ][] = apply_filters( 'wpml_translate_single_string', $range, 'wc_subscription_ranges', $range );
					}
				}
			}
		}

		return isset( $new_subscription_ranges ) ? $new_subscription_ranges : $subscription_ranges;
	}

	/**
	 * @param array           $query_vars
	 * @param array           $wc_vars
	 * @param \WCML_Endpoints $wcmlEndpoints
	 *
	 * @return array
	 */
	public function register_endpoint( $query_vars, $wc_vars, $wcmlEndpoints ) {
		$query_vars['view-subscription'] = $wcmlEndpoints->get_endpoint_translation( 'view-subscription' );
		$query_vars['subscriptions']     = $wcmlEndpoints->get_endpoint_translation( 'subscriptions' );

		return $query_vars;
	}

	public function endpoint_permalink_filter( $endpoint, $key ) {

		if ( 'view-subscription' === $key ) {
			return 'view-subscription';
		}

		return $endpoint;
	}

	public function show_pointer_info() {
		$pointerFactory = new WCML\PointerUi\Factory();
		$pointerFactory
			->create( [
				'content'    => sprintf(
					/* translators: %1$s and %2$s are opening and closing HTML link tags */
					esc_html__( 'You can translate text for subscription products from the %1$sTranslation Dashboard%2$s.', 'woocommerce-multilingual' ),
					'<a href="' . esc_url( \WCML\Utilities\AdminUrl::getWPMLTMDashboardProducts() ) . '">',
					'</a>'
				),
				'selectorId' => 'general_product_data .subscription_pricing',
				'method'     => 'prepend',
				'docLink'    => WCML_Tracking_Link::getWcmlSubscriptionsDoc(),
			] )
			->show();
	}

	/**
	 * @param array $allowed_types
	 *
	 * @return array
	 */
	public function set_allowed_variations_types_in_xliff( $allowed_types ) {

		$allowed_types[] = 'variable-subscription';
		$allowed_types[] = 'subscription_variation';

		return $allowed_types;
	}

	/**
	 * Translate strings of renewal notifications
	 *
	 * @param integer $order_id Order ID.
	 */
	public function translate_renewal_notification( $order_id ) {

		if ( isset( WC()->mailer()->emails['WCS_Email_Customer_Renewal_Invoice'] ) ) {
			$this->woocommerce_wpml->emails->refresh_email_lang( $order_id );

			$WCS_Email_Customer_Renewal_Invoice = WC()->mailer()->emails['WCS_Email_Customer_Renewal_Invoice'];
			// phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText
			$WCS_Email_Customer_Renewal_Invoice->heading = __( $WCS_Email_Customer_Renewal_Invoice->heading, 'woocommerce-subscriptions' );
			// phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText
			$WCS_Email_Customer_Renewal_Invoice->subject = __( $WCS_Email_Customer_Renewal_Invoice->subject, 'woocommerce-subscriptions' );

			add_filter( 'woocommerce_email_get_option', [ $this, 'translate_heading_subject' ], 10, 4 );
		}
	}

	/**
	 * Translate custom heading and subject for renewal notification
	 *
	 * @param string                             $return_value original string.
	 * @param WCS_Email_Customer_Renewal_Invoice $obj Object of email class.
	 * @param string                             $value Original value from setting.
	 * @param string                             $key Name of the key.
	 * @return string Translated value or original value incase of not translated
	 */
	public function translate_heading_subject( $return_value, $obj, $value, $key ) {

		if ( $obj instanceof WCS_Email_Customer_Renewal_Invoice ) {
			if ( 'subject' === $key || 'heading' === $key ) {
				$translated_admin_string = $this->woocommerce_wpml->emails->getStringTranslation( 'admin_texts_woocommerce_customer_renewal_invoice_settings', '[woocommerce_customer_renewal_invoice_settings]' . $key );
				return empty( $translated_admin_string ) ? $return_value : $translated_admin_string;
			}
		}

		return $return_value;
	}

	/**
	 * Add customer renewal invoice option to translate
	 *
	 * @param array $emails_options list of option to translate.
	 * @return array $emails_options
	 */
	public function translate_email_options( $emails_options ) {

		if ( is_array( $emails_options ) ) {
			$emails_options[] = 'woocommerce_customer_renewal_invoice_settings';
		}

		return $emails_options;
	}

	/**
	 * Change section name prefix to add language links
	 *
	 * @param string $section_prefix section prefix.
	 * @param string $emails_option current option name.
	 * @return string $section_prefix
	 */
	public function email_option_section_prefix( $section_prefix, $emails_option ) {

		if ( 'woocommerce_customer_renewal_invoice_settings' === $emails_option ) {
			return 'wcs_email_';
		}

		return $section_prefix;
	}

	/**
	 * We should translate all frontend subscription order items,
	 * so we compare the current subscription product to the purchased one,
	 * so we can decide whether a subscription was already purchased.
	 *
	 * @param bool             $translateOrderItems True if we should to translate order items.
	 * @param \WC_Order_Item[] $items               Order items.
	 * @param \WC_Order        $order               WC Order.
	 *
	 * @return bool
	 */
	public function translateSubscriptionProductItems( $translateOrderItems, $items, $order ) {
		if ( $order instanceof WC_Subscription ) {
			return true;
		}
		return $translateOrderItems;
	}

}
