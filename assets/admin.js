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

				// Get the submit button that was clicked.
				var $submitBtn = $(this).find('button[type="submit"]:focus');
				var action = $submitBtn.val();

				// Check if AJAX processing is enabled.
				if (typeof ureData !== 'undefined' && ureData.ajaxProcessing && (action === 'preview' || action === 'apply')) {
					e.preventDefault();
					URE_Admin.handleAjaxBatch($(this), action);
					return false;
				}

				// Show loading state for preview and apply actions.
				if (action === 'preview' || action === 'apply') {
					URE_Admin.showLoading($(this));
				}
			});
		},

		/**
		 * Handle AJAX batch processing.
		 *
		 * @param {jQuery} $form The form element.
		 * @param {string} action 'preview' or 'apply'.
		 */
		handleAjaxBatch: function($form, action) {
			var search = $('#ure_search').val();
			var replace = $('#ure_replace').val();
			var postTypes = [];
			$('input[name="ure_post_types[]"]:checked').each(function() {
				postTypes.push($(this).val());
			});
			var caseSensitive = $('#ure_case_sensitive').is(':checked');
			var regexMode = $('#ure_regex').is(':checked');

			// Show progress UI.
			URE_Admin.showProgressBar($form, action);

			// Start batch processing.
			URE_Admin.processBatch(action, search, replace, postTypes, caseSensitive, regexMode, 1, 0, []);
		},

		/**
		 * Process a single batch via AJAX.
		 *
		 * @param {string} action 'preview' or 'apply'.
		 * @param {string} search Search term.
		 * @param {string} replace Replace term.
		 * @param {array} postTypes Post types.
		 * @param {boolean} caseSensitive Case sensitive flag.
		 * @param {boolean} regexMode Regex mode flag.
		 * @param {number} batchPage Current batch page.
		 * @param {number} totalMatches Total matches so far.
		 * @param {array} allMatches All matches collected.
		 */
		processBatch: function(action, search, replace, postTypes, caseSensitive, regexMode, batchPage, totalMatches, allMatches) {
			var ajaxAction = action === 'preview' ? 'ure_preview_batch' : 'ure_apply_batch';

			$.ajax({
				url: ureData.ajaxUrl,
				type: 'POST',
				data: {
					action: ajaxAction,
					nonce: ureData.nonce,
					search: search,
					replace: replace,
					post_types: postTypes,
					case_sensitive: caseSensitive ? 'true' : 'false',
					regex_mode: regexMode ? 'true' : 'false',
					batch_page: batchPage
				},
				success: function(response) {
					if (response.success) {
						var data = response.data;
						var newMatches = data.matches || [];
						var newUpdates = data.updates || 0;

						allMatches = allMatches.concat(newMatches);
						totalMatches += (action === 'preview' ? newMatches.length : newUpdates);

						// Update progress bar.
						var progress = (data.current_batch / data.total_pages) * 100;
						URE_Admin.updateProgressBar(progress, data.current_batch, data.total_pages);

						if (data.is_last_batch) {
							// Finished - show results.
							URE_Admin.hideProgressBar();
							URE_Admin.showBatchResults(action, totalMatches, data.total_updates || 0, allMatches);
						} else {
							// Process next batch.
							URE_Admin.processBatch(action, search, replace, postTypes, caseSensitive, regexMode, batchPage + 1, totalMatches, allMatches);
						}
					} else {
						URE_Admin.hideProgressBar();
						alert('Error: ' + (response.data.message || 'Unknown error'));
					}
				},
				error: function(xhr, status, error) {
					URE_Admin.hideProgressBar();
					alert('AJAX error: ' + error);
				}
			});
		},

		/**
		 * Show progress bar.
		 *
		 * @param {jQuery} $form Form element.
		 * @param {string} action Action type.
		 */
		showProgressBar: function($form, action) {
			// Create progress bar HTML.
			var progressHtml = '<div class="ure-progress-wrapper" style="margin: 20px 0; padding: 20px; background: #f0f0f1; border: 1px solid #c3c4c7; border-radius: 4px;">' +
				'<h3 style="margin-top: 0;">Processing ' + (action === 'preview' ? 'Preview' : 'Replacement') + '...</h3>' +
				'<div class="ure-progress-bar" style="background: #fff; height: 30px; border: 1px solid #c3c4c7; border-radius: 3px; overflow: hidden; position: relative;">' +
				'<div class="ure-progress-fill" style="background: #2271b1; height: 100%; width: 0%; transition: width 0.3s;"></div>' +
				'</div>' +
				'<p class="ure-progress-text" style="margin: 10px 0 0; color: #50575e;">Batch 0 of 0</p>' +
				'</div>';

			// Insert after form.
			$form.after(progressHtml);
			$form.find('button').prop('disabled', true);
		},

		/**
		 * Update progress bar.
		 *
		 * @param {number} percent Percentage complete.
		 * @param {number} current Current batch.
		 * @param {number} total Total batches.
		 */
		updateProgressBar: function(percent, current, total) {
			$('.ure-progress-fill').css('width', percent + '%');
			$('.ure-progress-text').text('Batch ' + current + ' of ' + total + ' (' + Math.round(percent) + '%)');
		},

		/**
		 * Hide progress bar.
		 */
		hideProgressBar: function() {
			$('.ure-progress-wrapper').fadeOut(300, function() {
				$(this).remove();
			});
			$('.ure-form button').prop('disabled', false);
		},

		/**
		 * Show batch processing results.
		 *
		 * @param {string} action 'preview' or 'apply'.
		 * @param {number} totalMatches Total matches found.
		 * @param {number} totalUpdates Total updates made.
		 * @param {array} matches Match details.
		 */
		showBatchResults: function(action, totalMatches, totalUpdates, matches) {
			if (action === 'preview') {
				var message = 'Preview complete! Found ' + totalMatches + ' match(es) across all posts.\n\n';
				if (totalMatches > 0) {
					message += 'Showing first 10 matches:\n\n';
					matches.slice(0, 10).forEach(function(match, index) {
						message += (index + 1) + '. Post: "' + match.post_title + '" (' + match.post_type + ')\n';
						message += '   Match: "' + match.match_text + '"\n\n';
					});
				}
				message += 'Click "Apply Changes" to perform the replacement.';
				alert(message);
			} else {
				var message = 'Replacement complete!\n\n';
				message += 'Updated ' + totalUpdates + ' post(s).\n\n';
				message += 'The page will reload to show the operation history.';
				alert(message);
				location.reload();
			}
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
			$('.ure-save-profile form').on('submit', function(e) {
				var saveForm = $(this);
				var mainForm = $('.ure-search-section .ure-form');

				// Remove any previously added hidden fields
				saveForm.find('.ure-copied-field').remove();

				// Copy search value
				var searchValue = mainForm.find('#ure_search').val();
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
				return true;
			});
		}
	};

	/**
	 * Expose to global scope for extensibility.
	 */
	window.URE_Admin = URE_Admin;

})(jQuery);
