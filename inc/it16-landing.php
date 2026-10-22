<?php
/**
 * [IT-16] Landing page : 3 derniers produits ajoutés.
 */
defined( 'ABSPATH' ) || exit;

add_action( 'amorino_home_sections', function () {
	$news = wc_get_products( array( 'limit' => 3, 'orderby' => 'date', 'order' => 'DESC', 'status' => 'publish', 'return' => 'ids' ) );
	$shop = wc_get_page_permalink( 'shop' );
	?>
	<?php if ( $news ) : ?>
	<section class="am-news am-band am-band--grey">
		<div class="am-sechead"><h2>Nouveautés</h2><a href="<?php echo esc_url( $shop ); ?>">Toute la carte</a></div>
		<?php echo do_shortcode( '[products ids="' . implode( ',', $news ) . '" columns="3" orderby="date" order="DESC"]' ); // phpcs:ignore ?>
	</section>
	<?php endif; ?>
	<?php
}, 10 );
