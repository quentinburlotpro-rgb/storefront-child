<?php
/**
 * [IT-9] Commandes.
 */
defined( 'ABSPATH' ) || exit;

/* ---------------------------------------------------------------------
 * [IT-9] Statut de commande « Prête à retirer ».
 * ------------------------------------------------------------------- */
add_action( 'init', function () {
	register_post_status( 'wc-ready-pickup', array(
		'label'                     => _x( 'Prête à retirer', 'Order status', 'storefront-child' ),
		'public'                    => true,
		'show_in_admin_status_list' => true,
		'show_in_admin_all_list'    => true,
		'exclude_from_search'       => false,
		/* translators: %s: nombre */
		'label_count'               => _n_noop( 'Prête à retirer <span class="count">(%s)</span>', 'Prêtes à retirer <span class="count">(%s)</span>', 'storefront-child' ),
	) );
} );
add_filter( 'wc_order_statuses', function ( $s ) {
	$new = array();
	foreach ( $s as $k => $v ) {
		$new[ $k ] = $v;
		if ( 'wc-processing' === $k ) {
			$new['wc-ready-pickup'] = _x( 'Prête à retirer', 'Order status', 'storefront-child' );
		}
	}
	return $new;
} );
add_filter( 'bulk_actions-woocommerce_page_wc-orders', 'amorino_bulk_ready' );
add_filter( 'bulk_actions-edit-shop_order', 'amorino_bulk_ready' );
function amorino_bulk_ready( $a ) {
	$a['mark_ready-pickup'] = __( 'Changer le statut en « Prête à retirer »', 'storefront-child' );
	return $a;
}
add_filter( 'woocommerce_order_is_paid_statuses', function ( $s ) { $s[] = 'ready-pickup'; return $s; } );
add_filter( 'woocommerce_reports_order_statuses', function ( $s ) { if ( is_array( $s ) ) { $s[] = 'ready-pickup'; } return $s; } );
/* Le client est prévenu quand sa commande est prête (note client = e-mail WooCommerce). */
add_action( 'woocommerce_order_status_ready-pickup', function ( $order_id, $order ) {
	$order->add_order_note( __( 'Bonne nouvelle : votre commande est prête ! Elle vous attend au comptoir Click & Collect.', 'storefront-child' ), true );
}, 10, 2 );
