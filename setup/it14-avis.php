<?php
/**
 * [IT-14] Réglages des avis.
 * Lancement : ddev wp eval-file setup/<ce fichier> (relançable).
 */
$log  = function ( $m ) { WP_CLI::log( '  ' . $m ); };
$prod = function ( $s ) { $p = get_page_by_path( $s, OBJECT, 'product' ); return $p ? $p->ID : 0; };
$pid  = $prod;

foreach ( array( 'woocommerce_enable_reviews' => 'yes', 'woocommerce_review_rating_verification_label' => 'yes', 'woocommerce_review_rating_verification_required' => 'yes', 'woocommerce_enable_review_rating' => 'yes', 'woocommerce_review_rating_required' => 'yes' ) as $k => $v ) {
	update_option( $k, $v );
}
$log( 'Avis réservés aux acheteurs' );
