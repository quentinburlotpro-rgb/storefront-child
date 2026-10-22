<?php
/**
 * [IT-19] Poids des produits et tranches.
 * Lancement : ddev wp eval-file setup/<ce fichier> (relançable).
 */
$log  = function ( $m ) { WP_CLI::log( '  ' . $m ); };
$prod = function ( $s ) { $p = get_page_by_path( $s, OBJECT, 'product' ); return $p ? $p->ID : 0; };
$pid  = $prod;

/* [IT-19] Poids des produits (kg) */
foreach ( array( 'cornet-fleur' => .25, 'pot-coppetta' => .3, 'brioche-au-gelato' => .35, 'gaufre' => .25, 'crepe' => .2, 'waffle-sticks' => .3, 'chocolat-chaud' => .35, 'cappuccino' => .3, 'milkshake' => .5 ) as $s => $kg ) {
	if ( $id = $pid( $s ) ) { $p = wc_get_product( $id ); $p->set_weight( $kg ); $p->save(); }
}
foreach ( array( 'amorino_fee_threshold' => 8, 'amorino_fee_amount' => 0.5, 'amorino_w1_max' => 1, 'amorino_w1_price' => 3.9, 'amorino_w2_max' => 3, 'amorino_w2_price' => 5.9, 'amorino_w3_price' => 8.9 ) as $k => $v ) { add_option( $k, $v ); }
$log( 'Poids renseignés ; tranches 0–1 kg 3,90 € · 1–3 kg 5,90 € · +3 kg 8,90 € ; frais 0,50 € sous 8 €' );
