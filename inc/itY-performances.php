<?php
/**
 * [IT-Y] Performances (Lighthouse) : moins de requêtes bloquantes, JS en pied de page,
 * polices non bloquantes, zoom produit désactivé.
 */
defined( 'ABSPATH' ) || exit;

/* Une seule feuille de styles du thème enfant, intégrée dans la page (12 requêtes en moins). */
add_action( 'wp_enqueue_scripts', function () {
	if ( is_admin() ) {
		return;
	}
	$css = '';
	foreach ( glob( get_stylesheet_directory() . '/assets/css/*.css' ) ?: array() as $f ) {
		wp_dequeue_style( 'amorino-' . basename( $f, '.css' ) );
		$css .= file_get_contents( $f ) . "\n"; // phpcs:ignore WordPress.WP.AlternativeFunctions
	}
	$css = preg_replace( array( '#/\*.*?\*/#s', '/\s+/', '/\s*([{};,>])\s*/' ), array( '', ' ', '$1' ), $css );
	$css = str_replace( 'url(../', 'url(' . get_stylesheet_directory_uri() . '/assets/', $css );
	wp_dequeue_style( 'storefront-child-style' );
	wp_dequeue_style( 'storefront-woocommerce-brands-style' );
	$deps = wp_style_is( 'storefront-woocommerce-style', 'registered' ) ? array( 'storefront-woocommerce-style' ) : array( 'storefront-style' );
	wp_register_style( 'amorino', false, $deps, null );
	wp_enqueue_style( 'amorino' );
	wp_add_inline_style( 'amorino', $css );
}, 40 );

/* Polices Google et icônes Storefront chargées sans bloquer l'affichage. */
add_filter( 'style_loader_tag', function ( $tag, $handle ) {
	if ( in_array( $handle, array( 'amorino-fonts', 'storefront-icons' ), true ) ) {
		$tag = str_replace( "media='all'", "media='print' onload=\"this.media='all'\"", $tag );
	}
	return $tag;
}, 10, 2 );
add_filter( 'wp_resource_hints', function ( $urls, $type ) {
	if ( 'preconnect' === $type ) {
		$urls[] = 'https://fonts.googleapis.com';
		$urls[] = array( 'href' => 'https://fonts.gstatic.com', 'crossorigin' );
	}
	return $urls;
}, 10, 2 );

/* jQuery et ses dépendances en pied de page, sans jquery-migrate. */
add_action( 'wp_default_scripts', function ( $scripts ) {
	if ( is_admin() || ! isset( $scripts->registered['jquery'] ) ) {
		return;
	}
	$scripts->registered['jquery']->deps = array_diff( $scripts->registered['jquery']->deps, array( 'jquery-migrate' ) );
} );
add_action( 'wp_enqueue_scripts', function () {
	foreach ( array( 'jquery', 'jquery-core', 'underscore', 'wp-util', 'jquery-blockui', 'wc-jquery-blockui' ) as $h ) {
		wp_script_add_data( $h, 'group', 1 );
	}
}, 999 );

/* Fiche produit : pas de zoom au survol (une image pleine taille en moins). */
add_action( 'after_setup_theme', function () {
	remove_theme_support( 'wc-product-gallery-zoom' );
}, 100 );
