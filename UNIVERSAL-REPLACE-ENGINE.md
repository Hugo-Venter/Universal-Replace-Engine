# Universal Replace Engine � Master Documentation

Version: 1.4.0  
Requires WordPress: 5.9 or higher  
Requires PHP: 7.4 or higher  
License: GPL v2 or later  

A powerful WordPress plugin for safe search and replace operations across your content and database, with preview, logging, rollback, saved profiles, settings management, backup/restore, multilingual support, and Pro-only regex and advanced features.

---

## 1. Plugin Overview

Universal Replace Engine (URE) is designed for:

- Safe search and replace in post content
- Database-level operations (any table)
- Preview-before-apply workflow
- Operation history with rollback
- Saved profiles for repeat tasks
- Settings and performance tuning
- Backup and restore at SQL level
- Multilingual interface (10 languages)
- Clear Free vs Pro feature separation

The plugin is architected to be:

- Secure (audited, 98/100 security score)
- WordPress-standards compliant
- Extensible via hooks and a Pro add-on
- Ready for WordPress.org submission

---

## 2. Feature Set

### 2.1 Free Version Features

1) Search and Replace in Post Content  
- Search across posts, pages, and custom post types  
- Preview changes before applying (up to 20 matches)  
- Safe handling of serialized data  
- Case-sensitive and case-insensitive search options  

2) Preview Before Apply  
- Shows exactly what will change before committing  
- Side-by-side before/after snippets  
- Filter by post type  
- Context highlighting  

3) Operation History & Rollback  
- Automatic logging of operations  
- Stores last 5 operations (Free)  
- One-click undo/rollback  
- Restores content to previous state  

4) Saved Profiles (v1.4.0)  
Location: Main plugin page, below Operation History  

- Save search/replace configurations as named profiles  
- User-specific profiles (per user ID)  
- Profile data includes:
  - Search term
  - Replace term
  - Post types selection
  - Case-sensitive flag
  - Regex mode flag
  - Scope
- Load profile:
  - Form is automatically pre-filled from profile
  - Run preview or apply immediately  
- Manage profiles:
  - Save, load, delete
  - View all profiles in a table with timestamps

5) Settings Management (v1.4.0)  
Location: Tools ? URE Settings  

- Performance settings:
  - Content batch size: 10�1000 posts (default 100)
  - Database batch size: 100�10,000 rows (default 5000)
  - Backup batch size: 100�5000 rows (default 1000)
- Preview and history limits:
  - Free preview limit (default: 20)
  - History retention (default: 5 entries)
- Backup retention:
  - 1�30 days (default 7)
- Feature toggles:
  - Logging on/off
  - AJAX batch processing on/off
  - Warning messages on/off
- System information:
  - WordPress version
  - PHP version
  - Database info
- Reset to defaults:
  - One-click reset with confirmation
- Validation and sanitization:
  - Ensures values are within allowed ranges

6) Help & Documentation (v1.4.0)  
Location: Tools ? URE Help  

- Getting started guide
- Feature overview:
  - Content operations
  - Database operations
  - Pro-only features (labelled clearly)
- Using Saved Profiles
- Advanced Database Mode safety guidelines
- Troubleshooting:
  - Timeouts
  - Memory errors
  - Cache issues
- FAQ:
  - Common use-cases and issues
- Links to internal docs and key sections

7) Advanced Database Mode (v1.3.0, integrated in 1.4.0)  
Location: Main plugin page, collapsible section below Saved Profiles  

- Full database-level search and replace:
  - Any table: core, plugin, custom
  - wp_options, wp_postmeta, termmeta, usermeta, etc.
- Backup-before-change workflow:
  - SQL backups of selected tables
  - One-click restore
- Table selector UI:
  - Table name
  - Row count
  - Size in MB
  - Type:
    - core
    - woocommerce
    - yoast
    - elementor
    - plugin
    - custom
  - Protected tables highlighted (users, usermeta, options)
- Filters:
  - Select all
  - Select none
  - Core only
- GUID protection:
  - Option to skip GUID columns (recommended)
- Options safety:
  - Critical options (siteurl, home) updated last
  - Plugin�s own settings skipped when appropriate
- Batch processing:
  - Processes large tables in chunks (default 5000 rows)
  - Reduces memory usage and timeouts

8) Backup & Restore (Free base system, Pro lock in UI if you choose)  

Core engine capabilities:

- Create SQL backups of selected tables:
  - mysqldump-style portable SQL
  - Includes CREATE TABLE and INSERT statements
- Backup metadata:
  - Filename
  - Size
  - Number of tables
  - Timestamp
  - User who created it
  - Optional comments
- Restore:
  - One-click restore from admin UI
  - Safety warnings and confirmations
- Retention:
  - Automatic deletion of backups older than N days (configurable)
- Backup location:
  - wp-content/uploads/ure-backups/
  - Protected via .htaccess and index.php

9) Safety Features  

- Always runs preview (dry-run) before apply  
- Nonce-based CSRF protection  
- Capability checks (manage_options)  
- Safe serialized data handling (with recursive replace)  
- Skips posts if no changes detected  
- GUID protection for post GUIDs (optional toggle)  
- Options table deferral for critical options  

10) Multilingual Support (v1.4.0)  

- 10 languages included:
  - es_ES � Spanish
  - fr_FR � French
  - de_DE � German
  - pt_BR � Portuguese (Brazil)
  - ar � Arabic
  - zh_CN � Chinese (Simplified)
  - ja � Japanese
  - it_IT � Italian
  - nl_NL � Dutch
  - ru_RU � Russian
- 318+ translatable strings
- internationalized with standard WordPress functions and text domain
- Automatic language detection from WordPress �Site Language�
- Translation template: universal-replace-engine.pot
- Detailed translation guide: languages/README.md

