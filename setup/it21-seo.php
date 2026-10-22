<?php
/**
 * [IT-21] Exemples de balises SEO.
 * Lancement : ddev wp eval-file setup/<ce fichier> (relançable).
 */
$log  = function ( $m ) { WP_CLI::log( '  ' . $m ); };
$prod = function ( $s ) { $p = get_page_by_path( $s, OBJECT, 'product' ); return $p ? $p->ID : 0; };
$pid  = $prod;

/* [IT-21] Exemples de balises SEO personnalisées */
if ( function_exists( 'update_field' ) ) {
	if ( $id = $pid( 'cornet-fleur' ) ) {
		update_field( 'field_am_seo_title', 'Cornet fleur Amorino en Click & Collect · Limoges', $id );
		update_field( 'field_am_seo_desc', 'Le cornet signature Amorino : vos parfums de gelato sculptés en pétales. Commandez en ligne, retirez en boutique à Limoges sans attendre.', $id );
	}
	if ( $t = get_term_by( 'slug', 'glaces', 'product_cat' ) ) {
		update_field( 'field_am_seo_title', 'Glaces Amorino à emporter · Click & Collect Limoges', $t );
		update_field( 'field_am_seo_desc', 'Cornet fleur, coppetta, brioche au gelato : les glaces Amorino à composer en ligne et à retirer en boutique.', $t );
	}
	$log( 'SEO : Title et Meta personnalisés sur Cornet fleur et la catégorie Glaces' );
}
