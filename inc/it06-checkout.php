<?php
/**
 * [IT-6] Checkout.
 */
defined( 'ABSPATH' ) || exit;

/* ---------------------------------------------------------------------
 * [IT-6] Instructions pour le retrait — éditables dans
 * WooCommerce › Réglages › Général (option amorino_pickup_instructions).
 * ------------------------------------------------------------------- */
add_filter( 'woocommerce_general_settings', function ( $settings ) {
	$settings[] = array( 'title' => __( 'Click & Collect Amorino', 'storefront-child' ), 'type' => 'title', 'id' => 'amorino_cc' );
	$settings[] = array(
		'title'    => __( 'Instructions pour le retrait', 'storefront-child' ),
		'desc'     => __( 'Affichées au checkout, sur la page de confirmation et dans les e-mails.', 'storefront-child' ),
		'id'       => 'amorino_pickup_instructions',
		'type'     => 'textarea',
		'css'      => 'min-width:400px;height:90px;',
		'default'  => "Présentez votre numéro de commande au comptoir Click & Collect.\nAmorino Limoges — 12 rue du Clocher, 87000 Limoges.",
		'desc_tip' => true,
	);
	$settings[] = array( 'type' => 'sectionend', 'id' => 'amorino_cc' );
	return $settings;
} );

function amorino_pickup_instructions() {
	return trim( (string) get_option( 'amorino_pickup_instructions', '' ) );
}

function amorino_pickup_box( $plain = false ) {
	$txt = amorino_pickup_instructions();
	if ( '' === $txt ) {
		return;
	}
	if ( $plain ) {
		echo "\n" . esc_html__( 'Instructions pour le retrait', 'storefront-child' ) . "\n" . esc_html( $txt ) . "\n\n";
		return;
	}
	printf(
		'<div class="amorino-pickup"><h3>%s</h3><p>%s</p></div>',
		esc_html__( 'Instructions pour le retrait', 'storefront-child' ),
		nl2br( esc_html( $txt ) )
	);
}
add_action( 'woocommerce_review_order_before_payment', function () { amorino_pickup_box(); } );
add_action( 'woocommerce_thankyou', function () { amorino_pickup_box(); }, 5 );

/* ------------------------------------------------ Pas de livraison : champs réduits */
add_filter( 'woocommerce_cart_needs_shipping_address', '__return_false' );
add_filter( 'woocommerce_checkout_fields', function ( $f ) {
	foreach ( array( 'billing_company', 'billing_address_2', 'billing_state', 'billing_country' ) as $k ) {
		unset( $f['billing'][ $k ] );
	}
	$f['shipping'] = array();
	$f['billing']['billing_first_name']['class'] = array( 'form-row-first' );
	$f['billing']['billing_last_name']['class']  = array( 'form-row-last' );
	$f['billing']['billing_phone']['required']   = true;
	$f['billing']['billing_phone']['label']      = __( 'Téléphone (si la commande prend du retard)', 'storefront-child' );
	$f['billing']['billing_email']['label']      = __( 'E-mail (vous y recevrez votre code de retrait)', 'storefront-child' );
	$f['billing']['billing_phone']['priority']   = 30;
	$f['billing']['billing_email']['priority']   = 40;
	/* Adresse : seulement pour la livraison à domicile (affichée par JS, contrôlée côté serveur) */
	foreach ( array( 'billing_address_1' => 50, 'billing_postcode' => 60, 'billing_city' => 70 ) as $k => $p ) {
		if ( isset( $f['billing'][ $k ] ) ) {
			$f['billing'][ $k ]['required'] = false;
			$f['billing'][ $k ]['priority'] = $p;
			$f['billing'][ $k ]['class'][]  = 'am-delivery-only';
		}
	}
	$f['billing']['billing_address_1']['label'] = __( 'Adresse de livraison', 'storefront-child' );
	if ( isset( $f['order']['order_comments'] ) ) {
		$f['order']['order_comments']['label']       = __( 'Une précision pour l’équipe ?', 'storefront-child' );
		$f['order']['order_comments']['placeholder'] = __( 'Allergie, bougie d’anniversaire, prénom sur la commande…', 'storefront-child' );
	}
	return $f;
} );
add_filter( 'woocommerce_billing_fields', function ( $f ) {
	if ( is_checkout() ) {
		unset( $f['billing_country'] );
	}
	return $f;
}, 20 );
add_action( 'woocommerce_checkout_create_order', function ( $order ) {
	if ( ! $order->get_billing_country() ) {
		$order->set_billing_country( 'FR' );
	}
}, 5 );
add_filter( 'woocommerce_shipping_package_name', function () { return __( 'Retrait ou livraison', 'storefront-child' ); } );
add_filter( 'woocommerce_cart_shipping_method_full_label', function ( $label, $method ) {
	return 'local_pickup' === $method->get_method_id() ? __( 'En boutique · gratuit', 'storefront-child' ) : $label;
}, 10, 2 );
add_filter( 'woocommerce_enable_order_notes_field', '__return_true' );
add_filter( 'woocommerce_order_button_text', function () { return __( 'Valider ma commande', 'storefront-child' ); } );
add_filter( 'gettext', function ( $t, $text, $domain ) {
	if ( 'woocommerce' !== $domain ) {
		return $t;
	}
	$map = array(
		'Billing details'          => 'Vos coordonnées',
		'Billing &amp; Shipping'   => 'Vos coordonnées',
		'Additional information'   => 'Précisions',
		'Your order'               => 'Votre commande',
		'Proceed to checkout'      => 'Choisir mon créneau',
		'Cart totals'              => 'Récapitulatif',
		'Shipping'                 => 'Retrait ou livraison',
	);
	return $map[ $text ] ?? $t;
}, 20, 3 );

