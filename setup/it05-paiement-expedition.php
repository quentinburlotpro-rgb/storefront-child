<?php
/**
 * [IT-5] Paiements, zones d'expédition, pages classiques.
 * Lancement : ddev wp eval-file setup/<ce fichier> (relançable).
 */
$log  = function ( $m ) { WP_CLI::log( '  ' . $m ); };
$prod = function ( $s ) { $p = get_page_by_path( $s, OBJECT, 'product' ); return $p ? $p->ID : 0; };
$pid  = $prod;

/* [IT-5] Paiements : virement + paiement au retrait ; expédition : retrait gratuit */
update_option( 'woocommerce_bacs_settings', array_merge( (array) get_option( 'woocommerce_bacs_settings', array() ), array(
	'enabled'      => 'yes',
	'title'        => 'Virement bancaire',
	'description'  => 'Votre commande est préparée dès réception du virement. Indiquez le numéro de commande en référence.',
	'instructions' => 'Votre commande sera préparée dès réception du virement (numéro de commande en référence).',
) ) );
update_option( 'woocommerce_bacs_accounts', array( array(
	'account_name' => 'Amorino Limoges (fictif)', 'account_number' => '', 'bank_name' => 'Banque pédagogique',
	'sort_code' => '', 'iban' => 'FR76 0000 0000 0000 0000 0000 000', 'bic' => 'XXXXFRPP',
) ) );
update_option( 'woocommerce_cod_settings', array_merge( (array) get_option( 'woocommerce_cod_settings', array() ), array(
	'enabled'            => 'yes',
	'title'              => 'Paiement au retrait',
	'description'        => 'Réglez en boutique (carte ou espèces) au moment du retrait.',
	'instructions'       => 'Vous réglerez votre commande au comptoir Click & Collect.',
	'enable_for_methods' => array( 'local_pickup' ),
	'enable_for_virtual' => 'yes',
) ) );
foreach ( array( 'cheque', 'paypal' ) as $g ) {
	$s = (array) get_option( "woocommerce_{$g}_settings", array() );
	if ( $s ) { $s['enabled'] = 'no'; update_option( "woocommerce_{$g}_settings", $s ); }
}
update_option( 'woocommerce_gateway_order', array( 'cod' => 0, 'bacs' => 1 ) );
$log( 'Paiements : Paiement au retrait + Virement bancaire' );

update_option( 'woocommerce_store_address', '12 rue du Clocher' );
update_option( 'woocommerce_store_city', 'Limoges' );
update_option( 'woocommerce_store_postcode', '87000' );
update_option( 'woocommerce_default_country', 'FR' );
update_option( 'woocommerce_currency', 'EUR' );
update_option( 'woocommerce_ship_to_countries', 'specific' );
update_option( 'woocommerce_specific_ship_to_countries', array( 'FR' ) );
update_option( 'woocommerce_ship_to_destination', 'billing_only' );
update_option( 'woocommerce_enable_shipping_calc', 'no' );

$zone = null;
foreach ( WC_Shipping_Zones::get_zones() as $z ) {
	if ( 'Click & Collect' === $z['zone_name'] ) { $zone = new WC_Shipping_Zone( $z['id'] ); }
}
if ( ! $zone ) {
	$zone = new WC_Shipping_Zone();
	$zone->set_zone_name( 'Click & Collect' );
	$zone->add_location( 'FR', 'country' );
	$zone->save();
}
$has = false;
foreach ( $zone->get_shipping_methods() as $m ) {
	if ( 'local_pickup' === $m->id ) { $has = $m->instance_id; }
	elseif ( $m->is_enabled() ) { $zone->delete_shipping_method( $m->instance_id ); }
}
if ( ! $has ) { $has = $zone->add_shipping_method( 'local_pickup' ); }
update_option( "woocommerce_local_pickup_{$has}_settings", array( 'title' => 'Retrait en boutique (gratuit)', 'tax_status' => 'none', 'cost' => '0' ) );
$log( 'Expédition : zone France › Retrait en boutique (gratuit)' );

/* [IT-5/6] Panier et checkout classiques (shortcodes) pour les champs personnalisés */
foreach ( array( 'cart' => '[woocommerce_cart]', 'checkout' => '[woocommerce_checkout]', 'myaccount' => '[woocommerce_my_account]' ) as $p => $sc ) {
	$id = wc_get_page_id( $p );
	if ( $id > 0 ) { wp_update_post( array( 'ID' => $id, 'post_content' => $sc ) ); }
}
$log( 'Pages Panier / Commande / Mon compte : version classique' );
add_option( 'amorino_pickup_instructions', "Présentez votre numéro de commande au comptoir Click & Collect.\nAmorino Limoges — 12 rue du Clocher, 87000 Limoges. Commande conservée 30 min après le créneau." );


/* [IT-5] Livraison à domicile payante dans la zone (tarif au poids : IT-19) */
foreach ( WC_Shipping_Zones::get_zones() as $z ) {
	$zone = new WC_Shipping_Zone( $z['id'] );
	$has  = false;
	foreach ( $zone->get_shipping_methods() as $m ) { if ( 'flat_rate' === $m->id ) { $has = $m->instance_id; } }
	if ( ! $has ) { $has = $zone->add_shipping_method( 'flat_rate' ); }
	update_option( "woocommerce_flat_rate_{$has}_settings", array( 'title' => 'Livraison à domicile', 'tax_status' => 'none', 'cost' => '3.90' ) );
	$zone->save();
}
update_option( 'woocommerce_ship_to_destination', 'billing_only' );
$log( 'Expédition : Retrait en boutique (gratuit) + Livraison à domicile (tarif selon le poids)' );
