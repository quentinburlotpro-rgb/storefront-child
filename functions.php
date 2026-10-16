<?php
/**
 * Storefront Child — Amorino Click & Collect.
 * Storefront charge automatiquement le style.css du thème enfant
 * (handle « storefront-child-style ») : aucun enqueue de style.css ici.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Polices : substituts libres de Silver South Serif / Avenir Next (charte Amorino).
 */
add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style(
		'amorino-fonts',
		'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600&family=Nunito+Sans:wght@400;500;700&display=swap',
		array(),
		null
	);
}, 5 );

/**
 * Logo officiel à la place du titre du site.
 */
add_action( 'init', function () {
	remove_action( 'storefront_header', 'storefront_site_branding', 20 );
	remove_action( 'storefront_header', 'storefront_product_search', 40 );
	remove_action( 'storefront_footer', 'storefront_credit', 20 );
} );

add_action( 'storefront_header', function () {
	printf(
		'<div class="site-branding"><a class="amorino-logo" href="%1$s" rel="home"><img src="%2$s" alt="%3$s" width="199" height="40"></a></div>',
		esc_url( home_url( '/' ) ),
		esc_url( get_stylesheet_directory_uri() . '/assets/logo.svg' ),
		esc_attr( get_bloginfo( 'name' ) )
	);
}, 20 );

/**
 * Pied de page.
 */
add_action( 'storefront_footer', function () {
	printf(
		'<div class="amorino-footer"><img src="%1$s" alt="Amorino" width="159" height="32"><span>%2$s</span></div>',
		esc_url( get_stylesheet_directory_uri() . '/assets/logo-light.svg' ),
		esc_html__( 'Projet pédagogique — SAé 3.01, BUT MMI, IUT du Limousin. Site non officiel.', 'storefront-child' )
	);
}, 20 );

/**
 * Bandeau d'accueil au-dessus des sections produits du template « Homepage ».
 */
add_action( 'homepage', function () {
	$shop = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
	?>
	<section class="amorino-hero" aria-labelledby="amorino-hero-title">
		<span class="amorino-hero__eyebrow"><?php esc_html_e( 'Click & Collect', 'storefront-child' ); ?></span>
		<h2 id="amorino-hero-title"><?php esc_html_e( 'Votre rose de gelato, prête à votre arrivée', 'storefront-child' ); ?></h2>
		<p><?php esc_html_e( 'Composez votre glace parfum par parfum, choisissez votre créneau et récupérez-la en boutique sans faire la queue.', 'storefront-child' ); ?></p>
		<a class="button" href="<?php echo esc_url( $shop ); ?>"><?php esc_html_e( 'Voir la carte', 'storefront-child' ); ?></a>
	</section>
	<?php
}, 5 );

/**
 * Accueil : 3 colonnes, titres de sections en français.
 */
add_filter( 'storefront_recent_products_args', function ( $args ) {
	$args['limit']   = 3;
	$args['columns'] = 3;
	$args['title']   = __( 'Nouveautés', 'storefront-child' );
	return $args;
} );
add_filter( 'storefront_product_categories_args', function ( $args ) {
	$args['limit']   = 3;
	$args['columns'] = 3;
	$args['title']   = __( 'La carte', 'storefront-child' );
	return $args;
} );
add_filter( 'storefront_featured_products_args', function ( $args ) {
	$args['limit']   = 3;
	$args['columns'] = 3;
	$args['title']   = __( 'Nos incontournables', 'storefront-child' );
	return $args;
} );
add_action( 'init', function () {
	remove_action( 'homepage', 'storefront_popular_products', 50 );
	remove_action( 'homepage', 'storefront_on_sale_products', 60 );
	remove_action( 'homepage', 'storefront_best_selling_products', 70 );
} );

/**
 * Boutique : 3 colonnes, 9 produits par page.
 */
add_filter( 'loop_shop_columns', function () {
	return 3;
} );
add_filter( 'loop_shop_per_page', function () {
	return 9;
} );

/* =========================================================================
   Itération 3 — produits variables
   ========================================================================= */

/**
 * SCF : groupes de champs enregistrés en JSON dans le thème (versionnés sur GitHub,
 * déployés par git pull — pas besoin de réimporter la base).
 */
add_filter( 'acf/settings/save_json', function () {
	return get_stylesheet_directory() . '/acf-json';
} );
add_filter( 'acf/settings/load_json', function ( $paths ) {
	$paths[] = get_stylesheet_directory() . '/acf-json';
	return $paths;
} );

/**
 * Sélecteur d'options en tuiles (maquette) sur les fiches produit variables.
 */
