<?php
/**
 * IT-3 — transforme les 9 produits simples en produits variables
 * (attributs globaux WooCommerce, prix par variation, niveaux de stock).
 * Lancement : ddev wp eval-file it3-products.php
 * Relançable : les variations existantes sont recréées à l'identique.
 */

if ( ! class_exists( 'WooCommerce' ) ) {
	WP_CLI::error( 'WooCommerce inactif.' );
}

/**
 * Attribut global (Produits › Attributs) + ses termes. Retourne [id, taxonomie, slug par libellé].
 */
function am_attr( $name, $slug, $labels ) {
	$id = wc_attribute_taxonomy_id_by_name( $slug );
	if ( ! $id ) {
		$id = wc_create_attribute( array(
			'name'         => $name,
			'slug'         => $slug,
			'type'         => 'select',
			'order_by'     => 'menu_order',
			'has_archives' => false,
		) );
		if ( is_wp_error( $id ) ) {
			WP_CLI::error( $id->get_error_message() );
		}
		delete_transient( 'wc_attribute_taxonomies' );
	}
	$tax = wc_attribute_taxonomy_name( $slug );
	if ( ! taxonomy_exists( $tax ) ) {
		register_taxonomy( $tax, array( 'product' ), array( 'hierarchical' => false, 'show_ui' => false, 'query_var' => true, 'rewrite' => false ) );
	}
	$slugs = array();
	foreach ( $labels as $i => $label ) {
		$term = get_term_by( 'name', $label, $tax );
		if ( ! $term ) {
			$r = wp_insert_term( $label, $tax );
			if ( is_wp_error( $r ) ) {
				WP_CLI::error( $r->get_error_message() );
			}
			$term = get_term( $r['term_id'], $tax );
		}
		update_term_meta( $term->term_id, 'order', $i );
		$slugs[ $label ] = $term->slug;
	}
	return array( $id, $tax, $slugs );
}

/**
 * Produit simple → variable. $rows = [ [libellé, prix, stock|null], … ] (stock null = non géré, disponible).
 */
function am_make_variable( $slug, $attr_name, $attr_slug, $rows ) {
	$posts = get_posts( array( 'post_type' => 'product', 'name' => $slug, 'post_status' => 'any', 'numberposts' => 1 ) );
	if ( ! $posts ) {
		WP_CLI::warning( "$slug introuvable, ignoré" );
		return;
	}
	$pid = $posts[0]->ID;
	list( $aid, $tax, $slugs ) = am_attr( $attr_name, $attr_slug, array_column( $rows, 0 ) );

	wp_set_object_terms( $pid, 'variable', 'product_type' );
	$product = new WC_Product_Variable( $pid );
	foreach ( $product->get_children() as $child ) {
		wp_delete_post( $child, true );
	}

	$attribute = new WC_Product_Attribute();
	$attribute->set_id( $aid );
	$attribute->set_name( $tax );
	$attribute->set_options( array_values( $slugs ) );
	$attribute->set_position( 0 );
	$attribute->set_visible( true );
	$attribute->set_variation( true );

	$product->set_attributes( array( $attribute ) );
	$product->set_default_attributes( array( $tax => $slugs[ $rows[0][0] ] ) );
	$product->set_manage_stock( false );
	$product->set_regular_price( '' );
	$product->save();
	wp_set_object_terms( $pid, array_values( $slugs ), $tax );

	$summary = array();
	foreach ( $rows as $i => $row ) {
		list( $label, $price, $stock ) = $row;
		$v = new WC_Product_Variation();
		$v->set_parent_id( $pid );
		$v->set_attributes( array( $tax => $slugs[ $label ] ) );
		$v->set_regular_price( (string) $price );
		$v->set_menu_order( $i );
		if ( null === $stock ) {
			$v->set_manage_stock( false );
			$v->set_stock_status( 'instock' );
		} else {
			$v->set_manage_stock( true );
			$v->set_stock_quantity( $stock );
			$v->set_low_stock_amount( 3 );
			$summary[] = $label . ( $stock > 0 ? " (stock faible : $stock)" : ' (épuisé)' );
		}
		$v->save();
	}
	WC_Product_Variable::sync( $pid );
	wc_delete_product_transients( $pid );
	WP_CLI::log( sprintf( '  %-20s %d variations%s', get_the_title( $pid ), count( $rows ), $summary ? ' · ' . implode( ', ', $summary ) : '' ) );
	return $pid;
}


