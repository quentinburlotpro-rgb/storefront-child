<?php
/**
 * [IT-18] Widgets de filtrage.
 * Lancement : ddev wp eval-file setup/<ce fichier> (relançable).
 */
$log  = function ( $m ) { WP_CLI::log( '  ' . $m ); };
$prod = function ( $s ) { $p = get_page_by_path( $s, OBJECT, 'product' ); return $p ? $p->ID : 0; };
$pid  = $prod;

/* [IT-18] Filtres prix et taille dans la barre latérale de la boutique */
update_option( 'widget_woocommerce_price_filter', array( 2 => array( 'title' => 'Prix' ), '_multiwidget' => 1 ) );
update_option( 'widget_woocommerce_layered_nav', array( 2 => array( 'title' => 'Taille', 'attribute' => 'taille', 'display_type' => 'list', 'query_type' => 'or' ), '_multiwidget' => 1 ) );
update_option( 'widget_woocommerce_product_categories', array( 2 => array( 'title' => 'Catégories', 'count' => 1, 'hierarchical' => 1 ), '_multiwidget' => 1 ) );
$sw              = get_option( 'sidebars_widgets', array() );
$sw['sidebar-1'] = array( 'woocommerce_product_categories-2', 'woocommerce_price_filter-2', 'woocommerce_layered_nav-2' );
update_option( 'sidebars_widgets', $sw );
$log( 'Barre latérale boutique : catégories, filtre prix, filtre taille' );
