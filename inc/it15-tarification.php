<?php
/**
 * [IT-15] Règles de tarification.
 */
defined( 'ABSPATH' ) || exit;

/* ---------------------------------------------------------------------
 * [IT-15] Frais de préparation sous un seuil de commande.
 * ------------------------------------------------------------------- */
function am_fee_threshold() { return (float) apply_filters( 'amorino_fee_threshold', 8.0 ); }
function am_fee_amount() { return (float) apply_filters( 'amorino_fee_amount', 0.5 ); }
add_action( 'woocommerce_cart_calculate_fees', function ( $cart ) {
	if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
		return;
	}
	if ( $cart->get_subtotal() > 0 && $cart->get_subtotal() < am_fee_threshold() ) {
		$cart->add_fee( __( 'Frais de préparation', 'storefront-child' ), am_fee_amount() );
	}
} );
add_action( 'woocommerce_before_cart_table', 'amorino_fee_notice' );
add_action( 'woocommerce_before_checkout_form', 'amorino_fee_notice', 5 );
function amorino_fee_notice() {
	$sub = WC()->cart ? WC()->cart->get_subtotal() : 0;
	if ( $sub > 0 && $sub < am_fee_threshold() ) {
		/* translators: 1: montant manquant, 2: frais */
		wc_print_notice( sprintf( __( 'Plus que %1$s pour ne pas payer les frais de préparation (%2$s).', 'storefront-child' ), wc_price( am_fee_threshold() - $sub ), wc_price( am_fee_amount() ) ), 'notice' );
	}
}

function am_opt( $k, $d ) {
	$v = get_option( $k, '' );
	return '' === $v ? $d : $v;
}
add_filter( 'woocommerce_general_settings', function ( $s ) {
	$s[] = array( 'title' => __( 'Frais de préparation', 'storefront-child' ), 'type' => 'title', 'id' => 'amorino_fees' );
	$s[] = array( 'title' => __( 'Seuil des frais de préparation (€)', 'storefront-child' ), 'id' => 'amorino_fee_threshold', 'type' => 'number', 'default' => '8', 'custom_attributes' => array( 'step' => '0.01', 'min' => '0' ) );
	$s[] = array( 'title' => __( 'Montant des frais de préparation (€)', 'storefront-child' ), 'id' => 'amorino_fee_amount', 'type' => 'number', 'default' => '0.5', 'custom_attributes' => array( 'step' => '0.01', 'min' => '0' ) );
	$s[] = array( 'type' => 'sectionend', 'id' => 'amorino_fees' );
	return $s;
} );
add_filter( 'amorino_fee_threshold', function () { return (float) am_opt( 'amorino_fee_threshold', 8 ); } );
add_filter( 'amorino_fee_amount', function () { return (float) am_opt( 'amorino_fee_amount', 0.5 ); } );
