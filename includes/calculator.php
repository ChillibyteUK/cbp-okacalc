<?php
/**
 * Shortcode and asset registration for OK Alone Pricing Calculator.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_shortcode( 'cbp_okacalc', 'cbp_okacalc_shortcode' );
add_action( 'wp_enqueue_scripts', 'cbp_okacalc_enqueue_assets' );

function cbp_okacalc_enqueue_assets() {
	wp_register_style(
		'cbp-okacalc',
		CBP_OKACALC_URL . 'assets/calculator.css',
		array(),
		CBP_OKACALC_VERSION
	);

	wp_register_script(
		'cbp-okacalc',
		CBP_OKACALC_URL . 'assets/calculator.js',
		array(),
		CBP_OKACALC_VERSION,
		true
	);
}

function cbp_okacalc_shortcode( $atts ) {
	wp_enqueue_style( 'cbp-okacalc' );
	wp_enqueue_script( 'cbp-okacalc' );

	$settings = cbp_okacalc_get_settings();

	// Build the config object passed to JS.
	$js_config = array(
		'threshold'   => (int) $settings['threshold'],
		'webhookUrl'  => $settings['webhook_url'],
		'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
		'nonce'       => wp_create_nonce( 'cbp_okacalc_webhook' ),
		'currency'    => array(
			'CA'  => 'CAD',
			'GB'  => 'GBP',
			'US'  => 'USD',
		),
		'prices'      => array(
			'essential' => array(
				'USD' => (float) $settings['price_essential_usd'],
				'GBP' => (float) $settings['price_essential_gbp'],
				'CAD' => (float) $settings['price_essential_cad'],
			),
			'automated' => array(
				'USD' => (float) $settings['price_automated_usd'],
				'GBP' => (float) $settings['price_automated_gbp'],
				'CAD' => (float) $settings['price_automated_cad'],
			),
			'emergency' => array(
				'USD' => (float) $settings['price_emergency_usd'],
				'GBP' => null, // Not available in UK
				'CAD' => (float) $settings['price_emergency_cad'],
			),
		),
		'discounts'   => array(
			'tenure12m'  => (float) $settings['discount_tenure_12m'],
			'tenure24m'  => (float) $settings['discount_tenure_24m'],
			'volumeT1'   => (float) $settings['discount_volume_t1'],  // 10–50
			'volumeT2'   => (float) $settings['discount_volume_t2'],  // 51–99
		),
		'symbols'     => array(
			'USD' => '$',
			'GBP' => '£',
			'CAD' => 'CA$',
		),
		'i18n'        => array(
			'notAvailable'     => __( 'Not available', 'cbp-okacalc' ),
			'perWorker'        => __( 'Per worker / month', 'cbp-okacalc' ),
			'totalMonthly'     => __( 'Total / month', 'cbp-okacalc' ),
			'totalAnnual'      => __( 'Total / year', 'cbp-okacalc' ),
			'submitError'      => __( 'There was a problem submitting the form. Please try again.', 'cbp-okacalc' ),
			'requiredField'    => __( 'This field is required.', 'cbp-okacalc' ),
			'invalidEmail'     => __( 'Please enter a valid email address.', 'cbp-okacalc' ),
			'invalidPhone'     => __( 'Please enter a valid phone number.', 'cbp-okacalc' ),
		),
	);

	wp_localize_script( 'cbp-okacalc', 'cbpOkaCalc', $js_config );

	ob_start();
	include CBP_OKACALC_DIR . 'templates/form.php';
	return ob_get_clean();
}

// ---------------------------------------------------------------------------
// AJAX handler — proxy webhook POST for large accounts
// ---------------------------------------------------------------------------
add_action( 'wp_ajax_cbp_okacalc_webhook', 'cbp_okacalc_ajax_webhook' );
add_action( 'wp_ajax_nopriv_cbp_okacalc_webhook', 'cbp_okacalc_ajax_webhook' );

function cbp_okacalc_ajax_webhook() {
	check_ajax_referer( 'cbp_okacalc_webhook', 'nonce' );

	$settings    = cbp_okacalc_get_settings();
	$webhook_url = $settings['webhook_url'];

	if ( empty( $webhook_url ) ) {
		wp_send_json_error( array( 'message' => 'Webhook URL not configured.' ), 500 );
	}

	$allowed = array(
		'workers', 'firstName', 'lastName', 'email',
		'phone', 'company', 'country',
	);

	$payload = array();
	foreach ( $allowed as $field ) {
		$payload[ $field ] = isset( $_POST[ $field ] ) ? sanitize_text_field( wp_unslash( $_POST[ $field ] ) ) : '';
	}

	$response = wp_remote_post(
		$webhook_url,
		array(
			'headers'     => array( 'Content-Type' => 'application/json' ),
			'body'        => wp_json_encode( $payload ),
			'timeout'     => 15,
			'redirection' => 5,
		)
	);

	if ( is_wp_error( $response ) ) {
		wp_send_json_error( array( 'message' => $response->get_error_message() ), 502 );
	}

	wp_send_json_success( array( 'message' => 'Lead submitted.' ) );
}
