<?php
/**
 * [IT-10] Promotions.
 */
defined( 'ABSPATH' ) || exit;

/* ---------------------------------------------------------------------
 * [IT-10] Messages d'erreur des codes promo en clair.
 * ------------------------------------------------------------------- */
add_filter( 'woocommerce_coupon_error', function ( $msg, $code ) {
	$map = array(
		WC_Coupon::E_WC_COUPON_NOT_EXIST          => __( 'Ce code promo n’existe pas. Vérifiez l’orthographe.', 'storefront-child' ),
		WC_Coupon::E_WC_COUPON_EXPIRED            => __( 'Ce code promo a expiré.', 'storefront-child' ),
		WC_Coupon::E_WC_COUPON_MIN_SPEND_LIMIT_NOT_MET => __( 'Ce code nécessite un montant minimum de commande.', 'storefront-child' ),
		WC_Coupon::E_WC_COUPON_USAGE_LIMIT_REACHED => __( 'Ce code a déjà été utilisé le nombre maximum de fois.', 'storefront-child' ),
	);
	return $map[ $code ] ?? $msg;
}, 10, 2 );
