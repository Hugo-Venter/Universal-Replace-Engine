Pro Version Implementation Guide – Condensed

Goal: Explain how to turn existing Pro-ready code in Universal Replace Engine into a sellable Pro product, with licensing and feature unlocking.

Already Implemented in Free Plugin (just needs unlocking):

Full Regex Mode (with validation, delimiter handling, capture groups, serialized data support, error messages).

Unlimited Preview (via ure_preview_limit filter).

Extended History (via ure_history_limit filter).

Pro UI hooks (ure_is_pro filter, ure_pro_badge action, etc.).
In other words, Pro logic exists; you only need a way to signal “this is Pro” and handle licensing.

Approach 1: Separate Pro Plugin (recommended)

Create a second plugin “universal-replace-engine-pro” that depends on the free one.

Structure:

main file: universal-replace-engine-pro.php

includes/: license manager, Pro feature loader, extra components (postmeta, options, Elementor, etc.).

On load:

Check free plugin is active (class_exists URE_Plugin). If not, show an admin notice and deactivate Pro.

Load license class and features class.

If license is valid (stored as options and/or validated via remote API), enable Pro features.

License class:

Adds a “Pro License” submenu under the free plugin.

Stores license key and status (valid/invalid) in options.

Provides an activation form (license key field, activate/deactivate buttons).

Validates key either:

Simple hardcoded key (for dev), or

Via remote REST API on your server.

Pro features class:

Hooks into plugin via custom actions/filters (e.g. ure_plugin_loaded).

Overrides preview limits (ure_preview_limit filter).

Enables regex mode (e.g. ure_regex_mode_enabled filter).

Adds new search locations like postmeta/options.

Replaces upgrade teaser with a “Pro active” notice.

Advantages:

Clean separation between free and Pro.

Free plugin is fully functional alone.

Pro code is packaged separately, ideal for commercial sales and updates.

Approach 2: Single Plugin with License Check

Keep everything in one plugin and gate features with a license status.

Implement an is_pro_active() function that checks stored license key + status.

Provide that status through a filter like ure_is_pro.

Everywhere you need Pro behavior (regex toggle, preview limit, scopes, backup, etc.), check apply_filters( 'ure_is_pro', false ).

License activation page can live inside the main plugin (similar to the separate Pro plugin approach but in the same codebase).

Approach 3: Freemius (turnkey licensing/payments)

Integrate Freemius SDK in the plugin.

Freemius handles:

Licensing

Plans (free vs paid)

Payments and updates

You call ure_fs()->is_premium() or similar to decide when to enable Pro features.

Best option if you want to ship quickly and accept a revenue share.

Licensing Service Comparison (high level):

Freemius: fastest, integrated licensing + billing, revenue share.

Easy Digital Downloads + Software Licensing: more work, but full control over store and licensing.

WooCommerce + Software addon: good for existing WooCommerce shops.

Appsero: alternative managed service for analytics/licensing.

Custom license server: maximum control, maximum effort.

DIY License Server (simplified idea):

Generate license keys on your server (e.g. “URE-PRO-XXXX-XXXX-XXXX…”).

Store them in a custom table with status, site_url, expiry date.

Expose a REST endpoint /ure/v1/validate-license that:

Receives license_key and site_url.

Checks if key exists, is active, not expired, and matches/sets site_url.

Returns JSON { valid: true/false, expires: date }.

In the plugin, send key + site_url to this endpoint on activation; store “valid” in options.

Quick “Enable Pro for Testing”

For local testing, bypass licensing:

Add define('URE_PRO_ENABLED', true) or a filter: add_filter('ure_is_pro', '__return_true');

This instantly unlocks Pro branches without license logic.

Suggested Implementation Phases

Phase 1 – Choose Licensing and Wire Basics:

Decide between:

Separate Pro plugin + your own licensing, or

Freemius/Appsero, or

Single-plugin license check.

Implement license storage, activation form, and validation.

Implement a single source of truth for “is Pro” (e.g. ure_is_pro filter).

Phase 2 – Turn On Core Pro Features:

Use ure_preview_limit filter to unlock unlimited preview.

Use ure_history_limit filter for extended history.

Enable regex mode behind the Pro check.

Add extra scopes: postmeta, options, Elementor/ACF/etc. as Pro-only.

Phase 3 – Advanced Features:

Elementor, ACF, WooCommerce integrations.

WP-CLI commands.

Multisite awareness.

Better reporting, exports, scheduled operations.

Phase 4 – Polish and Launch:

Thorough testing under both free and Pro states.

Documentation and marketing copy (Free vs Pro comparison).

Pricing and checkout setup.

Support and onboarding pages.

Pricing Examples:

Annual:

Personal (1 site): ~49 USD/year.

Business (5 sites): ~99 USD/year.

Agency (unlimited): ~199 USD/year.

Lifetime alternatives at higher one-time prices.

Bottom Line:

The free plugin already contains the hooks and logic for Pro (regex, limits, Pro UI).

The only missing piece is a “truth source” for Pro status (license) and a mechanism to sell/distribute Pro.

Easiest path: Freemius or a separate Pro plugin that:

Validates license,

Hooks ure_is_pro → true when valid,

Loads additional files and removes restrictions.