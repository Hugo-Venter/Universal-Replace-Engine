Universal Replace Engine – Condensed Overview

Version: 1.4.0
Requires: WordPress 5.9+ and PHP 7.4+
License: GPL v2 or later

DESCRIPTION
Universal Replace Engine is a WordPress plugin for safe search-and-replace operations in post content, with preview, logging, rollback, saved profiles, settings, and optional Pro regex capabilities. It is suitable for small–medium sites right now, with a roadmap toward large-scale, batch-based processing.

MAIN FREE FEATURES

Search and replace in post content (posts, pages, custom post types).

Preview before applying (up to 20 matches shown).

Safe handling of serialized data.

Case-sensitive or case-insensitive matching.

Operation history (last 5 operations) with one-click rollback.

Saved Profiles (v1.4.0+):

Located on main plugin page, below Operation History.

Save/load search/replace settings, auto-fill the form, view profiles in a table.

Settings page:

Batch sizes, preview limit, history retention, backup retention, feature toggles, system info.

Help page:

In-plugin documentation, getting started, feature explanations, troubleshooting, FAQ.

Advanced Database Mode (v1.4.0+):

Collapsible section below Saved Profiles.

Full table-level access (e.g., wp_options, postmeta) with backups and restore tools.

Safety warnings and GUID protection.

Safety measures:

Dry-run preview, nonce checks, capability checks (manage_options), only updates changed posts.

Multilingual:

Available in 10 languages (Spanish, French, German, Portuguese Brazil, Arabic, Chinese Simplified, Japanese, Italian, Dutch, Russian).

Uses standard WordPress localization; language follows the Site Language setting.

PRO FEATURES (CURRENT AND PLANNED)
Already available in Pro:

Full Regex Mode: automatic delimiters, capture groups, validation, error messages, works with serialized data.

Unlimited preview results (no 20-match limit).

Extended history (up to 50 operations).

Planned future Pro scope:

Search/replace in post meta, options, term meta, Elementor, ACF, WooCommerce data.

Backup system enhancements, WP-CLI support, multisite, scheduled operations, export/import of rules.

INSTALLATION (SUMMARY)
Method 1 – Manual:

Upload “universal-replace-engine” to wp-content/plugins/.

Activate via the Plugins menu.

Open Tools → Universal Replace Engine.

Method 2 – Upload ZIP:

Create/obtain a ZIP of the plugin folder.

Go to Plugins → Add New → Upload Plugin, select ZIP, install and activate.

BASIC USAGE

Go to Tools → Universal Replace Engine.

Enter search text (required) and optional replacement text.

Select post types to search.

Optionally enable case-sensitive; regex mode is Pro-only.

Click “Run Preview” to see up to 20 sample changes.

If satisfied, click “Apply Changes” to commit.

Operations are logged.

REGEX MODE (PRO)

Allows pattern-based replacements with capture groups.

Supports typical use cases like phone numbers, date formats, whitespace cleanup, emails and URLs.

Invalid patterns show clear error messages.

SAVED PROFILES

Configure a search/replace operation, then save it with a name (e.g., “HTTPS migration”).

Later, select a profile, load it, and the main form auto-fills.

Helpful for repeated tasks such as domain migrations or common typo fixes.

ADVANCED DATABASE MODE

Accessed via a collapsible panel under Saved Profiles.

Enables table-level search/replace and backup/restore.

Strongly recommended to create a backup first and avoid touching GUIDs unless you fully understand the implications.

Intended for advanced users; use with caution.

DATABASE STRUCTURE

Custom table wp_ure_logs stores history and rollback data:

ID, timestamp, user ID, summary text, and detailed JSON data per operation.

ARCHITECTURE AND EXTENSIBILITY

Main plugin class (URE_Plugin) bootstraps everything.

URE_Admin handles admin UI and forms.

URE_Search_Replace contains the search/replace logic.

URE_Logger handles logging and rollback.

Hooks and filters provided (e.g., ure_plugin_loaded, ure_pro_upgrade_button, ure_preview_query_args, ure_apply_query_args) for Pro or add-on extensions.

Designed with singleton pattern and dependency injection for clean separation.

SECURITY

All forms and AJAX calls protected with nonces.

Only users with manage_options can run operations.

Inputs sanitized and outputs escaped using standard WordPress functions.

Database access uses prepared statements; no unsafe string concatenation.

No dangerous dynamic execution functions or obfuscated code.

No external “phone home” or tracking; all data stays in the WordPress database.

A full security audit (SECURITY-AUDIT-REPORT.md) rates the plugin 98/100 and confirms WordPress.org readiness.

KNOWN LIMITATIONS AND PRODUCTION READINESS
Current status: safe for small to medium sites, caution for large sites.
Key limitations right now:

Uses posts_per_page = -1 in some operations, which can load all posts into memory.

No execution-time management; long runs may hit PHP timeouts.

Limited rollback history (5 free / 50 Pro).

No progress bar or total-match count in the preview UI (preview is only a sample).

Not ideal yet for sites with thousands of posts without careful staging and backups.

Recommended usage:

Always backup the database before large operations.

Test first on staging.

Start with small, specific replacements.

Avoid using it for very large sites (> 5,000 posts) until batch processing is implemented.

ROADMAP (V2.0 GOALS)

AJAX batch processing with progress bar.

Execution-time and memory safeguards.

Accurate total-match counts.

Automatic backup exports, Elementor cache auto-clear.

Search-only CSV export, operation wizard, improved error recovery, and WP-CLI integration.

SUPPORT AND FEEDBACK

Issues and feature requests intended via GitHub or similar tracker.

Security issues should be reported privately to the maintainer’s security contact (placeholder in README).

LICENSE

GPL v2 or later, standard WordPress plugin licensing.