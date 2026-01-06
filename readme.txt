=== Universal Replace Engine ===
Contributors: hugoxtechred
Tags: search, replace, database, migration, content, backup, restore, regex, cli
Requires at least: 5.9
Tested up to: 6.9
Stable tag: 1.6.0
Requires PHP: 7.4 or higher
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Enterprise-grade search and replace for WordPress. Safe content operations with preview, rollback, and saved profiles.

== Description ==

**Universal Replace Engine** is a powerful yet safe search and replace plugin for WordPress. Perfect for domain migrations, content updates, and fixing typos across your site with preview-before-apply functionality.

= Key Features =

* **Preview Before Apply** - See exactly what will change before committing
* **Operation History & Rollback** - One-click undo with configurable history (up to 10 operations)
* **Saved Profiles** - Save and reuse common search/replace configurations
* **Settings Management** - Configure batch sizes, limits, and features
* **Multilingual Support** - Available in 11 languages
* **Safe & Secure** - WordPress nonces, capability checks, prepared statements
* **Batch Processing** - Handles large sites without timeout errors
* **WP-CLI Support** - Command-line interface for post content operations, backups, and automation

= Use Cases =

* **Domain Migration** - Change URLs when moving from staging to production
* **Content Updates** - Update product names, company info across all posts
* **Typo Fixes** - Correct spelling mistakes site-wide
* **URL Fixes** - Update image paths, link URLs, embedded content

= Pro Features =

* **Advanced Database Mode** - Direct table-level search and replace in any database table
* **Full Regex Mode** - Use regular expressions for advanced pattern matching
* **Priority Support** - Get help from our expert team

