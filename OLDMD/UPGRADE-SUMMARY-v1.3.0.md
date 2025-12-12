Universal Replace Engine v1.3.0 – Major Upgrade Summary
(Text-Only Version)

Version 1.3.0 is a major release that introduces database-level search and replace, SQL backup and restore, multisite capabilities, batch processing, GUID protection, options-table safety logic, and a complete advanced mode interface. This version brings the plugin to the same functional level as Better Search Replace Pro, while exceeding it in several areas such as preview visibility, undo system, JSON support, and architecture quality.

Database-Level Operations

The plugin now supports search and replace operations across any WordPress database table. This includes core tables, plugin tables, WooCommerce tables, and custom application tables.

Key points:

Search and replace can be performed in any table, not only posts or postmeta.

Supports wp_options, wp_usermeta, wp_termmeta, wp_comments, plugin-created tables, and any tables discovered in the database.

Works with serialized data, JSON, and deeply nested structures.

Database operations are isolated through a dedicated manager class.

This feature is implemented in: includes/class-ure-database-manager.php

SQL Backup and Restore

A complete SQL backup engine is now included. Backups contain CREATE TABLE and INSERT statements for portability. Administrators may restore selected backup files directly from the plugin interface.

Features:

Select one or more tables to back up.

SQL dump includes schema and data.

Metadata is stored including table count, file size, timestamp, and comments.

Backups are placed in wp-content/uploads/ure-backups.

Automatic cleanup of files older than seven days.

The directory is protected from public web access.

Implemented in: includes/class-ure-backup-manager.php

Multisite Support

Multisite installations are supported with proper detection of per-site table prefixes.

Details:

Network administrators may operate on all database tables across the network.

Subsite administrators are restricted to their own site tables.

Table prefix handling is automatic and safe.

Advanced Table Selection Interface

An advanced table selection interface has been added. It displays:

Table name

Table type (core, plugin, WooCommerce, custom)

Row count

Size in bytes or megabytes

Protection warnings for sensitive tables

Filtering options:

Select all tables

Select none

Filter by core, plugin, WooCommerce, or custom

GUID Protection

The system can skip WordPress GUID fields, consistent with WordPress documentation that GUIDs should not be modified after publishing.

The user can choose whether GUID columns should be ignored.

Options Table Safety

Certain operations in wp_options require caution. The plugin now:

Defers writing siteurl and home until all other changes have completed.

Avoids modifying transient and cron keys.

Prevents updates that would break plugin internal configuration.

Batch Processing for Large Tables

The plugin processes large database tables using batches of 5000 rows per iteration.

Advantages:

Prevents PHP memory exhaustion.

Allows safe operations on tables with hundreds of thousands of rows.

Handles WooCommerce and other large metadata tables.

Serialization and JSON Support

The plugin detects and properly handles:

Serialized PHP strings

JSON arrays and objects

Deeply nested structures

Operations:

Data is decoded.

Replacement is applied recursively.

Data is re-encoded or re-serialized safely.

This supports block editors, Elementor, WooCommerce data, and more.

Table Type Detection

Tables are automatically categorized to improve usability:

Types:

Core

WooCommerce

Plugin-specific

Custom

Elementor or Yoast specific tables when patterns are recognized

Protected Table System

Modification warnings are displayed for sensitive tables, such as:

wp_users

wp_usermeta

wp_options

This prevents accidental destructive changes.

Files Added in Version 1.3.0

includes/class-ure-database-manager.php
includes/class-ure-backup-manager.php
templates/admin-advanced-mode.php

Total code added: approximately 2000 lines.

Files Modified

class-ure-plugin.php
class-ure-admin.php
universal-replace-engine.php

New handlers added in class-ure-admin.php:

handle_create_backup

handle_restore_backup

handle_delete_backup

handle_database_preview

handle_database_apply

Comparison with Better Search Replace Pro (Text-Only)

Database operations: Both support this
SQL backup: Both support, URE has more metadata and safer directory design
Multisite support: Both support
Table selector UI: Both support, URE displays more metadata
GUID protection: Both support
Options table safety: URE has stronger deferred write logic
Batch processing: Both support
Preview before apply: Only URE
Undo system: Only URE
Regex mode: Only URE (Pro)
Elementor/JSON support: Only URE
Architecture quality: URE is modern and modular

Performance Expectations

Small sites: a few seconds
Medium sites: seconds to tens of seconds
Large sites: up to one minute
Very large tables: several minutes, depending on batch count

Batch size is configurable in the code.

Safety Guidelines

Always create a backup before using database mode.
Use staging environments for testing.
Avoid modifying the wp_options table without previewing changes.
Use GUID protection to prevent unintended link corruption.
Review all preview data before applying changes.

Conclusion

Version 1.3.0 transforms Universal Replace Engine into a complete database-capable replacement engine. It now operates at the same functional level as commercial database replacement plugins while surpassing them in preview visibility, undo features, JSON compatibility, and architectural design.

Version 1.3.0 is fully backward compatible and ready for production use.