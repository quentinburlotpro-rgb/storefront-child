<?php
/**
 * [IT-11] Équipe.
 */
defined( 'ABSPATH' ) || exit;

/* ---------------------------------------------------------------------
 * [IT-11] Équipe : champs SCF des membres (CPT « membre » créé avec SCF).
 * ------------------------------------------------------------------- */
function amorino_member_meta( $id ) {
	$g = function ( $k ) use ( $id ) {
		return function_exists( 'get_field' ) ? (string) get_field( $k, $id ) : (string) get_post_meta( $id, $k, true );
	};
	return array( 'role' => $g( 'role' ), 'specialite' => $g( 'specialite' ), 'parfum' => $g( 'parfum_prefere' ) );
}
function amorino_member_avatar( $id ) {
	if ( has_post_thumbnail( $id ) ) {
		return get_the_post_thumbnail( $id, 'medium', array( 'class' => 'amorino-member__photo' ) );
	}
	$name = get_the_title( $id );
	$ini  = mb_strtoupper( mb_substr( $name, 0, 1 ) );
	return '<span class="amorino-member__photo amorino-member__photo--initial" aria-hidden="true">' . esc_html( $ini ) . '</span>';
}
add_action( 'pre_get_posts', function ( $q ) {
	if ( ! is_admin() && $q->is_main_query() && $q->is_post_type_archive( 'membre' ) ) {
		$q->set( 'orderby', 'menu_order title' );
		$q->set( 'order', 'ASC' );
	}
} );



add_action( 'acf/init', function () {
	if ( function_exists( 'acf_add_options_sub_page' ) ) {
		acf_add_options_sub_page( array(
			'page_title'  => __( 'Réglages de la page Équipe', 'storefront-child' ),
			'menu_title'  => __( 'Réglages', 'storefront-child' ),
			'menu_slug'   => 'amorino-equipe',
			'parent_slug' => 'edit.php?post_type=membre',
			'capability'  => 'edit_posts',
			'post_id'     => 'amorino_equipe',
			'autoload'    => true,
		) );
	}
} );

function am_team_opt( $name, $default = '' ) {
	$v = function_exists( 'get_field' ) ? get_field( $name, 'amorino_equipe' ) : null;
	return ( null === $v || '' === $v ) ? $default : $v;
}

add_action( 'pre_get_posts', function ( $q ) {
	if ( is_admin() || ! $q->is_main_query() || ! $q->is_post_type_archive( 'membre' ) ) {
		return;
	}
	if ( 'alpha' === am_team_opt( 'tri', 'manuel' ) ) {
		$q->set( 'orderby', 'title' );
		$q->set( 'order', 'ASC' );
	}
	$q->set( 'posts_per_page', -1 );
}, 20 );
