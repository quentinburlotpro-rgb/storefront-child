<?php
/**
 * [IT-2] Fidélité à la maquette Figma : en-tête, accueil, cartes produit, boutique, fiche, pied de page.
 */
defined( 'ABSPATH' ) || exit;

function am_icon( $name, $size = 20 ) {
	$p = array(
		'pin'   => '<path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"/><circle cx="12" cy="10" r="3"/>',
		'user'  => '<path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
		'cart'  => '<circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/>',
		'cone'  => '<path d="m7 11 4.08 10.35a1 1 0 0 0 1.84 0L17 11"/><path d="M17 7A5 5 0 0 0 7 7"/><path d="M17 7a2 2 0 0 1 0 4H7a2 2 0 0 1 0-4"/>',
		'clock' => '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',
		'store' => '<path d="m2 7 4.41-4.41A2 2 0 0 1 7.83 2h8.34a2 2 0 0 1 1.42.59L22 7"/><path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"/><path d="M15 22v-4a2 2 0 0 0-2-2h-2a2 2 0 0 0-2 2v4"/><path d="M2 7h20"/><path d="M22 7v3a2 2 0 0 1-2 2a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 16 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 12 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 8 12a2.7 2.7 0 0 1-1.59-.63.7.7 0 0 0-.82 0A2.7 2.7 0 0 1 4 12a2 2 0 0 1-2-2V7"/>',
	);
	return '<svg class="am-ic" width="' . (int) $size . '" height="' . (int) $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ( $p[ $name ] ?? '' ) . '</svg>';
}

/* ------------------------------------------------------------------ En-tête */
add_action( 'wp', function () {
	remove_all_actions( 'storefront_header' );
	remove_all_actions( 'storefront_footer' );
	remove_all_actions( 'homepage' );
	remove_action( 'storefront_before_content', 'storefront_header_widget_region', 10 );
	remove_action( 'storefront_footer', 'storefront_handheld_footer_bar', 999 );
	/* En-tête, pied de page et accueil : appelés directement par les templates surchargés header.php, footer.php, template-homepage.php */
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );
	remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );
	remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 10 );
	remove_action( 'woocommerce_after_shop_loop', 'woocommerce_catalog_ordering', 10 );
	remove_action( 'woocommerce_after_shop_loop', 'woocommerce_result_count', 20 );
	if ( is_front_page() ) {
		remove_action( 'storefront_before_content', 'woocommerce_breadcrumb', 10 );
	}
} );
remove_action( 'storefront_footer', 'storefront_handheld_footer_bar', 999 );

function am_cart_count() {
	$n = WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
	return '<span class="am-cart__count"' . ( $n ? '' : ' hidden' ) . '>' . (int) $n . '</span>';
}
add_filter( 'woocommerce_add_to_cart_fragments', function ( $f ) {
	$f['span.am-cart__count'] = am_cart_count();
	return $f;
} );

function am_header() {
	?>
	<div class="am-header">
		<a class="am-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><img src="<?php echo esc_url( get_stylesheet_directory_uri() . '/assets/logo.svg' ); ?>" alt="Amorino — accueil" width="199" height="40"></a>
		<nav class="am-nav" aria-label="<?php esc_attr_e( 'Menu principal', 'storefront-child' ); ?>">
			<?php wp_nav_menu( array( 'theme_location' => 'primary', 'container' => false, 'menu_class' => 'am-nav__list', 'depth' => 1, 'fallback_cb' => false ) ); ?>
		</nav>
		<div class="am-header__actions">
			<a class="am-pill" href="<?php echo esc_url( home_url( '/#retrait' ) ); ?>" aria-label="<?php esc_attr_e( 'Ma boutique', 'storefront-child' ); ?>"><?php echo am_icon( 'pin', 18 ); // phpcs:ignore ?><span><?php esc_html_e( 'Ma boutique', 'storefront-child' ); ?></span></a>
			<a class="am-iconbtn" href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" aria-label="<?php esc_attr_e( 'Mon compte', 'storefront-child' ); ?>"><?php echo am_icon( 'user', 22 ); // phpcs:ignore ?></a>
			<a class="am-cart" href="<?php echo esc_url( wc_get_cart_url() ); ?>"><?php echo am_icon( 'cart', 20 ); // phpcs:ignore ?><span class="screen-reader-text"><?php esc_html_e( 'Panier', 'storefront-child' ); ?></span><?php echo am_cart_count(); // phpcs:ignore ?></a>
		</div>
	</div>
	<?php
}

