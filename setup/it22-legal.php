<?php
/**
 * [IT-22] Pages légales.
 * Lancement : ddev wp eval-file setup/<ce fichier> (relançable).
 */
$log  = function ( $m ) { WP_CLI::log( '  ' . $m ); };
$prod = function ( $s ) { $p = get_page_by_path( $s, OBJECT, 'product' ); return $p ? $p->ID : 0; };
$pid  = $prod;

/* [IT-22] Mentions légales + CGV ; case à cocher CGV au checkout */
$pages = array(
	'mentions-legales' => array( 'Mentions légales', "<h2>Éditeur</h2>\n<p>Site réalisé par Quentin Burlot, étudiant en BUT MMI à l’IUT du Limousin, dans le cadre de la SAé 3.01. Projet pédagogique, non affilié à la société Amorino : aucune commande n’est réellement traitée.</p>\n<h2>Hébergement</h2>\n<p>Serveur mmi.unilim.fr — Université de Limoges, 33 rue François Mitterrand, 87032 Limoges.</p>\n<h2>Propriété intellectuelle</h2>\n<p>La marque, le logo et les photographies Amorino appartiennent à leurs propriétaires et sont utilisés ici à des fins exclusivement pédagogiques.</p>\n<h2>Données personnelles</h2>\n<p>Les données saisies servent uniquement à tester le parcours de commande. Voir la <a href=\"/politique-de-confidentialite/\">politique de confidentialité</a>.</p>" ),
	'cgv'              => array( 'Conditions générales de vente', "<h2>1. Objet</h2>\n<p>Les présentes conditions régissent les commandes passées en Click & Collect sur ce site pédagogique.</p>\n<h2>2. Commande</h2>\n<p>La commande est validée après choix d’un créneau de retrait et acceptation des présentes CGV. Un e-mail de confirmation est envoyé.</p>\n<h2>3. Prix et frais</h2>\n<p>Prix en euros TTC. Des frais de préparation de 0,50 € s’appliquent aux commandes inférieures à 8 €.</p>\n<h2>4. Paiement</h2>\n<p>Paiement au retrait (carte ou espèces) ou par virement bancaire ; dans ce cas la préparation débute à réception du virement.</p>\n<h2>5. Retrait</h2>\n<p>La commande est conservée 30 minutes après la fin du créneau choisi. Au-delà, elle peut être annulée.</p>\n<h2>6. Rétractation</h2>\n<p>Conformément à l’article L221-28 du Code de la consommation, le droit de rétractation ne s’applique pas aux denrées susceptibles de se détériorer rapidement.</p>\n<h2>7. Allergènes</h2>\n<p>Les allergènes sont indiqués sur chaque fiche produit. En cas de doute, renseignez-vous au comptoir.</p>" ),
	'nos-envies'       => array( 'Nos envies', "<p>Une pause gourmande, un coup de frais ou un dessert à partager ? Choisissez votre moment.</p>\n[amorino_moments]" ),
);
foreach ( $pages as $slug => list( $title, $content ) ) {
	$p  = get_page_by_path( $slug );
	$id = $p ? $p->ID : wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_name' => $slug, 'post_title' => $title, 'post_content' => $content ) );
	if ( 'cgv' === $slug ) { update_option( 'woocommerce_terms_page_id', $id ); }
}
update_option( 'woocommerce_checkout_terms_and_conditions_checkbox_text', 'J’ai lu et j’accepte les [terms]' );
$log( 'Pages Mentions légales, CGV (case obligatoire au checkout), Nos envies' );
