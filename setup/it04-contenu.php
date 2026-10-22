<?php
/**
 * IT-4 — remplit les champs SCF « Informations produit » des 9 produits.
 * Lancement : ddev wp eval-file it4-products.php   (relançable)
 * Valeurs nutritionnelles INDICATIVES (ordres de grandeur pour des produits de ce type),
 * à remplacer par les fiches techniques réelles : projet pédagogique, site non officiel.
 */

if ( ! function_exists( 'update_field' ) ) {
	WP_CLI::error( 'Secure Custom Fields inactif.' );
}

$composition_glace = "Lait entier, sucre, crème, pistaches (9 %), jaunes d'œufs, lait écrémé en poudre, dextrose, farine de graines de caroube.";

$data = array(
	'cornet-fleur'      => array(
		array( 'lait', 'oeufs', 'fruits_a_coque', 'gluten', 'soja' ), 'arachides, sésame',
		array( '100 g', 235, 11, 6.4, 29, 24, 4.6, 0.15 ),
		"Gelato (exemple parfum Pistache Mawardi) : $composition_glace Cornet : farine de blé, sucre, beurre, lécithine de soja, sel.",
	),
	'pot-coppetta'      => array(
		array( 'lait', 'oeufs', 'fruits_a_coque' ), 'gluten, soja, arachides',
		array( '100 g', 215, 11, 6.5, 24, 22, 4.8, 0.12 ),
		"Gelato (exemple parfum Pistache Mawardi) : $composition_glace",
	),
	'brioche-au-gelato' => array(
		array( 'gluten', 'oeufs', 'lait', 'fruits_a_coque', 'soja' ), 'arachides, sésame',
		array( '100 g', 310, 13, 7.2, 41, 22, 6.5, 0.45 ),
		"Brioche : farine de blé, œufs, beurre, sucre, levure, sel. Gelato : $composition_glace Topping : chantilly (crème, sucre).",
	),
	'gaufre'            => array(
		array( 'gluten', 'oeufs', 'lait', 'soja' ), 'fruits à coque',
		array( '100 g', 330, 15, 8.5, 42, 18, 6.2, 0.55 ),
		'Pâte à gaufre : farine de blé, lait, œufs, beurre, sucre, levure, sel. Nappage au choix (L’Inimitable : chocolat noir, lécithine de soja ; caramel au beurre salé : sucre, crème, beurre, sel).',
	),
	'crepe'             => array(
		array( 'gluten', 'oeufs', 'lait' ), 'soja, fruits à coque',
		array( '100 g', 220, 8, 4.2, 30, 9, 7, 0.4 ),
		'Pâte à crêpe : lait, farine de blé, œufs, beurre, sucre, sel. Garniture au choix.',
	),
	'waffle-sticks'     => array(
		array( 'gluten', 'oeufs', 'lait', 'soja' ), 'fruits à coque',
		array( '100 g', 365, 18, 10, 45, 24, 6, 0.5 ),
		'Gaufre : farine de blé, beurre, œufs, sucre, lait, levure, sel. Sauce à tremper : chocolat noir, crème, lécithine de soja.',
	),
	'chocolat-chaud'    => array(
		array( 'lait', 'soja' ), 'fruits à coque, gluten',
		array( '100 ml', 95, 4.2, 2.7, 11, 10, 3.4, 0.1 ),
		'Lait entier, chocolat (pâte de cacao, sucre, beurre de cacao, lécithine de soja), sucre. Arôme selon le parfum choisi.',
	),
	'cappuccino'        => array(
		array( 'lait' ), '',
		array( '100 ml', 40, 1.9, 1.2, 3.1, 3.1, 2.2, 0.08 ),
		'Café espresso arabica, lait entier moussé, cacao en poudre.',
	),
	'milkshake'         => array(
		array( 'lait', 'oeufs', 'fruits_a_coque' ), 'gluten, soja',
		array( '100 ml', 150, 6.5, 4, 19, 17, 3.5, 0.1 ),
		'Lait entier, gelato ou sorbet au choix (lait, sucre, crème, jaunes d’œufs, pistaches), chantilly (crème, sucre).',
	),
);

$keys = array( 'portion', 'energie_kcal', 'matieres_grasses', 'acides_gras_satures', 'glucides', 'sucres', 'proteines', 'sel' );

foreach ( $data as $slug => list( $allergens, $traces, $nutri, $composition ) ) {
	$post = get_page_by_path( $slug, OBJECT, 'product' );
	if ( ! $post ) {
		WP_CLI::warning( "$slug introuvable, ignoré" );
		continue;
	}
	update_field( 'field_am_allergenes', $allergens, $post->ID );
	update_field( 'field_am_traces', $traces, $post->ID );
	update_field( 'field_am_nutrition', array_combine( $keys, $nutri ), $post->ID );
	update_field( 'field_am_composition', $composition, $post->ID );
	wc_delete_product_transients( $post->ID );
	WP_CLI::log( sprintf( '  %-20s %d allergènes · %s kcal / %s', $post->post_title, count( $allergens ), $nutri[1], $nutri[0] ) );
}
WP_CLI::success( 'Informations produit renseignées sur les 9 produits.' );
