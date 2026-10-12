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
