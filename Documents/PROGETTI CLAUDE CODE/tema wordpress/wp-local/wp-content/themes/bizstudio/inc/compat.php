<?php
/**
 * Plugin Compatibility
 *
 * CSS class mappings and compatibility overrides for popular plugins:
 * - Contact Form 7 (CF7)
 * - WPForms
 * - Gravity Forms
 * - Yoast SEO / Rank Math (Open Graph deduplication)
 *
 * This file contains only CSS overrides — no PHP dependency on any plugin.
 *
 * @package BizStudio
 * @since   1.0.0
 */

defined( 'ABSPATH' ) || exit;

/* ================================================================
   1. CONTACT FORM 7 — CSS MAPPING
   ================================================================ */

add_action( 'wp_enqueue_scripts', function () {
	// Only load if CF7 is active
	if ( ! defined( 'WPCF7_VERSION' ) ) {
		return;
	}

	$css = <<<CSS

/* ── BizStudio × Contact Form 7 ──────────────────────────── */

/* Container */
.wpcf7 {
	max-width: 640px;
}

/* Labels */
.wpcf7 label {
	display: block;
	font-size: 0.875rem;
	font-weight: 500;
	color: var(--biz-text);
	margin-bottom: 0.375rem;
}

/* Text inputs, email, URL, tel, number, date, textarea */
.wpcf7 .wpcf7-text,
.wpcf7 .wpcf7-email,
.wpcf7 .wpcf7-url,
.wpcf7 .wpcf7-tel,
.wpcf7 .wpcf7-number,
.wpcf7 .wpcf7-date,
.wpcf7 .wpcf7-textarea {
	width: 100%;
	padding: 0.75rem 1rem;
	border: 1.5px solid var(--biz-border);
	border-radius: var(--biz-radius);
	font-size: 0.9375rem;
	font-family: var(--biz-font);
	background: var(--biz-bg);
	color: var(--biz-text);
	transition: border-color var(--biz-fast), box-shadow var(--biz-fast);
}

.wpcf7 .wpcf7-text:focus,
.wpcf7 .wpcf7-email:focus,
.wpcf7 .wpcf7-url:focus,
.wpcf7 .wpcf7-tel:focus,
.wpcf7 .wpcf7-number:focus,
.wpcf7 .wpcf7-date:focus,
.wpcf7 .wpcf7-textarea:focus {
	outline: none;
	border-color: var(--biz-primary);
	box-shadow: 0 0 0 3px rgba(131, 103, 199, 0.12);
}

/* Select */
.wpcf7 .wpcf7-select {
	width: 100%;
	padding: 0.75rem 1rem;
	border: 1.5px solid var(--biz-border);
	border-radius: var(--biz-radius);
	font-size: 0.9375rem;
	font-family: var(--biz-font);
	background: var(--biz-bg);
	color: var(--biz-text);
	appearance: none;
	background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%23555570' viewBox='0 0 16 16'%3E%3Cpath d='M8 11L3 6h10l-5 5z'/%3E%3C/svg%3E");
	background-repeat: no-repeat;
	background-position: right 1rem center;
	padding-right: 2.5rem;
}

/* Submit button → BizStudio primary button */
.wpcf7 .wpcf7-submit {
	display: inline-flex;
	align-items: center;
	gap: 0.5rem;
	padding: 0.75rem 1.5rem;
	border: none;
	border-radius: var(--biz-radius);
	background: var(--biz-primary);
	color: #fff;
	font-weight: 600;
	font-size: 0.9375rem;
	font-family: var(--biz-font);
	cursor: pointer;
	transition: all var(--biz-base);
	white-space: nowrap;
}

.wpcf7 .wpcf7-submit:hover {
	background: var(--biz-primary-dark);
	transform: translateY(-1px);
	box-shadow: var(--biz-shadow-md);
}

/* Spacing between form rows */
.wpcf7 p {
	margin-bottom: 1.25rem;
}

/* Checkbox & radio */
.wpcf7 .wpcf7-checkbox label,
.wpcf7 .wpcf7-radio label {
	display: inline-flex;
	align-items: center;
	gap: 0.5rem;
	font-weight: 400;
	cursor: pointer;
}

/* Validation errors */
.wpcf7 .wpcf7-not-valid-tip {
	font-size: 0.8125rem;
	color: var(--biz-error);
	margin-top: 0.25rem;
}

.wpcf7 .wpcf7-response-output {
	padding: 1rem 1.25rem;
	margin: 1rem 0;
	border-radius: var(--biz-radius);
	font-size: 0.9375rem;
}

.wpcf7 .wpcf7-mail-sent-ok {
	background: rgba(46, 160, 67, 0.06);
	border: 1px solid var(--biz-success);
	color: var(--biz-success);
}

.wpcf7 .wpcf7-validation-errors,
.wpcf7 .wpcf7-mail-sent-ng {
	background: rgba(229, 56, 59, 0.06);
	border: 1px solid var(--biz-error);
	color: var(--biz-error);
}

/* File upload */
.wpcf7 .wpcf7-file {
	font-size: 0.875rem;
}

/* Acceptance / consent */
.wpcf7 .wpcf7-acceptance label {
	font-size: 0.8125rem;
	color: var(--biz-text-secondary);
}

CSS;

	wp_add_inline_style( 'contact-form-7', $css );
} );


