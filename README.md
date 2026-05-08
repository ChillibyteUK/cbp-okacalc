# CBP OK Alone Pricing Calculator

A WordPress plugin that embeds an interactive pricing calculator for [OK Alone](https://okaloneworker.com) lone worker monitoring plans. Submissions from large accounts are routed to a configurable webhook for sales follow-up; smaller accounts receive an instant price breakdown.

---

## Requirements

- WordPress 5.8+
- PHP 7.4+

---

## Installation

1. Copy the `cbp-okacalc` folder into `wp-content/plugins/`.
2. In the WordPress admin go to **Plugins** and activate **OK Alone Pricing Calculator**.
3. Go to **Settings → OK Alone Calculator** and configure prices, discounts, and the webhook URL.
4. Embed the calculator with the shortcode `[cbp_okacalc]`.

---

## Shortcode

```
[cbp_okacalc]
```

Paste this into any page, post, or widget area. No attributes are required.

---

## How it works

The calculator collects the following fields from the user:

| Field                  | Purpose                             |
| ---------------------- | ----------------------------------- |
| Number of Lone Workers | Determines pricing tier and routing |
| Country                | Determines currency                 |
| First Name             | Contact detail                      |
| Last Name              | Contact detail                      |
| Email                  | Contact detail                      |
| Phone                  | Contact detail                      |
| Company Name           | Contact detail                      |

### Submission routing

| Worker count                   | Outcome                                                                     |
| ------------------------------ | --------------------------------------------------------------------------- |
| Below threshold (default: 100) | Price is calculated and displayed immediately                               |
| At or above threshold          | Form data is POSTed to the configured webhook; a thank-you message is shown |

### Pricing logic

All prices are based on the plan base prices set in admin. The same discount structure is applied across all three plans.

**Currency** is resolved from the submitted country:

| Country             | Currency  |
| ------------------- | --------- |
| United Kingdom      | GBP (£)   |
| Canada              | CAD (CA$) |
| United States       | USD ($)   |
| All other countries | USD ($)   |

**Discounts** are additive:

| Workers | Tenure (12-month) | Volume | Total |
| ------- | ----------------- | ------ | ----- |
| 1–9     | 5%                | 0%     | 5%    |
| 10–50   | 5%                | 5%     | 10%   |
| 51–99   | 5%                | 10%    | 15%   |

The 12-month tenure discount is always applied. Volume discounts are based on worker count. All percentages are configurable in the admin settings.

**Results** show per-worker price, total monthly price, and total annual price for all three plans side by side. The 24/7 Emergency Response plan is marked as unavailable for UK submissions.

---

## Admin Settings

**Settings → OK Alone Calculator**

### Base Prices

Per-worker monthly base price for each plan, configurable per currency.

| Field                         | Default  |
| ----------------------------- | -------- |
| Essential — USD               | $10.00   |
| Essential — GBP               | £4.99    |
| Essential — CAD               | CA$10.00 |
| Automated Monitoring — USD    | $15.00   |
| Automated Monitoring — GBP    | £6.99    |
| Automated Monitoring — CAD    | CA$15.00 |
| 24/7 Emergency Response — USD | $20.00   |
| 24/7 Emergency Response — CAD | CA$20.00 |

> 24/7 Emergency Response is not available in the UK and has no GBP price field.

### Discounts

| Field                         | Default | Notes                                        |
| ----------------------------- | ------- | -------------------------------------------- |
| Tenure — 12-month             | 5%      | Always applied                               |
| Tenure — 24-month             | 10%     | Stored for future use, not currently applied |
| Volume Tier 1 (10–50 workers) | 5%      |                                              |
| Volume Tier 2 (51–99 workers) | 10%     |                                              |

### General

| Field                   | Default   | Notes                                                |
| ----------------------- | --------- | ---------------------------------------------------- |
| Large account threshold | 100       | Submissions at or above this count go to the webhook |
| Webhook URL             | _(empty)_ | Target URL for large account lead data               |

---

## Webhook payload

When a submission meets or exceeds the large account threshold, the plugin POSTs the following JSON to the configured webhook URL:

```json
{
  "workers": "120",
  "firstName": "Jane",
  "lastName": "Smith",
  "email": "jane@example.com",
  "phone": "+44 7700 900000",
  "company": "Acme Ltd",
  "country": "GB"
}
```

The request is sent server-side via `wp_remote_post` with a 15-second timeout. If no webhook URL is configured the submission will fail with an error message.

---

## File structure

```
cbp-okacalc/
├── cbp-okacalc.php          # Plugin bootstrap, constants, shared settings helper
├── includes/
│   ├── admin.php            # Admin settings page (Settings → OK Alone Calculator)
│   └── calculator.php       # [cbp_okacalc] shortcode and AJAX webhook handler
├── templates/
│   └── form.php             # HTML form template (form, results, and thank-you screens)
└── assets/
    ├── calculator.js        # Client-side pricing logic, validation, DOM management
    └── calculator.css       # Scoped front-end styles
```

---

## Styling

All front-end CSS is scoped under `.cbp-okacalc` and is intentionally light — layout, inputs, and focus states only. The form is designed to sit within any theme without conflict.

To override styles, target `.cbp-okacalc` and its children in your theme's stylesheet. No `!important` is used in the plugin CSS.

---

## Security

- The webhook AJAX endpoint is nonce-protected (`wp_create_nonce` / `check_ajax_referer`).
- All form inputs are sanitised server-side before being forwarded to the webhook.
- The settings page requires the `manage_options` capability.
- All output is escaped with `esc_html`, `esc_attr`, and `esc_url_raw` throughout.