/* ------------------------------------------------ Titre « Paiement » */
add_action( 'woocommerce_review_order_before_payment', function () {
	echo '<h3 class="am-pay-title">' . esc_html__( 'Paiement', 'storefront-child' ) . '</h3>';
}, 20 );

/* ------------------------------------------------ Code de retrait */
function am_pickup_code( $order ) {
	$code = $order->get_meta( '_amorino_pickup_code' );
	if ( ! $code ) {
		$alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
		do {
			$code = 'AM-';
			for ( $i = 0; $i < 4; $i++ ) {
				$code .= $alphabet[ random_int( 0, strlen( $alphabet ) - 1 ) ];
			}
		} while ( wc_get_orders( array( 'meta_key' => '_amorino_pickup_code', 'meta_value' => $code, 'limit' => 1, 'return' => 'ids' ) ) );
		$order->update_meta_data( '_amorino_pickup_code', $code );
		$order->save_meta_data();
	}
	return $code;
}
add_action( 'woocommerce_checkout_order_created', 'am_pickup_code' );
add_action( 'woocommerce_new_order', function ( $id ) {
	if ( $o = wc_get_order( $id ) ) {
		am_pickup_code( $o );
	}
} );

function am_pickup_card( $order, $context = 'web' ) {
	$delivery = am_order_is_delivery( $order );
	$code = am_pickup_code( $order );
	$slot = function_exists( 'amorino_slot_label' ) ? amorino_slot_label( $order ) : '';
	$paid = 'cod' === $order->get_payment_method() ? __( 'À régler au comptoir', 'storefront-child' ) : ( $order->is_paid() ? __( 'Payée', 'storefront-child' ) : __( 'En attente du virement', 'storefront-child' ) );
	if ( 'plain' === $context ) {
		echo "\n== CODE DE RETRAIT : $code ==\n" . ( $slot ? "Créneau : $slot\n" : '' ) . "Paiement : $paid\n\n"; // phpcs:ignore
		return;
	}
	if ( 'email' === $context ) {
		printf(
			'<table role="presentation" width="100%%" style="margin:0 0 24px;border-collapse:collapse;background:#fde8dc;border-radius:14px"><tr><td style="padding:20px;text-align:center;font-family:Arial,sans-serif;color:#100b0c"><div style="font-size:12px;letter-spacing:2px;text-transform:uppercase">Votre code de retrait</div><div style="font-size:34px;font-weight:700;letter-spacing:4px;margin:6px 0">%s</div><div style="font-size:14px">%s</div><div style="font-size:13px;color:#6e6869;margin-top:4px">%s</div></td></tr></table>',
			esc_html( $code ),
			esc_html( $slot ),
			esc_html( $paid )
		);
		return;
	}
	?>
	<section class="am-ticket" aria-label="<?php esc_attr_e( 'Votre retrait', 'storefront-child' ); ?>">
		<p class="am-ticket__label"><?php echo $delivery ? esc_html__( 'Référence de livraison', 'storefront-child' ) : esc_html__( 'Votre code de retrait', 'storefront-child' ); ?></p>
		<p class="am-ticket__code"><?php echo esc_html( $code ); ?></p>
		<dl class="am-ticket__facts">
			<?php if ( $slot ) : ?><div><dt><?php echo $delivery ? esc_html__( 'Livraison', 'storefront-child' ) : esc_html__( 'Créneau', 'storefront-child' ); ?></dt><dd><?php echo esc_html( $slot ); ?></dd></div><?php endif; ?>
			<div><dt><?php echo $delivery ? esc_html__( 'Adresse', 'storefront-child' ) : esc_html__( 'Boutique', 'storefront-child' ); ?></dt><dd><?php echo $delivery ? esc_html( $order->get_shipping_address_1() . ', ' . $order->get_shipping_postcode() . ' ' . $order->get_shipping_city() ) : 'Amorino Limoges, 12 rue du Clocher'; ?></dd></div>
			<div><dt><?php esc_html_e( 'Paiement', 'storefront-child' ); ?></dt><dd><?php echo esc_html( $paid ); ?></dd></div>
			<div><dt><?php esc_html_e( 'Commande', 'storefront-child' ); ?></dt><dd>n° <?php echo esc_html( $order->get_order_number() ); ?> · <?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></dd></div>
		</dl>
		<p class="am-ticket__hint"><?php esc_html_e( 'Montrez ce code au comptoir Click & Collect. Il vous a aussi été envoyé par e-mail.', 'storefront-child' ); ?></p>
	</section>
	<?php
}