/* ================================================================
   2. WPFORMS — CSS MAPPING
   ================================================================ */

add_action( 'wp_enqueue_scripts', function () {
	// Only load if WPForms is active
	if ( ! defined( 'WPFORMS_VERSION' ) ) {
		return;
	}

	$css = <<<CSS

/* ── BizStudio × WPForms ─────────────────────────────────── */

/* Container */
.wpforms-container {
	max-width: 640px;
}

/* Labels */
.wpforms-container .wpforms-field-label {
	font-size: 0.875rem;
	font-weight: 500;
	color: var(--biz-text);
	margin-bottom: 0.375rem;
}

.wpforms-container .wpforms-field-sublabel {
	font-size: 0.75rem;
	color: var(--biz-text-muted);
}

/* Inputs */
.wpforms-container input[type="text"],
.wpforms-container input[type="email"],
.wpforms-container input[type="url"],
.wpforms-container input[type="tel"],
.wpforms-container input[type="number"],
.wpforms-container input[type="password"],
.wpforms-container input[type="date"],
.wpforms-container textarea,
.wpforms-container select {
	width: 100%;
	padding: 0.75rem 1rem;
	border: 1.5px solid var(--biz-border);
	border-radius: var(--biz-radius);
	font-size: 0.9375rem;
	font-family: var(--biz-font);
	background: var(--biz-bg);
	color: var(--biz-text);
	transition: border-color var(--biz-fast), box-shadow var(--biz-fast);
}

.wpforms-container input:focus,
.wpforms-container textarea:focus,
.wpforms-container select:focus {
	outline: none;
	border-color: var(--biz-primary);
	box-shadow: 0 0 0 3px rgba(131, 103, 199, 0.12);
}

/* Submit button */
.wpforms-container .wpforms-submit-container button[type="submit"],
.wpforms-container .wpforms-submit {
	display: inline-flex;
	align-items: center;
	gap: 0.5rem;
	padding: 0.75rem 1.5rem;
	border: none;
	border-radius: var(--biz-radius);
	background: var(--biz-primary);
	color: #fff;
	font-weight: 600;
	font-size: 0.9375rem;
	font-family: var(--biz-font);
	cursor: pointer;
	transition: all var(--biz-base);
}

.wpforms-container .wpforms-submit-container button[type="submit"]:hover,
.wpforms-container .wpforms-submit:hover {
	background: var(--biz-primary-dark);
	transform: translateY(-1px);
	box-shadow: var(--biz-shadow-md);
}

/* Field spacing */
.wpforms-container .wpforms-field {
	margin-bottom: 1.25rem;
}

/* Errors */
.wpforms-container .wpforms-error {
	font-size: 0.8125rem;
	color: var(--biz-error);
}

.wpforms-container label.wpforms-error {
	font-weight: 400;
	margin-top: 0.25rem;
}

/* Confirmation message */
.wpforms-container .wpforms-confirmation-container-full {
	padding: 1rem 1.25rem;
	border-radius: var(--biz-radius);
	background: rgba(46, 160, 67, 0.06);
	border: 1px solid var(--biz-success);
	color: var(--biz-success);
}

CSS;

	wp_add_inline_style( 'wpforms-full', $css );

	// WPForms Lite uses a different handle
	if ( wp_style_is( 'wpforms-base', 'enqueued' ) ) {
		wp_add_inline_style( 'wpforms-base', $css );
	}
} );


/* ================================================================
   3. GRAVITY FORMS — CSS MAPPING
   ================================================================ */

