<?php
/**
 * [IT-20] Créneaux.
 */
defined( 'ABSPATH' ) || exit;

/* ---------------------------------------------------------------------
 * [IT-20] Créneau de retrait : jour + plage de 30 min (12 h – 22 h).
 * ------------------------------------------------------------------- */
function amorino_slot_days() {
	$days = array();
	for ( $i = 0; $i < 7; $i++ ) {
		$ts                              = strtotime( "+$i day", current_time( 'timestamp' ) );
		$days[ wp_date( 'Y-m-d', $ts ) ] = 0 === $i ? __( "Aujourd'hui", 'storefront-child' ) : ucfirst( wp_date( 'l j F', $ts ) );
	}
	return $days;
}
function amorino_slot_times() {
	$t = array();
	for ( $m = 12 * 60; $m < 22 * 60; $m += 30 ) {
		$k       = sprintf( '%02d:%02d', intdiv( $m, 60 ), $m % 60 );
		$e       = sprintf( '%02d:%02d', intdiv( $m + 30, 60 ), ( $m + 30 ) % 60 );
		$t[ $k ] = "$k – $e";
	}
	return $t;
}
add_action( 'woocommerce_checkout_before_customer_details', function () {
	$checkout = WC()->checkout();
	echo '<div class="amorino-slot"><h3>' . esc_html__( 'Créneau de retrait', 'storefront-child' ) . '</h3>';
	woocommerce_form_field( 'amorino_day', array( 'type' => 'select', 'label' => __( 'Jour', 'storefront-child' ), 'required' => true, 'class' => array( 'form-row-first' ), 'options' => array( '' => __( 'Choisir…', 'storefront-child' ) ) + amorino_slot_days() ), $checkout->get_value( 'amorino_day' ) );
	woocommerce_form_field( 'amorino_time', array( 'type' => 'select', 'label' => __( 'Heure', 'storefront-child' ), 'required' => true, 'class' => array( 'form-row-last' ), 'options' => array( '' => __( 'Choisir…', 'storefront-child' ) ) + amorino_slot_times() ), $checkout->get_value( 'amorino_time' ) );
	echo '</div>';
} );
add_action( 'woocommerce_checkout_process', function () {
	// phpcs:disable WordPress.Security.NonceVerification -- nonce vérifié par WooCommerce
	$day  = sanitize_text_field( wp_unslash( $_POST['amorino_day'] ?? '' ) );
	$time = sanitize_text_field( wp_unslash( $_POST['amorino_time'] ?? '' ) );
	// phpcs:enable
	if ( ! isset( amorino_slot_days()[ $day ] ) || ! isset( amorino_slot_times()[ $time ] ) ) {
		wc_add_notice( __( 'Choisissez un jour et une heure de retrait.', 'storefront-child' ), 'error' );
	} elseif ( strtotime( "$day $time" ) < current_time( 'timestamp' ) + 15 * MINUTE_IN_SECONDS ) {
		wc_add_notice( __( 'Ce créneau est passé : choisissez-en un au moins 15 minutes plus tard.', 'storefront-child' ), 'error' );
	}
} );
add_action( 'woocommerce_checkout_create_order', function ( $order ) {
	// phpcs:disable WordPress.Security.NonceVerification
	$day  = sanitize_text_field( wp_unslash( $_POST['amorino_day'] ?? '' ) );
	$time = sanitize_text_field( wp_unslash( $_POST['amorino_time'] ?? '' ) );
	// phpcs:enable
	if ( $day && $time ) {
		$order->update_meta_data( '_amorino_slot', $day . ' ' . $time );
	}
} );
function amorino_slot_label( $order ) {
	$slot = $order->get_meta( '_amorino_slot' );
	if ( ! $slot ) {
		return '';
	}
	list( $d, $t ) = explode( ' ', $slot );
	return ucfirst( wp_date( 'l j F', strtotime( $d ) ) ) . ', ' . ( amorino_slot_times()[ $t ] ?? $t );
}
add_action( 'woocommerce_admin_order_data_after_shipping_address', function ( $order ) {
	if ( $l = amorino_slot_label( $order ) ) {
		echo '<p><strong>' . esc_html__( 'Créneau de retrait', 'storefront-child' ) . ' :</strong> ' . esc_html( $l ) . '</p>';
	}
} );
add_action( 'woocommerce_order_details_after_order_table', function ( $order ) {
	if ( $l = amorino_slot_label( $order ) ) {
		echo '<p class="amorino-slot-recap"><strong>' . esc_html__( 'Créneau de retrait', 'storefront-child' ) . ' :</strong> ' . esc_html( $l ) . '</p>';
	}
} );
