<?php
/**
 * [IT-4] Enrichissement du contenu.
 */
defined( 'ABSPATH' ) || exit;

/* =========================================================================
   Itération 4 — enrichissement du contenu (SCF) : allergènes, valeurs
   nutritionnelles, composition, affichés en accordéon AVANT le prix.
   ========================================================================= */

/**
 * Ordre du résumé : titre (5) → description courte (7) → informations (8) → prix (10) → formulaire (30).
 */
add_action( 'init', function () {
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );
	add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 7 );
} );
add_action( 'woocommerce_single_product_summary', 'amorino_product_infos', 8 );

/**
 * Lecture d'un champ SCF avec repli sur la méta brute si le plugin est désactivé.
 */
function amorino_field( $name, $product_id ) {
	if ( function_exists( 'get_field' ) ) {
		return get_field( $name, $product_id );
	}
	return get_post_meta( $product_id, $name, true );
}

/**
 * Met en gras, dans la composition, les ingrédients liés aux allergènes cochés (INCO, art. 21).
 */
function amorino_highlight_allergens( $text, $codes ) {
	$words = array(
		'gluten'         => array( 'blé', 'farine', 'gluten', 'orge', 'seigle', 'avoine', 'épeautre' ),
		'oeufs'          => array( 'œufs', 'œuf', 'oeufs', 'oeuf' ),
		'lait'           => array( 'lait', 'crème', 'beurre', 'lactose', 'mascarpone', 'chantilly', 'lactosérum' ),
		'fruits_a_coque' => array( 'pistaches', 'pistache', 'noisettes', 'noisette', 'amandes', 'amande', 'noix' ),
		'soja'           => array( 'soja' ),
		'arachides'      => array( 'arachide', 'cacahuète' ),
		'sesame'         => array( 'sésame' ),
		'sulfites'       => array( 'sulfites' ),
	);
	$text = esc_html( $text );
	foreach ( (array) $codes as $code ) {
		if ( empty( $words[ $code ] ) ) {
			continue;
		}
		$alt  = implode( '|', array_map( 'preg_quote', $words[ $code ] ) );
		$text = preg_replace( '/(?<![\p{L}>])(' . $alt . ')(?![\p{L}<])/iu', '<strong>$1</strong>', $text );
	}
	return $text;
}

function amorino_product_infos() {
	global $product;
	if ( ! $product ) {
		return;
	}
	$id          = $product->get_id();
	$allergens   = (array) amorino_field( 'allergenes', $id );
	$traces      = trim( (string) amorino_field( 'traces', $id ) );
	$nutrition   = (array) amorino_field( 'nutrition', $id );
	$composition = trim( (string) amorino_field( 'composition', $id ) );

	$labels = array();
	$codes  = array();
	foreach ( $allergens as $a ) {
		$labels[] = is_array( $a ) ? $a['label'] : $a;
		$codes[]  = is_array( $a ) ? $a['value'] : $a;
	}

	$rows = array(
		'energie_kcal'        => array( __( 'Énergie', 'storefront-child' ), '', false ),
		'matieres_grasses'    => array( __( 'Matières grasses', 'storefront-child' ), 'g', false ),
		'acides_gras_satures' => array( __( 'dont acides gras saturés', 'storefront-child' ), 'g', true ),
		'glucides'            => array( __( 'Glucides', 'storefront-child' ), 'g', false ),
		'sucres'              => array( __( 'dont sucres', 'storefront-child' ), 'g', true ),
		'proteines'           => array( __( 'Protéines', 'storefront-child' ), 'g', false ),
		'sel'                 => array( __( 'Sel', 'storefront-child' ), 'g', false ),
	);
	$has_nutrition = false;
	foreach ( array_keys( $rows ) as $k ) {
		if ( isset( $nutrition[ $k ] ) && '' !== $nutrition[ $k ] && null !== $nutrition[ $k ] ) {
			$has_nutrition = true;
		}
	}

	if ( ! $labels && ! $traces && ! $has_nutrition && ! $composition ) {
		return;
	}
	$num = function ( $v, $dec = 1 ) {
		return number_format_i18n( (float) $v, (float) $v == (int) $v ? 0 : $dec );
	};
	?>
	<div class="amorino-infos">
		<?php if ( $labels || $traces ) : ?>
		<details class="amorino-infos__item" open>
			<summary><?php esc_html_e( 'Allergènes', 'storefront-child' ); ?></summary>
			<div class="amorino-infos__body">
				<?php if ( $labels ) : ?>
					<ul class="amorino-allergens" aria-label="<?php esc_attr_e( 'Allergènes présents', 'storefront-child' ); ?>">
						<?php foreach ( $labels as $l ) : ?>
							<li><?php echo esc_html( $l ); ?></li>
						<?php endforeach; ?>
					</ul>
				<?php else : ?>
					<p><?php esc_html_e( 'Aucun des 14 allergènes majeurs.', 'storefront-child' ); ?></p>
				<?php endif; ?>
				<?php if ( $traces ) : ?>
					<p class="amorino-infos__note"><?php echo esc_html( sprintf( __( 'Traces éventuelles : %s.', 'storefront-child' ), rtrim( $traces, '.' ) ) ); ?></p>
				<?php endif; ?>
			</div>
		</details>
		<?php endif; ?>

		<?php if ( $has_nutrition ) : ?>
		<details class="amorino-infos__item">
			<summary><?php esc_html_e( 'Valeurs nutritionnelles', 'storefront-child' ); ?></summary>
			<div class="amorino-infos__body">
				<table class="amorino-nutrition">
					<caption><?php echo esc_html( sprintf( __( 'Valeurs moyennes pour %s', 'storefront-child' ), $nutrition['portion'] ?: '100 g' ) ); ?></caption>
					<tbody>
					<?php foreach ( $rows as $k => list( $label, $unit, $sub ) ) :
						if ( ! isset( $nutrition[ $k ] ) || '' === $nutrition[ $k ] || null === $nutrition[ $k ] ) {
							continue;
						}
						$value = 'energie_kcal' === $k
							? sprintf( '%s kJ / %s kcal', $num( $nutrition[ $k ] * 4.184, 0 ), $num( $nutrition[ $k ], 0 ) )
							: $num( $nutrition[ $k ], 'sel' === $k ? 2 : 1 ) . ' ' . $unit;
						?>
						<tr<?php echo $sub ? ' class="is-sub"' : ''; ?>><th scope="row"><?php echo esc_html( $label ); ?></th><td><?php echo esc_html( $value ); ?></td></tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</details>
		<?php endif; ?>

		<?php if ( $composition ) : ?>
		<details class="amorino-infos__item">
			<summary><?php esc_html_e( 'Composition', 'storefront-child' ); ?></summary>
			<div class="amorino-infos__body">
				<p><?php echo amorino_highlight_allergens( $composition, $codes ); // phpcs:ignore WordPress.Security.EscapeOutput -- échappé dans la fonction ?></p>
			</div>
		</details>
		<?php endif; ?>
	</div>
	<?php
}
