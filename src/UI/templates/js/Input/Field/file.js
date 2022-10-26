/**
 * this script wraps dropzone.js library for inputs of
 * ILIAS\UI\Component\Input\Field\File.
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 *
 * @TODO: dropzone.js library can easily be dropped, the only thing
 * 		  it still does is keeping track of files and uploading them.
 */

// global dropzone.js setting
Dropzone.autoDiscover = false;

var il = il || {};
il.UI = il.UI || {};
il.UI.Input = il.UI.Input || {};
(function ($, Input) {
	Input.File = (function ($) {
		/**
		 * Holds a list of all the query selectors used throughout this script.
		 * @type {{}}
		 */
		const SELECTOR = {
			file_input: '.ui-input-file',
			file_list: '.ui-input-file-input-list',
			file_list_entry: '.ui-input-file-input',
			file_entry_metadata: '.ui-input-file-metadata',
			file_entry_input: 'input[type="hidden"]',

			dropzone: '.ui-input-file-input-dropzone',
			error_message: '.ui-input-file-input-error-msg',
			removal_glyph: '[data-action="remove"] .glyph',
			expand_glyph: '[data-action="expand"] .glyph',
			collapse_glyph: '[data-action="collapse"] .glyph',
			form_submit_buttons: '.il-standard-form-cmd > button',

			progress_container: '.ui-input-file-input-progress-container',
			progress_indicator: '.ui-input-file-input-progress-indicator',
		};

		/**
		 * Holds a list of translated messages that could be displayed to humans.
		 * @type {{}}
		 */
		let I18N = {
			invalid_mime: `Files of type '%s' are not allowed`,
			invalid_size: `File exceeds the maximum size of %s.`,
			invalid_amount: `You cannot upload this many files, please remove some in order to continue.`,
			general_error: `An error occurred, check the console for more information.`,
		}

		/**
		 * Holds whether the global event listeners were added.
		 * @type {boolean}
		 */
		let instantiated = false;

		/**
		 * When a form is processed that holds an instance of this input,
		 * this holds the total amount of dropzones in it.
		 * @type {int}
		 */
		let current_dropzone_count = 0;

		/**
		 * When a form is processed that holds an instance of this input,
		 * this keeps track of how many of the dropzones were processed.
		 * @type {int}
		 */
		let current_dropzone = 0;

		/**
		 * When a form is processed that holds an instance of this input,
		 * this holds an instance of it, so it can be submitted manually.
		 * @type {jQuery|{}}
		 */
		let current_form = {};

		/**
		 * Holds a list of Dropzone instances mapped to the file input id.
		 * @type {Dropzone[]}
		 */
		let dropzones = [];

		/**
		 * Holds a list of Files per file input id to remove before sending the form
		 * @type string[]
		 */
		let removal_items = [];

		/**
		 * @param {string} input_id
		 * @param {string} upload_url
		 * @param {string} removal_url
		 * @param {string} file_identifier
		 * @param {int} current_file_count
		 * @param {int} max_file_amount
		 * @param {int} max_file_size
		 * @param {string} mime_types
		 * @param {boolean} is_disabled
		 * @param {string[]} translations
		 */
		let init = function (
			input_id,
			upload_url,
			removal_url,
			file_identifier,
			current_file_count,
			max_file_amount,
			max_file_size,
			mime_types,
			is_disabled,
			translations,
			chunked_upload,
			chunk_size
		) {
			if (typeof dropzones[input_id] !== 'undefined') {
				console.error(`Error: tried to register input '${input_id}' as file input twice.`);
				return;
			}

			if (is_disabled) {
				disableActionButton(input_id);
				return;
			}

			I18N = Object.assign(translations);

			// retrieve file-list and action button in vanilla js,
			// because of dropzone.js compatibility.
			let file_list = document.querySelector(`#${input_id} ${SELECTOR.file_list}`);
			let action_button = document.querySelector(`#${input_id} ${SELECTOR.dropzone} button`);

			removal_items[input_id] = [];

			dropzones[input_id] = new Dropzone(
				`#${input_id} ${SELECTOR.dropzone}`,
				{
					url: encodeURI(upload_url),
					uploadMultiple: (1 < max_file_amount),
					acceptedFiles: (0 < mime_types.length) ? mime_types : null,
					maxFiles: max_file_amount,
					maxFileSize: max_file_size,
					previewsContainer: file_list,
					clickable: action_button,
					autoProcessQueue: false,
					parallelUploads: 1,
					current_file_count: current_file_count,
					file_identifier: file_identifier,
					removal_url: removal_url,
					input_id: input_id,
					chunking: chunked_upload,
					chunkSize: chunk_size,
					forceChunking: chunked_upload,

					// override default rendering function.
					addedfile: file => {
						renderFileEntryHook(file, input_id);
					},
				}
			);

			initGlobalFileEventListeners();
			initDropzoneEventListeners(dropzones[input_id]);
			maybeToggleActionButtonAndErrorMessage(input_id);
			setupExpansionGlyphs();
		}

		let initGlobalFileEventListeners = function () {
			// abort if the global event listeners were already added.
			if (instantiated) {
				return;
			}

			$(document).on('click',
				`${SELECTOR.file_list} ${SELECTOR.collapse_glyph}, ${SELECTOR.file_list} ${SELECTOR.expand_glyph}`,
				toggleMetadataInputsHook);

			$(document).on('click',
				`${SELECTOR.file_list} ${SELECTOR.collapse_glyph}, ${SELECTOR.file_list} ${SELECTOR.expand_glyph}`,
				toggleExpansionGlyphsHook);

			$(document).on('click',
				`${SELECTOR.file_list} ${SELECTOR.removal_glyph}`,
				removeFileManuallyHook);

			$(SELECTOR.dropzone).closest('form').on('click',
				SELECTOR.form_submit_buttons,
				processFormSubmissionHook);

			instantiated = true;
		}

		/**
		 * @param {Dropzone} dropzone
		 */
		let initDropzoneEventListeners = function (dropzone) {
			dropzone.on('maxfilesexceeded', alertMaxFilesReachedHook);
			dropzone.on('maxfilesreached', disableActionButtonHook);
			dropzone.on('queuecomplete', submitCurrentFormHook);
			dropzone.on('processing', enableAutoProcessingHook);
			dropzone.on('success', setResourceStorageIdHook);
			dropzone.on('error', function () {
				return false;
			});
			dropzone.on('uploadprogress', function (file, progress, bytesSent) {
				let file_id_input = $(`#${file.input_id}`);
				let file_preview = file_id_input.closest(SELECTOR.file_list_entry);

				if (file_preview) {
					let progressContainer = file_preview.find(SELECTOR.progress_container);
					let progressIndicator = file_preview.find(SELECTOR.progress_indicator);
					let number = Math.round(progress);

					if (number === 100 && bytesSent < file.size) {
						// return;
					}
					if (progressContainer && progressIndicator) {
						progressContainer.css('display', 'block');
						if (!file.hasOwnProperty('progress_storage') || number > file.progress_storage) {
							progressIndicator.css('width', number + '%');
						}
						if (number === 100) {
							progressIndicator.addClass('success');
						}
					}
					file.progress_storage = number;
				}
			});
		}

		// ==========================================
		// BEGIN global event hooks
		// ==========================================

		/**
		 * @param {Event} event
		 */
		let removeFileManuallyHook = function (event) {
			let removal_glyph = $(this);
			let input_id = removal_glyph.closest(SELECTOR.file_input).attr('id');
			let dropzone = dropzones[input_id];
			current_form.errors = false;

			if (typeof dropzone === 'undefined') {
				console.error(`Error: tried to remove file from uninitialized input: '${input_id}'`);
				return;
			}

			let file_entry = removal_glyph.closest(SELECTOR.file_list_entry);
			let file_entry_input = getFileEntryInput(file_entry);

			dropzone.options.current_file_count--;
			maybeRemoveFileFromQueue(dropzone, file_entry_input.attr('id'));
			maybeToggleActionButtonAndErrorMessage(input_id);

			// only remove files that have a file id and are therefore stored
			// on the server.
			if ('' === file_entry_input.val()) {
				return;
			}

			// stop event propagation as there may occur an error.
			event.stopImmediatePropagation();

			// disable the removal button, by changing the aria-label
			// the global event listener won't trigger this hook again.
			removal_glyph.attr('disabled');
			removal_glyph.css('color', 'grey');
			// collect the file id for removal.
			removal_items[input_id].push(file_entry_input.val());
			$(this).closest(SELECTOR.file_list_entry).remove();
		}

		/**
		 * @param {Event} event
		 */
		let processFormSubmissionHook = function (event) {
			current_form = $(this).closest('form');
			current_form.errors = false;

			event.preventDefault();

			// disable ALL submit buttons on the current page,
			// so the data is submitted AFTER the queue is
			// processed (queuecomplete is fired).
			$(document)
			.find(SELECTOR.form_submit_buttons)
			.each(function () {
				$(this).attr('disabled', true);
			});
			processCurrentFormDropzones(event);
		}

		let toggleExpansionGlyphsHook = function () {
			let current_glyph = $(this);

			let other_glyph = current_glyph.parent().data('action') === 'expand' ?
				current_glyph.closest(SELECTOR.file_list_entry).find(SELECTOR.collapse_glyph) :
				current_glyph.closest(SELECTOR.file_list_entry).find(SELECTOR.expand_glyph)
			;

			other_glyph.show();
			current_glyph.hide();
		}

		let toggleMetadataInputsHook = function () {
			$(this)
			.closest(SELECTOR.file_list_entry)
			.find(SELECTOR.file_entry_metadata)
			.toggle();
		}

		// ==========================================
		// END global event hooks
		// ==========================================

		// ==========================================
		// BEGIN dropzone event hooks
		// ==========================================

		/**
		 * @param {File} file
		 * @param {string} input_id
		 */
		let renderFileEntryHook = function (file, input_id) {
			if (typeof dropzones[input_id] === 'undefined') {
				console.error(`Error: tried rendering a file entry for '${input_id}' which is not yet initialized.`);
				return;
			}

			// abort if the given file is not an allowed file type.
			if (dropzones[input_id].options.acceptedFiles !== null &&
				!dropzones[input_id].options.acceptedFiles.includes(file.type)
			) {
				displayErrorMessage(
					I18N.invalid_mime.replace('%s', file.type),
					$(`#${input_id} ${SELECTOR.dropzone}`)
				);

				// we need to remove the file manually from the dropzone becausee
				// it (mistakenly?) gets added anyhow.
				dropzones[input_id].removeFile(file);

				return;
			}

			// abort if the given file size exceeds the max limit.
			if (dropzones[input_id].options.maxFileSize < file.size) {
				let allowed_file_size = dropzones[input_id].filesize(dropzones[input_id].options.maxFileSize);
				displayErrorMessage(
					I18N.invalid_size.replace('%s', allowed_file_size),
					$(`#${input_id} ${SELECTOR.dropzone}`)
				);

				// we need to remove the file manually from the dropzone becausee
				// it (mistakenly?) gets added anyhow.
				dropzones[input_id].removeFile(file);

				return;
			}

			let preview = il.UI.Input.DynamicInputsRenderer.render(input_id);
			if (null === preview) {
				console.error(`Error: could not append preview for newly added file: ${file}`);
				return false;
			}

			// add file info to preview and setup expansion toggles.
			preview.find('[data-dz-name]').text(file.name);
			preview.find('[data-dz-size]').html(dropzones[input_id].filesize(file.size));
			setupExpansionGlyphs(preview);

			// store rendered preview id temporarily in file, to retrieve
			// the corresponding input later.
			file.input_id = getFileEntryInput(preview).attr('id');
			dropzones[input_id].options.current_file_count++;

			// enqueue file to dropzone
			if (typeof file.status === 'undefined' || file.status !== Dropzone.ADDED) {
				registerDropzoneFile(dropzones[input_id], file);
			}

			maybeToggleActionButtonAndErrorMessage(input_id);
		}

		/**
		 * @param {File} file
		 * @param {string} json_response
		 */
		let setResourceStorageIdHook = function (file, json_response) {
			let response = Object.assign(JSON.parse(json_response));
			let file_id_input = $(`#${file.input_id}`);
			let file_preview = file_id_input.closest(SELECTOR.file_list_entry);
			let dropzone = dropzones[file_id_input.closest(SELECTOR.file_input).attr('id')];

			if (typeof response.status === 'undefined' || 1 !== response.status) {
				current_form.errors = true;
				response.responseText = response.message;
				ajaxResponseFailureHook(response, file_preview);
				return false;
			}

			// set the upload results IRSS file id.
			file_id_input.val(response[dropzone.options.file_identifier]);
		}

		let submitCurrentFormHook = function () {
			// submit the current form only if all dropzones
			// were processed.
			if (current_form.errors === false && ++current_dropzone === current_dropzone_count) {
				current_form.submit();
			}
		}

		let enableAutoProcessingHook = function () {
			let dropzone = $(this)[0];

			// if there are more than one file in the current
			// dropzone's queue, the auto-processing can be
			// enabled after the first file was processed.
			if (1 !== dropzone.files.length) {
				dropzone.options.autoProcessQueue = true;
			}
		}

		let alertMaxFilesReachedHook = function () {
			let input_id = $(this)[0].options.input_id;
			displayMaxFilesReachedMessage(input_id);
		}

		let disableActionButtonHook = function () {
			let input_id = $(this)[0].options.input_id;
			disableActionButton(input_id);
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
				displayErrorMessage(response.message, file_preview);
			}
		}

		/**
		 * @param {jQuery.jqXHR} response
		 * @param {jQuery} file_preview
		 */
		let ajaxResponseFailureHook = function (response, file_preview) {
			console.error(response.status, response.responseText);
			displayErrorMessage(
				response.responseText,
				file_preview
			);
		}

		// ==========================================
		// END dropzone event hooks
		// ==========================================

		// ==========================================
		// BEGIN helper functions
		// ==========================================

		/**
		 * @param {Dropzone} dropzone
		 * @param {File} file
		 */
		let registerDropzoneFile = function (dropzone, file) {
			file.status = Dropzone.ADDED;
			file.accepted = true;
			file.upload = {
				uuid: Dropzone.uuidv4(),
				progress: 0,
				bytesSent: 0,
				total: file.size,
				filename: dropzone._renameFile(file),
				chunked: dropzone.options.chunking && (dropzone.options.forceChunking || file.size > dropzone.options.chunkSize),
				totalChunkCount: Math.ceil(file.size / dropzone.options.chunkSize)
			};

			dropzone.files.push(file);
			dropzone.enqueueFile(file);
		}

		/**
		 * @param {jQuery|null} file_entry
		 */
		let setupExpansionGlyphs = function (file_entry = null) {
			if (null === file_entry) {
				// hide collapse glyph globally (in file list).
				$(`${SELECTOR.file_list} ${SELECTOR.collapse_glyph}`).hide();
			} else {
				// hide collapse glyph locally (in file entry).
				file_entry.find(SELECTOR.collapse_glyph).hide();
			}
		}

		/**
		 * @param {Dropzone} dropzone
		 * @param {string} input_id
		 */
		let maybeRemoveFileFromQueue = function (dropzone, input_id) {
			let file_to_remove = null;
			for (let i = 0, i_max = dropzone.files.length; i < i_max; i++) {
				let current_input_id = dropzone.files[i].input_id;
				if (typeof current_input_id !== 'undefined' && current_input_id === input_id) {
					file_to_remove = dropzone.files[i];
          			break;
				}
			}

			if (null !== file_to_remove) {
				// removes ONE file object at found position.
				dropzone.removeFile(file_to_remove);
			}
		}

		let removeAllFilesFromQueue = function (input_id) {
			if (typeof dropzones[input_id] === 'undefined') {
				console.error(`Error: tried to access unknown input '${input_id}'.`);
				return;
			}

			for (let i = 0; i < dropzones[input_id].files.length; ++i) {
				let file = dropzones[input_id].files[i];
				let file_id_input = $(`#${file.input_id}`);
				let file_preview = file_id_input.closest(SELECTOR.file_list_entry);
				file_preview.remove();
			}

			dropzones[input_id].removeAllFiles();
		}

		/**
		 * @param {string} input_id
		 */
		let maybeToggleActionButtonAndErrorMessage = function (input_id) {
			let current_file_count = dropzones[input_id].options.current_file_count;
			let max_file_amount = dropzones[input_id].options.maxFiles;

			if (current_file_count > max_file_amount) {
				displayMaxFilesReachedMessage(input_id);
				disableSubmitButtons($(`#${input_id}`).closest('form'));
			} else {
				removeErrorMessage($(`#${input_id} ${SELECTOR.dropzone}`));
				enableSubmitButtons($(`#${input_id}`).closest('form'));
			}

			if (current_file_count >= max_file_amount) {
				disableActionButton(input_id);
			} else {
				enableActionButton(input_id);
			}
		}

		let processRemovals = function (input_id, event) {
			let file_to_remove = removal_items[input_id];
			let dropzone = dropzones[input_id];
			for (let i = 0, i_max = file_to_remove.length; i < i_max; i++) {
				let file_id = file_to_remove[i];
				$.ajax({
					type: 'GET',
					url: dropzone.options.removal_url,
					data: {
						[dropzone.options.file_identifier]: file_id,
					},
					success: json_response => {

					},
					error: json_response => {

					},
				});
			}
		}

		let processCurrentFormDropzones = function (event) {
			// retrieve all file inputs of the current form.
			let file_inputs = current_form.find(SELECTOR.file_input);
			current_dropzone_count = file_inputs.length;

			// in case multiple file-inputs were added to ONE form, they
			// all need to be processed.
			if (Array.isArray(file_inputs)) {
				for (let i = 0; i < file_inputs.length; i++) {
					let input_id = file_inputs[i].attr('id');
					let dropzone = dropzones[input_id];
					processRemovals(input_id, event);
					dropzone.processQueue();
				}
			} else {
				let input_id = file_inputs.attr('id');
				let dropzone = dropzones[input_id];
				processRemovals(input_id, event);
				if (0 !== dropzone.files.length) {
					dropzone.processQueue();
				} else {
					current_form.submit();
				}
			}
		}

		/**
		 * @param {jQuery} file_entry
		 * @return {jQuery} the file-id input
		 */
		let getFileEntryInput = function (file_entry) {
			// since there could be multiple hidden inputs in the future (due
			// to introduction as UI component) we have to check if it's one
			// or more. When multiple are found it's always the last one.
			let hidden_inputs = file_entry.find(SELECTOR.file_entry_input);
			return (1 < hidden_inputs.length) ?
				$(hidden_inputs[hidden_inputs.length - 1]) :
				hidden_inputs;
		}

		/**
		 * @param {jQuery} form
		 */
		let disableSubmitButtons = function (form) {
			form
			.find(SELECTOR.form_submit_buttons)
			.each(function () {
				$(this).attr('disabled', true);
			});
		}

		/**
		 * @param {jQuery} form
		 */
		let enableSubmitButtons = function (form) {
			form
			.find(SELECTOR.form_submit_buttons)
			.each(function () {
				$(this).attr('disabled', false);
			});
		}

		/**
		 * @param {string} input_id
		 */
		let displayMaxFilesReachedMessage = function (input_id) {
			displayErrorMessage(
				I18N.invalid_amount,
				$(`#${input_id} ${SELECTOR.dropzone}`)
			);
		}

		/**
		 * @param {string} input_id
		 */
		let disableActionButton = function (input_id) {
			let action_button = $(`#${input_id} ${SELECTOR.dropzone} button`);
			action_button.attr('disabled', true);
		}

		/**
		 * @param {string} input_id
		 */
		let enableActionButton = function (input_id) {
			let action_button = $(`#${input_id} ${SELECTOR.dropzone} button`);
			action_button.attr('disabled', false);
		}

		/**
		 * @param {string} message
		 * @param {jQuery} container
		 */
		let displayErrorMessage = function (message, container) {
			container.find(SELECTOR.error_message).html(message);
			container.find(SELECTOR.progress_indicator).addClass('error');
		}

		/**
		 * @param {jQuery} container
		 */
		let removeErrorMessage = function (container) {
			container.find(SELECTOR.error_message).text('');
		}

		// ==========================================
		// END helper functions
		// ==========================================

		return {
			removeAllFilesFromQueue: removeAllFilesFromQueue,
			renderFileEntry: renderFileEntryHook,
			init: init,
		}
	})($)
})($, il.UI.Input);
