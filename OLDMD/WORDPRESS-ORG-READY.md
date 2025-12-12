Files and structure – final sanity check

Make sure your plugin folder looks roughly like this:

universal-replace-engine/
universal-replace-engine.php
uninstall.php
readme.txt
README.md
SECURITY-AUDIT-REPORT.md
PACKAGING.md
includes/
class-ure-plugin.php
class-ure-admin.php
class-ure-search-replace.php
class-ure-logger.php
class-ure-database-manager.php
class-ure-backup-manager.php
class-ure-settings.php
class-ure-profiles.php
class-ure-ajax.php
installer.php
templates/
admin-profiles.php
admin-settings.php
admin-help.php
admin-db-search.php
admin-backup.php
assets/
admin.css
admin.js
languages/
universal-replace-engine.pot
universal-replace-engine-.po
universal-replace-engine-.mo
README.md

No node_modules, .git, IDE folders, or temporary files should be inside this directory for the distributed ZIP.

Replace all placeholders

In universal-replace-engine.php header:

Plugin URI: set to your real plugin page URL (your site).

Author: your actual name or company.

Author URI: your real site.

Version: 1.4.0 (confirm it matches readme.txt).

In readme.txt:

Contributors: your wordpress.org username(s).

Plugin URI / Donate link (if you have them).

Support / docs URLs (if you mention them).

Any placeholders like [your-security-email], [your-github-repo].

In README.md and SECURITY-AUDIT-REPORT.md:

Replace [your-security-email] with the real address for security reports.

Replace [your-github-repo] with your actual repository URL (if you reference it).

If you mention a support email, make sure it is a real inbox you check.

Confirm version alignment

Make sure all of these say 1.4.0:

universal-replace-engine.php: Version header.

Any URE_VERSION constant in PHP.

readme.txt: “Stable tag” and any version mentions.

README.md: top-level version references.

CHANGELOG.md: latest entry is 1.4.0 and matches the features you described.

Quick technical checks

From inside the plugin directory:

Run a basic syntax check (if you have CLI access):

php -l universal-replace-engine.php
php -l includes/class-ure-plugin.php
php -l includes/class-ure-admin.php
php -l includes/class-ure-search-replace.php
php -l includes/class-ure-database-manager.php
php -l includes/class-ure-backup-manager.php
php -l includes/class-ure-settings.php
php -l includes/class-ure-profiles.php
php -l includes/class-ure-ajax.php

(Or just run php -l on all PHP files in the tree.)

Check text domain usage:
All translation calls should use "universal-replace-engine".

Confirm uninstall.php:

Uses WP_UNINSTALL_PLUGIN guard.

Cleans up options, user meta, custom tables, and transients as intended.

Functional test on a clean site

On a fresh WordPress install:

Upload the plugin (ZIP) via Plugins → Add New → Upload Plugin.

Activate it.

Go to Tools → Universal Replace Engine and verify:

Basic search and replace works.

Preview is limited appropriately in free mode.

History and rollback work for a simple operation.

Scroll down:

Saved Profiles section is visible.

Save a profile, then load it and confirm the form pre-fills.

Click the Advanced Database Mode button:

Panel opens and closes correctly.

Backup creation works.

Go to Tools → URE Settings:

Change some settings and save.

Reload page: settings persist and validation works.

Go to Tools → URE Help:

Content loads correctly.

Deactivate and then delete the plugin:

Confirm uninstall.php runs without fatal errors.

Confirm custom table(s) and options are removed as expected (or at least what you chose to remove).

Prepare screenshots and assets (optional but recommended)

For the WordPress.org listing later, create PNG files (you can do this after approval, but having them ready is ideal):

screenshot-1.png: main search/replace screen.

screenshot-2.png: preview results.

screenshot-3.png: operation history and rollback.

screenshot-4.png: Saved Profiles section.

screenshot-5.png: Settings page.

screenshot-6.png: Advanced Database Mode.

screenshot-7.png: Help page.

Also prepare:

icon-128x128.png and icon-256x256.png for the plugin icon.

banner-772x250.png and banner-1544x500.png for the plugin banner.

These go in the SVN assets/ directory later (not inside the plugin ZIP itself).

Submission to WordPress.org

Once the plugin ZIP is ready and tested:

Create a wordpress.org account (if needed):

https://wordpress.org/support/register.php

Submit the plugin:

https://wordpress.org/plugins/developers/add/

Fill in name: “Universal Replace Engine”.

Paste description from readme.txt (short section).

Upload your ZIP.

Wait for automated checks:

Plugin should pass, given your security audit and standards compliance.

When approved:

You will receive an SVN repository URL.

Check out the repository with svn, copy your plugin into trunk, create tags/1.4.0, and commit.

Minimal “author todo” list