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
	$terms = get_terms( array( 'taxonomy' => 'moment', 'hide_empty' => false ) );
	if ( ! $terms || is_wp_error( $terms ) ) {
		return '';
	}
	$nav = '';
	$out = '';
	foreach ( array_values( $terms ) as $n => $t ) {
		$nav .= sprintf( '<a href="#moment-%s">%s</a>', esc_attr( $t->slug ), esc_html( $t->name ) );
		$items = '';
		foreach ( wc_get_products( array( 'status' => 'publish', 'limit' => 6, 'tax_query' => array( array( 'taxonomy' => 'moment', 'terms' => $t->term_id ) ) ) ) as $p ) {
			$items .= sprintf(
				'<li><a href="%1$s"><span class="am-mo-img">%2$s</span><span class="am-mo-txt"><strong>%3$s</strong><span>%4$s</span></span><span class="am-mo-go" aria-hidden="true">→</span></a></li>',
				esc_url( $p->get_permalink() ),
				$p->get_image( 'woocommerce_thumbnail', array( 'loading' => 'lazy', 'alt' => '' ) ),
				esc_html( $p->get_name() ),
				wp_kses_post( $p->get_price_html() )
			);
		}
		$out .= sprintf(
			'<section class="am-moment" id="moment-%1$s"><header class="am-mo-head"><span class="am-mo-num">%2$02d</span><h2>%3$s</h2><p>%4$s</p><a class="button" href="%5$s">%6$s</a></header><ul class="am-mo-list">%7$s</ul></section>',
			esc_attr( $t->slug ),
			$n + 1,
			esc_html( $t->name ),
			esc_html( $t->description ),
			esc_url( get_term_link( $t ) ),
			/* translators: %d: nombre de produits */
			esc_html( sprintf( _n( 'Voir le produit', 'Voir les %d produits', $t->count, 'storefront-child' ), $t->count ) ),
			$items
		);
	}
	return '<nav class="am-mo-nav" aria-label="' . esc_attr__( 'Moments', 'storefront-child' ) . '">' . $nav . '</nav>' . $out;
} );
