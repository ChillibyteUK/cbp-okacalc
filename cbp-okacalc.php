<?php
/**
 * Plugin Name: OK Alone Pricing Calculator
 * Plugin URI:  https://okalone.net
 * Description: Embeddable pricing calculator for OK Alone lone worker monitoring plans.
 * Version:     1.0.0
 * Author:      Chillibyte
 * Text Domain: cbp-okacalc
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'CBP_OKACALC_VERSION', '1.0.0' );
define( 'CBP_OKACALC_DIR', plugin_dir_path( __FILE__ ) );
define( 'CBP_OKACALC_URL', plugin_dir_url( __FILE__ ) );
define( 'CBP_OKACALC_OPTION_KEY', 'cbp_okacalc_settings' );

require_once CBP_OKACALC_DIR . 'includes/admin.php';
require_once CBP_OKACALC_DIR . 'includes/calculator.php';

/**
 * Return plugin settings with defaults merged in.
 *
 * @return array
 */
function cbp_okacalc_get_settings() {
	$defaults = array(
		// Base prices — Essential
		'price_essential_usd'  => '10.00',
		'price_essential_gbp'  => '4.99',
		'price_essential_cad'  => '10.00',
		// Base prices — Automated Monitoring
		'price_automated_usd'  => '15.00',
		'price_automated_gbp'  => '6.99',
		'price_automated_cad'  => '15.00',
		// Base prices — 24/7 Emergency Response (no GBP)
		'price_emergency_usd'  => '20.00',
		'price_emergency_cad'  => '20.00',
		// Tenure discounts (%)
		'discount_tenure_12m'  => '5',
		'discount_tenure_24m'  => '10',
		// Volume discount tiers (%)
		'discount_volume_t1'   => '5',   // 10–50 workers
		'discount_volume_t2'   => '10',  // 51–99 workers
		// General
		'webhook_url'          => '',
		'threshold'            => '100',
	);

	$saved = get_option( CBP_OKACALC_OPTION_KEY, array() );
	return wp_parse_args( $saved, $defaults );
}
