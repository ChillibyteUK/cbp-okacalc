<?php
/**
 * Admin settings page for OK Alone Pricing Calculator.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_menu', 'cbp_okacalc_admin_menu' );
add_action( 'admin_init', 'cbp_okacalc_admin_init' );
add_action( 'admin_enqueue_scripts', 'cbp_okacalc_admin_assets' );

function cbp_okacalc_admin_menu() {
	add_options_page(
		__( 'OK Alone Calculator', 'cbp-okacalc' ),
		__( 'OK Alone Calculator', 'cbp-okacalc' ),
		'manage_options',
		'cbp-okacalc',
		'cbp_okacalc_admin_page'
	);
}

function cbp_okacalc_admin_init() {
	// Only register the setting — we render the fields manually.
	register_setting(
		'cbp_okacalc_group',
		CBP_OKACALC_OPTION_KEY,
		array(
			'sanitize_callback' => 'cbp_okacalc_sanitize_settings',
		)
	);
}

function cbp_okacalc_admin_assets( $hook ) {
	if ( 'settings_page_cbp-okacalc' !== $hook ) {
		return;
	}
	wp_add_inline_style( 'wp-admin', cbp_okacalc_admin_css() );
}

// ---------------------------------------------------------------------------
// Sanitization
// ---------------------------------------------------------------------------

function cbp_okacalc_sanitize_settings( $input ) {
	$clean = array();

	$float_keys = array(
		'price_essential_usd', 'price_essential_gbp', 'price_essential_cad',
		'price_automated_usd', 'price_automated_gbp', 'price_automated_cad',
		'price_emergency_usd', 'price_emergency_cad',
	);

	$percent_keys = array(
		'discount_tenure_12m', 'discount_tenure_24m',
		'discount_volume_t1', 'discount_volume_t2',
	);

	foreach ( $float_keys as $key ) {
		$clean[ $key ] = isset( $input[ $key ] )
			? number_format( (float) $input[ $key ], 2, '.', '' )
			: '0.00';
	}

	foreach ( $percent_keys as $key ) {
		$val           = isset( $input[ $key ] ) ? (float) $input[ $key ] : 0;
		$clean[ $key ] = (string) max( 0, min( 100, $val ) );
	}

	$clean['threshold']   = isset( $input['threshold'] ) ? absint( $input['threshold'] ) : 100;
	$clean['webhook_url'] = isset( $input['webhook_url'] ) ? esc_url_raw( $input['webhook_url'] ) : '';

	return $clean;
}

// ---------------------------------------------------------------------------
// Inline admin CSS
// ---------------------------------------------------------------------------

function cbp_okacalc_admin_css() {
	return '
/* ---- OK Alone Calculator admin page ---- */
.cbp-admin { max-width: 860px; }
.cbp-admin h1 { margin-bottom: 0.25em; }
.cbp-admin .cbp-admin-subtitle {
	color: #646970;
	font-size: 13px;
	margin-top: 0;
	margin-bottom: 1.75em;
}

/* Cards */
.cbp-card {
	background: #fff;
	border: 1px solid #c3c4c7;
	border-radius: 4px;
	padding: 0;
	margin-bottom: 20px;
	box-shadow: 0 1px 2px rgba(0,0,0,.04);
}
.cbp-card__header {
	padding: 14px 20px;
	border-bottom: 1px solid #e6e6e6;
	display: flex;
	align-items: baseline;
	gap: 12px;
}
.cbp-card__header h2 {
	font-size: 14px;
	font-weight: 600;
	margin: 0;
	padding: 0;
	color: #1d2327;
}
.cbp-card__header p {
	margin: 0;
	font-size: 12px;
	color: #646970;
}
.cbp-card__body { padding: 20px; }

/* Price plan grid */
.cbp-plan-grid {
	display: grid;
	grid-template-columns: 1fr 1fr 1fr;
	gap: 16px;
}
.cbp-plan-col {
	background: #f6f7f7;
	border: 1px solid #e6e6e6;
	border-radius: 4px;
	padding: 14px 16px;
}
.cbp-plan-col__title {
	font-size: 12px;
	font-weight: 700;
	text-transform: uppercase;
	letter-spacing: .04em;
	color: #646970;
	margin: 0 0 12px;
}
.cbp-plan-col--emergency { opacity: 1; }

/* Currency rows inside a plan column */
.cbp-currency-row {
	display: flex;
	align-items: center;
	gap: 8px;
	margin-bottom: 10px;
}
.cbp-currency-row:last-child { margin-bottom: 0; }
.cbp-currency-row label {
	width: 48px;
	font-size: 12px;
	font-weight: 600;
	color: #1d2327;
	flex-shrink: 0;
}
.cbp-currency-row input[type="number"] {
	width: 90px !important;
	flex-shrink: 0;
}
.cbp-currency-row .cbp-na {
	font-size: 12px;
	color: #999;
	font-style: italic;
}