add_action( 'wp_enqueue_scripts', function () {
	// Only load if Gravity Forms is active
	if ( ! class_exists( 'GFForms' ) ) {
		return;
	}

	$css = <<<CSS

/* ── BizStudio × Gravity Forms ────────────────────────────── */

/* Container */
.gform_wrapper {
	max-width: 640px;
}

/* Labels */
.gform_wrapper .gfield_label {
	font-size: 0.875rem;
	font-weight: 500;
	color: var(--biz-text);
	margin-bottom: 0.375rem;
}

.gform_wrapper .gfield_description {
	font-size: 0.8125rem;
	color: var(--biz-text-muted);
	margin-top: 0.25rem;
}

/* Inputs */
.gform_wrapper input[type="text"],
.gform_wrapper input[type="email"],
.gform_wrapper input[type="url"],
.gform_wrapper input[type="tel"],
.gform_wrapper input[type="number"],
.gform_wrapper input[type="password"],
.gform_wrapper textarea,
.gform_wrapper select {
	width: 100%;
	padding: 0.75rem 1rem;
	border: 1.5px solid var(--biz-border);
	border-radius: var(--biz-radius);
	font-size: 0.9375rem;
	font-family: var(--biz-font);
	background: var(--biz-bg);
	color: var(--biz-text);
	transition: border-color var(--biz-fast), box-shadow var(--biz-fast);
}

.gform_wrapper input:focus,
.gform_wrapper textarea:focus,
.gform_wrapper select:focus {
	outline: none;
	border-color: var(--biz-primary);
	box-shadow: 0 0 0 3px rgba(131, 103, 199, 0.12);
}

/* Submit button */
.gform_wrapper .gform_button,
.gform_wrapper input[type="submit"] {
	display: inline-flex;
	align-items: center;
	gap: 0.5rem;
	padding: 0.75rem 1.5rem;
	border: none;
	border-radius: var(--biz-radius);
	background: var(--biz-primary);
	color: #fff;
	font-weight: 600;
	font-size: 0.9375rem;
	font-family: var(--biz-font);
	cursor: pointer;
	transition: all var(--biz-base);
}

.gform_wrapper .gform_button:hover,
.gform_wrapper input[type="submit"]:hover {
	background: var(--biz-primary-dark);
	transform: translateY(-1px);
	box-shadow: var(--biz-shadow-md);
}

/* Field spacing */
.gform_wrapper .gfield {
	margin-bottom: 1.25rem;
}

/* Validation */
.gform_wrapper .gfield_error input,
.gform_wrapper .gfield_error textarea,
.gform_wrapper .gfield_error select {
	border-color: var(--biz-error);
}

.gform_wrapper .validation_message {
	font-size: 0.8125rem;
	color: var(--biz-error);
	margin-top: 0.25rem;
}

.gform_wrapper .gform_validation_errors {
	padding: 1rem 1.25rem;
	margin-bottom: 1rem;
	border-radius: var(--biz-radius);
	background: rgba(229, 56, 59, 0.06);
	border: 1px solid var(--biz-error);
	color: var(--biz-error);
}

/* Confirmation */
.gform_wrapper .gform_confirmation_message {
	padding: 1rem 1.25rem;
	border-radius: var(--biz-radius);
	background: rgba(46, 160, 67, 0.06);
	border: 1px solid var(--biz-success);
	color: var(--biz-success);
}

/* Progress bar (multi-page) */
.gform_wrapper .gf_progressbar {
	background: var(--biz-surface);
	border-radius: var(--biz-radius);
	overflow: hidden;
	margin-bottom: 1.5rem;
}

.gform_wrapper .gf_progressbar_percentage {
	background: var(--biz-primary);
	color: #fff;
	font-size: 0.75rem;
	font-weight: 600;
	padding: 0.375rem 0.75rem;
	border-radius: var(--biz-radius);
	transition: width var(--biz-slow);
}

CSS;

	// Gravity Forms uses 'gforms_css' or theme-specific handles
	wp_register_style( 'bizstudio-gforms-compat', false );
	wp_enqueue_style( 'bizstudio-gforms-compat' );
	wp_add_inline_style( 'bizstudio-gforms-compat', $css );
} );


/* ================================================================
   4. SEO PLUGIN COMPATIBILITY — OPEN GRAPH DEDUPLICATION
   ================================================================

   If Yoast SEO or Rank Math is active, remove BizStudio's own
   Open Graph and Schema.org output to prevent duplicate meta tags.

   The schema check is already in inc/seo.php via
   bizstudio_is_seo_plugin_active(). This section handles the
   Open Graph removal as an extra safety net from compat.php.
   ================================================================ */

add_action( 'after_setup_theme', function () {
	if ( ! function_exists( 'bizstudio_is_seo_plugin_active' ) ) {
		return;
	}

	// Check early (plugins loaded at 'after_setup_theme' time may not be ready)
	// Use 'wp' action for the actual check, as plugin data is fully available
	add_action( 'wp', function () {
		if ( ! bizstudio_is_seo_plugin_active() ) {
			return;
		}

		// Remove BizStudio's Open Graph output
		remove_action( 'wp_head', 'bizstudio_output_open_graph', 2 );

		// Remove BizStudio's Schema.org JSON-LD output
		remove_action( 'wp_head', 'bizstudio_output_schema_jsonld', 1 );
	} );
} );
