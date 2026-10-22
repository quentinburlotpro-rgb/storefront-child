<?php
/**
 * [IT-17] Factures PDF.
 * Lancement : ddev wp eval-file setup/<ce fichier> (relançable).
 */
$log  = function ( $m ) { WP_CLI::log( '  ' . $m ); };
$prod = function ( $s ) { $p = get_page_by_path( $s, OBJECT, 'product' ); return $p ? $p->ID : 0; };
$pid  = $prod;

/* [IT-17] Factures PDF (WooCommerce PDF Invoices & Packing Slips) */
if ( class_exists( 'WPO_WCPDF' ) || function_exists( 'WPO_WCPDF' ) ) {
	$logo = attachment_url_to_postid( get_stylesheet_directory_uri() . '/assets/logo.png' );
	if ( ! $logo ) {
		require_once ABSPATH . 'wp-admin/includes/image.php';
		$src  = get_stylesheet_directory() . '/assets/logo.png';
		$up   = wp_upload_bits( 'amorino-logo-facture.png', null, file_get_contents( $src ) );
		$logo = wp_insert_attachment( array( 'post_mime_type' => 'image/png', 'post_title' => 'Logo Amorino (factures)', 'post_status' => 'inherit' ), $up['file'] );
		wp_update_attachment_metadata( $logo, wp_generate_attachment_metadata( $logo, $up['file'] ) );
	}
	update_option( 'wpo_wcpdf_settings_general', array_merge( (array) get_option( 'wpo_wcpdf_settings_general', array() ), array(
		'download_display' => 'display', 'template_path' => 'default/Simple', 'paper_size' => 'a4',
		'header_logo' => $logo, 'header_logo_height' => '15mm',
		'shop_name' => array( 'default' => 'Amorino Limoges' ),
		'shop_address' => array( 'default' => "12 rue du Clocher\n87000 Limoges" ),
		'footer' => array( 'default' => 'Projet pédagogique SAé 3.01 — facture fictive.' ),
	) ) );
	update_option( 'wpo_wcpdf_documents_settings_invoice', array_merge( (array) get_option( 'wpo_wcpdf_documents_settings_invoice', array() ), array(
		'enabled' => 1, 'attach_to_email_ids' => array( 'customer_completed_order' => 1, 'customer_invoice' => 1 ),
		'display_number' => 'invoice_number', 'display_date' => 'invoice_date', 'my_account_buttons' => 'available',
	) ) );
	$log( 'Factures PDF : logo, coordonnées, jointes à « Commande terminée », téléchargeables dans Mon compte' );
}