WP_CLI::log( 'Produits variables :' );
$cornet = am_make_variable( 'cornet-fleur', 'Taille', 'taille', array(
	array( 'Petit', '4.50', null ), array( 'Moyen', '5.90', null ), array( 'Grand', '7.30', 3 ),
) );
$pot = am_make_variable( 'pot-coppetta', 'Taille', 'taille', array(
	array( 'Petit', '4.50', null ), array( 'Moyen', '5.90', null ), array( 'Grand', '7.30', null ),
) );
$brioche = am_make_variable( 'brioche-au-gelato', 'Topping', 'topping', array(
	array( 'Sans topping', '6.50', null ), array( 'Coulis', '7.00', null ), array( 'Éclats', '7.00', null ),
) );
am_make_variable( 'gaufre', 'Nappage', 'nappage', array(
	array( 'Sucre glace', '4.50', null ), array( "L'Inimitable", '5.50', null ), array( 'Caramel beurre demi-sel', '5.50', null ),
) );
am_make_variable( 'crepe', 'Garniture', 'garniture', array(
	array( 'Sucre', '4.00', null ), array( "L'Inimitable", '5.00', null ), array( 'Caramel beurre demi-sel', '5.00', null ),
) );
am_make_variable( 'waffle-sticks', 'Nappage', 'nappage', array(
	array( "L'Inimitable", '5.90', null ), array( 'Caramel beurre demi-sel', '5.90', null ), array( 'Pistache', '5.90', null ),
) );
am_make_variable( 'chocolat-chaud', 'Parfum', 'parfum', array(
	array( 'Lait', '4.50', null ), array( 'Noir', '4.50', null ), array( 'Noisette', '4.50', null ),
	array( 'Gianduja', '4.50', null ), array( 'Caramel', '4.50', null ), array( 'Amande', '4.50', null ),
	array( 'Blanc', '4.50', null ), array( 'Coco', '4.50', null ), array( 'Orange', '4.50', null ),
	array( 'Piment', '4.50', 0 ),
) );
am_make_variable( 'cappuccino', 'Taille', 'taille', array(
	array( 'Petit', '3.50', null ), array( 'Moyen', '4.20', null ), array( 'Grand', '4.90', null ),
) );
am_make_variable( 'milkshake', 'Parfum', 'parfum', array(
	array( 'Pistache Mawardi', '6.50', null ), array( 'Vanille Bourbon', '6.50', null ),
	array( "L'Inimitable", '6.50', null ), array( 'Fraise', '6.50', null ), array( 'Sorbet Mangue Alphonso', '6.50', null ),
) );

/* Champ « Vos parfums » (SCF) sur les 3 glaces */
$flavours = "Pistache Mawardi\nVanille Bourbon de Madagascar\nL'Inimitable\nStracciatella\nChocolat Amorino\nCaramel beurre demi-sel\nNoisette d'Italie\nTiramisù\nSorbet Mangue Alphonso\nSorbet Framboise\nSorbet Citron de Syracuse\nSorbet Fruit de la passion";
foreach ( array_filter( array( $cornet, $pot, $brioche ) ) as $pid ) {
	update_post_meta( $pid, 'choix_parfums', 1 );
	update_post_meta( $pid, '_choix_parfums', 'field_amorino_choix_parfums' );
	update_post_meta( $pid, 'parfums_disponibles', $flavours );
	update_post_meta( $pid, '_parfums_disponibles', 'field_amorino_parfums_disponibles' );
}
update_option( 'woocommerce_notify_low_stock_amount', 3 );
update_option( 'woocommerce_stock_format', '' );
WP_CLI::success( 'Champ « Vos parfums » activé sur Cornet fleur, Pot et Brioche.' );
