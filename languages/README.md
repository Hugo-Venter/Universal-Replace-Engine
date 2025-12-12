# Universal Replace Engine - Translations

This directory contains translation files for the Universal Replace Engine plugin.

## Available Languages

The plugin is translated into the following languages:

1. **Spanish (Español)** - es_ES
2. **French (Français)** - fr_FR
3. **German (Deutsch)** - de_DE
4. **Portuguese Brazil (Português)** - pt_BR
5. **Arabic (العربية)** - ar
6. **Chinese Simplified (简体中文)** - zh_CN
7. **Japanese (日本語)** - ja
8. **Italian (Italiano)** - it_IT
9. **Dutch (Nederlands)** - nl_NL
10. **Russian (Русский)** - ru_RU

## How It Works

WordPress automatically loads the appropriate translation file based on your site's language setting.

### To Change Your Site Language:

1. Go to **Settings → General** in WordPress admin
2. Select your language from the **Site Language** dropdown
3. Save changes
4. The plugin will automatically display in the selected language

## File Types

- **.pot** - Template file containing all translatable strings
- **.po** - Portable Object files (human-readable translation files)
- **.mo** - Machine Object files (compiled binary files used by WordPress)

## Updating Translations

### Regenerate POT File

When adding new translatable strings to the plugin:

```bash
cd /path/to/plugin
wp i18n make-pot . languages/universal-replace-engine.pot --domain=universal-replace-engine
```

### Update PO Files

After updating the POT file, merge changes into existing PO files:

```bash
msgmerge -U languages/universal-replace-engine-es_ES.po languages/universal-replace-engine.pot
```

### Compile MO Files

After editing PO files, compile them to MO format:

```bash
msgfmt -o languages/universal-replace-engine-es_ES.mo languages/universal-replace-engine-es_ES.po
```

## Contributing Translations

### Improve Existing Translations

1. Edit the `.po` file for your language
2. Translate the empty `msgstr ""` entries
3. Compile to `.mo` using msgfmt
4. Test in WordPress

### Add New Language

1. Copy the `.pot` file to a new `.po` file with appropriate locale code
   ```bash
   cp universal-replace-engine.pot universal-replace-engine-LOCALE.po
   ```
2. Edit the `.po` file header with language information
3. Translate all msgstr entries
4. Compile to `.mo` format
5. Test in WordPress

## Translation Tools

### Recommended Tools:

- **Poedit** - https://poedit.net/ (GUI editor for PO files)
- **Loco Translate** - WordPress plugin for in-browser translation
- **WPML** - Premium multilingual plugin with translation management
- **GlotPress** - WordPress.org translation platform

### Command Line Tools:

- `wp i18n` - WP-CLI internationalization commands
- `msgfmt` - Compile PO to MO
- `msgmerge` - Merge POT updates into PO files
- `msginit` - Create new PO file from POT

## Translation Coverage

Current translations include the most frequently used strings:
- Plugin name and navigation
- Tab labels
- Form labels and buttons
- Common messages and errors
- Settings page text

## Need Help?

For translation questions or to contribute translations:
- Open an issue on GitHub
- Contact the plugin author
- Join the WordPress translation community

---

**Last Updated:** December 10, 2024
**Plugin Version:** 1.4.0
**Total Translatable Strings:** 318
