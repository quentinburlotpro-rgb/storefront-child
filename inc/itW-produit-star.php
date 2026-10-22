<?php
/**
 * [IT-W] Produit star.
 */
defined( 'ABSPATH' ) || exit;

function am_star_product_id() {
	$id = get_transient( 'amorino_star_id' );
	if ( false === $id ) {
		$ids = wc_get_products( array( 'limit' => 1, 'orderby' => array( 'meta_value_num' => 'DESC', 'date' => 'DESC' ), 'meta_key' => 'total_sales', 'return' => 'ids', 'status' => 'publish' ) );
		$id  = $ids ? (int) $ids[0] : 0;
		set_transient( 'amorino_star_id', $id, HOUR_IN_SECONDS );
	}
	return (int) $id;
}
foreach ( array( 'woocommerce_order_status_processing', 'woocommerce_order_status_completed', 'woocommerce_order_status_on-hold', 'woocommerce_order_status_ready-pickup', 'woocommerce_order_status_cancelled', 'woocommerce_order_status_refunded' ) as $h ) {
	add_action( $h, function () { delete_transient( 'amorino_star_id' ); }, 99 );
}

add_action( 'amorino_home_sections', function () {
	$star = am_star_product_id() ? wc_get_product( am_star_product_id() ) : null;
	?>
	<?php if ( $star ) : ?>
	<section class="am-star am-band am-band--dark">
		<div class="am-star__media"><?php echo $star->get_image( 'large' ); // phpcs:ignore ?></div>
		<div class="am-star__text">
			<span class="amorino-badge amorino-badge--peach">★ Produit star — le plus commandé</span>
			<h2><?php echo esc_html( $star->get_name() ); ?></h2>
			<p><?php echo esc_html( wp_strip_all_tags( $star->get_short_description() ) ); ?></p>
			<a class="button am-btn--peach" href="<?php echo esc_url( $star->get_permalink() ); ?>">Je le compose</a>
		</div>
	</section>
	<?php endif; ?>
	<?php
}, 20 );
