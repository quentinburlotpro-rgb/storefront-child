<?php
/**
 * Surcharge Amorino du template « Homepage » de Storefront (thème enfant).
 * Sections de la maquette : bandeau, comment ça marche, nouveautés (IT-16),
 * produit star (IT-W), la carte. Rendu par am_home() dans inc/maquette.php.
 *
 * Template name: Homepage
 *
 * @package storefront-child
 */

get_header(); ?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">
			<?php am_home(); ?>
		</main><!-- #main -->
	</div><!-- #primary -->
<?php
get_footer();
