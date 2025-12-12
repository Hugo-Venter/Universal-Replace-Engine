Changelog (Condensed)

[1.4.0] – 2024-12-10
Major UX and feature update. Added Saved Profiles to main interface, Advanced Database Mode toggle, profile pre-fill, jQuery animations, and multilingual support for 10 languages. All features fully tested.

Added:

Settings page with configurable batch sizes, preview limits, history and backup retention, feature toggles, and system info.

Saved Profiles with load/save/delete, timestamps, user-specific storage, ideal for migrations and repeated operations.

Help & Documentation tab with full guides, examples, and troubleshooting.

AJAX framework with batch endpoints and nonce security.

Translation system with 318+ strings, POT/PO/MO files.

Changed:

New submenu items under Tools (Main, Settings, Help).

Updated plugin description, asset loading, and installer defaults.

Technical:

New classes: URE_Settings, URE_Profiles, URE_Ajax.

New templates: admin-settings.php, admin-profiles.php, admin-help.php.

Settings stored in wp_options; profiles in user meta. No schema changes.

Upgrade Notes:

No breaking changes; seamless upgrade from 1.3.0.

Comparison with Better Search Replace Pro:

Matches or exceeds feature parity except for full AJAX progress UI (in development).

[1.3.0] – 2024-12-09
Enterprise-grade features previously released (see v1.3.0 notes).

[1.2.0] – 2024-12-09
Stability and performance improvements. Introduced 100-post batch processing to prevent memory exhaustion, added execution timeout handling (300 seconds), suspended cache additions, validated post types, and added automatic Elementor cache clearing.
Preview and Apply now fully batched for handling sites up to 100k+ posts.

[1.1.0] – 2024-12-09
Beta release suitable for small and medium sites.
Known limitations included high memory usage, timeout risk, limited rollback depth, no progress indicator, partial preview mismatch, and no post type validation.
Added Pro infrastructure: full regex mode with validation, delimiters, capture groups, and safe handling of serialized data. Extended history, unlimited preview, and Pro feature toggles.
Refined regex logic across admin panels and logger. Fixed regex validation issues and serialized replacement inaccuracies.

[1.0.0] – 2024-12-09
Initial release with search/replace for posts and custom post types, 20-result preview, safe serialized data handling, operation history and rollback, admin UI under Tools, nonce and capability checks, and Pro-ready architecture. Compatible with WordPress 5.9+ and PHP 7.4+.

Unreleased (2.0 Planned)
Goals: Full production-readiness for large sites.
High priority: AJAX batch engine, real-time progress bar, timeout/memory protection, full match count display, automatic backups, Elementor cache clearing.
Medium: Search-only mode with CSV export, improved logging, error recovery, post type validation, regex timeout protection, uninstall cleanup.
Pro roadmap: postmeta/termmeta/options, ACF and WooCommerce support, WP-CLI commands, scheduled operations, rule export/import, detailed reporting.

Version History:
1.0.0 – Initial release
1.1.0–1.4.0 – Feature, stability, UX, and performance improvements

License: GPL v2 or later.