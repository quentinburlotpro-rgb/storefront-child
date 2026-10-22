<?php
/**
 * [IT-7] Compte client.
 * Lancement : ddev wp eval-file setup/<ce fichier> (relançable).
 */
$log  = function ( $m ) { WP_CLI::log( '  ' . $m ); };
$prod = function ( $s ) { $p = get_page_by_path( $s, OBJECT, 'product' ); return $p ? $p->ID : 0; };
$pid  = $prod;

foreach ( array( 'woocommerce_enable_myaccount_registration' => 'yes', 'woocommerce_enable_signup_and_login_from_checkout' => 'yes', 'woocommerce_enable_checkout_login_reminder' => 'yes', 'woocommerce_enable_guest_checkout' => 'yes', 'woocommerce_registration_generate_password' => 'no', 'woocommerce_registration_generate_username' => 'yes' ) as $k => $v ) {
	update_option( $k, $v );
}
$log( 'Création de compte et connexion activées' );