/* ------------------------------------------------------------------ Pied de page */
function am_footer() {
	$link = function ( $path, $label ) { return '<li><a href="' . esc_url( home_url( $path ) ) . '">' . esc_html( $label ) . '</a></li>'; };
	$cats = '';
	foreach ( am_top_cats() as $t ) {
		$cats .= '<li><a href="' . esc_url( get_term_link( $t ) ) . '">' . esc_html( $t->name ) . '</a></li>';
	}
	?>
	<div class="am-footer">
		<div class="am-footer__grid">
			<a class="am-footer__logo" href="<?php echo esc_url( home_url( '/' ) ); ?>"><img src="<?php echo esc_url( get_stylesheet_directory_uri() . '/assets/logo.svg' ); ?>" alt="Amorino" width="159" height="32"></a>
			<div><h2><?php esc_html_e( 'Commander', 'storefront-child' ); ?></h2><ul><?php echo $cats; // phpcs:ignore ?></ul></div>
			<div><h2><?php esc_html_e( 'Informations', 'storefront-child' ); ?></h2><ul><?php echo $link( '/nos-envies/', 'Nos envies' ) . $link( '/equipe/', 'L’équipe' ) . $link( '/#retrait', 'Retrait en boutique' ); // phpcs:ignore ?></ul></div>
			<div><h2><?php esc_html_e( 'Légal', 'storefront-child' ); ?></h2><ul><?php echo $link( '/mentions-legales/', 'Mentions légales' ) . $link( '/cgv/', 'CGV' ) . $link( '/politique-de-confidentialite/', 'Confidentialité' ); // phpcs:ignore ?></ul></div>
		</div>
		<p class="am-footer__bottom"><?php esc_html_e( 'Projet pédagogique — SAé 3.01, BUT MMI, IUT du Limousin. Site non officiel.', 'storefront-child' ); ?></p>
	</div>
	<?php
}

/* ------------------------------------------------------------------ Accueil */
function am_top_cats() {
	return get_terms( array( 'taxonomy' => 'product_cat', 'parent' => 0, 'hide_empty' => true, 'exclude' => array( (int) get_option( 'default_product_cat' ) ), 'orderby' => 'term_order' ) );
}
function am_pid( $slug ) {
	$p = get_page_by_path( $slug, OBJECT, 'product' );
	return $p ? $p->ID : 0;
}
function am_home() {
	$shop  = wc_get_page_permalink( 'shop' );
	$hero  = get_the_post_thumbnail_url( am_pid( 'pot-coppetta' ), 'large' );
	$star  = function_exists( 'am_star_product_id' ) && am_star_product_id() ? wc_get_product( am_star_product_id() ) : null;
	$steps = array(
		array( 'cone', '1. Composez', 'Format, taille, autant de parfums que vous voulez.' ),
		array( 'clock', '2. Choisissez un créneau', 'Retrait par tranche de 30 minutes, payez en ligne ou sur place.' ),
		array( 'store', '3. Retirez en boutique', 'Présentez votre numéro au comptoir Click & Collect.' ),
	);
	?>
	<section class="am-hero am-band">
		<div class="am-hero__text">
			<p class="am-eyebrow">Click &amp; Collect</p>
			<h1>Votre rose de gelato, prête à votre arrivée</h1>
			<p>Composez votre glace parfum par parfum, choisissez votre créneau et récupérez-la en boutique sans faire la queue.</p>
			<p class="am-actions"><a class="button" href="<?php echo esc_url( $star ? $star->get_permalink() : $shop ); ?>">Composer ma glace</a> <a class="button am-btn--ghost" href="<?php echo esc_url( $shop ); ?>">Voir la carte</a></p>
		</div>
		<?php if ( $hero ) : ?><div class="am-hero__media"><img src="<?php echo esc_url( $hero ); ?>" alt="Coppetta Amorino garnie de gelato et de chantilly" width="460" height="460"></div><?php endif; ?>
	</section>

	<section class="am-steps" id="retrait">
		<h2>Comment ça marche</h2>
		<ol class="am-steps__list">
			<?php foreach ( $steps as list( $ic, $t, $d ) ) : ?>
				<li><span class="am-steps__ic"><?php echo am_icon( $ic, 22 ); // phpcs:ignore ?></span><h3><?php echo esc_html( $t ); ?></h3><p><?php echo esc_html( $d ); ?></p></li>
			<?php endforeach; ?>
		</ol>
	</section>

	<?php
	/* Sections ajoutées par les itérations suivantes : Nouveautés (IT-16), Produit star (IT-W). */
	do_action( 'amorino_home_sections' );
	?>


	<section class="am-cats">
		<h2>La carte</h2>
		<ul class="am-cats__list">
			<?php foreach ( am_top_cats() as $t ) :
				?>
				<li><a href="<?php echo esc_url( get_term_link( $t ) ); ?>"><h3><?php echo esc_html( $t->name ); ?></h3><p><?php echo esc_html( implode( ', ', array_map( 'strtolower', array_map( fn( $p ) => $p->get_name(), wc_get_products( array( 'category' => array( $t->slug ), 'limit' => 3 ) ) ) ) ) ); ?></p></a></li>
			<?php endforeach; ?>
		</ul>
	</section>
	<?php
}

