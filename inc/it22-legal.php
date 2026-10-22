<?php
/**
 * [IT-22] Mentions légales et CGV.
 */
defined( 'ABSPATH' ) || exit;

add_filter( 'woocommerce_get_terms_and_conditions_checkbox_text', function () {
	return __( 'J’ai lu et j’accepte les [terms]', 'storefront-child' );
} );
