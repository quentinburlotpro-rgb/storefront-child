<?php
/**
 * [IT-X] Sécurité des formulaires.
 */
defined( 'ABSPATH' ) || exit;

/* ------------------------------------------------ [IT-X] Anti-spam invisible (pot de miel + délai) */
function am_hp_field() {
	echo '<p class="am-hp" aria-hidden="true"><label>Laissez ce champ vide <input type="text" name="am_website" value="" tabindex="-1" autocomplete="off"></label><input type="hidden" name="am_ts" value="' . esc_attr( time() ) . '"></p>';
}
function am_hp_is_spam() {
	// phpcs:disable WordPress.Security.NonceVerification
	$bot  = ! empty( $_POST['am_website'] );
	$ts   = isset( $_POST['am_ts'] ) ? (int) $_POST['am_ts'] : 0;
	// phpcs:enable
	return $bot || ( $ts && time() - $ts < 3 );
}
add_action( 'woocommerce_after_checkout_billing_form', 'am_hp_field' );
add_action( 'woocommerce_register_form', 'am_hp_field' );
add_action( 'comment_form', 'am_hp_field' );
add_action( 'woocommerce_after_checkout_validation', function ( $data, $errors ) {
	if ( am_hp_is_spam() ) { $errors->add( 'spam', __( 'Envoi refusé : comportement automatisé détecté.', 'storefront-child' ) ); }
}, 10, 2 );
add_filter( 'woocommerce_process_registration_errors', function ( $e ) {
	if ( am_hp_is_spam() ) { $e->add( 'spam', __( 'Inscription refusée : comportement automatisé détecté.', 'storefront-child' ) ); }
	return $e;
} );
add_filter( 'preprocess_comment', function ( $c ) {
	if ( ! is_admin() && am_hp_is_spam() ) { wp_die( esc_html__( 'Avis refusé : comportement automatisé détecté.', 'storefront-child' ), '', array( 'response' => 403, 'back_link' => true ) ); }
	return $c;
} );
