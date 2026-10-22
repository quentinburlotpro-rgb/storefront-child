<?php
/**
 * [IT-8] Réglages des e-mails WooCommerce.
 * Lancement : ddev wp eval-file setup/<ce fichier> (relançable).
 */
$log  = function ( $m ) { WP_CLI::log( '  ' . $m ); };
$prod = function ( $s ) { $p = get_page_by_path( $s, OBJECT, 'product' ); return $p ? $p->ID : 0; };
$pid  = $prod;

foreach ( array( 'woocommerce_email_from_name' => 'Amorino Limoges', 'woocommerce_email_base_color' => '#100b0c', 'woocommerce_email_background_color' => '#fde8dc', 'woocommerce_email_body_background_color' => '#ffffff', 'woocommerce_email_text_color' => '#383838', 'woocommerce_email_header_image' => get_stylesheet_directory_uri() . '/assets/logo.png', 'woocommerce_email_footer_text' => 'Amorino Limoges · 12 rue du Clocher · Click & Collect — projet pédagogique SAé 3.01, site non officiel.' ) as $k => $v ) {
	update_option( $k, $v );
}
$log( 'E-mails aux couleurs Amorino' );
