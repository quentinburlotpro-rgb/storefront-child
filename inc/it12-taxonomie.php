<?php
/**
 * [IT-12] Taxonomie.
 */
defined( 'ABSPATH' ) || exit;

/* ---------------------------------------------------------------------
 * [IT-12] Taxonomie « Moment » (CPT UI) : page dédiée via [amorino_moments].
 * ------------------------------------------------------------------- */
add_shortcode( 'amorino_moments', function () {
	if ( ! taxonomy_exists( 'moment' ) ) {
		return '';
	}
	$out = '';
	foreach ( get_terms( array( 'taxonomy' => 'moment', 'hide_empty' => false ) ) as $t ) {
		$out .= sprintf(
			'<section class="amorino-moment"><h2><a href="%1$s">%2$s</a></h2><p>%3$s</p>%4$s<p><a class="button" href="%1$s">%5$s</a></p></section>',
			esc_url( get_term_link( $t ) ),
			esc_html( $t->name ),
			esc_html( $t->description ),
			( $ids = get_objects_in_term( $t->term_id, 'moment' ) ) ? do_shortcode( '[products limit="3" columns="3" ids="' . esc_attr( implode( ',', $ids ) ) . '"]' ) : '',
			/* translators: %s: nom du moment */
			esc_html( sprintf( __( 'Tout « %s »', 'storefront-child' ), $t->name ) )
		);
	}
	return $out;
} );
