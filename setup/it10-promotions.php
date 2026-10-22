<?php
/**
 * [IT-10] Codes promo.
 * Lancement : ddev wp eval-file setup/<ce fichier> (relançable).
 */
$log  = function ( $m ) { WP_CLI::log( '  ' . $m ); };
$prod = function ( $s ) { $p = get_page_by_path( $s, OBJECT, 'product' ); return $p ? $p->ID : 0; };
$pid  = $prod;

/* [IT-10] Codes promo */
foreach ( array(
	'BIENVENUE2' => array( 'fixed_cart', 2, 'Bienvenue : 2 € offerts dès 10 € d’achat', 10 ),
	'AMORINO10'  => array( 'percent', 10, '10 % sur toute la carte', 0 ),
) as $code => list( $type, $amount, $desc, $min ) ) {
	$c = new WC_Coupon( $code );
	$c->set_code( $code );
	$c->set_discount_type( $type );
	$c->set_amount( $amount );
	$c->set_description( $desc );
	$c->set_minimum_amount( $min ?: '' );
	$c->set_date_expires( '2026-12-31' );
	$c->set_individual_use( true );
	$c->save();
}
$log( 'Codes promo : BIENVENUE2 (2 € dès 10 €), AMORINO10 (10 %)' );


/* [IT-10] Codes promo appliqués à deux produits chacun */
foreach ( array( 'BIENVENUE2' => array( 'cornet-fleur', 'pot-coppetta' ), 'AMORINO10' => array( 'gaufre', 'crepe' ) ) as $code => $slugs ) {
	$c = new WC_Coupon( $code );
	if ( $c->get_id() ) {
		$c->set_product_ids( array_filter( array_map( $pid, $slugs ) ) );
		if ( 'BIENVENUE2' === $code ) { $c->set_discount_type( 'fixed_product' ); $c->set_minimum_amount( '' ); $c->set_description( '2 € de remise sur le Cornet fleur et le Pot' ); }
		else { $c->set_description( '10 % sur la Gaufre et la Crêpe' ); }
		$c->save();
	}
}
$log( 'BIENVENUE2 : −2 € sur Cornet fleur et Pot · AMORINO10 : −10 % sur Gaufre et Crêpe' );