---

### 2.2 Pro Features

The base plugin is architected for Pro extensions and licensing, but is fully usable as Free.

Current and planned Pro features:

Already implemented in code (gated by Pro checks):

- Full Regex Mode:
  - Pattern validation (validate_regex())
  - prepare_regex_pattern() for automatic delimiters
  - Capture groups: $1, $2, etc.
  - Works with serialized data and JSON
  - Helpful error messages on invalid patterns

- Unlimited Preview:
  - Free uses 20-match limit
  - Pro can override via ure_preview_limit filter to effectively unlimited

- Extended History:
  - Free retains 5 operations
  - Pro can retain up to 50 operations via ure_history_limit filter

Planned / future Pro-only scopes (already prepared in architecture):

- Post meta (custom fields)
- Options table entries
- Term meta
- Elementor data
- ACF fields
- WooCommerce product data
- Additional Pro-level database tools

Advanced features for future Pro updates:

- Comprehensive backup UI and management
- WP-CLI commands
- Scheduled operations
- Export/import replace rules

---

## 3. Architecture and File Structure

### 3.1 File Structure

Top-level layout:

universal-replace-engine/  
  universal-replace-engine.php        (main plugin file)  
  uninstall.php                       (cleanup on uninstall)  
  readme.txt                          (WordPress.org readme)  
  README.md                           (GitHub / main documentation)  
  CHANGELOG.md                        (version history)  
  PACKAGING.md                        (packaging and release guide)  
  SECURITY-AUDIT-REPORT.md            (full audit report)  
  INSTALLATION.md                     (setup instructions)  
  V1.4.0-IMPLEMENTATION-SUMMARY.md    (internal summary, optional)  
  includes/  
    class-ure-plugin.php             (main plugin singleton)  
    class-ure-admin.php              (admin UI and routing)  
    class-ure-search-replace.php     (core search/replace engine)  
    class-ure-logger.php             (logging and rollback)  
    class-ure-database-manager.php   (database-level operations)  
    class-ure-backup-manager.php     (SQL backups and restore)  
    class-ure-settings.php           (settings API integration)  
    class-ure-profiles.php           (saved profiles handling)  
    class-ure-ajax.php               (AJAX batch endpoints)  
    installer.php                    (activation and DB table creation)  
  templates/  
    admin-settings.php               (settings page UI)  
    admin-profiles.php               (profiles UI)  
    admin-help.php                   (help page UI)  
    admin-db-search.php              (database search UI)  
    admin-backup.php                 (backup UI)  
    admin-advanced-mode.php          (advanced mode panel)  
  assets/  
    admin.css                        (admin styles)  
    admin.js                         (admin JS and toggles)  
  languages/  
    universal-replace-engine.pot  
    universal-replace-engine-*.po  
    universal-replace-engine-*.mo  
    README.md                        (translation docs)

### 3.2 Main Classes

- URE_Plugin  
  - Bootstraps plugin  
  - Registers hooks  
  - Loads text domain  
  - Instantiates:
    - URE_Admin
    - URE_Search_Replace
    - URE_Logger
    - URE_Database_Manager
    - URE_Backup_Manager
    - URE_Settings
    - URE_Profiles
    - URE_Ajax

- URE_Admin  
  - Registers admin pages (Tools ? URE, Settings, Help)  
  - Handles form submissions (preview/apply/undo/backup/profiles/settings)  
  - Renders templates  
  - Implements Advanced Database Mode toggle

- URE_Search_Replace  
  - Implements content-level search and replace  
  - Handles regex mode  
  - Deals with serialized data and JSON  
  - Coordinates with URE_Logger for logging and rollback

- URE_Logger  
  - Manages wp_ure_logs table  
  - Stores timestamp, user, summary, and details_json  
  - Provides data for history UI and undo operations

- URE_Database_Manager  
  - Enumerates tables (core, plugin, custom)  
  - Detects table types and protected tables  
  - Performs database-level search/replace in batches  
  - Deals with GUID protection and options-table safety

- URE_Backup_Manager  
  - Creates SQL backups (SELECT and CREATE TABLE dumps)  
  - Restores backups  
  - Manages backup files and retention period  
  - Protects backup directory with .htaccess and index.php

- URE_Settings  
  - Settings page and registration  
  - Form sanitization and defaults  
  - Provides getters for batch sizes, limits, retention, toggles

- URE_Profiles  
  - Stores profiles in user_meta keyed by user  
  - Provides create/read/update/delete operations  
  - Sanitizes profile data  
  - Works with transients for form pre-fill

- URE_Ajax  
  - AJAX endpoints for batch preview/apply/status  
  - Adds nonces and capability checks  
  - Stores progress in transients

---

## 4. Database Schema

The plugin creates one table for logs:

Table: wp_ure_logs  

Columns:

- id (BIGINT, primary key)  
- timestamp (DATETIME)  
- user_id (BIGINT)  
- summary (TEXT)  
- details_json (LONGTEXT)  

Used to store operation history and allow rollback where possible.

Backups are stored as SQL files in uploads/ure-backups/; they are not tables but files.

---

## 5. Multilingual Support � Implementation Summary

- POT template: universal-replace-engine.pot  
- 10 PO files (one per locale)  
- 10 MO files compiled from PO  
- languages/README.md describing:
  - Supported locales
  - How to change WordPress language
  - How to update translations
  - How to add a new language:
    - Copy POT ? NEW_LOCALE.po
    - Edit translations
    - Compile MO with msgfmt

Loading:

```php
public function load_textdomain() {
    load_plugin_textdomain(
        'universal-replace-engine',
        false,
        dirname( URE_PLUGIN_BASENAME ) . '/languages'
    );
}
