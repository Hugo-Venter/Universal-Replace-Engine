/**
 * Universal Replace Engine - Admin JavaScript
 *
 * @package UniversalReplaceEngine
 * @since 1.0.0
 */

(function($) {
	'use strict';

	/**
	 * Initialize when document is ready.
	 */
	$(document).ready(function() {
		URE_Admin.init();
	});

	/**
	 * Admin functionality object.
	 */
	var URE_Admin = {

		/**
		 * Initialize admin functionality.
		 */
		init: function() {
			this.confirmApply();
			this.confirmUndo();
			this.validateForm();
			this.enhanceUI();
			this.handleProfileSave();
		},

		/**
		 * Confirm before applying changes.
		 */
		confirmApply: function() {
			$('.ure-apply-btn').on('click', function(e) {
				var confirmMessage = 'Are you sure you want to apply these changes? This will modify your database.';

				if (!confirm(confirmMessage)) {
					e.preventDefault();
					return false;
				}
			});
		},

		/**
		 * Confirm before undoing (already in HTML, but adding JS fallback).
		 */
		confirmUndo: function() {
			$('.ure-undo-btn').on('click', function(e) {
				var confirmMessage = 'Are you sure you want to undo this operation? This will restore the previous content.';

				if (!confirm(confirmMessage)) {
					e.preventDefault();
					return false;
				}
			});
		},

		/**
		 * Form validation.
		 */
		validateForm: function() {
			$('.ure-form').on('submit', function(e) {
				var searchField = $('#ure_search');
				var searchValue = searchField.val().trim();

				// Ensure search field is not empty.
				if (searchValue === '') {
					alert('Please enter a search term.');
					searchField.focus();
					e.preventDefault();
					return false;
				}

				// Check if at least one post type is selected.
				var postTypesChecked = $('input[name="ure_post_types[]"]:checked').length;

				if (postTypesChecked === 0) {
					alert('Please select at least one post type to search in.');
					e.preventDefault();
					return false;
				}

				// Show loading state for preview and apply actions.
				var action = $(this).find('button[type="submit"]:focus').val();

				if (action === 'preview' || action === 'apply') {
					URE_Admin.showLoading($(this));
				}
			});
		},

		/**
		 * Enhance UI with additional features.
		 */
		enhanceUI: function() {
			// Add "Select All" / "Deselect All" for post types.
			this.addPostTypeToggle();

			// Highlight search term in preview results.
			this.enhancePreviewHighlights();

			// Add character count to search/replace fields.
			this.addCharacterCount();
		},

		/**
		 * Add toggle buttons for post type selection.
		 */
		addPostTypeToggle: function() {
			var postTypeContainer = $('input[name="ure_post_types[]"]').first().parent().parent();

			if (postTypeContainer.length) {
				var toggleButtons = $('<div class="ure-post-type-toggle" style="margin-bottom: 10px;"></div>');
				toggleButtons.append('<button type="button" class="button button-small ure-select-all">Select All</button> ');
				toggleButtons.append('<button type="button" class="button button-small ure-deselect-all">Deselect All</button>');

				postTypeContainer.prepend(toggleButtons);

				// Select all.
				$('.ure-select-all').on('click', function(e) {
					e.preventDefault();
					$('input[name="ure_post_types[]"]').prop('checked', true);
				});

				// Deselect all.
				$('.ure-deselect-all').on('click', function(e) {
					e.preventDefault();
					$('input[name="ure_post_types[]"]').prop('checked', false);
				});
			}
		},

		/**
		 * Enhance preview result highlights with smooth animations.
		 */
		enhancePreviewHighlights: function() {
			$('.ure-highlight').each(function() {
				$(this).hover(
					function() {
						$(this).css('background-color', '#ffeb3b');
					},
					function() {
						$(this).css('background-color', '#ffff00');
					}
				);
			});
		},

		/**
		 * Add character count to search and replace fields.
		 */
		addCharacterCount: function() {
			var searchField = $('#ure_search');
			var replaceField = $('#ure_replace');

			if (searchField.length) {
				var searchCounter = $('<span class="ure-char-count" style="margin-left: 10px; color: #666;"></span>');
				searchField.after(searchCounter);

				searchField.on('input', function() {
					var count = $(this).val().length;
					searchCounter.text(count + ' character' + (count !== 1 ? 's' : ''));
				});
			}

			if (replaceField.length) {
				var replaceCounter = $('<span class="ure-char-count" style="margin-left: 10px; color: #666;"></span>');
				replaceField.after(replaceCounter);

				replaceField.on('input', function() {
					var count = $(this).val().length;
					replaceCounter.text(count + ' character' + (count !== 1 ? 's' : ''));
				});
			}
		},

		/**
		 * Show loading state.
		 *
		 * @param {jQuery} form The form element.
		 */
		showLoading: function(form) {
			form.find('button[type="submit"]').prop('disabled', true);
			form.addClass('ure-loading');
		},

		/**
		 * Handle profile save - copy main form values to save profile form.
		 */
		handleProfileSave: function() {
			console.log('URE: handleProfileSave initialized');
			$('.ure-save-profile form').on('submit', function(e) {
				console.log('URE: Profile save form submitted');
				var saveForm = $(this);
				var mainForm = $('.ure-search-section .ure-form');
				console.log('URE: saveForm found:', saveForm.length);
				console.log('URE: mainForm found:', mainForm.length);
				
				// Remove any previously added hidden fields
				saveForm.find('.ure-copied-field').remove();
				
				// Copy search value
				var searchValue = mainForm.find('#ure_search').val();
				console.log('URE: Copying search value:', searchValue);
				saveForm.append('<input type="hidden" name="ure_search" value="' + $('<div>').text(searchValue).html() + '" class="ure-copied-field">');
				
				// Copy replace value
				var replaceValue = mainForm.find('#ure_replace').val();
				saveForm.append('<input type="hidden" name="ure_replace" value="' + $('<div>').text(replaceValue).html() + '" class="ure-copied-field">');
				
				// Copy post types (all checked boxes)
				mainForm.find('input[name="ure_post_types[]"]:checked').each(function() {
					saveForm.append('<input type="hidden" name="ure_post_types[]" value="' + $(this).val() + '" class="ure-copied-field">');
				});
				
				// Copy scope (selected radio button)
				var scopeValue = mainForm.find('input[name="ure_scope"]:checked').val();
				if (scopeValue) {
					saveForm.append('<input type="hidden" name="ure_scope" value="' + scopeValue + '" class="ure-copied-field">');
				}
				
				// Copy case sensitive checkbox
				if (mainForm.find('input[name="ure_case_sensitive"]').is(':checked')) {
					saveForm.append('<input type="hidden" name="ure_case_sensitive" value="1" class="ure-copied-field">');
				}
				
				// Copy regex mode checkbox
				if (mainForm.find('input[name="ure_regex_mode"]').is(':checked')) {
					saveForm.append('<input type="hidden" name="ure_regex_mode" value="1" class="ure-copied-field">');
				}
				
				// Allow form to submit
				console.log('URE: Form will be submitted with copied fields');
				console.log('URE: Total copied fields:', saveForm.find('.ure-copied-field').length);
				return true;
			});
		}
	};

	/**
	 * Expose to global scope for extensibility.
	 */
	window.URE_Admin = URE_Admin;

})(jQuery);
