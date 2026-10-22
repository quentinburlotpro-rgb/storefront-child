<?php
/**
 * [IT-3] Produits variables.
 */
defined( 'ABSPATH' ) || exit;

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
