Installation & Quick Start Guide (Condensed)

Quick Installation

Go to your WordPress admin: https://yoursite.com/wp-admin/

Navigate to Plugins → Installed Plugins, find “Universal Replace Engine,” click Activate.

Verify by going to Tools → Universal Replace Engine.

First-Time Setup
The plugin creates the table wp_ure_logs automatically on activation.
You can verify with: SHOW TABLES LIKE 'wp_ure_logs';
Language support includes 10 languages. Change via Settings → General → Site Language.
To debug issues, enable WP_DEBUG and check wp-content/debug.log.

Quick Start Examples
Replace text: Enter search and replace terms, select post types, run Preview, then Apply.
Search only: Enter search term, leave replace blank, run Preview.
Case sensitive: Enable “Case sensitive.”
Undo: Use the History section, click Undo on a previous operation.
Regex (Pro): Enable Regex mode and use standard patterns (dates, phone numbers, URLs, email detection, etc.).

Interface Overview
Main fields:

Search for: required

Replace with: optional

Post Types: required

Case sensitive: optional

Regex mode: Pro only

Preview columns include Location, Before, After.
History shows timestamp, user, summary, and undo option.

System Requirements
Minimum: WP 5.9+, PHP 7.4+, MySQL 5.6+, manage_options capability.
Recommended: WP 6+, PHP 8+, MySQL 5.7+, 256MB memory, 60s execution time.

Troubleshooting
Plugin won’t activate: Check PHP/WP versions and logs.
Table missing: Deactivate/reactivate plugin; ensure DB user can create tables; run installer manually if needed.
Apply button missing: No matches found or JS error—check console, clear cache.
Undo issues: Missing rollback data or deleted posts.
Preview inaccuracies: Check case sensitivity, post type selection, and caches.
Performance issues: Target smaller post sets or increase server limits.

File Permissions
Directories: 755
Files: 644
Ownership depends on server (e.g., www-data:www-data or ISPConfig user).

Security
Only manage_options users can use the plugin.
Best practices: Always preview, test on staging, keep backups, use specific search terms, review history, restrict admin access.

Support
Use README.md, PACKAGING.md, CHANGELOG.md, and this guide.
Submit issues via GitHub, WordPress forums, or email.
When requesting help, provide WP version, PHP version, plugin version, issue description, reproduction steps, logs, and screenshots.

Next Steps
Test a simple search, run a small replacement, verify undo, review history, read the README, bookmark plugin page.

Uninstallation
Deactivate plugin, delete plugin folder, optionally remove database entries:
DROP TABLE wp_ure_logs;
DELETE FROM wp_options WHERE option_name LIKE 'ure_%';

Plugin Version: 1.0.0
Last Updated: 2024-12-09

If you'd like, I can also:
• Compress this even further
• Format it as an install.txt file
• Create a “Quick 30-second install sheet” for end-users
• Produce a developer-focused version for your GitHub repo
• Produce a developer-focused version for your GitHub repo