/* Discount + general rows */
.cbp-field-rows { display: flex; flex-direction: column; gap: 14px; }
.cbp-field-row {
	display: grid;
	grid-template-columns: 280px 1fr;
	align-items: center;
	gap: 16px;
}
.cbp-field-row label {
	font-size: 13px;
	font-weight: 500;
	color: #1d2327;
}
.cbp-field-row .cbp-field-desc {
	font-size: 12px;
	color: #646970;
	margin-top: 2px;
}
.cbp-field-row-inner {
	display: flex;
	align-items: center;
	gap: 8px;
}
.cbp-field-row-inner input[type="number"] { width: 80px !important; }
.cbp-field-row-inner input[type="url"] { width: 340px; }
.cbp-field-row-inner .cbp-unit {
	font-size: 13px;
	color: #646970;
}
.cbp-field-divider {
	border: none;
	border-top: 1px solid #e6e6e6;
	margin: 4px 0;
}

/* Shortcode box */
.cbp-shortcode-box {
	display: flex;
	align-items: center;
	gap: 12px;
	background: #f6f7f7;
	border: 1px solid #e6e6e6;
	border-radius: 4px;
	padding: 12px 16px;
}
.cbp-shortcode-box code {
	font-size: 14px;
	background: none;
	padding: 0;
	color: #1d2327;
}
.cbp-shortcode-box p {
	margin: 0;
	font-size: 12px;
	color: #646970;
}

/* Save bar */
.cbp-save-bar {
	display: flex;
	align-items: center;
	gap: 16px;
	margin-top: 8px;
}

@media ( max-width: 782px ) {
	.cbp-plan-grid { grid-template-columns: 1fr; }
	.cbp-field-row { grid-template-columns: 1fr; gap: 4px; }
}
';
}

// ---------------------------------------------------------------------------
// Page render
// ---------------------------------------------------------------------------

