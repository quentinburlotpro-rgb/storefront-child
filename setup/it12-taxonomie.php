<?php
/**
 * [IT-12] Taxonomie « moment », termes et page « Nos envies ».
 * Lancement : ddev wp eval-file setup/<ce fichier> (relançable).
 */
$log  = function ( $m ) { WP_CLI::log( '  ' . $m ); };
$prod = function ( $s ) { $p = get_page_by_path( $s, OBJECT, 'product' ); return $p ? $p->ID : 0; };
$pid  = $prod;

$yes = 'true'; $no = 'false';
if ( ! taxonomy_exists( 'moment' ) ) { register_taxonomy( 'moment', 'product', array( 'hierarchical' => true, 'public' => true ) ); }
$tx = array();
$tx['moment'] = array(
	'name' => 'moment', 'label' => 'Moments', 'singular_label' => 'Moment', 'description' => 'Moment de dégustation',
	'public' => $yes, 'publicly_queryable' => $yes, 'hierarchical' => $yes, 'show_ui' => $yes, 'show_in_menu' => $yes,
	'show_in_nav_menus' => $yes, 'query_var' => $yes, 'query_var_slug' => '', 'rewrite' => $yes, 'rewrite_slug' => 'moment',
	'rewrite_withfront' => '0', 'rewrite_hierarchical' => '0', 'show_admin_column' => $yes, 'show_in_rest' => $yes,
	'show_tagcloud' => $no, 'sort' => $no, 'show_in_quick_edit' => $yes, 'rest_base' => '', 'rest_controller_class' => '',
	'rest_namespace' => '', 'labels' => array(), 'meta_box_cb' => '', 'default_term' => '', 'object_types' => array( 'product' ),
);
/* Enregistrement dans SCF (Contenus › Types de publication / Taxonomies), sans CPT UI */
$found = array_filter( acf_get_acf_taxonomies(), function ( $o ) { return 'moment' === $o['taxonomy']; } );
if ( ! $found ) {
	$r = acf_get_internal_post_type_instance( 'acf-taxonomy' )->import_cptui_taxonomy( $tx['moment'] );
	$log( 'SCF : « moment » créé' . ( isset( $r['ID'] ) ? ' (#' . $r['ID'] . ')' : '' ) );
} else {
	$log( 'SCF : « moment » déjà présent' );
}
$old = get_option( 'cptui_taxonomies', array() );
if ( is_array( $old ) && isset( $old['moment'] ) ) { unset( $old['moment'] ); $old ? update_option( 'cptui_taxonomies', $old ) : delete_option( 'cptui_taxonomies' ); }
flush_rewrite_rules();
/* [IT-12] 3 moments + rattachement des produits */
$moments = array(
	'pause-gourmande' => array( 'Pause gourmande', 'Une douceur chaude ou croustillante pour le goûter.', array( 'gaufre', 'crepe', 'chocolat-chaud', 'brioche-au-gelato' ) ),
	'coup-de-frais'   => array( 'Coup de frais', 'Gelato et boissons glacées quand il fait chaud.', array( 'cornet-fleur', 'pot-coppetta', 'milkshake' ) ),
	'a-partager'      => array( 'À partager', 'À picorer à deux… ou plus.', array( 'waffle-sticks', 'gaufre', 'pot-coppetta' ) ),
);
if ( taxonomy_exists( 'moment' ) ) {
	foreach ( $moments as $slug => list( $name, $desc, $products ) ) {
		$t = term_exists( $slug, 'moment' ) ?: wp_insert_term( $name, 'moment', array( 'slug' => $slug, 'description' => $desc ) );
		foreach ( $products as $s ) { if ( $id = $prod( $s ) ) { wp_set_object_terms( $id, (int) $t['term_id'], 'moment', true ); } }
	}
	$log( 'Taxonomie « moment » : 3 termes, produits rattachés' );
} else {
	WP_CLI::warning( 'Taxonomie « moment » non enregistrée : SCF est-il actif ?' );
}
