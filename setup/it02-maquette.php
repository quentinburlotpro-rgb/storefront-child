<?php
/* Fidélité maquette : menu, accroches, badges, barre latérale à gauche. (wp eval-file, relançable) */
$loc = get_nav_menu_locations();
if ( ! empty( $loc['primary'] ) ) {
	foreach ( wp_get_nav_menu_items( $loc['primary'] ) ?: array() as $it ) { wp_delete_post( $it->ID, true ); }
	$shop = wc_get_page_id( 'shop' );
	wp_update_nav_menu_item( $loc['primary'], 0, array( 'menu-item-title' => 'Commander', 'menu-item-object' => 'page', 'menu-item-object-id' => $shop, 'menu-item-type' => 'post_type', 'menu-item-status' => 'publish' ) );
	if ( $p = get_page_by_path( 'nos-envies' ) ) {
		wp_update_nav_menu_item( $loc['primary'], 0, array( 'menu-item-title' => 'Nos envies', 'menu-item-object' => 'page', 'menu-item-object-id' => $p->ID, 'menu-item-type' => 'post_type', 'menu-item-status' => 'publish' ) );
	}
	wp_update_nav_menu_item( $loc['primary'], 0, array( 'menu-item-title' => 'L’équipe', 'menu-item-url' => home_url( '/equipe/' ), 'menu-item-type' => 'custom', 'menu-item-status' => 'publish' ) );
	WP_CLI::log( '  Menu : Commander, Nos envies, L’équipe' );
}
$acc = array(
	'cornet-fleur' => 'parfums au choix', 'pot-coppetta' => 'parfums au choix', 'brioche-au-gelato' => '2 parfums + topping',
	'gaufre' => 'nappage au choix', 'crepe' => 'garniture au choix', 'waffle-sticks' => 'à tremper',
	'chocolat-chaud' => '10 parfums', 'cappuccino' => '3 tailles', 'milkshake' => 'gelato ou sorbet',
);
foreach ( $acc as $slug => $line ) {
	$p = get_page_by_path( $slug, OBJECT, 'product' );
	if ( ! $p ) { continue; }
	update_post_meta( $p->ID, 'amorino_accroche', $line );
	if ( 'brioche-au-gelato' === $slug ) { wp_set_object_terms( $p->ID, 'nouveau', 'product_tag', true ); }
	if ( 'milkshake' === $slug ) { wp_set_object_terms( $p->ID, 'vegan', 'product_tag', true ); }
	wc_delete_product_transients( $p->ID );
}
foreach ( array( 'nouveau' => 'Nouveau', 'vegan' => 'Végan' ) as $s => $n ) {
	if ( $t = get_term_by( 'slug', $s, 'product_tag' ) ) { wp_update_term( $t->term_id, 'product_tag', array( 'name' => $n ) ); }
}
$i = 0;
foreach ( array_keys( $acc ) as $slug ) {
	if ( $p = get_page_by_path( $slug, OBJECT, 'product' ) ) { wp_update_post( array( 'ID' => $p->ID, 'menu_order' => $i++ ) ); }
}
update_option( 'woocommerce_default_catalog_orderby', 'menu_order' );
foreach ( array( 'shop' => 'La carte', 'cart' => 'Panier', 'checkout' => 'Commander', 'myaccount' => 'Mon compte' ) as $k => $t ) {
	if ( ( $id = wc_get_page_id( $k ) ) > 0 ) { wp_update_post( array( 'ID' => $id, 'post_title' => $t ) ); }
}
update_option( 'amorino_pickup_instructions', "Présentez votre code de retrait (AM-XXXX) au comptoir Click & Collect.\nAmorino Limoges — 12 rue du Clocher, 87000 Limoges. Commande conservée 30 min après la fin du créneau." );
set_theme_mod( 'storefront_layout', 'left' );
WP_CLI::success( 'Accroches, badges Nouveau / Végan, barre latérale à gauche.' );
