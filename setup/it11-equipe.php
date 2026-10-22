<?php
/**
 * [IT-11] Type « membre » (CPT UI) et 3 membres.
 * Lancement : ddev wp eval-file setup/<ce fichier> (relançable).
 */
$log  = function ( $m ) { WP_CLI::log( '  ' . $m ); };
$prod = function ( $s ) { $p = get_page_by_path( $s, OBJECT, 'product' ); return $p ? $p->ID : 0; };
$pid  = $prod;

$yes = 'true'; $no = 'false';
if ( ! post_type_exists( 'membre' ) ) { register_post_type( 'membre', array( 'public' => true ) ); }
$pt  = get_option( 'cptui_post_types', array() );
$pt['membre'] = array(
	'name' => 'membre', 'label' => 'Équipe', 'singular_label' => 'Membre', 'description' => 'Membres de l’équipe Amorino Limoges',
	'public' => $yes, 'publicly_queryable' => $yes, 'show_ui' => $yes, 'show_in_nav_menus' => $yes, 'delete_with_user' => $no,
	'show_in_rest' => $yes, 'rest_base' => '', 'rest_controller_class' => '', 'rest_namespace' => '',
	'has_archive' => $yes, 'has_archive_string' => 'equipe', 'exclude_from_search' => $no, 'capability_type' => 'post',
	'hierarchical' => $no, 'can_export' => $yes, 'rewrite' => $yes, 'rewrite_slug' => 'equipe', 'rewrite_withfront' => $no,
	'query_var' => $yes, 'query_var_slug' => '', 'menu_position' => '25', 'show_in_menu' => $yes, 'show_in_menu_string' => '',
	'menu_icon' => 'dashicons-groups', 'register_meta_box_cb' => null, 'supports' => array( 'title', 'editor', 'thumbnail', 'page-attributes' ),
	'taxonomies' => array(), 'labels' => array( 'add_new_item' => 'Ajouter un membre', 'all_items' => 'Tous les membres' ),
	'custom_supports' => '', 'enter_title_here' => 'Prénom du membre',
);
update_option( 'cptui_post_types', $pt );

flush_rewrite_rules();
/* [IT-11] 3 membres de l'équipe */
$team = array(
	array( 'Giulia', 'Gelatiera', 'Sculpture des fleurs de gelato', 'Pistache Mawardi', 'Formée à Turin, Giulia prépare chaque matin les gelati de la boutique et sculpte les pétales à la spatule.' ),
	array( 'Marco', 'Responsable de boutique', 'Organisation du Click & Collect', 'L’Inimitable', 'Marco veille à ce que chaque commande soit prête à l’heure du créneau choisi.' ),
	array( 'Léa', 'Barista', 'Chocolats chauds et cappuccinos', 'Mangue Alphonso', 'Léa prépare les boissons chaudes et glacées, et conseille les accords gelato-café.' ),
);
foreach ( $team as $i => list( $name, $role, $spe, $parfum, $bio ) ) {
	$ex = get_posts( array( 'post_type' => 'membre', 'title' => $name, 'post_status' => 'any', 'numberposts' => 1 ) );
	$id = $ex ? $ex[0]->ID : wp_insert_post( array( 'post_type' => 'membre', 'post_status' => 'publish', 'post_title' => $name, 'post_content' => $bio, 'menu_order' => $i ) );
	foreach ( array( 'field_am_m_role' => $role, 'field_am_m_spe' => $spe, 'field_am_m_parfum' => $parfum ) as $k => $v ) {
		function_exists( 'update_field' ) ? update_field( $k, $v, $id ) : null;
	}
}
$log( 'Équipe : Giulia, Marco, Léa' );