[Learn more about Pro](https://xtech.red/)

= Multilingual =

Available in 11 languages:

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
* Complete WP-CLI command reference

= WP-CLI Commands =

Universal Replace Engine includes WP-CLI support for command-line operations:

**Search for text (in post content):**
`wp ure search "old-domain.com"`
`wp ure search "text" --post-type=post,page --case-sensitive --limit=50`

**Replace text (in post content):**
`wp ure replace "old.com" "new.com" --dry-run`
`wp ure replace "old.com" "new.com" --yes`

**Backup management:**
`wp ure backup --comment="Before migration"`
`wp ure backup_list`
`wp ure restore backup_2024-01-15_123456.sql`

**Profile management:**
`wp ure profile list`
`wp ure profile load "Domain Migration"`

**Settings & history:**
`wp ure settings`
`wp ure history --limit=20`
`wp ure rollback 123`

**Note:** WP-CLI currently searches and replaces in post content only. For Post Meta and Elementor data operations, use the web interface at **Tools → Universal Replace Engine**. Scope support for CLI coming in a future update.

For complete WP-CLI documentation, see **Tools → URE Help → WP-CLI** in your WordPress admin.

= Contributing & Bug Reports =

This plugin is open source and hosted on [GitHub](https://github.com/Hugo-Venter/Universal-Replace-Engine). We welcome:

* **Bug reports** - Found an issue? [Report it on GitHub](https://github.com/Hugo-Venter/Universal-Replace-Engine/issues)
* **Feature requests** - Have an idea? [Submit a feature request](https://github.com/Hugo-Venter/Universal-Replace-Engine/issues)
* **Contributions** - Pull requests are welcome!

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

Yes! The plugin includes preview mode so you can see exactly what will change before applying. 
We always recommend:

1. Taking a full site backup before major operations
2. Testing on a staging site first

= Will this work on large sites? =

Yes! The plugin uses batch processing to handle sites of any size without timeout errors. Processing is done in small chunks to prevent memory exhaustion. The free version has no limits on the number of replacements it can perform.

= Can I undo changes after applying? =

Yes! The plugin keeps a history of the last 10 operations. Each operation can be rolled back with one click, restoring the previous content.

= Does it handle serialized data? =

Yes! The plugin safely handles WordPress serialized data, properly adjusting string lengths after replacements.

= Can I search in custom fields (postmeta)? =

Yes! The free version includes search and replace in:
- Post content
- Custom fields / postmeta
- Elementor page builder data
- All locations combined

= Is regex supported? =

Yes, with the Pro version! Pro includes full regex mode with pattern validation, capture groups, and error messages. The free version supports standard text search and replace in all content locations.

= What about database tables? =

The Advanced Database Mode provides direct access to any database table with preview, GUID protection, and case-sensitive search. This powerful feature is available in the Pro version for advanced users who need table-level operations.

= Can I save my search/replace settings? =

Yes! Use Saved Profiles to save common configurations and reload them with one click. Perfect for recurring tasks like domain migrations.

= Will it slow down my site? =

No! The plugin only loads on admin pages and uses efficient batch processing. It has zero impact on frontend performance.

= Does it support WP-CLI? =

Yes! Universal Replace Engine includes WP-CLI support for major functions:
- Search and replace operations with `wp ure search` and `wp ure replace` (post content only)
- Backup creation and restoration with `wp ure backup` and `wp ure restore`
- Profile management with `wp ure profile`
- Settings configuration with `wp ure settings`
- Operation history and rollback with `wp ure history` and `wp ure rollback`

Note: CLI currently supports post content searches only. For Post Meta and Elementor data operations, use the web interface. Perfect for automation, scripting, and server management. See the WP-CLI section above for examples.

== Screenshots ==

1. Main search and replace interface with preview before apply
2. Saved Profiles for recurring search/replace operations
3. Operation History with one-click rollback functionality
4. Settings page - configure performance and limits

== Changelog ==

= 1.6.0 - 2025-12-21 =
* Added: Post Meta (custom fields) search and replace now available in FREE version!
* Added: Elementor page builder data search and replace now available in FREE version!
* Added: "All Locations" scope (content + postmeta + Elementor) now available in FREE version!
* Improved: Removed restrictions forcing users to upgrade for basic content scopes
* Improved: Better messaging about what's included in Free vs Pro versions
* Note: Pro version still includes Advanced Database Mode, Full Regex Mode, and Priority Support

= 1.5.1 - 2025-12-20 =
* Fixed: All plugin settings now actually work (critical bug fix)
* Fixed: database_batch_size setting now properly controls database operations
* Fixed: backup_batch_size setting now properly controls backup operations
* Fixed: max_preview_results setting now properly limits preview results
* Fixed: history_limit setting now properly controls operation history retention
* Fixed: backup_retention_days setting now properly triggers automatic cleanup
* Fixed: enable_logging toggle now actually enables/disables operation logging
* Fixed: ajax_processing toggle now controls AJAX batch processing mode
* Fixed: show_warnings toggle now controls display of warning messages
* Fixed: Preview highlighting now correctly highlights replacement text when lengths differ (e.g., http: → https:)
* Improved: Preview now clearly explains that "Apply Changes" affects ALL matches, not just previewed ones
* Security: Added index.php files to all directories (assets, includes, languages, templates) to prevent directory browsing
* Important: Settings page was non-functional in 1.5.0 - all settings were stored but ignored

= 1.5.0 - 2025-12-20 =
* Added: Complete WP-CLI support for all major functions
* Added: `wp ure search` - Search for text via command line
* Added: `wp ure replace` - Replace text with dry-run and confirmation options
* Added: `wp ure backup` and `wp ure backup_list` - Backup management commands
* Added: `wp ure restore` - Restore from backups via CLI
* Added: `wp ure profile` - Manage saved profiles from command line
* Added: `wp ure settings` - View and update plugin settings
* Added: `wp ure history` and `wp ure rollback` - History and rollback commands
* Improved: Help documentation with complete WP-CLI reference
* Improved: Automation and scripting capabilities for DevOps workflows

== Upgrade Notice ==

= 1.6.0 =
MAJOR UPDATE: Post Meta and Elementor search/replace are now FREE! No longer restricted to Pro version. Update now to unlock these features for all users.

= 1.5.1 =
CRITICAL BUG FIX: All plugin settings now work properly. Version 1.5.0 had non-functional settings that were stored but ignored. Upgrade immediately to ensure your settings take effect.

= 1.5.0 =
Complete WP-CLI support added! Automate search/replace operations, manage backups, profiles, and settings from command line. Perfect for DevOps workflows and server management.

== Security ==

This plugin takes security seriously and follows WordPress coding standards:

Security measures implemented:

* All user inputs sanitized with WordPress functions
* All outputs escaped to prevent XSS
* SQL injection prevention with prepared statements
* CSRF protection with nonce verification
* Capability checks on all operations (Administrator only)
* No dangerous PHP functions (eval, exec, system, etc.)
* Direct file access prevention
* Transient-based user data isolation

== Privacy ==

This plugin:

* Does not collect or transmit any user data
* Does not use cookies
* Does not communicate with external servers
* Stores all data locally in your WordPress database
* Uses WordPress user capabilities for access control

== Support ==

**Need help or found a bug?**

* **Report bugs & request features:** [Submit an issue on GitHub](https://github.com/Hugo-Venter/Universal-Replace-Engine/issues)
* **Documentation:** Check the built-in Help (Tools → URE → Help)
* **Email support:** support@xtech.red

We actively monitor GitHub issues and appreciate your feedback!

== Credits ==

Developed with security and performance in mind. Uses WordPress best practices and coding standards throughout.

Special thanks to the WordPress community for their excellent documentation and security guidelines.
