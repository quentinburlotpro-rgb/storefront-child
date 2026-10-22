<?php
/**
 * [IT-13] Produits associés.
 * Lancement : ddev wp eval-file setup/<ce fichier> (relançable).
 */
$log  = function ( $m ) { WP_CLI::log( '  ' . $m ); };
$prod = function ( $s ) { $p = get_page_by_path( $s, OBJECT, 'product' ); return $p ? $p->ID : 0; };
$pid  = $prod;

$links = array(
	'cornet-fleur'      => array( array( 'pot-coppetta', 'brioche-au-gelato', 'milkshake' ), array( 'cappuccino', 'chocolat-chaud', 'waffle-sticks' ) ),
	'pot-coppetta'      => array( array( 'cornet-fleur', 'milkshake', 'brioche-au-gelato' ), array( 'cappuccino', 'waffle-sticks', 'gaufre' ) ),
	'brioche-au-gelato' => array( array( 'cornet-fleur', 'pot-coppetta', 'gaufre' ), array( 'cappuccino', 'milkshake', 'chocolat-chaud' ) ),
	'gaufre'            => array( array( 'crepe', 'waffle-sticks', 'brioche-au-gelato' ), array( 'chocolat-chaud', 'cappuccino', 'pot-coppetta' ) ),
	'crepe'             => array( array( 'gaufre', 'waffle-sticks', 'brioche-au-gelato' ), array( 'chocolat-chaud', 'cappuccino', 'cornet-fleur' ) ),
	'waffle-sticks'     => array( array( 'gaufre', 'crepe', 'brioche-au-gelato' ), array( 'milkshake', 'chocolat-chaud', 'pot-coppetta' ) ),
	'chocolat-chaud'    => array( array( 'cappuccino', 'milkshake', 'crepe' ), array( 'gaufre', 'waffle-sticks', 'crepe' ) ),
	'cappuccino'        => array( array( 'chocolat-chaud', 'milkshake', 'pot-coppetta' ), array( 'gaufre', 'crepe', 'cornet-fleur' ) ),
	'milkshake'         => array( array( 'cornet-fleur', 'pot-coppetta', 'chocolat-chaud' ), array( 'waffle-sticks', 'gaufre', 'brioche-au-gelato' ) ),
);
foreach ( $links as $slug => list( $up, $cross ) ) {
	if ( ! $id = $prod( $slug ) ) { continue; }
	$p = wc_get_product( $id );
	$p->set_upsell_ids( array_filter( array_map( $prod, $up ) ) );
	$p->set_cross_sell_ids( array_filter( array_map( $prod, $cross ) ) );
	$p->save();
}
$log( 'Cornet fleur = produit star ; 3 suggestions + 3 ventes croisées par produit' );
