<?php
/**
 * [IT-11] Liste des membres de l'équipe — réglages dans Équipe › Réglages (SCF).
 */
get_header();
$cols  = (int) am_team_opt( 'colonnes', 3 );
$photo = (int) am_team_opt( 'photo', 0 );
$role  = (bool) am_team_opt( 'afficher_role', true );
?>
<div id="primary" class="content-area amorino-team">
	<main id="main" class="site-main">
		<header class="page-header">
			<h1 class="page-title"><?php echo esc_html( am_team_opt( 'titre', __( 'L’équipe', 'storefront-child' ) ) ); ?></h1>
			<?php if ( $intro = am_team_opt( 'intro' ) ) : ?><p class="amorino-team__intro"><?php echo esc_html( $intro ); ?></p><?php endif; ?>
		</header>
		<?php if ( $photo ) : ?><figure class="amorino-team__photo"><?php echo wp_get_attachment_image( $photo, 'large' ); ?></figure><?php endif; ?>
		<?php if ( have_posts() ) : ?>
			<ul class="amorino-team__grid" style="--am-cols: <?php echo esc_attr( max( 2, min( 4, $cols ) ) ); ?>">
			<?php while ( have_posts() ) : the_post(); $m = amorino_member_meta( get_the_ID() ); ?>
				<li class="amorino-member">
					<a href="<?php the_permalink(); ?>">
						<?php echo amorino_member_avatar( get_the_ID() ); // phpcs:ignore ?>
						<h2 class="amorino-member__name"><?php the_title(); ?></h2>
						<?php if ( $role && $m['role'] ) : ?><p class="amorino-member__role"><?php echo esc_html( $m['role'] ); ?></p><?php endif; ?>
					</a>
				</li>
			<?php endwhile; ?>
			</ul>
		<?php else : ?>
			<p><?php esc_html_e( 'L’équipe arrive bientôt.', 'storefront-child' ); ?></p>
		<?php endif; ?>
		<?php if ( am_team_opt( 'recrutement', true ) ) : $mail = am_team_opt( 'recrutement_email' ); ?>
			<aside class="amorino-team__jobs">
				<p><?php echo esc_html( am_team_opt( 'recrutement_texte', __( 'Envie de sculpter des fleurs de gelato avec nous ?', 'storefront-child' ) ) ); ?></p>
				<?php if ( $mail ) : ?><a class="button" href="mailto:<?php echo esc_attr( $mail ); ?>"><?php esc_html_e( 'Envoyer ma candidature', 'storefront-child' ); ?></a><?php endif; ?>
			</aside>
		<?php endif; ?>
	</main>
</div>
<?php get_footer();
