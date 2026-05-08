/* global cbpOkaCalc */
( function () {
	'use strict';

	// ---------------------------------------------------------------------------
	// Config (injected via wp_localize_script as window.cbpOkaCalc)
	// ---------------------------------------------------------------------------
	const cfg = window.cbpOkaCalc;

	// ---------------------------------------------------------------------------
	// DOM refs
	// ---------------------------------------------------------------------------
	const root       = document.getElementById( 'cbp-okacalc' );
	if ( ! root ) return;

	const stepForm    = root.querySelector( '[data-step="form"]' );
	const stepResults = root.querySelector( '[data-step="results"]' );
	const stepThankyou = root.querySelector( '[data-step="thankyou"]' );
	const loading     = root.querySelector( '.cbp-okacalc__loading' );
	const form        = root.querySelector( '#cbp-okacalc-form' );
	const resultsBody = root.querySelector( '#cbp-results-body' );
	const resultsIntro = root.querySelector( '.cbp-okacalc__results-intro' );
	const formError   = root.querySelector( '[data-error="form"]' );

	// ---------------------------------------------------------------------------
	// Helpers
	// ---------------------------------------------------------------------------

	function showStep( name ) {
		[ stepForm, stepResults, stepThankyou ].forEach( function ( el ) {
			if ( ! el ) return;
			el.hidden = el.dataset.step !== name;
		} );
	}

	function setLoading( on ) {
		if ( loading ) loading.hidden = ! on;
	}

	function getFieldError( el ) {
		return root.querySelector( '[data-error-for="' + el.name + '"]' );
	}

	function showFieldError( el, msg ) {
		const errEl = getFieldError( el );
		if ( errEl ) errEl.textContent = msg;
		el.setAttribute( 'aria-invalid', 'true' );
	}

	function clearFieldError( el ) {
		const errEl = getFieldError( el );
		if ( errEl ) errEl.textContent = '';
		el.removeAttribute( 'aria-invalid' );
	}

	function clearAllErrors() {
		root.querySelectorAll( '[data-error-for]' ).forEach( function ( el ) {
			el.textContent = '';
		} );
		root.querySelectorAll( '[aria-invalid]' ).forEach( function ( el ) {
			el.removeAttribute( 'aria-invalid' );
		} );
		if ( formError ) formError.textContent = '';
	}

	function isValidEmail( val ) {
		return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test( val );
	}

	function isValidPhone( val ) {
		return /^[+\d][\d\s\-().]{6,}$/.test( val.trim() );
	}

	function validate( data ) {
		let valid = true;

		function require( el, msg ) {
			if ( ! el.value.trim() ) {
				showFieldError( el, msg || cfg.i18n.requiredField );
				valid = false;
			} else {
				clearFieldError( el );
			}
		}

		const fields = {
			workers   : form.querySelector( '[name="workers"]' ),
			country   : form.querySelector( '[name="country"]' ),
			firstName : form.querySelector( '[name="firstName"]' ),
			lastName  : form.querySelector( '[name="lastName"]' ),
			email     : form.querySelector( '[name="email"]' ),
			phone     : form.querySelector( '[name="phone"]' ),
			company   : form.querySelector( '[name="company"]' ),
		};

		// Workers
		if ( ! fields.workers.value || parseInt( fields.workers.value, 10 ) < 1 ) {
			showFieldError( fields.workers, cfg.i18n.requiredField );
			valid = false;
		} else {
			clearFieldError( fields.workers );
		}

		require( fields.country );
		require( fields.firstName );
		require( fields.lastName );
		require( fields.company );

		// Email
		if ( ! fields.email.value.trim() ) {
			showFieldError( fields.email, cfg.i18n.requiredField );
			valid = false;
		} else if ( ! isValidEmail( fields.email.value ) ) {
			showFieldError( fields.email, cfg.i18n.invalidEmail );
			valid = false;
		} else {
			clearFieldError( fields.email );
		}

		// Phone
		if ( ! fields.phone.value.trim() ) {
			showFieldError( fields.phone, cfg.i18n.requiredField );
			valid = false;
		} else if ( ! isValidPhone( fields.phone.value ) ) {
			showFieldError( fields.phone, cfg.i18n.invalidPhone );
			valid = false;
		} else {
			clearFieldError( fields.phone );
		}

		return valid;
	}

	// ---------------------------------------------------------------------------
	// Pricing logic
	// ---------------------------------------------------------------------------

	/**
	 * Resolve ISO 3166-1 alpha-2 country code to a currency code.
	 * Falls back to USD.
	 */
	function getCurrency( countryCode ) {
		return cfg.currency[ countryCode ] || 'USD';
	}

	/**
	 * Determine total discount % for a given worker count.
	 * Tenure 12m discount is always applied.
	 */
	function getTotalDiscount( workers ) {
		const tenure = cfg.discounts.tenure12m;
		let volume = 0;

		if ( workers >= 10 && workers <= 50 ) {
			volume = cfg.discounts.volumeT1;
		} else if ( workers >= 51 ) {
			volume = cfg.discounts.volumeT2;
		}

		// Discounts are additive per the pricing model.
		return tenure + volume;
	}

	/**
	 * Calculate per-worker and total price for a single plan.
	 *
	 * @param {number}      basePrice  Base price in the given currency.
	 * @param {number}      workers    Number of workers.
	 * @param {number}      discount   Total discount as a percentage (e.g. 10 for 10%).
	 * @returns {{ perWorker: number, totalMonthly: number, totalAnnual: number }}
	 */
	function calcPlan( basePrice, workers, discount ) {
		const perWorker    = basePrice * ( 1 - discount / 100 );
		const totalMonthly = perWorker * workers;
		const totalAnnual  = totalMonthly * 12;
		return {
			perWorker    : round2( perWorker ),
			totalMonthly : round2( totalMonthly ),
			totalAnnual  : round2( totalAnnual ),
		};
	}

	function round2( n ) {
		return Math.round( n * 100 ) / 100;
	}

	function formatMoney( amount, symbol ) {
		return symbol + amount.toFixed( 2 );
	}

	// ---------------------------------------------------------------------------
	// Results rendering
	// ---------------------------------------------------------------------------

	const planMeta = [
		{ key: 'essential', label: 'Essential Protection',      recommended: true  },
		{ key: 'automated', label: 'Automated Monitoring',      recommended: false },
		{ key: 'emergency', label: '24/7 Emergency Response',   recommended: false },
	];

	function renderResults( workers, currency ) {
		const symbol   = cfg.symbols[ currency ] || '$';
		const discount = getTotalDiscount( workers );

		resultsIntro.textContent =
			workers + ' worker' + ( workers !== 1 ? 's' : '' ) +
			' \u2022 ' + symbol.trim() + ' currency' +
			' \u2022 ' + discount + '% discount applied';

		resultsBody.innerHTML = '';

		planMeta.forEach( function ( plan ) {
			const basePrice = cfg.prices[ plan.key ][ currency ];
			const tr        = document.createElement( 'tr' );

			if ( plan.recommended ) tr.classList.add( 'cbp-okacalc__row--recommended' );

			// Plan name cell
			const tdName = document.createElement( 'td' );
			tdName.textContent = plan.label;
			if ( plan.recommended ) {
				const badge = document.createElement( 'span' );
				badge.className   = 'cbp-okacalc__badge';
				badge.textContent = 'Recommended';
				tdName.appendChild( badge );
			}
			tr.appendChild( tdName );

			// N/A for emergency in GBP
			if ( basePrice === null || basePrice === undefined ) {
				const naCell = document.createElement( 'td' );
				naCell.setAttribute( 'colspan', '3' );
				naCell.className   = 'cbp-okacalc__na';
				naCell.textContent = cfg.i18n.notAvailable;
				tr.appendChild( naCell );
			} else {
				const result = calcPlan( basePrice, workers, discount );

				[ result.perWorker, result.totalMonthly, result.totalAnnual ].forEach( function ( val ) {
					const td       = document.createElement( 'td' );
					td.textContent = formatMoney( val, symbol );
					tr.appendChild( td );
				} );
			}

			resultsBody.appendChild( tr );
		} );
	}

	// ---------------------------------------------------------------------------
	// Form data collection
	// ---------------------------------------------------------------------------

	function collectFormData() {
		const fd = new FormData( form );
		const data = {};
		fd.forEach( function ( val, key ) {
			data[ key ] = val;
		} );
		return data;
	}

	// ---------------------------------------------------------------------------
	// Webhook POST (large accounts)
	// ---------------------------------------------------------------------------

	function postWebhook( data ) {
		const body = new FormData();
		Object.keys( data ).forEach( function ( k ) { body.append( k, data[ k ] ); } );
		body.append( 'action', 'cbp_okacalc_webhook' );
		body.append( 'nonce',  cfg.nonce );

		return fetch( cfg.ajaxUrl, {
			method      : 'POST',
			credentials : 'same-origin',
			body        : body,
		} ).then( function ( res ) {
			if ( ! res.ok ) throw new Error( 'Network response was not ok' );
			return res.json();
		} );
	}

	// ---------------------------------------------------------------------------
	// Form submit handler
	// ---------------------------------------------------------------------------

	form.addEventListener( 'submit', function ( e ) {
		e.preventDefault();
		clearAllErrors();

		const data = collectFormData();

		if ( ! validate( data ) ) return;

		const workers  = parseInt( data.workers, 10 );
		const currency = getCurrency( data.country );

		if ( workers >= cfg.threshold ) {
			// Large account path — fire webhook
			setLoading( true );
			postWebhook( data )
				.then( function () {
					showStep( 'thankyou' );
				} )
				.catch( function () {
					if ( formError ) formError.textContent = cfg.i18n.submitError;
				} )
				.finally( function () {
					setLoading( false );
				} );
		} else {
			// Pricing calculator path
			renderResults( workers, currency );
			showStep( 'results' );
		}
	} );

	// ---------------------------------------------------------------------------
	// Reset / recalculate buttons
	// ---------------------------------------------------------------------------

	const recalcBtn   = root.querySelector( '#cbp-recalculate' );
	const startOverBtn = root.querySelector( '#cbp-start-over' );

	if ( recalcBtn ) {
		recalcBtn.addEventListener( 'click', function () {
			showStep( 'form' );
		} );
	}

	if ( startOverBtn ) {
		startOverBtn.addEventListener( 'click', function () {
			form.reset();
			clearAllErrors();
			showStep( 'form' );
		} );
	}

	// ---------------------------------------------------------------------------
	// Init
	// ---------------------------------------------------------------------------
	showStep( 'form' );

} )();
