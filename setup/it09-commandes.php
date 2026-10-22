<?php
/**
 * [IT-9] Client de test et commandes de démonstration.
 * Lancement : ddev wp eval-file setup/<ce fichier> (relançable).
 */
$log  = function ( $m ) { WP_CLI::log( '  ' . $m ); };
$prod = function ( $s ) { $p = get_page_by_path( $s, OBJECT, 'product' ); return $p ? $p->ID : 0; };
$pid  = $prod;

/* [IT-7/9] Compte client de test + 3 commandes de démonstration (retrait, virement, prête) */
$email = 'client.test@sae301.ddev.site';
$user  = get_user_by( 'email', $email );
if ( ! $user ) {
	$pass = wp_generate_password( 14, false );
	$uid  = wc_create_new_customer( $email, 'client-test', $pass, array( 'first_name' => 'Camille', 'last_name' => 'Test' ) );
	file_put_contents( ABSPATH . 'identifiants-test.txt', "Compte client de test (local)\nIdentifiant : client-test\nE-mail : $email\nMot de passe : $pass\n" );
	$user = get_user_by( 'id', $uid );
	$log( 'Compte client « client-test » créé (identifiants dans identifiants-test.txt)' );
}
if ( ! wc_get_orders( array( 'meta_key' => '_amorino_demo', 'limit' => 1, 'return' => 'ids' ) ) ) {
	$var = function ( $slug, $attr, $val ) use ( $prod ) {
		$p = wc_get_product( $prod( $slug ) );
		foreach ( $p ? $p->get_children() : array() as $c ) {
			$v = wc_get_product( $c );
			if ( $v->get_attribute( $attr ) === $val ) { return $v; }
		}
		return $p;
	};
	$scenarios = array(
		array( 'cod', 'Paiement au retrait', 'processing', array( array( $var( 'cornet-fleur', 'pa_taille', 'Moyen' ), 1, 'Pistache Mawardi, Mangue Alphonso' ), array( $var( 'cappuccino', 'pa_taille', 'Petit' ), 1, '' ) ), '+1 day 15:00' ),
		array( 'bacs', 'Virement bancaire', 'on-hold', array( array( $var( 'gaufre', 'pa_nappage', 'Sucre glace' ), 2, '' ) ), '+2 day 16:30' ),
		array( 'cod', 'Paiement au retrait', 'ready-pickup', array( array( $var( 'milkshake', 'pa_parfum', 'Fraise' ), 1, '' ) ), '+0 day 21:00' ),
	);
	foreach ( $scenarios as list( $pm, $pt, $status, $lines, $when ) ) {
		$o = wc_create_order( array( 'customer_id' => $user->ID ) );
		foreach ( $lines as list( $product, $qty, $parfums ) ) {
			$item_id = $o->add_product( $product, $qty );
			if ( $parfums ) { wc_add_order_item_meta( $item_id, 'Parfums', $parfums ); }
		}
		$o->set_address( array( 'first_name' => 'Camille', 'last_name' => 'Test', 'email' => $email, 'phone' => '0600000000', 'country' => 'FR' ), 'billing' );
		$ship = new WC_Order_Item_Shipping();
		$ship->set_method_title( 'Retrait en boutique (gratuit)' );
		$ship->set_method_id( 'local_pickup' );
		$ship->set_total( 0 );
		$o->add_item( $ship );
		$o->set_payment_method( $pm );
		$o->set_payment_method_title( $pt );
		$o->update_meta_data( '_amorino_slot', wp_date( 'Y-m-d H:i', strtotime( $when, current_time( 'timestamp' ) ) ) );
		$o->update_meta_data( '_amorino_demo', 1 );
		$o->calculate_totals();
		$o->update_status( $status, 'Commande de démonstration (scénario SAé).' );
	}
	$log( '3 commandes de démo : retrait payé au comptoir, virement en attente, prête à retirer' );
}