/* Page de confirmation : le ticket remplace le récapitulatif standard */
add_action( 'woocommerce_before_thankyou', function ( $order_id ) {
	$o = wc_get_order( $order_id );
	if ( $o && ! $o->has_status( 'failed' ) ) {
		echo '<h1 class="am-thanks">' . esc_html__( 'Merci ! Votre commande est confirmée', 'storefront-child' ) . '</h1>';
		am_pickup_card( $o );
	}
} );
add_filter( 'woocommerce_thankyou_order_received_text', '__return_empty_string' );
/* Espace client (détail d'une commande) */
add_action( 'woocommerce_view_order', function ( $order_id ) {
	if ( $o = wc_get_order( $order_id ) ) {
		am_pickup_card( $o );
	}
}, 5 );
/* E-mails client : le code en tête */
add_action( 'woocommerce_email_order_details', function ( $order, $sent_to_admin, $plain ) {
	if ( ! $sent_to_admin && ! $order->has_status( array( 'cancelled', 'failed', 'refunded' ) ) ) {
		am_pickup_card( $order, $plain ? 'plain' : 'email' );
	}
}, 5, 3 );
/* Back-office : code visible et recherchable */
add_action( 'woocommerce_admin_order_data_after_billing_address', function ( $order ) {
	echo '<p><strong>' . esc_html__( 'Code de retrait', 'storefront-child' ) . ' :</strong> <code style="font-size:16px">' . esc_html( am_pickup_code( $order ) ) . '</code></p>';
} );
add_filter( 'woocommerce_order_table_search_query_meta_keys', function ( $k ) { $k[] = '_amorino_pickup_code'; return $k; } );
add_filter( 'woocommerce_shop_order_search_fields', function ( $k ) { $k[] = '_amorino_pickup_code'; return $k; } );
foreach ( array( 'manage_woocommerce_page_wc-orders_columns', 'manage_edit-shop_order_columns' ) as $h ) {
	add_filter( $h, function ( $c ) {
		$n = array();
		foreach ( $c as $k => $v ) {
			$n[ $k ] = $v;
			if ( 'order_number' === $k ) {
				$n['am_code'] = __( 'Retrait', 'storefront-child' );
			}
		}
		return $n;
	} );
}
add_action( 'manage_woocommerce_page_wc-orders_custom_column', 'am_admin_col', 10, 2 );
add_action( 'manage_shop_order_posts_custom_column', 'am_admin_col', 10, 2 );
function am_admin_col( $col, $order ) {
	if ( 'am_code' !== $col ) {
		return;
	}
	$order = is_object( $order ) ? $order : wc_get_order( $order );
	if ( $order ) {
		echo '<strong>' . esc_html( am_pickup_code( $order ) ) . '</strong><br><small>' . esc_html( function_exists( 'amorino_slot_label' ) ? amorino_slot_label( $order ) : '' ) . '</small>';
	}
}
