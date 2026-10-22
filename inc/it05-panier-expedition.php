<?php
/**
 * [IT-5] Panier et expédition.
 */
defined( 'ABSPATH' ) || exit;

/* ------------------------------------------------ Panier */
add_action( 'woocommerce_proceed_to_checkout', function () {
	echo '<p class="am-cart-note">' . am_icon( 'clock', 16 ) . ' ' . esc_html__( 'Retrait gratuit en boutique : vous choisissez le créneau à l’étape suivante.', 'storefront-child' ) . '</p>'; // phpcs:ignore
}, 30 );
add_filter( 'body_class', function ( $c ) {
	if ( is_cart() || is_checkout() ) {
		$c[] = 'am-commerce';
	}
	return $c;
} );


/* ------------------------------------------------ [IT-5 / IT-19 / IT-20] Livraison à domicile */
function am_is_delivery_chosen() {
	$m = WC()->session ? (array) WC()->session->get( 'chosen_shipping_methods' ) : array();
	return $m && 0 === strpos( (string) reset( $m ), 'flat_rate' );
}
function am_order_is_delivery( $order ) {
	foreach ( $order->get_shipping_methods() as $m ) {
		if ( 'flat_rate' === $m->get_method_id() ) { return true; }
	}
	return false;
}
add_action( 'woocommerce_after_checkout_validation', function ( $data, $errors ) {
	if ( am_is_delivery_chosen() ) {
		foreach ( array( 'billing_address_1' => 'l’adresse', 'billing_postcode' => 'le code postal', 'billing_city' => 'la ville' ) as $k => $l ) {
			if ( empty( $data[ $k ] ) ) {
				/* translators: %s: champ */
				$errors->add( $k, sprintf( __( 'Livraison à domicile : indiquez %s.', 'storefront-child' ), $l ) );
			}
		}
	}
}, 10, 2 );
add_action( 'woocommerce_checkout_create_order', function ( $order ) {
	if ( am_is_delivery_chosen() ) {
		$order->set_shipping_address_1( $order->get_billing_address_1() );
		$order->set_shipping_postcode( $order->get_billing_postcode() );
		$order->set_shipping_city( $order->get_billing_city() );
		$order->set_shipping_country( 'FR' );
		$order->set_shipping_first_name( $order->get_billing_first_name() );
		$order->set_shipping_last_name( $order->get_billing_last_name() );
	}
}, 20 );
add_action( 'wp_footer', function () {
	if ( ! is_checkout() || is_order_received_page() ) { return; }
	?>
	<script>
	jQuery(function ($) {
		function sync() {
			var m = $('input[name^="shipping_method"]:checked').val() || $('input[name^="shipping_method"]').val() || '';
			var delivery = m.indexOf('flat_rate') === 0;
			$('.am-delivery-only').toggle(delivery);
			$('.amorino-slot h3').text(delivery ? 'Créneau de livraison' : 'Créneau de retrait');
		}
		$(document.body).on('updated_checkout change', 'input[name^="shipping_method"]', sync).on('updated_checkout', sync);
		sync();
	});
	</script>
	<?php
}, 30 );