/* ------------------------------------------------------------------ Cartes produit */
/* Ligne « catégorie · accroche » : rendue par la surcharge woocommerce/content-product.php */
add_filter( 'woocommerce_product_add_to_cart_text', function ( $t, $p ) {
	return $p->is_type( 'variable' ) || $p->is_type( 'simple' ) ? __( 'Composer', 'storefront-child' ) : $t;
}, 10, 2 );
add_filter( 'woocommerce_loop_add_to_cart_link', function ( $html, $p ) {
	return sprintf( '<a href="%s" class="button am-card__btn">%s</a>', esc_url( $p->get_permalink() ), esc_html__( 'Composer', 'storefront-child' ) );
}, 10, 2 );
add_filter( 'woocommerce_variable_price_html', function ( $html, $p ) {
	$min = $p->get_variation_price( 'min', true );
	$max = $p->get_variation_price( 'max', true );
	return $min === $max ? wc_price( $min ) : sprintf( __( 'dès %s', 'storefront-child' ), wc_price( $min ) );
}, 10, 2 );

/* Badges : produit star, nouveau, végan, variation épuisée */
function am_badges( $product ) {
	$b = array();
	if ( function_exists( 'am_star_product_id' ) && $product->get_id() === am_star_product_id() ) { $b[] = array( 'star', '★ Produit star' ); }
	if ( has_term( 'nouveau', 'product_tag', $product->get_id() ) ) { $b[] = array( 'peach', 'Nouveau' ); }
	if ( has_term( 'vegan', 'product_tag', $product->get_id() ) ) { $b[] = array( 'vegan', 'Option végane' ); }
	if ( $product->is_type( 'variable' ) ) {
		foreach ( $product->get_children() as $c ) {
			$v = wc_get_product( $c );
			if ( $v && ! $v->is_in_stock() ) { $b[] = array( 'out', implode( ' ', $v->get_attributes() ) . ' épuisé' ); break; }
		}
	}
	return $b;
}
function am_print_badges( $where ) {
	global $product;
	$b = am_badges( $product );
	if ( 'loop' === $where ) { $b = array_slice( $b, 0, 1 ); }
	if ( ! $b ) { return; }
	echo '<div class="am-badges am-badges--' . esc_attr( $where ) . '">';
	foreach ( $b as list( $type, $label ) ) {
		echo '<span class="amorino-badge amorino-badge--' . esc_attr( $type ) . '">' . esc_html( ucfirst( $label ) ) . '</span>';
	}
	echo '</div>';
}
/* Badges des cartes : affichés par la surcharge woocommerce/content-product.php */
add_action( 'woocommerce_single_product_summary', function () { am_print_badges( 'single' ); }, 4 );
remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 10 );

/* ------------------------------------------------------------------ Boutique */
add_filter( 'woocommerce_page_title', function ( $t ) { return is_shop() ? __( 'La carte', 'storefront-child' ) : $t; } );
add_action( 'woocommerce_archive_description', function () {
	if ( ! is_shop() && ! is_product_category() ) { return; }
	$current = is_product_category() ? get_queried_object_id() : 0;
	echo '<nav class="am-chips" aria-label="' . esc_attr__( 'Catégories', 'storefront-child' ) . '">';
	printf( '<a class="am-chip%s" href="%s">Tout</a>', $current ? '' : ' is-active', esc_url( wc_get_page_permalink( 'shop' ) ) );
	foreach ( am_top_cats() as $t ) {
		printf( '<a class="am-chip%s" href="%s">%s</a>', $current === $t->term_id ? ' is-active' : '', esc_url( get_term_link( $t ) ), esc_html( $t->name ) );
	}
	echo '</nav>';
}, 5 );
add_filter( 'woocommerce_breadcrumb_defaults', function ( $d ) { $d['delimiter'] = '<span class="am-sep"> › </span>'; return $d; } );

/* Mise en page : barre latérale seulement sur la boutique et les catégories */
add_filter( 'body_class', function ( $c ) {
	$c[] = ( function_exists( 'is_shop' ) && ( is_shop() || is_product_taxonomy() ) ) ? 'am-has-sidebar' : 'am-full';
	return $c;
} );
add_filter( 'is_active_sidebar', function ( $active, $id ) {
	if ( 'sidebar-1' === $id && ! ( is_shop() || is_product_taxonomy() ) ) { return false; }
	return $active;
}, 10, 2 );

/* ------------------------------------------------------------------ Fiche produit */
add_action( 'woocommerce_single_product_summary', function () {
	echo '<p class="am-pickup-note">' . am_icon( 'clock', 16 ) . ' ' . esc_html__( 'Prêt en 10 min · retrait à Amorino Limoges, 12 rue du Clocher', 'storefront-child' ) . '</p>'; // phpcs:ignore
}, 35 );
add_filter( 'woocommerce_product_tabs', function ( $tabs ) {
	unset( $tabs['additional_information'] );
	return $tabs;
}, 98 );


add_filter( 'woocommerce_product_categories_widget_args', function ( $a ) { $a['exclude'] = (int) get_option( 'default_product_cat' ); return $a; } );
