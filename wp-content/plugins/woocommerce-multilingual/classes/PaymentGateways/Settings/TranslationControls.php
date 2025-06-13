<?php

namespace WCML\PaymentGateways\Settings;

use WCML\PaymentGateways\Strings;
use WCML\TranslationControls\Hooks as TranslationControlsBase;
use WCML\Utilities\WcAdminPages;
use WPML\FP\Obj;
use WPML\FP\Str;

class TranslationControls extends TranslationControlsBase {

	protected function addAdminPageHooks() {
		add_action( 'woocommerce_settings_checkout', function() {
			if ( empty( $GLOBALS['hide_save_button'] ) ) {
				add_action( 'woocommerce_after_settings_checkout', [ $this, 'translationInstructions' ] );
				add_action( 'woocommerce_after_settings_checkout', [ $this, 'translationControls' ] );
			}
		} );
		add_action( 'woocommerce_update_options_checkout', [ $this, 'registerStringsOnSave' ], 9 );
	}

	protected function isAdminPage() {
		return WcAdminPages::isPaymentSettings() && WcAdminPages::hasSection();
	}

	/**
	 * @param string $domain
	 * @param string $search
	 *
	 * @return string
	 */
	protected function getInstructionsWithRegisteredStrings( $domain, $search = '' ) {
		return sprintf(
			/* translators: %1$s and %2$s are opening and closing HTML link tags */
			esc_html__( 'To translate custom text for payment methods, go to the %1$sTranslation Dashboard%2$s.', 'woocommerce-multilingual' ),
			'<a href="' . esc_url( $this->getInstructionsLink( $domain, $search ) ) . '">',
			'</a>'
		);
	}

	public function translationInstructions() {
		$gateway = $this->getCurrentGateway();
		if ( is_null( $gateway ) ) {
			return;
		}

		$this->pointerFactory
			->create( [
				'content'    => $this->getInstructions( Strings::TRANSLATION_DOMAIN, Strings::getStringName( $gateway->id, '' ) ),
				'selectorId' => 'wpbody-content .woocommerce .form-table',
				'method'     => 'before',
			] )->show();
	}

	/**
	 * @return \WC_Payment_Gateway|null
	 */
	private function getCurrentGateway() {
		static $currentGateway = false;
		if ( false !== $currentGateway ) {
			return $currentGateway;
		}

		$gatewaysManager = \WC_Payment_Gateways::instance();
		$gateways        = $gatewaysManager->payment_gateways();

		/**
		 * Checks if a given gateway provides content for the payments settings section.
		 *
		 * @param \WC_Payment_Gateway $gateway
		 *
		 * @return bool
		 */
		$isCurrentGatewaySection = function( $gateway ) {
			return WcAdminPages::isSection( $gateway->id );
		};

		$currentGateway = wpml_collect( $gateways )->filter( $isCurrentGatewaySection )->first();
		return $currentGateway;
	}

	/**
	 * @return array
	 */
	protected function getTranslationControls() {
		$translationControls = [];
		$gateway             = $this->getCurrentGateway();
		if ( is_null( $gateway ) ) {
			return $translationControls;
		}

		$textKeys = $this->getGatewayTextKeys();
		foreach ( $textKeys as $textKey ) {
			if ( '' === $textKey ) {
				$settingValue = $gateway->description;
			} elseif ( isset( $gateway->settings[ $textKey ] ) ) {
				$settingValue = $gateway->settings[ $textKey ];
			} else {
				$settingValue = Obj::prop( $textKey, $gateway );
			}

			$gatewayKey            = $gateway->plugin_id . $gateway->id;
			$translationControls[] = $this->getTranslationControl(
				$gatewayKey,
				$textKey,
				$settingValue,
				Strings::TRANSLATION_DOMAIN,
				$this->getStringName( $gateway->id, $textKey )
			);
		}

		return $translationControls;
	}

	public function registerStringsOnSave() {
		$gateway = $this->getCurrentGateway();
		if ( is_null( $gateway ) ) {
			return;
		}

		$gatewayKey                 = $gateway->plugin_id . $gateway->id;
		$languageSelectorNamePrefix = $this->getLanguageSelectorNamePrefix( $gateway->plugin_id . $gateway->id );
		/* phpcs:ignore WordPress.VIP.SuperGlobalInputUsage.AccessDetected */
		$itemsToProcess = wpml_collect( $_POST )
			->filter( function( $value, $key ) use ( $languageSelectorNamePrefix ) {
				return Str::startsWith( $languageSelectorNamePrefix, $key );
			} )
			->toArray();

		array_walk( $itemsToProcess, function( $language, $key ) use ( $gateway, $gatewayKey, $languageSelectorNamePrefix ) {
			$textKey                = str_replace( $languageSelectorNamePrefix, '', $key );
			$name                   = $this->getStringName( $gateway->id, $textKey );
			$stringValue            = wp_kses_post( Obj::propOr(
				'',
				$this->getInputName( $gatewayKey, $textKey ),
				$_POST
			) );
			if ( empty( $stringValue ) ) {
				return;
			}
			$this->replaceStringAndLanguage( $stringValue, Strings::TRANSLATION_DOMAIN, $name, $language );
		} );
	}

	/**
	 * Gets the keys for email options that relate to translatable strings.
	 *
	 * @return array
	 */
	private function getGatewayTextKeys() {
		/**
		 * @param array Strings::TRANSLATABLE_SETTINGS
		 *
		 * @return array
		 */
		return apply_filters( 'wcml_gateway_text_keys_to_translate', Strings::TRANSLATABLE_SETTINGS ); // @phpstan-ignore-line
	}

	/**
	 * @param string $gatewayId
	 * @param string $textKey
	 *
	 * @return string
	 */
	protected function getStringName( $gatewayId, $textKey ) {
		return Strings::getStringName( $gatewayId, $textKey );
	}

	/**
	 * Gets the id attribute value of the input node holding a translatable string.
	 *
	 * @param string $gatewayKey
	 * @param string $textKey
	 *
	 * @return string
	 */
	protected function getInputId( $gatewayKey, $textKey ) {
		return $gatewayKey . '_' . $textKey;
	}

	/**
	 * @param string $gatewayKey
	 * @param string $textKey
	 *
	 * @return string
	 */
	protected function getLanguageSelectorId( $gatewayKey, $textKey ) {
		return $gatewayKey . '_settings_' . $textKey . '_' . self::LANGUAGE_SELECTOR_ID_SUFFIX;
	}

	/**
	 * @param string $gatewayKey
	 *
	 * @return string
	 */
	private function getLanguageSelectorNamePrefix( $gatewayKey ) {
		return self::KEY_PREFIX . '-' . $gatewayKey . '_settings-';
	}

	/**
	 * @param string $gatewayKey
	 * @param string $textKey
	 *
	 * @return string
	 */
	protected function getLanguageSelectorName( $gatewayKey, $textKey ) {
		return $this->getLanguageSelectorNamePrefix( $gatewayKey ) . $textKey;
	}

}
