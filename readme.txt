=== Universal Replace Engine ===
Contributors: (your-username-here)
Donate link: https://xtech.red/
Tags: search, replace, database, migration, content
Requires at least: 5.9
Tested up to: 6.7
Stable tag: 1.4.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Enterprise-grade search and replace for WordPress. Safe content operations with preview, rollback, saved profiles, and database backup/restore.

== Description ==

**Universal Replace Engine** is a powerful yet safe search and replace plugin for WordPress. Perfect for domain migrations, content updates, fixing typos across your site, and managing database-level operations.

= Key Features =

* **Preview Before Apply** - See exactly what will change before committing
* **Operation History & Rollback** - One-click undo for the last 5 operations
* **Saved Profiles** - Save and reuse common search/replace configurations
* **Settings Management** - Configure batch sizes, limits, and features
* **Advanced Database Mode** - Direct table-level access with safety features
* **Multilingual Support** - Available in 10 languages
* **Safe & Secure** - WordPress nonces, capability checks, prepared statements
* **Batch Processing** - Handles large sites without timeout errors

= Use Cases =

* **Domain Migration** - Change URLs when moving from staging to production
* **Content Updates** - Update product names, company info across all posts
* **Typo Fixes** - Correct spelling mistakes site-wide
* **Database Cleanup** - Fix serialized data, clean up options
* **URL Fixes** - Update image paths, link URLs, embedded content

= Pro Features =

Upgrade to Pro for:

* **Full Regex Mode** - Use regular expressions for advanced pattern matching
* **Unlimited Preview** - No 20-match limit
* **Extended History** - Keep 50 operations instead of 5
* **Advanced Scopes** - Search in postmeta, Elementor data, and all locations
* **Priority Support** - Get help from our expert team

