<?php
/**
 * Storefront Child — Amorino Click & Collect (SAé 3.01).
 * Storefront charge le style.css du thème enfant (handle « storefront-child-style »).
 */

defined( 'ABSPATH' ) || exit;

/* Polices : substituts libres de Silver South Serif / Avenir Next (charte Amorino). */
add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style( 'amorino-fonts', 'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600&family=Nunito+Sans:wght@400;500;700&display=swap', array(), null );
}, 5 );

/* Une feuille par itération, dans l'ordre alphabétique (= ordre de la cascade). */
add_action( 'wp_enqueue_scripts', function () {
	foreach ( glob( get_stylesheet_directory() . '/assets/css/*.css' ) ?: array() as $f ) {
		wp_enqueue_style( 'amorino-' . basename( $f, '.css' ), get_stylesheet_directory_uri() . '/assets/css/' . basename( $f ), array( 'storefront-child-style' ), filemtime( $f ) );
	}
}, 30 );

/* Un fichier PHP par itération. */
foreach ( glob( __DIR__ . '/inc/*.php' ) ?: array() as $amorino_file ) {
	require_once $amorino_file;
}
