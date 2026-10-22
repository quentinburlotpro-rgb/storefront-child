<?php
/**
 * [IT-13] Ventes croisées.
 */
defined( 'ABSPATH' ) || exit;

remove_action( 'woocommerce_cart_collaterals', 'woocommerce_cross_sell_display' );
add_action( 'woocommerce_after_cart', 'woocommerce_cross_sell_display' );
/* ---------------------------------------------------------------------
 * [IT-13] Ventes croisées : 3 suggestions sur la fiche et dans le panier.
 * ------------------------------------------------------------------- */
add_filter( 'woocommerce_upsell_display_args', function ( $a ) { $a['posts_per_page'] = 3; $a['columns'] = 3; return $a; } );
add_filter( 'woocommerce_cross_sells_total', function () { return 3; } );
add_filter( 'woocommerce_cross_sells_columns', function () { return 3; } );
add_filter( 'woocommerce_product_upsells_products_heading', function () { return __( 'Pour accompagner', 'storefront-child' ); } );
add_filter( 'woocommerce_product_cross_sells_products_heading', function () { return __( 'Envie d’autre chose ?', 'storefront-child' ); } );