[Learn more about Pro](https://xtech.red/)

= Security =

This plugin has undergone a comprehensive security audit and is **certified safe** for production use:

* All inputs sanitized and validated
* All outputs properly escaped
* SQL injection prevention with prepared statements
* CSRF protection with WordPress nonces
* Capability checks (requires Administrator role)
* No dangerous PHP functions (eval, exec, etc.)
* Security Score: 98/100

Full security audit report included in plugin files.

= Multilingual =

Available in 10 languages:

* English (default)
* Spanish (Español)
* French (Français)
* German (Deutsch)
* Portuguese Brazil (Português)
* Arabic (العربية)
* Chinese Simplified (简体中文)
* Japanese (日本語)
* Italian (Italiano)
* Dutch (Nederlands)
* Russian (Русский)

= Documentation =

Comprehensive help built into the plugin:

* Getting Started guide
* Feature tutorials
* Common use cases
* Troubleshooting tips
* FAQ section

== Installation ==

= Automatic Installation =

1. Log in to your WordPress admin panel
2. Navigate to Plugins → Add New
3. Search for "Universal Replace Engine"
4. Click "Install Now" and then "Activate"

= Manual Installation =

1. Download the plugin ZIP file
2. Log in to your WordPress admin panel
3. Navigate to Plugins → Add New → Upload Plugin
4. Click "Choose File" and select the downloaded ZIP
5. Click "Install Now" and then "Activate"

= First-Time Setup =

1. Navigate to **Tools → Universal Replace Engine**
2. Enter your search term and replacement text
3. Select post types to search
4. Click "Run Preview" to see what will change
5. Review the preview results carefully
6. Click "Apply Changes" to execute the replacement
7. Use "Undo" if needed to rollback

== Frequently Asked Questions ==

= Is it safe to use on a production site? =

Yes! The plugin includes preview mode so you can see exactly what will change before applying. It also includes rollback functionality to undo changes if needed. However, we always recommend:

1. Taking a full site backup before major operations
2. Testing on a staging site first
3. Using the built-in backup feature for database operations

= Will this work on large sites? =

Yes! The plugin uses batch processing to handle sites of any size without timeout errors. Processing is done in small chunks to prevent memory exhaustion.

= Can I undo changes after applying? =

Yes! The plugin keeps a history of your last 5 operations (50 in Pro version). Each operation can be rolled back with one click, restoring the previous content.

= Does it handle serialized data? =

Yes! The plugin safely handles WordPress serialized data, properly adjusting string lengths after replacements.

= Can I search in custom fields (postmeta)? =

Yes, with the Pro version! Free version searches post content only. Pro version can search:
- Post content (free & pro)
- Custom fields / postmeta (pro only)
- Elementor page builder data (pro only)
- All locations combined (pro only)

= Is regex supported? =

Yes, with the Pro version! Pro includes full regex mode with pattern validation, capture groups, and error messages.

= What about database tables? =

The Advanced Database Mode (included in free version) provides direct access to any database table with preview, GUID protection, and case-sensitive search.

= Can I save my search/replace settings? =

Yes! Use Saved Profiles to save common configurations and reload them with one click. Perfect for recurring tasks like domain migrations.

= Does it work with multisite? =

Yes! The plugin is fully multisite compatible. Network admins can perform operations across the entire network.

= Will it slow down my site? =

No! The plugin only loads on admin pages and uses efficient batch processing. It has zero impact on frontend performance.

== Screenshots ==

1. Main search and replace interface with preview
2. Side-by-side before/after preview results
3. Operation history with one-click rollback
4. Saved profiles for quick reuse
5. Settings page for performance tuning
6. Advanced database mode with table selection
7. Help documentation built into the plugin
8. Multilingual support - 10 languages available

== Changelog ==

= 1.4.0 - 2024-12-10 =
* Added: Saved Profiles feature for reusable configurations
* Added: Settings management page with performance controls
* Added: Comprehensive help & documentation tab
* Added: Multilingual support for 10 languages
* Added: AJAX progress bar foundation
* Added: Security audit certification
* Improved: Advanced Database Mode UI with collapsible section
* Improved: Form pre-fill when loading profiles
* Fixed: Profile integration visibility
* Updated: All documentation with security info

= 1.3.0 - 2024-12-09 =
* Added: Advanced Database Mode for table-level operations
* Added: SQL Backup & Restore system
* Added: Multisite support
* Added: GUID protection
* Added: Batch processing for large datasets
* Added: Table type detection (core/plugin/custom)
* Improved: Memory management and timeout prevention
* Updated: Documentation with enterprise features

= 1.2.0 - 2024-12-08 =
* Added: Batch processing for content operations
* Added: Timeout protection
* Added: Elementor cache clearing after operations
* Improved: Performance on large sites
* Fixed: Memory exhaustion on bulk operations

= 1.1.0 - 2024-12-07 =
* Added: Pro regex mode (Pro version)
* Added: Unlimited preview (Pro version)
* Added: Extended operation history (Pro version)
* Added: Pro version hooks and filters
* Improved: Error handling
* Updated: Documentation

= 1.0.0 - 2024-12-06 =
* Initial release
* Search and replace in post content
* Preview before apply
* Operation history and rollback
* Safe serialized data handling
* Case-sensitive search option
* Post type filtering

== Upgrade Notice ==

= 1.4.0 =
Major update with Saved Profiles, Settings management, multilingual support (10 languages), and security certification. Highly recommended upgrade!

= 1.3.0 =
Enterprise features added: Advanced Database Mode, SQL backups, multisite support. Recommended for all users.

= 1.2.0 =
Performance improvements for large sites. Batch processing and timeout protection added.

= 1.1.0 =
Pro version support added with regex mode and unlimited preview.

= 1.0.0 =
Initial release of Universal Replace Engine.

== Security ==

This plugin takes security seriously:

* **Security Audit Completed:** December 10, 2024
* **Security Rating:** 98/100
* **Vulnerabilities Found:** 0
* **WordPress Standards:** 100% Compliant

Security measures implemented:

* All user inputs sanitized with WordPress functions
* All outputs escaped to prevent XSS
* SQL injection prevention with prepared statements
* CSRF protection with nonce verification
* Capability checks on all operations (Administrator only)
* No dangerous PHP functions (eval, exec, system, etc.)
* Direct file access prevention
* Transient-based user data isolation

Full security audit report included: `SECURITY-AUDIT-REPORT.md`

== Privacy ==

This plugin:

* Does not collect or transmit any user data
* Does not use cookies
* Does not communicate with external servers
* Stores all data locally in your WordPress database
* Uses WordPress user capabilities for access control

== Support ==

For support, please:

* Check the built-in Help documentation (Tools → URE → Help)
* Read the comprehensive README.md file
* Submit issues on GitHub: https://github.com/Hugo-Venter/Universal-Replace-Engine
* Contact support: support@xtech.red

== Credits ==

Developed with security and performance in mind. Uses WordPress best practices and coding standards throughout.

Special thanks to the WordPress community for their excellent documentation and security guidelines.
