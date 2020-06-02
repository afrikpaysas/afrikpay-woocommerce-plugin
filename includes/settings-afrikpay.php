<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings for PayPal Gateway.
 */
return array(
	'enabled' => array(
		'title'   => __( 'Enable/Disable', 'woocommerce' ),
		'type'    => 'checkbox',
		'label'   => __( 'Enable Afrikpay', 'woocommerce' ),
		'default' => 'no',
	),
	'title' => array(
		'title'       => __( 'Title', 'woocommerce' ),
		'type'        => 'text',
		'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
		'default'     => __( 'Afrikpay', 'woocommerce' ),
		'desc_tip'    => true,
	),
	'description' => array(
		'title'       => __( 'Description', 'woocommerce' ),
		'type'        => 'text',
		'desc_tip'    => true,
		'description' => __( 'This controls the description which the user sees during checkout.', 'woocommerce' ),
		'default'     => __( 'Payer avec Afrikpay;', 'woocommerce' ),
	),
	'store' => array(
		'title'       => __( 'AfrikEcommerce Store Code', 'woocommerce' ),
		'type'        => 'text',
		'desc_tip'    => true,
		'description' => __( 'Please enter your AfrikEcommerce Store Code; this is needed in order to take payment.', 'woocommerce' ),
	),
	'urlafrikpay' => array(
		'title'       => __( 'URL Afrikpay', 'woocommerce' ),
		'type'        => 'text',
		'desc_tip'    => true,		
		'description' => __( 'URL Afrikpay', 'woocommerce' ),
	),
);
