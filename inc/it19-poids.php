<?php
/**
 * [IT-19] Tarifs selon le poids.
 */
defined( 'ABSPATH' ) || exit;

add_filter( 'woocommerce_general_settings', function ( $s ) {
	$s[] = array( 'title' => __( 'Livraison selon le poids', 'storefront-child' ), 'type' => 'title', 'id' => 'amorino_weight', 'desc' => __( 'Tarif de la livraison à domicile selon le poids total du panier (3 tranches).', 'storefront-child' ) );
	foreach ( array( 1 => array( 1, 3.9 ), 2 => array( 3, 5.9 ), 3 => array( '', 8.9 ) ) as $i => list( $kg, $eur ) ) {
		if ( $i < 3 ) {
			/* translators: %d: numéro de tranche */
			$s[] = array( 'title' => sprintf( __( 'Tranche %d : jusqu’à (kg)', 'storefront-child' ), $i ), 'id' => "amorino_w{$i}_max", 'type' => 'number', 'default' => (string) $kg, 'custom_attributes' => array( 'step' => '0.1', 'min' => '0' ) );
		}
		/* translators: %d: numéro de tranche */
		$s[] = array( 'title' => 3 === $i ? __( 'Tranche 3 : au-delà (€)', 'storefront-child' ) : sprintf( __( 'Tranche %d : tarif (€)', 'storefront-child' ), $i ), 'id' => "amorino_w{$i}_price", 'type' => 'number', 'default' => (string) $eur, 'custom_attributes' => array( 'step' => '0.01', 'min' => '0' ) );
	}
	$s[] = array( 'type' => 'sectionend', 'id' => 'amorino_weight' );
	return $s;
} );

function am_weight_price( $kg ) {
	$t1 = (float) am_opt( 'amorino_w1_max', 1 );
	$t2 = (float) am_opt( 'amorino_w2_max', 3 );
	if ( $kg <= $t1 ) { return (float) am_opt( 'amorino_w1_price', 3.9 ); }
	if ( $kg <= $t2 ) { return (float) am_opt( 'amorino_w2_price', 5.9 ); }
	return (float) am_opt( 'amorino_w3_price', 8.9 );
}
add_filter( 'woocommerce_package_rates', function ( $rates, $package ) {
	$kg = 0;
	foreach ( $package['contents'] as $item ) {
		$kg += (float) $item['data']->get_weight() * $item['quantity'];
	}
	foreach ( $rates as $rate ) {
		if ( 'flat_rate' === $rate->get_method_id() ) {
			$rate->set_cost( am_weight_price( wc_get_weight( $kg, 'kg' ) ) );
			/* translators: %s: poids */
			$rate->set_label( sprintf( __( 'Livraison à domicile (%s kg)', 'storefront-child' ), wc_format_decimal( $kg, 2 ) ) );
			$rate->set_taxes( array() );
		}
	}
	return $rates;
}, 20, 2 );
