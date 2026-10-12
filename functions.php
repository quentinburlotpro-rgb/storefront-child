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
