<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$front           = \FKCart\Includes\Front::get_instance();
$settings        = \FKCart\Includes\Data::get_settings();
$hide_cart_empty = ( \FKCart\Includes\Data::hide_empty_cart() && ( is_null( WC()->cart ) || WC()->cart->is_empty() ) );
$icon            = $settings['floating_icon'];
$cart_item_count = $front->get_cart_content_count();
if ( isset( $floating_icon ) ) {
	$icon = $floating_icon;
}
?>
<div id="fkcart-floating-toggler" class="fkcart-toggler" data-position="<?php esc_attr_e( $settings['cart_icon_position'] ); ?>" style="<?php esc_attr_e( $hide_cart_empty ? 'display:none' : '' ); ?>">
    <div class="fkcart-floating-icon">
		<?php fkcart_get_template_part( 'icon/cart/' . $icon, '', [], true ) ?>
    </div>
    <div class="fkcart-item-count" id="fkit-floating-count" data-item-count="<?php echo floatval( $cart_item_count ); ?>"><?php echo wp_kses_post( $cart_item_count ); ?></div>
</div>
