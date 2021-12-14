/**
 * this script wraps dropzone.js library for inputs of
 * ILIAS\UI\Component\Input\Field\File.
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */

// global dropzone.js setting
Dropzone.autoDiscover = false;

var il = il || {};
il.UI = il.UI || {};
il.UI.Input = il.UI.Input || {};
(function ($, Input) {
	Input.File = (function ($) {
		const SELECTOR = {
			file_entry: '.ui-input-file-input',
			file_list: '.ui-input-file-input-list',
			file_metadata: '.ui-input-file-metadata',
			file_removable: '.ui-input-file-input-removable',
			file_error_msg: '.ui-input-file-input-error-msg',
			removal_glyph: '.glyph[aria-label="Close"]',
			expand_glyph: '.glyph[aria-label="Expand Content"]',
			collapse_glyph: '.glyph[aria-label="Collapse Content"]',
			dropzone: '.ui-input-file-input-dropzone',
		};

		/**
		 * @type {boolean}
		 */
		let instantiated = false;

		/**
		 * @type {Dropzone[]}
		 */
		let dropzones = [];

		/**
		 * @param {string} input_id
		 * @param {string} upload_url
		 * @param {string} removal_url
		 * @param {string} file_identifier
		 * @param {int} max_file_amount
		 * @param {int} max_file_size
		 * @param {boolean} has_zip_options
		 * @param {boolean} is_disabled
		 * @param {string[]} mime_types
		 */
		let init = function (
			input_id,
			upload_url,
			removal_url,
			file_identifier,
			max_file_amount,
			max_file_size,
			is_disabled,
			has_zip_options,
			mime_types
		) {
			if (typeof dropzones[input_id] !== 'undefined') {
				console.error(`Error: tried to register input '${input_id}' as file input twice.`);
				return;
			}

			let file_list = document.querySelector(`#${input_id} ${SELECTOR.file_list}`);
			let action_button = document.querySelector(`#${input_id} ${SELECTOR.dropzone} button`);
			if (is_disabled) {
				action_button.is_disabled = true;
				return;
			}

			dropzones[input_id] = new Dropzone(`#${input_id} ${SELECTOR.dropzone}`, {
				url: encodeURI(upload_url),
				uploadMultiple: (1 < max_file_amount),
				acceptedFiles: (0 < mime_types.length) ? mime_types : null,
				maxFiles: max_file_amount,
				maxFileSize: max_file_size,
				previewsContainer: file_list,
				clickable: action_button,
				autoProcessQueue: false,
				parallelUploads: 1,
				has_zip_options: has_zip_options,
				file_identifier: file_identifier,
				removal_url: removal_url,
				input_id: input_id,

				// override default rendering function
				addedfile: file => {
					addFileHook(file, input_id);
				}
			});

			initDropzoneEventListeners(dropzones[input_id]);
			setupExpansionGlyphs();

			if (!instantiated) {
				initGlobalFileEventListeners();
				instantiated = true;
			}
		}

		/**
		 * @param {Dropzone} dropzone
		 */
		let initDropzoneEventListeners = function (dropzone) {
			dropzone.on('processing', enableAutoProcessingHook);
		}

		/**
		 * @param {Event} event
		 */
		let removeFileManuallyHook = function (event) {
			let removal_glyph = $(this);
			let file_input_id = removal_glyph.closest(SELECTOR.file_list).parent().attr('id');

			// abort if the file input was not yet initialized properly.
			if (typeof dropzones[file_input_id] === 'undefined') {
				console.error(`Error: tried to remove file from uninitialized input: '${file_input_id}'`);
				return;
			}

			let file_preview = removal_glyph.parent();
			let removable_file = file_preview.find(SELECTOR.file_removable);
			let dropzone_options = dropzones[file_input_id].options;

			// only remove files that have the removable class and were
			// already stored on the server.
			if (0 === removable_file.length) {
				return;
			}

			// stop event propagation as there may occurs an error.
			event.stopImmediatePropagation();

			// disable the removal button, by changing the aria-label
			// the global event listener won't trigger this hook again.
			removal_glyph.attr('aria-label', 'Close Disabled');
			removal_glyph.css('color', 'grey');

			$.ajax({
				type: 'GET',
				url: dropzone_options.removal_url,
				data: {
					[dropzone_options.file_identifier]: removable_file.attr('value'),
				},
				success: json_response => {
					$(this).parent().remove();
					ajaxResponseSuccessHook(json_response, file_preview);
				},
				error: json_response => {
					ajaxResponseFailureHook(json_response, file_preview);
				},
			});
		}

		/**
		 * @param {File} file
		 * @param {string} input_id
		 */
		let addFileHook = function (file, input_id) {
			let preview = il.UI.Input.DynamicInputsRenderer.render(input_id);
			console.log(preview);
			if (null === preview) {
				console.error(`Error: could not append preview for newly added file: ${file}`);
				return;
			}

			preview.find('[data-dz-name]').text(file.name);
			preview.find('[data-dz-size]').text(beautifyFileSize(file.size));
			setupExpansionGlyphs(preview);

			// if the file is not of type zip but the zip options
			// were provided, they must be removed.
			if (dropzones[input_id].options.has_zip_options && 'application/zip' !== file.type) {
				removeZipOptions(preview);
			}
		}

		/**
		 * @param {jQuery} preview
		 */
		let removeZipOptions = function (preview) {
			// this only works if the zip-options are the two top-
			// most inputs within the metadata div, but idc.
			for (let i = 0, i_max = 2; i < i_max; i++) {
				let zip_option = preview.find(`${SELECTOR.file_metadata} input:eq(0)`);
				zip_option.closest('.form-group.row').remove();
			}

			// if they were the only metadata inputs, the toggles
			// must be removed as well.
			if (0 === preview.find(`${SELECTOR.file_metadata} input`).length) {
				preview.find(SELECTOR.expand_glyph).remove();
				preview.find(SELECTOR.collapse_glyph).remove();
			}
		}

		let enableAutoProcessingHook = function () {
			let dropzone = $(this)[0];
			console.log($(this));
			console.log(dropzone);
		}

		/**
		 * @param {string} json_response
		 * @param {jQuery} file_preview
		 */
		let ajaxResponseSuccessHook = function (json_response, file_preview) {
			let response = Object.assign(JSON.parse(json_response));

			// if the delivered response status is not 1 an
			// error occurred and the failure hook is fired.
			if (typeof response.status === 'undefined' || 1 !== response.status) {
				displayPreviewErrorMessage(response.message, file_preview);
			}
		}

		/**
		 * @param {jQuery.jqXHR} response
		 * @param {jQuery} file_preview
		 */
		let ajaxResponseFailureHook = function (response, file_preview) {
			console.error(response.status, response.responseText);
			displayPreviewErrorMessage(
				'An error occurred, check the console for more information.',
				file_preview
			);
		}

		/**
		 * @param {string} message
		 * @param {jQuery} file_preview
		 */
		let displayPreviewErrorMessage = function (message, file_preview) {
			file_preview.find(SELECTOR.file_error_msg).text(message);
		}

		let initGlobalFileEventListeners = function () {
			$(document).on(
				'click',
				`${SELECTOR.file_list} ${SELECTOR.removal_glyph}`,
				removeFileManuallyHook
			);

			$(document).on(
				'click',
				`${SELECTOR.file_list} ${SELECTOR.collapse_glyph}, ${SELECTOR.file_list} ${SELECTOR.expand_glyph}`,
				toggleExpansionGlyphsHook
			);
		}

		let toggleExpansionGlyphsHook = function () {
			let current_glyph = $(this);
			let other_glyph = ('Expand Content' === current_glyph.attr('aria-label')) ?
				current_glyph.parent().find(SELECTOR.collapse_glyph) :
				current_glyph.parent().find(SELECTOR.expand_glyph)
			;

			current_glyph.parent().find(SELECTOR.file_metadata).toggle();
			other_glyph.show();
			current_glyph.hide();
		}

		/**
		 * @param {jQuery|null} file_entry
		 */
		let setupExpansionGlyphs = function (file_entry = null) {
			if (null === file_entry) {
				$(`${SELECTOR.file_entry} ${SELECTOR.collapse_glyph}`).hide();
			} else {
				file_entry.find(SELECTOR.collapse_glyph).hide();
			}
		}

		/**
		 * @param {int} bytes
		 * @return {string}
		 */
		let beautifyFileSize = function (bytes) {
			return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
		}

		return {
			init: init,
		}
	})($)
})($, il.UI.Input);