function cbp_okacalc_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$s   = cbp_okacalc_get_settings();
	$opt = CBP_OKACALC_OPTION_KEY;

	// Helper: render a currency number input.
	$price_input = function( $key, $label, $disabled = false ) use ( $s, $opt ) {
		if ( $disabled ) {
			echo '<div class="cbp-currency-row">';
			echo '<label>' . esc_html( $label ) . '</label>';
			echo '<span class="cbp-na">Not available</span>';
			echo '</div>';
			return;
		}
		$val = isset( $s[ $key ] ) ? $s[ $key ] : '0.00';
		echo '<div class="cbp-currency-row">';
		printf( '<label for="cbp-%s">%s</label>', esc_attr( $key ), esc_html( $label ) );
		printf(
			'<input type="number" id="cbp-%s" name="%s[%s]" value="%s" step="0.01" min="0" />',
			esc_attr( $key ),
			esc_attr( $opt ),
			esc_attr( $key ),
			esc_attr( $val )
		);
		echo '</div>';
	};

	// Helper: render a percent input row.
	$pct_row = function( $key, $label, $desc = '' ) use ( $s, $opt ) {
		$val = isset( $s[ $key ] ) ? $s[ $key ] : '0';
		echo '<div class="cbp-field-row">';
		echo '<div>';
		printf( '<label for="cbp-%s">%s</label>', esc_attr( $key ), esc_html( $label ) );
		if ( $desc ) {
			printf( '<div class="cbp-field-desc">%s</div>', esc_html( $desc ) );
		}
		echo '</div>';
		echo '<div class="cbp-field-row-inner">';
		printf(
			'<input type="number" id="cbp-%s" name="%s[%s]" value="%s" step="0.1" min="0" max="100" />',
			esc_attr( $key ),
			esc_attr( $opt ),
			esc_attr( $key ),
			esc_attr( $val )
		);
		echo '<span class="cbp-unit">%</span>';
		echo '</div>';
		echo '</div>';
	};
	?>
	<div class="wrap cbp-admin">

		<h1><?php esc_html_e( 'OK Alone Pricing Calculator', 'cbp-okacalc' ); ?></h1>
		<p class="cbp-admin-subtitle"><?php esc_html_e( 'Configure plan prices, discounts, and lead routing.', 'cbp-okacalc' ); ?></p>

		<form method="post" action="options.php">
			<?php settings_fields( 'cbp_okacalc_group' ); ?>

			<?php /* ---- Base Prices ---- */ ?>
			<div class="cbp-card">
				<div class="cbp-card__header">
					<h2><?php esc_html_e( 'Base Prices', 'cbp-okacalc' ); ?></h2>
					<p><?php esc_html_e( 'Per worker / month, before discounts.', 'cbp-okacalc' ); ?></p>
				</div>
				<div class="cbp-card__body">
					<div class="cbp-plan-grid">

						<div class="cbp-plan-col">
							<p class="cbp-plan-col__title"><?php esc_html_e( 'Essential Protection', 'cbp-okacalc' ); ?></p>
							<?php
							$price_input( 'price_essential_usd', 'USD $' );
							$price_input( 'price_essential_gbp', 'GBP £' );
							$price_input( 'price_essential_cad', 'CAD $' );
							?>
						</div>

						<div class="cbp-plan-col">
							<p class="cbp-plan-col__title"><?php esc_html_e( 'Automated Monitoring', 'cbp-okacalc' ); ?></p>
							<?php
							$price_input( 'price_automated_usd', 'USD $' );
							$price_input( 'price_automated_gbp', 'GBP £' );
							$price_input( 'price_automated_cad', 'CAD $' );
							?>
						</div>

						<div class="cbp-plan-col cbp-plan-col--emergency">
							<p class="cbp-plan-col__title"><?php esc_html_e( '24/7 Emergency Response', 'cbp-okacalc' ); ?></p>
							<?php
							$price_input( 'price_emergency_usd', 'USD $' );
							$price_input( 'price_emergency_gbp', 'GBP £', true ); // Not available in UK
							$price_input( 'price_emergency_cad', 'CAD $' );
							?>
						</div>

					</div>
				</div>
			</div>

			<?php /* ---- Discounts ---- */ ?>
			<div class="cbp-card">
				<div class="cbp-card__header">
					<h2><?php esc_html_e( 'Discounts', 'cbp-okacalc' ); ?></h2>
					<p><?php esc_html_e( 'The 12-month tenure discount is always applied. Volume discounts stack on top.', 'cbp-okacalc' ); ?></p>
				</div>
				<div class="cbp-card__body">
					<div class="cbp-field-rows">

						<p style="margin:0 0 4px;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#646970;">
							<?php esc_html_e( 'Tenure', 'cbp-okacalc' ); ?>
						</p>
						<?php
						$pct_row( 'discount_tenure_12m', '12-month commitment', 'Always applied.' );
						$pct_row( 'discount_tenure_24m', '24-month commitment', 'Reserved for future use — not currently applied by the calculator.' );
						?>

						<hr class="cbp-field-divider" />

						<p style="margin:0 0 4px;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.04em;color:#646970;">
							<?php esc_html_e( 'Volume', 'cbp-okacalc' ); ?>
						</p>
						<?php
						$pct_row( 'discount_volume_t1', 'Tier 1 — 10 to 50 workers' );
						$pct_row( 'discount_volume_t2', 'Tier 2 — 51 to 99 workers' );
						?>

					</div>
				</div>
			</div>

			<?php /* ---- General ---- */ ?>
			<div class="cbp-card">
				<div class="cbp-card__header">
					<h2><?php esc_html_e( 'General', 'cbp-okacalc' ); ?></h2>
				</div>
				<div class="cbp-card__body">
					<div class="cbp-field-rows">

						<div class="cbp-field-row">
							<div>
								<label for="cbp-threshold"><?php esc_html_e( 'Large account threshold', 'cbp-okacalc' ); ?></label>
								<div class="cbp-field-desc"><?php esc_html_e( 'Submissions at or above this worker count are sent to the webhook instead of showing a price.', 'cbp-okacalc' ); ?></div>
							</div>
							<div class="cbp-field-row-inner">
								<input
									type="number"
									id="cbp-threshold"
									name="<?php echo esc_attr( $opt ); ?>[threshold]"
									value="<?php echo esc_attr( $s['threshold'] ); ?>"
									step="1"
									min="1"
								/>
								<span class="cbp-unit"><?php esc_html_e( 'workers', 'cbp-okacalc' ); ?></span>
							</div>
						</div>

						<hr class="cbp-field-divider" />

						<div class="cbp-field-row">
							<div>
								<label for="cbp-webhook-url"><?php esc_html_e( 'Webhook URL', 'cbp-okacalc' ); ?></label>
								<div class="cbp-field-desc"><?php esc_html_e( 'Large account form data is POSTed here as JSON. Leave blank to disable.', 'cbp-okacalc' ); ?></div>
							</div>
							<div class="cbp-field-row-inner">
								<input
									type="url"
									id="cbp-webhook-url"
									name="<?php echo esc_attr( $opt ); ?>[webhook_url]"
									value="<?php echo esc_attr( $s['webhook_url'] ); ?>"
									placeholder="https://…"
								/>
							</div>
						</div>

					</div>
				</div>
			</div>

			<?php /* ---- Save ---- */ ?>
			<div class="cbp-save-bar">
				<?php submit_button( __( 'Save Changes', 'cbp-okacalc' ), 'primary', 'submit', false ); ?>
				<?php if ( isset( $_GET['settings-updated'] ) ) : ?>
					<span style="color:#00a32a;font-size:13px;">&#10003; <?php esc_html_e( 'Settings saved.', 'cbp-okacalc' ); ?></span>
				<?php endif; ?>
			</div>

		</form>

		<?php /* ---- Shortcode ---- */ ?>
		<div class="cbp-card" style="margin-top:28px;">
			<div class="cbp-card__header">
				<h2><?php esc_html_e( 'Shortcode', 'cbp-okacalc' ); ?></h2>
			</div>
			<div class="cbp-card__body">
				<div class="cbp-shortcode-box">
					<code>[cbp_okacalc]</code>
					<p><?php esc_html_e( 'Paste this shortcode into any page or post to embed the pricing calculator.', 'cbp-okacalc' ); ?></p>
				</div>
			</div>
		</div>

	</div><!-- /.cbp-admin -->
	<?php
}
