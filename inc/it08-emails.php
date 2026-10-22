<?php
/**
 * [IT-8] E-mails transactionnels.
 */
defined( 'ABSPATH' ) || exit;

/* ---------------------------------------------------------------------
 * [IT-8] E-mails : créneau + instructions de retrait sous le récapitulatif.
 * ------------------------------------------------------------------- */
add_action( 'woocommerce_email_after_order_table', function ( $order, $sent_to_admin, $plain ) {
	if ( $l = amorino_slot_label( $order ) ) {
		echo $plain ? "\nCréneau de retrait : $l\n" : '<p><strong>' . esc_html__( 'Créneau de retrait', 'storefront-child' ) . ' :</strong> ' . esc_html( $l ) . '</p>'; // phpcs:ignore
	}
	if ( ! $sent_to_admin ) {
		amorino_pickup_box( $plain );
	}
}, 10, 3 );

add_filter( 'woocommerce_email_styles', function ( $css ) {
	return $css . '
	#template_header { background:#100b0c !important; border-radius:14px 14px 0 0; }
	#template_header h1 { font-family: Georgia, serif !important; font-weight:500 !important; letter-spacing:1px; text-transform:uppercase; color:#ffffff !important; }
	.am-mail-tagline { margin:6px 0 0; color:#e9bc96; font-size:12px; letter-spacing:2px; text-transform:uppercase; }
	#template_container { border-radius:14px !important; border:1px solid #ece4df !important; }
	a { color:#100b0c; }';
} );
