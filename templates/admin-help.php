<?php
/**
 * Help/Documentation Page Template
 *
 * Template for help and documentation.
 *
 * @package UniversalReplaceEngine
 * @since 1.4.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$is_pro = apply_filters( 'ure_is_pro', false );
?>

<div class="wrap ure-help-page">
	<h1><?php esc_html_e( 'Universal Replace Engine - Help & Documentation', 'universal-replace-engine' ); ?></h1>

	<div class="ure-help-nav">
		<a href="#getting-started" class="ure-help-link"><?php esc_html_e( 'Getting Started', 'universal-replace-engine' ); ?></a>
		<a href="#features" class="ure-help-link"><?php esc_html_e( 'Features', 'universal-replace-engine' ); ?></a>
		<a href="#profiles" class="ure-help-link"><?php esc_html_e( 'Profiles', 'universal-replace-engine' ); ?></a>
		<a href="#regex" class="ure-help-link"><?php esc_html_e( 'Regex Mode', 'universal-replace-engine' ); ?> <?php if ( ! $is_pro ) : ?><span class="ure-badge ure-badge-pro">PRO</span><?php endif; ?></a>
		<a href="#advanced" class="ure-help-link"><?php esc_html_e( 'Advanced Mode', 'universal-replace-engine' ); ?></a>
		<a href="#troubleshooting" class="ure-help-link"><?php esc_html_e( 'Troubleshooting', 'universal-replace-engine' ); ?></a>
		<a href="#faq" class="ure-help-link"><?php esc_html_e( 'FAQ', 'universal-replace-engine' ); ?></a>
		<a href="#support" class="ure-help-link"><?php esc_html_e( 'Support', 'universal-replace-engine' ); ?></a>
	</div>

	<!-- Getting Started -->
	<div id="getting-started" class="ure-help-section">
		<h2><?php esc_html_e( 'Getting Started', 'universal-replace-engine' ); ?></h2>

		<h3><?php esc_html_e( 'Basic Search & Replace', 'universal-replace-engine' ); ?></h3>
		<ol>
			<li><?php esc_html_e( 'Navigate to Tools → Universal Replace Engine', 'universal-replace-engine' ); ?></li>
			<li><?php esc_html_e( 'Enter your search term in the "Search For" field', 'universal-replace-engine' ); ?></li>
			<li><?php esc_html_e( 'Enter the replacement text in the "Replace With" field (leave empty to just search)', 'universal-replace-engine' ); ?></li>
			<li><?php esc_html_e( 'Select which post types to search in', 'universal-replace-engine' ); ?></li>
			<li><?php esc_html_e( 'Click "Run Preview" to see what will change', 'universal-replace-engine' ); ?></li>
			<li><?php esc_html_e( 'Review the preview carefully', 'universal-replace-engine' ); ?></li>
			<li><?php esc_html_e( 'Click "Apply Changes" to make the replacements', 'universal-replace-engine' ); ?></li>
		</ol>

		<div class="ure-notice ure-notice-info">
			<p><strong><?php esc_html_e( 'Pro Tip:', 'universal-replace-engine' ); ?></strong> <?php esc_html_e( 'Use the rollback feature to undo changes if needed. Keep your last 5 operations safe!', 'universal-replace-engine' ); ?></p>
		</div>
	</div>

	<!-- Features -->
	<div id="features" class="ure-help-section">
		<h2><?php esc_html_e( 'Features Overview', 'universal-replace-engine' ); ?></h2>

		<h3><?php esc_html_e( 'Content Operations', 'universal-replace-engine' ); ?></h3>
		<ul>
			<li><strong><?php esc_html_e( 'Post Content:', 'universal-replace-engine' ); ?></strong> <?php esc_html_e( 'Search and replace in post/page content', 'universal-replace-engine' ); ?></li>
			<li><strong><?php esc_html_e( 'Custom Post Types:', 'universal-replace-engine' ); ?></strong> <?php esc_html_e( 'Works with any post type (products, portfolios, etc.)', 'universal-replace-engine' ); ?></li>
			<li><strong><?php esc_html_e( 'Preview:', 'universal-replace-engine' ); ?></strong> <?php esc_html_e( 'See changes before applying them', 'universal-replace-engine' ); ?></li>
			<li><strong><?php esc_html_e( 'Undo/Rollback:', 'universal-replace-engine' ); ?></strong> <?php esc_html_e( 'Reverse changes with one click', 'universal-replace-engine' ); ?></li>
		</ul>

		<h3><?php esc_html_e( 'Database Operations (Advanced Mode)', 'universal-replace-engine' ); ?></h3>
		<ul>
			<li><strong><?php esc_html_e( 'All Tables:', 'universal-replace-engine' ); ?></strong> <?php esc_html_e( 'Search any WordPress table', 'universal-replace-engine' ); ?></li>
			<li><strong><?php esc_html_e( 'Backup:', 'universal-replace-engine' ); ?></strong> <?php esc_html_e( 'Create SQL backups before changes', 'universal-replace-engine' ); ?></li>
			<li><strong><?php esc_html_e( 'Restore:', 'universal-replace-engine' ); ?></strong> <?php esc_html_e( 'Restore from backup if needed', 'universal-replace-engine' ); ?></li>
			<li><strong><?php esc_html_e( 'GUID Protection:', 'universal-replace-engine' ); ?></strong> <?php esc_html_e( 'Automatically skips WordPress GUIDs', 'universal-replace-engine' ); ?></li>
		</ul>

		<?php if ( $is_pro ) : ?>
			<h3><?php esc_html_e( 'Pro Features', 'universal-replace-engine' ); ?></h3>
			<ul>
				<li><strong><?php esc_html_e( 'Regex Mode:', 'universal-replace-engine' ); ?></strong> <?php esc_html_e( 'Use regular expressions for powerful pattern matching', 'universal-replace-engine' ); ?></li>
				<li><strong><?php esc_html_e( 'Unlimited Preview:', 'universal-replace-engine' ); ?></strong> <?php esc_html_e( 'No limit on preview results', 'universal-replace-engine' ); ?></li>
				<li><strong><?php esc_html_e( 'Extended History:', 'universal-replace-engine' ); ?></strong> <?php esc_html_e( 'Keep up to 50 operations for rollback', 'universal-replace-engine' ); ?></li>
			</ul>
		<?php endif; ?>
	</div>

	<!-- Profiles -->
	<div id="profiles" class="ure-help-section">
		<h2><?php esc_html_e( 'Using Saved Profiles', 'universal-replace-engine' ); ?></h2>
		<p><?php esc_html_e( 'Profiles let you save common search/replace configurations for quick reuse.', 'universal-replace-engine' ); ?></p>

		<h3><?php esc_html_e( 'Saving a Profile', 'universal-replace-engine' ); ?></h3>
		<ol>
			<li><?php esc_html_e( 'Configure your search/replace settings', 'universal-replace-engine' ); ?></li>
			<li><?php esc_html_e( 'Scroll to "Save Current Settings"', 'universal-replace-engine' ); ?></li>
			<li><?php esc_html_e( 'Enter a descriptive name (e.g., "Domain Migration")', 'universal-replace-engine' ); ?></li>
			<li><?php esc_html_e( 'Click "Save Current Settings as Profile"', 'universal-replace-engine' ); ?></li>
		</ol>

		<h3><?php esc_html_e( 'Loading a Profile', 'universal-replace-engine' ); ?></h3>
		<ol>
			<li><?php esc_html_e( 'Select a profile from the dropdown', 'universal-replace-engine' ); ?></li>
			<li><?php esc_html_e( 'Click "Load"', 'universal-replace-engine' ); ?></li>
			<li><?php esc_html_e( 'Review the loaded settings', 'universal-replace-engine' ); ?></li>
			<li><?php esc_html_e( 'Run Preview or Apply', 'universal-replace-engine' ); ?></li>
		</ol>

		<h3><?php esc_html_e( 'Common Profile Examples', 'universal-replace-engine' ); ?></h3>
		<ul>
			<li><strong><?php esc_html_e( 'Domain Migration:', 'universal-replace-engine' ); ?></strong> <code>http://old-domain.com</code> → <code>https://new-domain.com</code></li>
			<li><strong><?php esc_html_e( 'HTTPS Migration:', 'universal-replace-engine' ); ?></strong> <code>http://</code> → <code>https://</code></li>
			<li><strong><?php esc_html_e( 'Fix Typos:', 'universal-replace-engine' ); ?></strong> <code>occured</code> → <code>occurred</code></li>
			<li><strong><?php esc_html_e( 'Update Company Name:', 'universal-replace-engine' ); ?></strong> <code>Old Company Inc.</code> → <code>New Company LLC</code></li>
		</ul>
	</div>

	<!-- Regex Mode -->
	<div id="regex" class="ure-help-section">
		<h2><?php esc_html_e( 'Regex Mode (Pro)', 'universal-replace-engine' ); ?></h2>

		<?php if ( ! $is_pro ) : ?>
			<div class="ure-notice ure-notice-info">
				<p><strong><?php esc_html_e( 'Pro Feature', 'universal-replace-engine' ); ?></strong></p>
				<p><?php esc_html_e( 'Regular expressions (regex) mode is available in the Pro version. Upgrade to unlock advanced pattern matching capabilities.', 'universal-replace-engine' ); ?></p>
				<p><a href="https://xtech.red/" target="_blank" class="button button-primary"><?php esc_html_e( 'Upgrade to Pro', 'universal-replace-engine' ); ?></a></p>
			</div>
		<?php endif; ?>

		<h3><?php esc_html_e( 'What is Regex Mode?', 'universal-replace-engine' ); ?></h3>
		<p><?php esc_html_e( 'Regular expressions (regex) allow you to search for complex patterns instead of exact text matches. This is powerful for finding and replacing text that follows a specific format.', 'universal-replace-engine' ); ?></p>

		<h3><?php esc_html_e( 'When to Use Regex', 'universal-replace-engine' ); ?></h3>
		<ul>
			<li><?php esc_html_e( 'Finding phone numbers in various formats', 'universal-replace-engine' ); ?></li>
			<li><?php esc_html_e( 'Matching email addresses', 'universal-replace-engine' ); ?></li>
			<li><?php esc_html_e( 'Finding URLs with specific patterns', 'universal-replace-engine' ); ?></li>
			<li><?php esc_html_e( 'Replacing dates in different formats', 'universal-replace-engine' ); ?></li>
			<li><?php esc_html_e( 'Extracting or modifying HTML tags', 'universal-replace-engine' ); ?></li>
			<li><?php esc_html_e( 'Finding repeated words or patterns', 'universal-replace-engine' ); ?></li>
		</ul>

		<h3><?php esc_html_e( 'Common Regex Patterns & Examples', 'universal-replace-engine' ); ?></h3>

		<div class="ure-regex-examples">
			<h4><?php esc_html_e( '1. Phone Numbers', 'universal-replace-engine' ); ?></h4>
			<table class="ure-example-table">
				<tr>
					<td><strong><?php esc_html_e( 'Search:', 'universal-replace-engine' ); ?></strong></td>
					<td><code>\(?\d{3}\)?[-.\s]?\d{3}[-.\s]?\d{4}</code></td>
				</tr>
				<tr>
					<td><strong><?php esc_html_e( 'Matches:', 'universal-replace-engine' ); ?></strong></td>
					<td>555-123-4567, (555) 123-4567, 555.123.4567</td>
				</tr>
				<tr>
					<td><strong><?php esc_html_e( 'Replace:', 'universal-replace-engine' ); ?></strong></td>
					<td><code>1-800-NEW-PHONE</code></td>
				</tr>
			</table>

			<h4><?php esc_html_e( '2. Email Addresses', 'universal-replace-engine' ); ?></h4>
			<table class="ure-example-table">
				<tr>
					<td><strong><?php esc_html_e( 'Search:', 'universal-replace-engine' ); ?></strong></td>
					<td><code>[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}</code></td>
				</tr>
				<tr>
					<td><strong><?php esc_html_e( 'Matches:', 'universal-replace-engine' ); ?></strong></td>
					<td>user@example.com, support@xtech.red</td>
				</tr>
				<tr>
					<td><strong><?php esc_html_e( 'Replace:', 'universal-replace-engine' ); ?></strong></td>
					<td><code>contact@newdomain.com</code></td>
				</tr>
			</table>

			<h4><?php esc_html_e( '3. URLs with Specific Domain', 'universal-replace-engine' ); ?></h4>
			<table class="ure-example-table">
				<tr>
					<td><strong><?php esc_html_e( 'Search:', 'universal-replace-engine' ); ?></strong></td>
					<td><code>https?://oldsite\.com(/[^\s"\']*)?</code></td>
				</tr>
				<tr>
					<td><strong><?php esc_html_e( 'Matches:', 'universal-replace-engine' ); ?></strong></td>
					<td>http://oldsite.com/page, https://oldsite.com/blog/post</td>
				</tr>
				<tr>
					<td><strong><?php esc_html_e( 'Replace:', 'universal-replace-engine' ); ?></strong></td>
					<td><code>https://newsite.com$1</code> <?php esc_html_e( '(preserves path with $1)', 'universal-replace-engine' ); ?></td>
				</tr>
			</table>

			<h4><?php esc_html_e( '4. Dates (MM/DD/YYYY to DD-MM-YYYY)', 'universal-replace-engine' ); ?></h4>
			<table class="ure-example-table">
				<tr>
					<td><strong><?php esc_html_e( 'Search:', 'universal-replace-engine' ); ?></strong></td>
					<td><code>(\d{2})/(\d{2})/(\d{4})</code></td>
				</tr>
				<tr>
					<td><strong><?php esc_html_e( 'Matches:', 'universal-replace-engine' ); ?></strong></td>
					<td>12/25/2024, 01/15/2025</td>
				</tr>
				<tr>
					<td><strong><?php esc_html_e( 'Replace:', 'universal-replace-engine' ); ?></strong></td>
					<td><code>$2-$1-$3</code> <?php esc_html_e( '(becomes 25-12-2024)', 'universal-replace-engine' ); ?></td>
				</tr>
			</table>

			<h4><?php esc_html_e( '5. HTML Image Tags', 'universal-replace-engine' ); ?></h4>
			<table class="ure-example-table">
				<tr>
					<td><strong><?php esc_html_e( 'Search:', 'universal-replace-engine' ); ?></strong></td>
					<td><code>&lt;img\s+src="([^"]*)"</code></td>
				</tr>
				<tr>
					<td><strong><?php esc_html_e( 'Matches:', 'universal-replace-engine' ); ?></strong></td>
					<td>&lt;img src="/old-path/image.jpg"&gt;</td>
				</tr>
				<tr>
					<td><strong><?php esc_html_e( 'Replace:', 'universal-replace-engine' ); ?></strong></td>
					<td><code>&lt;img src="https://cdn.example.com$1"</code></td>
				</tr>
			</table>

			<h4><?php esc_html_e( '6. Shortcodes with Parameters', 'universal-replace-engine' ); ?></h4>
			<table class="ure-example-table">
				<tr>
					<td><strong><?php esc_html_e( 'Search:', 'universal-replace-engine' ); ?></strong></td>
					<td><code>\[button\s+url="([^"]*)"\](.*?)\[/button\]</code></td>
				</tr>
				<tr>
					<td><strong><?php esc_html_e( 'Matches:', 'universal-replace-engine' ); ?></strong></td>
					<td>[button url="/old-link"]Click Here[/button]</td>
				</tr>
				<tr>
					<td><strong><?php esc_html_e( 'Replace:', 'universal-replace-engine' ); ?></strong></td>
					<td><code>[btn link="$1"]$2[/btn]</code></td>
				</tr>
			</table>

			<h4><?php esc_html_e( '7. Remove Extra Whitespace', 'universal-replace-engine' ); ?></h4>
			<table class="ure-example-table">
				<tr>
					<td><strong><?php esc_html_e( 'Search:', 'universal-replace-engine' ); ?></strong></td>
					<td><code>\s{2,}</code></td>
				</tr>
				<tr>
					<td><strong><?php esc_html_e( 'Matches:', 'universal-replace-engine' ); ?></strong></td>
					<td><?php esc_html_e( 'Multiple consecutive spaces/newlines', 'universal-replace-engine' ); ?></td>
				</tr>
				<tr>
					<td><strong><?php esc_html_e( 'Replace:', 'universal-replace-engine' ); ?></strong></td>
					<td><code> </code> <?php esc_html_e( '(single space)', 'universal-replace-engine' ); ?></td>
				</tr>
			</table>

			<h4><?php esc_html_e( '8. Find Price Ranges', 'universal-replace-engine' ); ?></h4>
			<table class="ure-example-table">
				<tr>
					<td><strong><?php esc_html_e( 'Search:', 'universal-replace-engine' ); ?></strong></td>
					<td><code>\$(\d+)\.(\d{2})</code></td>
				</tr>
				<tr>
					<td><strong><?php esc_html_e( 'Matches:', 'universal-replace-engine' ); ?></strong></td>
					<td>$19.99, $149.00</td>
				</tr>
				<tr>
					<td><strong><?php esc_html_e( 'Replace:', 'universal-replace-engine' ); ?></strong></td>
					<td><code>€$1,$2</code> <?php esc_html_e( '(convert to Euro format)', 'universal-replace-engine' ); ?></td>
				</tr>
			</table>
		</div>

		<h3><?php esc_html_e( 'Capture Groups & Backreferences', 'universal-replace-engine' ); ?></h3>
		<p><?php esc_html_e( 'Use parentheses () to capture parts of your match, then reference them in the replacement with $1, $2, $3, etc.', 'universal-replace-engine' ); ?></p>
		<table class="ure-example-table">
			<tr>
				<th><?php esc_html_e( 'Pattern', 'universal-replace-engine' ); ?></th>
				<th><?php esc_html_e( 'Description', 'universal-replace-engine' ); ?></th>
			</tr>
			<tr>
				<td><code>$1</code></td>
				<td><?php esc_html_e( 'First capture group', 'universal-replace-engine' ); ?></td>
			</tr>
			<tr>
				<td><code>$2</code></td>
				<td><?php esc_html_e( 'Second capture group', 'universal-replace-engine' ); ?></td>
			</tr>
			<tr>
				<td><code>$3</code></td>
				<td><?php esc_html_e( 'Third capture group (and so on...)', 'universal-replace-engine' ); ?></td>
			</tr>
		</table>

		<h3><?php esc_html_e( 'Common Regex Symbols', 'universal-replace-engine' ); ?></h3>
		<table class="ure-example-table">
			<tr>
				<th><?php esc_html_e( 'Symbol', 'universal-replace-engine' ); ?></th>
				<th><?php esc_html_e( 'Meaning', 'universal-replace-engine' ); ?></th>
				<th><?php esc_html_e( 'Example', 'universal-replace-engine' ); ?></th>
			</tr>
			<tr>
				<td><code>.</code></td>
				<td><?php esc_html_e( 'Any character', 'universal-replace-engine' ); ?></td>
				<td><code>a.c</code> <?php esc_html_e( 'matches abc, a1c, a*c', 'universal-replace-engine' ); ?></td>
			</tr>
			<tr>
				<td><code>\d</code></td>
				<td><?php esc_html_e( 'Any digit (0-9)', 'universal-replace-engine' ); ?></td>
				<td><code>\d{3}</code> <?php esc_html_e( 'matches 123, 999', 'universal-replace-engine' ); ?></td>
			</tr>
			<tr>
				<td><code>\w</code></td>
				<td><?php esc_html_e( 'Word character (a-z, A-Z, 0-9, _)', 'universal-replace-engine' ); ?></td>
				<td><code>\w+</code> <?php esc_html_e( 'matches word, name_123', 'universal-replace-engine' ); ?></td>
			</tr>
			<tr>
				<td><code>\s</code></td>
				<td><?php esc_html_e( 'Whitespace (space, tab, newline)', 'universal-replace-engine' ); ?></td>
				<td><code>\s+</code> <?php esc_html_e( 'matches one or more spaces', 'universal-replace-engine' ); ?></td>
			</tr>
			<tr>
				<td><code>*</code></td>
				<td><?php esc_html_e( 'Zero or more times', 'universal-replace-engine' ); ?></td>
				<td><code>a*</code> <?php esc_html_e( 'matches "", "a", "aaa"', 'universal-replace-engine' ); ?></td>
			</tr>
			<tr>
				<td><code>+</code></td>
				<td><?php esc_html_e( 'One or more times', 'universal-replace-engine' ); ?></td>
				<td><code>a+</code> <?php esc_html_e( 'matches "a", "aaa"', 'universal-replace-engine' ); ?></td>
			</tr>
			<tr>
				<td><code>?</code></td>
				<td><?php esc_html_e( 'Zero or one time (optional)', 'universal-replace-engine' ); ?></td>
				<td><code>https?</code> <?php esc_html_e( 'matches http, https', 'universal-replace-engine' ); ?></td>
			</tr>
			<tr>
				<td><code>^</code></td>
				<td><?php esc_html_e( 'Start of line', 'universal-replace-engine' ); ?></td>
				<td><code>^Hello</code> <?php esc_html_e( 'matches lines starting with Hello', 'universal-replace-engine' ); ?></td>
			</tr>
			<tr>
				<td><code>$</code></td>
				<td><?php esc_html_e( 'End of line', 'universal-replace-engine' ); ?></td>
				<td><code>end$</code> <?php esc_html_e( 'matches lines ending with end', 'universal-replace-engine' ); ?></td>
			</tr>
			<tr>
				<td><code>[]</code></td>
				<td><?php esc_html_e( 'Character class', 'universal-replace-engine' ); ?></td>
				<td><code>[aeiou]</code> <?php esc_html_e( 'matches any vowel', 'universal-replace-engine' ); ?></td>
			</tr>
			<tr>
				<td><code>|</code></td>
				<td><?php esc_html_e( 'OR operator', 'universal-replace-engine' ); ?></td>
				<td><code>cat|dog</code> <?php esc_html_e( 'matches cat or dog', 'universal-replace-engine' ); ?></td>
			</tr>
		</table>

		<h3><?php esc_html_e( 'Safety Tips for Regex', 'universal-replace-engine' ); ?></h3>
		<div class="ure-notice ure-notice-warning">
			<ul>
				<li><strong><?php esc_html_e( 'Always test first:', 'universal-replace-engine' ); ?></strong> <?php esc_html_e( 'Run a preview to see what will be matched', 'universal-replace-engine' ); ?></li>
				<li><strong><?php esc_html_e( 'Start simple:', 'universal-replace-engine' ); ?></strong> <?php esc_html_e( 'Build your pattern gradually and test each addition', 'universal-replace-engine' ); ?></li>
				<li><strong><?php esc_html_e( 'Escape special characters:', 'universal-replace-engine' ); ?></strong> <?php esc_html_e( 'Use backslash \\ before . $ ^ * + ? ( ) [ ] { } |', 'universal-replace-engine' ); ?></li>
				<li><strong><?php esc_html_e( 'Use specific patterns:', 'universal-replace-engine' ); ?></strong> <?php esc_html_e( 'Avoid overly broad patterns like .* that match too much', 'universal-replace-engine' ); ?></li>
				<li><strong><?php esc_html_e( 'Test on staging:', 'universal-replace-engine' ); ?></strong> <?php esc_html_e( 'Never test complex regex on production first', 'universal-replace-engine' ); ?></li>
			</ul>
		</div>

		<h3><?php esc_html_e( 'Testing Your Regex', 'universal-replace-engine' ); ?></h3>
		<p><?php esc_html_e( 'Before using regex in Universal Replace Engine, you can test your patterns at:', 'universal-replace-engine' ); ?></p>
		<ul>
			<li><a href="https://regex101.com/" target="_blank">Regex101.com</a> <?php esc_html_e( '- Interactive regex tester with explanations', 'universal-replace-engine' ); ?></li>
			<li><a href="https://regexr.com/" target="_blank">RegExr.com</a> <?php esc_html_e( '- Another excellent regex testing tool', 'universal-replace-engine' ); ?></li>
		</ul>
	</div>

	<!-- Advanced Mode -->
	<div id="advanced" class="ure-help-section">
		<h2><?php esc_html_e( 'Advanced Database Mode', 'universal-replace-engine' ); ?></h2>

		<div class="ure-notice ure-notice-warning">
			<p><strong><?php esc_html_e( 'Warning:', 'universal-replace-engine' ); ?></strong> <?php esc_html_e( 'Advanced mode can modify any database table. Always create a backup first!', 'universal-replace-engine' ); ?></p>
		</div>

		<h3><?php esc_html_e( 'When to Use Advanced Mode', 'universal-replace-engine' ); ?></h3>
		<ul>
			<li><?php esc_html_e( 'Domain migrations (update siteurl, home options)', 'universal-replace-engine' ); ?></li>
			<li><?php esc_html_e( 'Fix serialized data in post meta', 'universal-replace-engine' ); ?></li>
			<li><?php esc_html_e( 'Update user metadata', 'universal-replace-engine' ); ?></li>
			<li><?php esc_html_e( 'Fix plugin settings after migration', 'universal-replace-engine' ); ?></li>
		</ul>

		<h3><?php esc_html_e( 'Safe Usage', 'universal-replace-engine' ); ?></h3>
		<ol>
			<li><?php esc_html_e( 'Create a backup first', 'universal-replace-engine' ); ?></li>
			<li><?php esc_html_e( 'Select only the tables you need to modify', 'universal-replace-engine' ); ?></li>
			<li><?php esc_html_e( 'Use very specific search terms', 'universal-replace-engine' ); ?></li>
			<li><?php esc_html_e( 'Run a preview first', 'universal-replace-engine' ); ?></li>
			<li><?php esc_html_e( 'Test on a staging site before production', 'universal-replace-engine' ); ?></li>
		</ol>

		<h3><?php esc_html_e( 'Understanding the "Protected" Badge', 'universal-replace-engine' ); ?></h3>
		<p><?php esc_html_e( 'In the database table selector, some tables are marked with a "Protected" badge. This is a visual warning to indicate critical WordPress core tables.', 'universal-replace-engine' ); ?></p>

		<h4><?php esc_html_e( 'Protected Tables:', 'universal-replace-engine' ); ?></h4>
		<ul>
			<li><strong>wp_users</strong> - <?php esc_html_e( 'Contains all user account information', 'universal-replace-engine' ); ?></li>
			<li><strong>wp_usermeta</strong> - <?php esc_html_e( 'Stores user metadata (preferences, capabilities, roles)', 'universal-replace-engine' ); ?></li>
			<li><strong>wp_options</strong> - <?php esc_html_e( 'Contains site settings and configuration', 'universal-replace-engine' ); ?></li>
		</ul>

		<h4><?php esc_html_e( 'Why Are They Protected?', 'universal-replace-engine' ); ?></h4>
		<p><?php esc_html_e( 'These tables contain critical data. Modifying them incorrectly can:', 'universal-replace-engine' ); ?></p>
		<ul>
			<li><?php esc_html_e( 'Lock you out of your admin account', 'universal-replace-engine' ); ?></li>
			<li><?php esc_html_e( 'Break site functionality and features', 'universal-replace-engine' ); ?></li>
			<li><?php esc_html_e( 'Corrupt user permissions and roles', 'universal-replace-engine' ); ?></li>
			<li><?php esc_html_e( 'Break plugin and theme settings', 'universal-replace-engine' ); ?></li>
		</ul>

		<h4><?php esc_html_e( 'Can I Still Modify Protected Tables?', 'universal-replace-engine' ); ?></h4>
		<p><?php esc_html_e( 'Yes! The "Protected" badge is just a warning, not a restriction. You can still select and modify these tables. The badge reminds you to be extra careful when working with critical data.', 'universal-replace-engine' ); ?></p>

		<div class="ure-notice ure-notice-warning">
			<h4><?php esc_html_e( 'Best Practices for Protected Tables:', 'universal-replace-engine' ); ?></h4>
			<ul>
				<li><strong><?php esc_html_e( 'Always create a backup first', 'universal-replace-engine' ); ?></strong> - <?php esc_html_e( 'Especially important for these tables', 'universal-replace-engine' ); ?></li>
				<li><strong><?php esc_html_e( 'Use very specific search terms', 'universal-replace-engine' ); ?></strong> - <?php esc_html_e( 'Avoid broad patterns that might match too much', 'universal-replace-engine' ); ?></li>
				<li><strong><?php esc_html_e( 'Run a preview first', 'universal-replace-engine' ); ?></strong> - <?php esc_html_e( 'Verify exactly what will change', 'universal-replace-engine' ); ?></li>
				<li><strong><?php esc_html_e( 'Test on staging', 'universal-replace-engine' ); ?></strong> - <?php esc_html_e( 'Never test on production first', 'universal-replace-engine' ); ?></li>
				<li><strong><?php esc_html_e( 'Know what you\'re changing', 'universal-replace-engine' ); ?></strong> - <?php esc_html_e( 'Understand the data structure before modifying', 'universal-replace-engine' ); ?></li>
			</ul>
		</div>

		<h4><?php esc_html_e( 'Common Safe Operations on Protected Tables:', 'universal-replace-engine' ); ?></h4>
		<p><strong>wp_options <?php esc_html_e( 'table:', 'universal-replace-engine' ); ?></strong></p>
		<ul>
			<li><?php esc_html_e( 'Changing site URLs during migration (siteurl, home)', 'universal-replace-engine' ); ?></li>
			<li><?php esc_html_e( 'Updating email addresses', 'universal-replace-engine' ); ?></li>
			<li><?php esc_html_e( 'Fixing plugin settings after domain change', 'universal-replace-engine' ); ?></li>
		</ul>

		<p><strong>wp_users / wp_usermeta <?php esc_html_e( 'tables:', 'universal-replace-engine' ); ?></strong></p>
		<ul>
			<li><?php esc_html_e( 'Updating email domains after company rebranding', 'universal-replace-engine' ); ?></li>
			<li><?php esc_html_e( 'Fixing user URLs after site migration', 'universal-replace-engine' ); ?></li>
		</ul>
	</div>

	<!-- Troubleshooting -->
	<div id="troubleshooting" class="ure-help-section">
		<h2><?php esc_html_e( 'Troubleshooting', 'universal-replace-engine' ); ?></h2>

		<h3><?php esc_html_e( 'Operation Times Out', 'universal-replace-engine' ); ?></h3>
		<ul>
			<li><?php esc_html_e( 'Go to Settings and reduce the batch size', 'universal-replace-engine' ); ?></li>
			<li><?php esc_html_e( 'Try Content Batch Size: 50 or Database Batch Size: 2500', 'universal-replace-engine' ); ?></li>
			<li><?php esc_html_e( 'Contact your host to increase PHP max_execution_time', 'universal-replace-engine' ); ?></li>
		</ul>

		<h3><?php esc_html_e( 'Memory Limit Errors', 'universal-replace-engine' ); ?></h3>
		<ul>
			<li><?php esc_html_e( 'Reduce batch sizes in Settings', 'universal-replace-engine' ); ?></li>
			<li><?php esc_html_e( 'Process fewer post types at once', 'universal-replace-engine' ); ?></li>
			<li><?php esc_html_e( 'Ask your host to increase PHP memory_limit', 'universal-replace-engine' ); ?></li>
		</ul>

		<h3><?php esc_html_e( 'Changes Not Visible', 'universal-replace-engine' ); ?></h3>
		<ul>
			<li><?php esc_html_e( 'Clear all caches (WordPress, plugin, CDN, browser)', 'universal-replace-engine' ); ?></li>
			<li><?php esc_html_e( 'For Elementor pages: Regenerate CSS in Elementor settings', 'universal-replace-engine' ); ?></li>
			<li><?php esc_html_e( 'Check the Operation History to verify changes were applied', 'universal-replace-engine' ); ?></li>
		</ul>
	</div>

	<!-- FAQ -->
	<div id="faq" class="ure-help-section">
		<h2><?php esc_html_e( 'Frequently Asked Questions', 'universal-replace-engine' ); ?></h2>

		<h3><?php esc_html_e( 'Q: Can I undo changes after applying them?', 'universal-replace-engine' ); ?></h3>
		<p><?php esc_html_e( 'A: Yes! Click "Operation History" and use the "Undo" button to rollback. The plugin keeps your last 5 operations (free) or 50 operations (Pro).', 'universal-replace-engine' ); ?></p>

		<h3><?php esc_html_e( 'Q: Does this work with Elementor/Gutenberg?', 'universal-replace-engine' ); ?></h3>
		<p><?php esc_html_e( 'A: Yes! The plugin handles serialized data and JSON correctly. For Elementor, caches are automatically cleared after changes.', 'universal-replace-engine' ); ?></p>

		<h3><?php esc_html_e( 'Q: Will this break my site?', 'universal-replace-engine' ); ?></h3>
		<p><?php esc_html_e( 'A: Not if you use it correctly! Always preview changes first, be specific with search terms, and test on staging before production. The undo feature provides additional safety.', 'universal-replace-engine' ); ?></p>

		<h3><?php esc_html_e( 'Q: What\'s the difference between Content and Advanced mode?', 'universal-replace-engine' ); ?></h3>
		<p><?php esc_html_e( 'A: Content mode searches only post/page content. Advanced mode searches any database table (wp_options, postmeta, usermeta, etc.). Advanced mode requires more caution.', 'universal-replace-engine' ); ?></p>

		<h3><?php esc_html_e( 'Q: Can I use this for domain migrations?', 'universal-replace-engine' ); ?></h3>
		<p><?php esc_html_e( 'A: Yes! Use Advanced mode, create a backup first, then replace old URLs with new ones across wp_options and wp_posts tables. Save this as a profile for future use.', 'universal-replace-engine' ); ?></p>

		<h3><?php esc_html_e( 'Q: How do I upgrade to Pro?', 'universal-replace-engine' ); ?></h3>
		<p><?php esc_html_e( 'A: Contact the plugin developer for Pro licensing. Pro includes regex mode, unlimited previews, and extended history.', 'universal-replace-engine' ); ?></p>
	</div>

	<!-- Support -->
	<div id="support" class="ure-help-section">
		<h2><?php esc_html_e( 'Contact Support', 'universal-replace-engine' ); ?></h2>

		<?php if ( $is_pro ) : ?>
			<div class="ure-notice ure-notice-info">
				<p><strong><?php esc_html_e( '🎉 Priority Support Active', 'universal-replace-engine' ); ?></strong></p>
				<p><?php esc_html_e( 'As a Pro user, you have access to priority email support with faster response times.', 'universal-replace-engine' ); ?></p>
			</div>
		<?php endif; ?>

		<h3><?php esc_html_e( 'Email Support', 'universal-replace-engine' ); ?></h3>
		<p>
			<?php esc_html_e( 'Need help? Our support team is here to assist you.', 'universal-replace-engine' ); ?>
		</p>
		<p>
			<strong><?php esc_html_e( 'Email:', 'universal-replace-engine' ); ?></strong>
			<a href="mailto:support@xtech.red">support@xtech.red</a>
		</p>

		<?php if ( $is_pro ) : ?>
			<div class="ure-support-box">
				<h4><?php esc_html_e( 'Important: Include Your Serial Number', 'universal-replace-engine' ); ?></h4>
				<p><?php esc_html_e( 'To ensure priority support, please include your Pro license serial number in your email. This helps us:', 'universal-replace-engine' ); ?></p>
				<ul>
					<li><?php esc_html_e( 'Verify your Pro license status', 'universal-replace-engine' ); ?></li>
					<li><?php esc_html_e( 'Prioritize your support request', 'universal-replace-engine' ); ?></li>
					<li><?php esc_html_e( 'Provide version-specific assistance', 'universal-replace-engine' ); ?></li>
					<li><?php esc_html_e( 'Fast-track bug fixes and updates', 'universal-replace-engine' ); ?></li>
				</ul>
				<p><strong><?php esc_html_e( 'Response time for Pro users: 24-48 hours', 'universal-replace-engine' ); ?></strong></p>
			</div>
		<?php else : ?>
			<div class="ure-support-box">
				<h4><?php esc_html_e( 'For Pro Users: Include Your Serial Number', 'universal-replace-engine' ); ?></h4>
				<p><?php esc_html_e( 'If you have a Pro license, please include your serial number in your support email for priority assistance.', 'universal-replace-engine' ); ?></p>
				<p><strong><?php esc_html_e( 'Pro users receive priority support with 24-48 hour response time.', 'universal-replace-engine' ); ?></strong></p>
				<p><?php esc_html_e( 'Free version users: We provide community support on a best-effort basis. Response time may vary.', 'universal-replace-engine' ); ?></p>
			</div>
		<?php endif; ?>

		<h3><?php esc_html_e( 'Before Contacting Support', 'universal-replace-engine' ); ?></h3>
		<p><?php esc_html_e( 'To help us assist you faster, please include:', 'universal-replace-engine' ); ?></p>
		<ul>
			<li><?php esc_html_e( 'Plugin version (check Settings page)', 'universal-replace-engine' ); ?></li>
			<li><?php esc_html_e( 'WordPress version', 'universal-replace-engine' ); ?></li>
			<li><?php esc_html_e( 'PHP version (check Settings page)', 'universal-replace-engine' ); ?></li>
			<li><?php esc_html_e( 'Description of the issue or question', 'universal-replace-engine' ); ?></li>
			<li><?php esc_html_e( 'Steps to reproduce (if reporting a bug)', 'universal-replace-engine' ); ?></li>
			<li><?php esc_html_e( 'Screenshots (if applicable)', 'universal-replace-engine' ); ?></li>
			<?php if ( $is_pro ) : ?>
				<li><strong><?php esc_html_e( 'Your Pro license serial number', 'universal-replace-engine' ); ?></strong></li>
			<?php endif; ?>
		</ul>

		<h3><?php esc_html_e( 'Upgrade to Pro', 'universal-replace-engine' ); ?></h3>
		<p>
			<?php esc_html_e( 'Want priority support and advanced features?', 'universal-replace-engine' ); ?>
			<a href="https://xtech.red/" target="_blank"><?php esc_html_e( 'Upgrade to Pro', 'universal-replace-engine' ); ?></a>
		</p>
		<p><?php esc_html_e( 'Pro includes: Regex mode, unlimited preview, extended history, and priority email support.', 'universal-replace-engine' ); ?></p>
	</div>

	<!-- Back to Top -->
	<p class="ure-back-to-top">
		<a href="#top"><?php esc_html_e( '↑ Back to Top', 'universal-replace-engine' ); ?></a>
	</p>
</div>

<style>
.ure-help-page {
	max-width: 900px;
}

.ure-help-nav {
	background: #fff;
	border: 1px solid #ccd0d4;
	padding: 15px 20px;
	margin: 20px 0;
	border-radius: 4px;
	display: flex;
	flex-wrap: wrap;
	gap: 15px;
}

.ure-help-link {
	text-decoration: none;
	font-weight: 600;
}

.ure-help-section {
	background: #fff;
	border: 1px solid #ccd0d4;
	padding: 20px 30px;
	margin: 20px 0;
	border-radius: 4px;
}

.ure-help-section h2 {
	margin-top: 0;
	border-bottom: 2px solid #0073aa;
	padding-bottom: 10px;
}

.ure-help-section h3 {
	color: #0073aa;
}

.ure-help-section code {
	background: #f3f4f5;
	padding: 2px 6px;
	border-radius: 3px;
	font-size: 13px;
}

.ure-notice {
	padding: 15px;
	border-left: 4px solid;
	margin: 15px 0;
}

.ure-notice-info {
	background: #e5f5fa;
	border-color: #00a0d2;
}

.ure-notice-warning {
	background: #fff8e5;
	border-color: #ffb900;
}

.ure-back-to-top {
	text-align: center;
	margin: 30px 0;
}

.ure-back-to-top a {
	text-decoration: none;
	font-weight: 600;
	font-size: 16px;
}

.ure-support-box {
	background: #f8f9fa;
	border: 2px solid #0073aa;
	border-radius: 4px;
	padding: 20px;
	margin: 20px 0;
}

.ure-support-box h4 {
	margin-top: 0;
	color: #0073aa;
	font-size: 16px;
}

.ure-support-box ul {
	margin-left: 20px;
}

.ure-support-box strong {
	color: #d63638;
}

.ure-regex-examples {
	margin: 20px 0;
}

.ure-regex-examples h4 {
	color: #0073aa;
	margin-top: 25px;
	margin-bottom: 10px;
	font-size: 15px;
}

.ure-example-table {
	width: 100%;
	border-collapse: collapse;
	margin-bottom: 20px;
	background: #fff;
	border: 1px solid #ddd;
}

.ure-example-table th,
.ure-example-table td {
	padding: 10px 12px;
	border: 1px solid #ddd;
	text-align: left;
}

.ure-example-table th {
	background: #f9f9f9;
	font-weight: 600;
	color: #333;
}

.ure-example-table td:first-child {
	width: 20%;
	font-weight: 600;
	background: #fafafa;
}

.ure-example-table code {
	background: #f0f0f0;
	padding: 3px 6px;
	border-radius: 3px;
	font-size: 13px;
	color: #d63638;
	font-family: 'Courier New', Courier, monospace;
}

.ure-example-table tr:hover {
	background: #f5f5f5;
}
</style>
