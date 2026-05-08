<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div id="cbp-okacalc" class="cbp-okacalc" role="main" aria-live="polite">

	<!-- Step 1: Form -->
	<div class="cbp-okacalc__step cbp-okacalc__step--form" data-step="form">

		<form id="cbp-okacalc-form" class="cbp-okacalc__form" novalidate>

			<div class="cbp-okacalc__field">
				<label for="cbp-workers"><?php esc_html_e( 'Number of Lone Workers', 'cbp-okacalc' ); ?> <span aria-hidden="true">*</span></label>
				<input
					type="number"
					id="cbp-workers"
					name="workers"
					min="1"
					step="1"
					required
					autocomplete="off"
					placeholder="e.g. 25"
				/>
				<span class="cbp-okacalc__error" data-error-for="workers" role="alert"></span>
			</div>

			<div class="cbp-okacalc__field">
				<label for="cbp-country"><?php esc_html_e( 'Country', 'cbp-okacalc' ); ?> <span aria-hidden="true">*</span></label>
				<select id="cbp-country" name="country" required>
					<option value=""><?php esc_html_e( '— Select country —', 'cbp-okacalc' ); ?></option>
					<option value="US"><?php esc_html_e( 'United States', 'cbp-okacalc' ); ?></option>
					<option value="GB"><?php esc_html_e( 'United Kingdom', 'cbp-okacalc' ); ?></option>
					<option value="CA"><?php esc_html_e( 'Canada', 'cbp-okacalc' ); ?></option>
					<option value="AU"><?php esc_html_e( 'Australia', 'cbp-okacalc' ); ?></option>
					<option value="NZ"><?php esc_html_e( 'New Zealand', 'cbp-okacalc' ); ?></option>
					<option value="IE"><?php esc_html_e( 'Ireland', 'cbp-okacalc' ); ?></option>
					<option value="ZA"><?php esc_html_e( 'South Africa', 'cbp-okacalc' ); ?></option>
					<option value="SG"><?php esc_html_e( 'Singapore', 'cbp-okacalc' ); ?></option>
					<option value="AE"><?php esc_html_e( 'United Arab Emirates', 'cbp-okacalc' ); ?></option>
					<option value="OTHER"><?php esc_html_e( 'Other', 'cbp-okacalc' ); ?></option>
				</select>
				<span class="cbp-okacalc__error" data-error-for="country" role="alert"></span>
			</div>

			<div class="cbp-okacalc__field">
				<label for="cbp-first-name"><?php esc_html_e( 'First Name', 'cbp-okacalc' ); ?> <span aria-hidden="true">*</span></label>
				<input type="text" id="cbp-first-name" name="firstName" required autocomplete="given-name" />
				<span class="cbp-okacalc__error" data-error-for="firstName" role="alert"></span>
			</div>

			<div class="cbp-okacalc__field">
				<label for="cbp-last-name"><?php esc_html_e( 'Last Name', 'cbp-okacalc' ); ?> <span aria-hidden="true">*</span></label>
				<input type="text" id="cbp-last-name" name="lastName" required autocomplete="family-name" />
				<span class="cbp-okacalc__error" data-error-for="lastName" role="alert"></span>
			</div>

			<div class="cbp-okacalc__field">
				<label for="cbp-email"><?php esc_html_e( 'Email Address', 'cbp-okacalc' ); ?> <span aria-hidden="true">*</span></label>
				<input type="email" id="cbp-email" name="email" required autocomplete="email" />
				<span class="cbp-okacalc__error" data-error-for="email" role="alert"></span>
			</div>

			<div class="cbp-okacalc__field">
				<label for="cbp-phone"><?php esc_html_e( 'Phone Number', 'cbp-okacalc' ); ?> <span aria-hidden="true">*</span></label>
				<input type="tel" id="cbp-phone" name="phone" required autocomplete="tel" />
				<span class="cbp-okacalc__error" data-error-for="phone" role="alert"></span>
			</div>

			<div class="cbp-okacalc__field">
				<label for="cbp-company"><?php esc_html_e( 'Company Name', 'cbp-okacalc' ); ?> <span aria-hidden="true">*</span></label>
				<input type="text" id="cbp-company" name="company" required autocomplete="organization" />
				<span class="cbp-okacalc__error" data-error-for="company" role="alert"></span>
			</div>

			<div class="cbp-okacalc__field cbp-okacalc__field--submit">
				<button type="submit" class="cbp-okacalc__btn cbp-okacalc__btn--primary">
					<?php esc_html_e( 'Get My Price', 'cbp-okacalc' ); ?>
				</button>
			</div>

			<p class="cbp-okacalc__form-error" data-error="form" role="alert"></p>

		</form>

	</div><!-- /.step--form -->

	<!-- Step 2a: Results (< threshold workers) -->
	<div class="cbp-okacalc__step cbp-okacalc__step--results" data-step="results" hidden>

		<p class="cbp-okacalc__results-intro"></p>

		<div class="cbp-okacalc__results-table-wrap">
			<table class="cbp-okacalc__results-table">
				<thead>
					<tr>
						<th scope="col"><?php esc_html_e( 'Plan', 'cbp-okacalc' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Per Worker / Month', 'cbp-okacalc' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Total / Month', 'cbp-okacalc' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Total / Year', 'cbp-okacalc' ); ?></th>
					</tr>
				</thead>
				<tbody id="cbp-results-body">
					<!-- Populated by JS -->
				</tbody>
			</table>
		</div>

		<p class="cbp-okacalc__results-note">
			<?php esc_html_e( 'All prices shown include the 12-month commitment discount. Prices are per worker, per month.', 'cbp-okacalc' ); ?>
		</p>

		<div class="cbp-okacalc__results-actions">
			<button type="button" class="cbp-okacalc__btn cbp-okacalc__btn--secondary" id="cbp-recalculate">
				<?php esc_html_e( 'Recalculate', 'cbp-okacalc' ); ?>
			</button>
		</div>

	</div><!-- /.step--results -->

	<!-- Step 2b: Large account thank-you -->
	<div class="cbp-okacalc__step cbp-okacalc__step--thankyou" data-step="thankyou" hidden>

		<div class="cbp-okacalc__thankyou">
			<h2><?php esc_html_e( 'Thank you!', 'cbp-okacalc' ); ?></h2>
			<p><?php esc_html_e( 'One of our team will be in touch shortly with a tailored quote for your organisation.', 'cbp-okacalc' ); ?></p>
			<button type="button" class="cbp-okacalc__btn cbp-okacalc__btn--secondary" id="cbp-start-over">
				<?php esc_html_e( 'Start over', 'cbp-okacalc' ); ?>
			</button>
		</div>

	</div><!-- /.step--thankyou -->

	<!-- Loading overlay -->
	<div class="cbp-okacalc__loading" hidden aria-hidden="true">
		<span class="cbp-okacalc__spinner"></span>
	</div>

</div><!-- /#cbp-okacalc -->
