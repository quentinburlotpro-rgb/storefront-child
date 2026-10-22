<?php
/**
 * [IT-14] Avis clients.
 */
defined( 'ABSPATH' ) || exit;

/* ---------------------------------------------------------------------
 * [IT-14] Avis réservés aux acheteurs (options) — libellé du badge.
 * ------------------------------------------------------------------- */
add_filter( 'gettext', function ( $t, $text, $domain ) {
	if ( 'woocommerce' === $domain && 'Only logged in customers who have purchased this product may leave a review.' === $text ) {
		return __( 'Seuls les clients ayant acheté ce produit peuvent laisser un avis.', 'storefront-child' );
	}
	return $t;
}, 10, 3 );
