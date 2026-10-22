<?php
/**
 * Surcharge Amorino de woocommerce/content-product.php : carte produit de la maquette
 * (photo + badge, titre, « catégorie · accroche », prix « dès X € », bouton « Composer »).
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package storefront-child
 * @version 9.4.0
 */

defined( 'ABSPATH' ) || exit;

global $product;
if ( ! is_a( $product, WC_Product::class ) || ! $product->is_visible() ) {
	return;
}
$link = $product->get_permalink();
$cat  = wc_get_product_terms( $product->get_id(), 'product_cat', array( 'parent' => 0 ) );
$line = get_post_meta( $product->get_id(), 'amorino_accroche', true );
$desc = trim( ( $cat ? $cat[0]->name : '' ) . ( $line ? ' · ' . $line : '' ), ' ·' );
?>
<li <?php wc_product_class( 'am-card', $product ); ?>>
	<a class="am-card__media" href="<?php echo esc_url( $link ); ?>" tabindex="-1" aria-hidden="true">
		<?php am_print_badges( 'loop' ); ?>
		<?php echo $product->get_image( 'woocommerce_thumbnail', array( 'loading' => 'lazy' ) ); // phpcs:ignore ?>
	</a>
	<div class="am-card__body">
		<h2 class="woocommerce-loop-product__title am-card__title"><a href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( $product->get_name() ); ?></a></h2>
		<?php if ( $desc ) : ?><p class="am-card__desc"><?php echo esc_html( $desc ); ?></p><?php endif; ?>
		<div class="am-card__foot">
			<span class="price"><?php echo $product->get_price_html(); // phpcs:ignore ?></span>
			<a class="button am-card__btn" href="<?php echo esc_url( $link ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Composer : %s', 'storefront-child' ), $product->get_name() ) ); ?>"><?php esc_html_e( 'Composer', 'storefront-child' ); ?></a>
		</div>
	</div>
</li>
