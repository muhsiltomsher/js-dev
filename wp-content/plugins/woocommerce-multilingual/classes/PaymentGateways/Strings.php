<?php

namespace WCML\PaymentGateways;

use WCML\TranslationControls as TranslationControlsBase;
use WCML\Utilities\WcAdminPages;
use WPML\FP\Obj;
use WPML\FP\Str;

class Strings {

	const TRANSLATION_DOMAIN = 'admin_texts_woocommerce_gateways';

	const TRANSLATABLE_SETTINGS = [
		'title',
		'description',
		'instructions',
	];

	/**
	 * @param string $gatewayId
	 * @param string $stringName
	 *
	 * @return string
	 */
	public static function getStringName( $gatewayId, $stringName ) {
		return $gatewayId . '_gateway_' . $stringName;
	}
}