add_action( 'wp_enqueue_scripts', function () {
	if ( function_exists( 'is_product' ) && is_product() ) {
		wp_enqueue_script(
			'amorino-variations',
			get_stylesheet_directory_uri() . '/assets/amorino-variations.js',
			array( 'jquery', 'wc-add-to-cart-variation' ),
			'1.0.0',
			true
		);
	}
}, 20 );

/**
 * Champ « Vos parfums » : activé produit par produit dans le back-office (SCF).
 */
function amorino_wants_flavours( $product_id ) {
	return function_exists( 'get_field' )
		? (bool) get_field( 'choix_parfums', $product_id )
		: (bool) get_post_meta( $product_id, 'choix_parfums', true );
}

add_action( 'woocommerce_before_add_to_cart_button', function () {
	global $product;
	if ( ! $product || ! amorino_wants_flavours( $product->get_id() ) ) {
		return;
	}
	$list  = function_exists( 'get_field' ) ? (string) get_field( 'parfums_disponibles', $product->get_id() ) : '';
	$names = array_filter( array_map( 'trim', preg_split( '/\R/', $list ) ) );
	?>
	<div class="amorino-flavours">
		<label for="amorino_parfums"><?php esc_html_e( 'Vos parfums', 'storefront-child' ); ?> <span class="required" aria-hidden="true">*</span></label>
		<input type="text" id="amorino_parfums" name="amorino_parfums" class="input-text" required
			placeholder="<?php esc_attr_e( 'Ex. : Pistache Mawardi, Sorbet Framboise', 'storefront-child' ); ?>"
			aria-describedby="amorino_parfums_hint">
		<?php if ( $names ) : ?>
			<p id="amorino_parfums_hint" class="amorino-flavours__hint"><?php esc_html_e( 'Touchez les parfums pour les ajouter, autant que vous voulez.', 'storefront-child' ); ?></p>
			<div class="amorino-flavours__chips" role="group" aria-label="<?php esc_attr_e( 'Parfums proposés', 'storefront-child' ); ?>">
				<?php foreach ( $names as $name ) : ?>
					<button type="button" class="am-chip" aria-pressed="false" data-flavour="<?php echo esc_attr( $name ); ?>"><?php echo esc_html( $name ); ?></button>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
	<?php
} );

add_filter( 'woocommerce_add_to_cart_validation', function ( $passed, $product_id ) {
	if ( amorino_wants_flavours( $product_id ) && '' === trim( wp_unslash( $_POST['amorino_parfums'] ?? '' ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		wc_add_notice( __( 'Indiquez au moins un parfum.', 'storefront-child' ), 'error' );
		return false;
	}
	return $passed;
}, 10, 2 );

add_filter( 'woocommerce_add_cart_item_data', function ( $data ) {
	if ( ! empty( $_POST['amorino_parfums'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		$data['amorino_parfums'] = sanitize_text_field( wp_unslash( $_POST['amorino_parfums'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
	}
	return $data;
} );

add_filter( 'woocommerce_get_item_data', function ( $items, $cart_item ) {
	if ( ! empty( $cart_item['amorino_parfums'] ) ) {
		$items[] = array(
			'key'   => __( 'Parfums', 'storefront-child' ),
			'value' => $cart_item['amorino_parfums'],
		);
	}
	return $items;
}, 10, 2 );

add_action( 'woocommerce_checkout_create_order_line_item', function ( $item, $key, $values ) {
	if ( ! empty( $values['amorino_parfums'] ) ) {
		$item->add_meta_data( __( 'Parfums', 'storefront-child' ), $values['amorino_parfums'] );
	}
}, 10, 3 );

/**
 * Niveaux de stock : « Stock faible » sous le seuil, « Épuisé » à 0.
 */
add_filter( 'woocommerce_get_availability_text', function ( $text, $product ) {
	if ( $product->managing_stock() && $product->is_in_stock() ) {
		$qty = $product->get_stock_quantity();
		$low = $product->get_low_stock_amount();
		$low = '' === $low || null === $low ? (int) get_option( 'woocommerce_notify_low_stock_amount', 3 ) : (int) $low;
		if ( null !== $qty && $qty <= $low ) {
			/* translators: %d: quantité restante */
			return sprintf( __( 'Stock faible : plus que %d', 'storefront-child' ), $qty );
		}
		return __( 'En stock', 'storefront-child' );
	}
	if ( ! $product->is_in_stock() ) {
		return __( 'Épuisé', 'storefront-child' );
	}
	return $text;
}, 10, 2 );

/**
 * Fidélité maquette : pas de vignettes précédent/suivant, pas d'UGS vide.
 */
add_action( 'init', function () {
	remove_action( 'woocommerce_after_single_product_summary', 'storefront_single_product_pagination', 30 );
	remove_action( 'storefront_after_footer', 'storefront_single_product_pagination', 30 );
} );
add_filter( 'wc_product_sku_enabled', '__return_false' );

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
