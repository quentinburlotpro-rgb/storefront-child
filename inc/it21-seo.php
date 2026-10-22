<?php
/**
 * [IT-21] Optimisation SEO.
 */
defined( 'ABSPATH' ) || exit;

/* ---------------------------------------------------------------------
 * [IT-21] SEO : balise Title, meta description, Open Graph.
 * ------------------------------------------------------------------- */
add_filter( 'document_title_separator', function () { return '·'; } );
function amorino_meta_description() {
	if ( is_singular() ) {
		$p = get_queried_object();
		$d = has_excerpt( $p ) ? get_the_excerpt( $p ) : wp_strip_all_tags( $p->post_content );
	} elseif ( is_tax() || is_category() || is_tag() ) {
		$d = term_description();
	} elseif ( function_exists( 'is_shop' ) && is_shop() ) {
		$d = __( 'La carte Amorino en Click & Collect : glaces en forme de fleur, gaufres, crêpes et boissons, à commander en ligne et à retirer en boutique.', 'storefront-child' );
	} elseif ( is_post_type_archive( 'membre' ) ) {
		$d = __( 'Rencontrez l’équipe Amorino Limoges : gelatiera, baristas et responsable de boutique.', 'storefront-child' );
	} else {
		$d = __( 'Commandez votre glace Amorino en ligne, choisissez votre créneau et récupérez-la en boutique sans attendre.', 'storefront-child' );
	}
	$d = trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( (string) $d ) ) );
	$d = (string) apply_filters( 'amorino_meta_description', $d );
	return mb_strlen( $d ) > 155 ? rtrim( mb_substr( $d, 0, 152 ) ) . '…' : $d;
}
add_action( 'wp_head', function () {
	$desc  = amorino_meta_description();
	$title = wp_get_document_title();
	$url   = is_singular() ? get_permalink() : home_url( add_query_arg( array(), $GLOBALS['wp']->request ?? '' ) );
	$img   = is_singular() && has_post_thumbnail() ? get_the_post_thumbnail_url( null, 'large' ) : get_stylesheet_directory_uri() . '/assets/logo.png';
	$type  = function_exists( 'is_product' ) && is_product() ? 'product' : 'website';
	printf( "<meta name=\"description\" content=\"%s\">\n", esc_attr( $desc ) );
	foreach ( array( 'og:type' => $type, 'og:site_name' => get_bloginfo( 'name' ), 'og:title' => $title, 'og:description' => $desc, 'og:url' => $url, 'og:image' => $img, 'og:locale' => 'fr_FR' ) as $k => $v ) {
		printf( "<meta property=\"%s\" content=\"%s\">\n", esc_attr( $k ), esc_attr( $v ) );
	}
	echo "<meta name=\"twitter:card\" content=\"summary_large_image\">\n";
}, 1 );

function am_seo_field( $name ) {
	if ( ! function_exists( 'get_field' ) ) { return ''; }
	if ( is_singular( 'product' ) ) { return (string) get_field( $name, get_queried_object_id() ); }
	if ( is_product_category() || is_tax( 'moment' ) ) { return (string) get_field( $name, get_queried_object() ); }
	return '';
}
add_filter( 'pre_get_document_title', function ( $t ) {
	$custom = am_seo_field( 'seo_title' );
	return $custom ? $custom : $t;
}, 20 );
add_filter( 'amorino_meta_description', function ( $d ) {
	$custom = am_seo_field( 'seo_description' );
	return $custom ? $custom : $d;
} );
