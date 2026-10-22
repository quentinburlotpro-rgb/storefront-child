<?php
/**
 * [IT-11] Fiche d'un membre de l'équipe.
 */
get_header(); ?>
<div id="primary" class="content-area amorino-team">
	<main id="main" class="site-main">
	<?php while ( have_posts() ) : the_post(); $m = amorino_member_meta( get_the_ID() ); ?>
		<p class="amorino-back"><a href="<?php echo esc_url( get_post_type_archive_link( 'membre' ) ); ?>">‹ <?php esc_html_e( 'Toute l’équipe', 'storefront-child' ); ?></a></p>
		<article class="amorino-member-single">
			<?php echo amorino_member_avatar( get_the_ID() ); // phpcs:ignore ?>
			<div>
				<h1><?php the_title(); ?></h1>
				<?php if ( $m['role'] ) : ?><p class="amorino-member__role"><?php echo esc_html( $m['role'] ); ?></p><?php endif; ?>
				<div class="entry-content"><?php the_content(); ?></div>
				<dl class="amorino-member__facts">
					<?php if ( $m['specialite'] ) : ?><dt><?php esc_html_e( 'Spécialité', 'storefront-child' ); ?></dt><dd><?php echo esc_html( $m['specialite'] ); ?></dd><?php endif; ?>
					<?php if ( $m['parfum'] ) : ?><dt><?php esc_html_e( 'Parfum préféré', 'storefront-child' ); ?></dt><dd><?php echo esc_html( $m['parfum'] ); ?></dd><?php endif; ?>
				</dl>
			</div>
		</article>
	<?php endwhile; ?>
	</main>
</div>
<?php get_footer();
