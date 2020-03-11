// the semi-colon before function invocation is a safety net against concatenated
// scripts and/or other plugins which may not be closed properly.
; (function ($, window, document, undefined)
{
    // constants
    var DEBUG_ENABLED = false;
    var DRAG_LEAVE_DELAY = 50;
    var OVERLAY_HIDE_ID = "overlay_hide_timeout";
    var FILE_DRAG_AND_DROP_SUPPORTED = isFileDragAndDropSupported();

    // variables
    var overlaysVisible = false;

    // logging also in IE
    if (Function.prototype.bind && console && typeof console.log == "object")
    {
        ["log", "info", "warn", "error", "assert", "dir", "clear", "profile", "profileEnd"].forEach(function (method)
        {
            console[method] = this.call(console[method], console);
        }, Function.prototype.bind);
    }

    // konsole is a safe wrapper for the console
    var konsole = {
        log: function (args) { },
        dir: function (args) { }
    };

    if (typeof window.console != "undefined" && typeof window.console.log == "function")
    {
        konsole = window.console;
        if (DEBUG_ENABLED)
            konsole.log("konsole initialized");
    }
    log = function ()
    {
        if (DEBUG_ENABLED)
            konsole.log.apply(konsole, arguments);
    };

    // dictionary class
    var ilDictionary = function ()
    {
        this.length = 0;
        var items = {};

        this.get = function (key, value)
        {
            if (this.contains(key))
                return items[key];

            return null;
        };

        this.add = function (key, value)
        {
            if (!this.contains(key))
                this.length++;

            items[key] = value;
        };

        this.count = function ()
        {
            return this.length;
        };

        this.remove = function (key)
        {
            if (!this.contains(key))
                return;

            delete items[key];
            this.length--;
        };

        this.contains = function (key)
        {
            return items.hasOwnProperty(key);
        };

        this.clear = function ()
        {
            items = {};
            this.length = 0;
        };

        this.each = function (fn)
        {
            for (var key in items)
            {
                if (this.contains(key))
                {
                    if (fn(key, items[key]) == true)
                        break;
                }
            }
        };
    };

    // file data class
    var ilFile = function (id, file, fileUpload, uploadData)
    {
        this.id = id;

        this.fileUpload = fileUpload;
        this.uploadData = uploadData;
        this.name = getFileName(file);
        this.ext = /(?:\.([^.]+))?$/.exec(this.name)[1];
        this.size = typeof file.size === "number" ? file.size : null;
        this.sizeFormatted = getFormattedFileSize(file.size);
        this.canExtract = false;
        this.canUpload = true;

        // private variables
        var self = this;
        var isUploading = false;
        var uploadDone = false;
        var hasUploadError = false;
        var keepStructure = true;
        var extract = false;

        var $html = null;
        var $cancelButton = null;
        var $options = null;
        var $progressBar = null;
        var $progressPercentage = null;

        /**
         * Loads the HTML template with the specified id that represents this file.
         */
        this.loadHtmlTemplate = function (templateId)
        {
            // create html from template
            $html = $(tmpl(templateId, this));

            // store some jQuery objects
            $options = $html.find(".ilFileUploadEntryOptions");
            $progressBar = $html.find(".ilFileUploadEntryProgressBar");
            $progressPercentage = $html.find(".ilFileUploadEntryProgressPercent");


            // hide error and option region
            $html.find(".ilFileUploadEntryError").hide();
            $options.hide();

            // file can be uploaded?
            if (this.canUpload)
            {
                $html.find(".ilFileUploadEntryHeader").on("click", onToggleOptions);
            }
            else // set error
            {
                this.setError(this, errorText);
            }

            // extract possible?
            if (this.canExtract)
            {
                $html.find(".ilFileUploadEntryKeepStructure").hide();
                $html.find("#extract_" + this.id).on("click", onExtractChanged);
				
				$options.show();
                $html.addClass("ilFileUploadEntryExpanded");
            }

            // add click handler
            $cancelButton = $html.find("#cancel_" + this.id);
            $cancelButton.on("click", onCancelClicked);

            return $html;
        };

        this.markAsNew = function ()
        {
            // remove new style after some time
            $html.addClass("ilFileUploadNewEntry");
            $.doTimeout("new_" + this.id, 500, function ()
            {
                $html.removeClass("ilFileUploadNewEntry");
            });
        };

        /**
         * Submits this file to the server.
         */
        this.submit = function ()
        {
            isUploading = true;
            this.uploadData.submit();
        };

        /**
         * Gets the data to submit for this file.
         */
        this.getFormData = function ()
        {
            // get values
            var titleValue = $html.find("#title_" + this.id).val();
            var descValue = $html.find("#desc_" + this.id).val();
            var extractValue = $html.find("#extract_" + this.id).prop("checked") ? 1 : 0;
            var keepStructureValue = $html.find("#keepstructure_" + this.id).prop("checked") ? 1 : 0;

            // store whether to extract or not
            if (this.canExtract)
            {
                extract = extractValue;
                keepStructure = keepStructureValue;
            }

            log("File.getFormData (%s): title: %s, description: %s, extract: %s, keep structure: %s", this.id, titleValue, descValue, extractValue, keepStructureValue);

            return { title: titleValue, description: descValue, extract: extractValue, keep_structure: keepStructureValue };
        };

        /**
         * Set the progress of the upload.
         */
        this.setUploadProgress = function (progress, text)
        {
            if (text == null)
                text = progress + "%";

            $progressBar.width(progress);
            $progressPercentage.text(text);
        };

        /**
         * Disabled all controls for this file.
         */
        this.disableInputs = function ()
        {
            var $table = $html.find(".ilFileUploadEntryOptionsTable");
            $table.find("label").prop("disabled", true);
            $table.find("input").prop("disabled", true);
            $table.find("textarea").prop("disabled", true);
        };

        /**
         * Cancels the upload of this file.
         */
        this.cancel = function ()
        {
            onCancelClicked(null);
        };
 		
        /**
         * Sets an error for this file.
         */
        this.setError = function (errorText)
        {
            // set text and show or hide it
            $html.find(".ilFileUploadEntryErrorText").html(errorText.replace(/<(?!br\s*\/?)[^>]+>/g, ''));

            // show error
            $html.addClass("ilFileUploadError");
            $html.find(".ilFileUploadEntryError").show();

            // enable remove button
            $cancelButton.prop("disabled", false);

            // remove options
            $options.remove();
        };

        /**
         * Disables this file for uploading.
         */
        this.disableUpload = function (reason)
        {
            this.canUpload = false;
            this.setError(reason);
        };

        /**
         * Sets an upload error for this file.
         */
        this.setUploadError = function (errorText)
        {
            this.removeProgressBar();
            hasUploadError = true;
            isUploading = false;
            this.setError(errorText);
        };

        /**
         * Sets that the upload of this file was successful.
         */
        this.setUploadSuccess = function ()
        {
            this.removeProgressBar();
            uploadDone = true;
            isUploading = false;

            $cancelButton.remove();
            $html.addClass("ilFileUploadSuccess");
        };

        /**
         * Removes the HTML that belongs to this file.
         */
        this.removeHtml = function ()
        {
            $html.remove();
            $html = null;
        };

        /**
         * Removes the progress bar from the HTML.
         */
        this.removeProgressBar = function ()
        {
            $progressBar.remove();
            $progressPercentage.html("&nbsp;");

            $html.addClass("ilFileUploadNoUpload");
        };

        /**
         * Returns whether the upload of the file is still pending.
         */
        this.isUploadPending = function ()
        {
            // file cannot be uploaded?
            if (!this.canUpload)
                return false;

            return !isUploading && !uploadDone && !hasUploadError;
        };

        /**
         * Returns whether the upload of the file is done.
         */
        this.isUploadDone = function ()
        {
            // file cannot be uploaded?
            if (!this.canUpload)
                return true;

            return uploadDone;
        };

        /**
         * Returns whether the upload of the file is finished - either with or without error.
         */
        this.isUploadFinished = function ()
        {
            // file cannot be uploaded?
            if (!this.canUpload)
                return true;

            return uploadDone || hasUploadError;
        };

        /**
         * Returns whether the upload of the file is in progress.
         */
        this.isUploadInProgress = function ()
        {
            // file cannot be uploaded?
            if (!this.canUpload)
                return false;

            return isUploading;
        };

        /**
         * Returns whether the upload of the file was successful and no error occured.
         */
        this.isUploadSuccessful = function ()
        {
            // file cannot be uploaded?
            if (!this.canUpload)
                return false;

            return uploadDone && !hasUploadError;
        };

        /**
         * Determines whether the file is extracted after uploading.
         */
        this.extractAfterUpload = function (withStructure)
        {
            var unzip = this.canExtract && extract;
            if (withStructure)
                return unzip && keepStructure;

            return unzip;
        };

        /**
         * Called when the extract checkbox was clicked.
         */
        function onExtractChanged(e)
        {
            var isChecked = $(this).prop("checked");
            log("File.onExtractChanged (%s): checked = %s", self.id, isChecked);
            
            $html.find(".ilFileUploadEntryKeepStructure").toggle(isChecked);
            $html.find(".ilFileUploadEntryTitle").toggle(!isChecked);
            $html.find(".ilFileUploadEntryDescription").toggle(!isChecked);
        };
		
        /**
         * Toggles the visibility of the file details.
         */
        function onToggleOptions(e)
        {
            log("File.onToggleOptions (%s)", self.id);

            if ($html.is(".ilFileUploadEntryExpanded"))
            {
                $options.hide();
                $html.removeClass("ilFileUploadEntryExpanded");
            }
            else
            {
                $options.show();
                $html.addClass("ilFileUploadEntryExpanded");
            }
        }

        /**
         * Called when the upload of a file should be cancelled.
         */
        function onCancelClicked(e)
        {
            log("File.onCancelClicked (%s, uploaded=%s)", self.id, uploadDone);

            // not uploaded yet?
            if (!uploadDone || hasUploadError)
            {
                if (!self.uploadData.jqXHR || hasUploadError)
                {
                    self.uploadData.errorThrown = "abort";
                    self.fileUpload._trigger("fail", e, self.uploadData);
                }
                else
                {
                    self.uploadData.jqXHR.abort();
                }
            }
        }
    };

    /**
     * File upload manager.
     */
    var ilFileUploadManager = function ()
    {
        this.texts = {
            fileTooLarge: "The file exceeds the file size limit.",
            invalidFileType: "The file is of the wrong type.",
            fileZeroBytes: "The file size is 0 bytes or it is a folder.",
            uploadWasZeroBytes: "The upload failed as this is a folder, the file size is 0 bytes or the file was renamed meanwhile.",
            cancelAllQuestion: "Do you really want to cancel all pending uploads?",
            extractionFailed: "The extraction of the archive and its directories failed. Probably because you don't have the rights to create folders or categories within this object.",
            uploading: "Uploading...",
            extracting: "Extracting...",
            dropFilesHere: "Drop the files here to upload them to this object."
        };

        this.defaults = {
            // settings
            isManaged: false,
            url: null,
            maxNumberOfFiles: null,
            concurrentUploads: 3,
            multipleFiles: true,
            maxFileSize: null,
            allowedExtensions: [],
            supportedArchives: ["zip"],
            reloadPageWhenDone: false,

            // HTML elements
            fileRowTemplateId: "fileupload_row_tmpl",
            fileInput: null,
            dropZone: null,
            dropArea: null,
            fileSelectButton: null,
            fileList: null,
            submitButton: null,
            cancelButton: null,

            // callbacks
            onFileAdded: null,
            onFileUploaded: null,
            onFileRemoved: null
        };

        // variables
        var self = this;
        var isInitialized = false;
        var isPanelVisible = false;
        var panelAdded = false;
        var fileUploads = [];
        var files = new ilDictionary();
        var isUploadInProgress = false;
        var concurrentUploads = 3;
        var currentUploads = new ilDictionary();
        var reloadPage = false;

        // jquery objects
        var $panel = null;
        var $fileLists = null;
        var $cancelButton = null;
        var $uploadButton = null;
        var $showOptions = null;
        var $hideOptions = null;

        /**
         * Adds a new object where files can be uploaded to.
         */
        this.add = function (id, options, isCurrentObj)
        {
            // drag and drop not supported by browser? don't do anything
            if (!FILE_DRAG_AND_DROP_SUPPORTED)
                return;

            log("Manager.add: Id=%s, DropZone=%s, Title=%s", id, options.dropZone, options.listTitle);

            // initialize
            init();

            // create list where to add the files
            var $fileList = $("<div id='ilFileUploadList_" + id + "' class='ilFileUploadList'><div class='ilFileUploadListTitle'>" + options.listTitle + "</div></div>");

            if (isCurrentObj)
                $fileLists.prepend($fileList);
            else
                $fileLists.append($fileList);

            options.fileList = $fileList;
            options.reloadPageWhenDone = isCurrentObj;
            options.isManaged = true;

            // add callbacks
            options.onFileAdded = fileAddedCallback;
            options.onFileRemoved = fileRemovedCallback;
            options.onUploadDone = fileUploadDoneCallback;
            options.onUploadFailed = fileUploadFailedCallback;

            // create file upload instance
            var fileUpload = new ilFileUpload(id, options);
            if (isCurrentObj)
                fileUploads.splice(0, 0, fileUpload);
            else
                fileUploads.push(fileUpload);
        };

        /**
         * Initializes the file upload manager.
         */
        function init()
        {
            // already initialized?
            if (isInitialized)
                return;

            // get the panel template
            $panel = $(tmpl("fileupload_panel_tmpl", {}));

            // cache jQuery objects
            $fileLists = $panel.find("#ilFileUploadLists");
            $cancelButton = $panel.find(".ilFileUploadCancel");
            $uploadButton = $panel.find(".ilFileUploadStart");
            $showOptions = $panel.find(".ilFileUploadShowOptions");
            $hideOptions = $panel.find(".ilFileUploadHideOptions");

            // hide the "hide all details" options
            $hideOptions.hide();
            $hideOptions.on("click", function () { expandAll(false); });
            $showOptions.on("click", function () { expandAll(true); });

            // subscribe to submit and cancel buttons
            $cancelButton.on("click", cancelAllCallback);
            $uploadButton.on("click", startUploadsCallback);

            // attach to unload event
            $(window).on("beforeunload", pageUnloadingCallback);

            isInitialized = true;
        }

        /**
         * Shows the upload panel.
         */
        function show()
        {
            if (isPanelVisible)
                return;

            if (!panelAdded)
            {
                $("body").append($panel);
                panelAdded = true;
            }

            $panel.show();
            isPanelVisible = true;
        }

        /**
         * Hides the upload panel.
         */
        function hide()
        {
            if (!isPanelVisible)
                return;

            $panel.hide();
            isPanelVisible = false;
            reloadPage = false;

            // cancel all pending uploads
            for (var index in fileUploads)
            {
                fileUploads[index].cancelAllUploads();
                fileUploads[index].removeAllFiles();
            }

            // restore state for next call
            expandAll(false);
            refreshButtons();
        }

        /**
         * Expands the options of all files.
         */
        function expandAll(show)
        {
            log("Manager.expandAll: %s", show ? "show" : "hide");

            for (var index in fileUploads)
            {
                fileUploads[index].expandAll(show);
            }

            // update the show/hide labels
            if (show)
            {
                $showOptions.hide();
                $hideOptions.show();
            }
            else
            {
                $hideOptions.hide();
                $showOptions.show();
            }
        };

        /**
         * Callback that starts the upload of the files.
         */
        function startUploadsCallback()
        {
            log("Manager.startUpload");
            isUploadInProgress = true;

            // remove files that cannot be uploaded
            files.each(function (id, file)
            {
                if (file.isUploadFinished())
                {
                    file.cancel();
                }
            });

            // collapse all details
            expandAll(false);

            // start uploading
            refreshButtons();
            startNextUpload();
        }

        /**
         * Callback that cancels all uploads.
         */
        function cancelAllCallback()
        {
            log("Manager.cancelAll");

            // uploading, ask user if he really wants to cancel?
            if (isUploadInProgress)
            {
                var result = confirm(self.texts.cancelAllQuestion);
                if (result == false)
                {
                    e.preventDefault();
                    return;
                }
            }

            // cancel all pending uploads
            for (var index in fileUploads)
            {
                fileUploads[index].cancelAllUploads();
                fileUploads[index].removeAllFiles();
            }
        }

        /**
         * Callback when the user leaves or refreshes the page.
         */
        function pageUnloadingCallback()
        {
            // ask user whether he wants to cancel all pending uploads
            if (isUploadInProgress)
                return self.texts.cancelAllQuestion;
        }

        /**
         * Callback when a file was added to one of the managed file uploads.
         */
        function fileAddedCallback(file)
        {
            log("Manager.fileAdded: FileId=%s", file.id);
            files.add(file.id, file);
            refreshButtons();
            show();
        }

        /**
         * Callback when a file was removed from one of the managed file uploads.
         */
        function fileRemovedCallback(file)
        {
            log("Manager.fileRemoved: FileId=%s", file.id);
            files.remove(file.id);
            if (files.length < 1 && !isUploadInProgress)
            {
                hide();
            }
            else
            {
                refreshButtons();
                startNextUpload();
            }
        }

        /**
         * Callback when a file upload is done.
         */
        function fileUploadDoneCallback(file, reloadPageWhenDone)
        {
            log("Manager.fileUploadDone: FileId=%s", file.id);

            // update collections
            currentUploads.remove(file.id);
            files.remove(file.id);

            if (reloadPageWhenDone)
                reloadPage = true;

            startNextUpload();
        }

        /**
         * Callback when a file upload has failed.
         */
        function fileUploadFailedCallback(file)
        {
            log("Manager.fileUploadFailed: FileId=%s", file.id);
            currentUploads.remove(file.id);
            startNextUpload();
        }

        /**
         * Refreshes the buttons.
         */
        function refreshButtons()
        {
            var hasUploadableFiles = false;
            files.each(function (id, file)
            {
                if (file.isUploadPending())
                {
                    hasUploadableFiles = true;
                    return true;
                }
            });

            $cancelButton.prop("disabled", false); // always enabled
            $uploadButton.prop("disabled", isUploadInProgress || !hasUploadableFiles);
        }

        /**
         * Starts the next file upload.
         */
        function startNextUpload()
        {
            // no uploads? 
            if (!isUploadInProgress)
                return;

            // max uploads running?
            if (currentUploads.length >= concurrentUploads)
                return;

            // get next available file to start
            var nextFile = null;
            files.each(function (id, file)
            {
                if (file.isUploadPending())
                {
                    nextFile = file;
                    return true;
                }
            });

            // has file to start?
            if (nextFile != null)
            {
                currentUploads.add(nextFile.id, nextFile);
                nextFile.submit();

                // start next
                startNextUpload();
            }
            else
            {
                // no new file to start. did we finish all?
                isUploadInProgress = currentUploads.length > 0;

                log("Manager.startNextUpload: upload in progress=%s, files=%s", isUploadInProgress, files.length);

                // hide if all files processed successfully
                if (files.length == 0)
                {
                    if (reloadPage)
                    {
                        log(" -> reloading page '%s'", window.location);
                        window.location.reload(false);
                    }
                    else
                    {
                        hide();
                    }
                }
            }
        }
    };
    il.FileUpload = new ilFileUploadManager();

    // ilFileUpload constructor
    var ilFileUpload = function(id, options)
    {
        // variables
        var self = this;
        var id = id;
        var isManaged = false;
        var supportedArchivesRegex = null;
        var allowedExtensionsRegex = null;
        var fileRowTemplateId = null;
        var maxFileSize = null;
        var fileUpload = null;
        var files = new ilDictionary();
        var fileCount = 0;
        var fileCountText = null;
        var isUploadInProgress = false;
        var reloadPageWhenDone = false;
        var overlayTimeoutId = null;

        // jquery variables
        var $inputField = null;
        var $dropZone = null;
        var $fileSelectButton = null;
        var $fileList = null;
        var $submitButton = null;
        var $cancelButton = null;
        var $overlay = null;
        var $showOptions = null;
        var $hideOptions = null;
        var $fileCount = null;

        // callbacks
        var onFileAdded = null;
        var onFileRemoved = null;
        var onUploadDone = null;
        var onUploadFailed = null;

        // initialize object
        init(options);

        /**
         * Initializes the file upload object.
         */
        function init(options)
        {
            log("Upload.init (Id=%s)", id);

            // the plugin's final properties are the merged default and 
            // user-provided options (if any)
            var settings = $.extend(true, {}, il.FileUpload.defaults, options);

            // get needed jQuery elements
            $inputField = $(settings.fileInput);
            $dropZone = $(settings.dropZone);
            $fileSelectButton = $(settings.fileSelectButton);
            $fileList = $(settings.fileList);
            $fileCount = $dropZone.find(".ilFileUploadFileCount");

            // set properties
            isManaged = settings.isManaged;
            fileRowTemplateId = settings.fileRowTemplateId;
            maxFileSize = settings.maxFileSize;
            fileCountText = $fileCount.text();
            reloadPageWhenDone = settings.reloadPageWhenDone;

            // set callbacks
            onFileAdded = settings.onFileAdded;
            onFileRemoved = settings.onFileRemoved;
            onUploadDone = settings.onUploadDone;
            onUploadFailed = settings.onUploadFailed;

            // url to use
            var url = settings.url;

            // not managed? there's some stuff we have to do on our own
            if (!isManaged)
            {
                var $form = $inputField.closest("form");

                // use url of form
                url = $form.attr("action");

                // find elements
                $submitButton = $form.find("input[name='cmd[" + settings.submitButton + "]']");
                $cancelButton = $form.find("input[name='cmd[" + settings.cancelButton + "]']");
                $showOptions = $fileList.find(".ilFileUploadShowOptions");
                $hideOptions = $fileList.find(".ilFileUploadHideOptions");

                // upload button event
                $submitButton.on("click", uploadFiles);
                $cancelButton.on("click", cancelAllFiles);

                // attach to unload event
                $(window).on("beforeunload", pageUnloading);

                // hide the "hide all details" options
                $hideOptions.hide();
                $hideOptions.on("click", function () { self.expandAll(false); });
                $showOptions.on("click", function () { self.expandAll(true); });
            }
            else
            {
                // create empty jquery objects
                $submitButton = $cancelButton = $();
                $showOptions = $hideOptions = $();
            }
			
            // build archive regex
            if (settings.supportedArchives && settings.supportedArchives.length > 0)
                supportedArchivesRegex = new RegExp("\\.(" + settings.supportedArchives.join("|") + ")$", "i");
			
            // build archive regex
            if (settings.allowedExtensions && settings.allowedExtensions.length > 0)
                allowedExtensionsRegex = new RegExp("\\.(" + settings.allowedExtensions.join("|") + ")$", "i");
			
            // drag and drop supported?
            if (FILE_DRAG_AND_DROP_SUPPORTED)
            {
                overlayTimeoutId = "overlay_hide_" + $dropZone.attr("id");

                // create overlay div
                if (isManaged)
                    $overlay = $("<div class='ilFileDropTargetOverlay'><div class='ilFileDropTargetOverlayText'><i class='ilFileDropTargetOverlayImage'></i> " + il.FileUpload.texts.dropFilesHere + "</div></div>");
                else
                    $overlay = $dropZone;

                $overlay.on("dragleave", dropZoneDragLeave);

                // mark drop zone and add overlay
                $dropZone.addClass("ilFileDropTarget");
                if (isManaged)
                {
                    $dropZone.append($overlay);
                }
            }
            else
            {
                $overlay = null;

                // hide the drop area
                $(settings.dropArea).hide();
            }

            // if available attach the file upload widget to the input field
            // else we use the drop zone
            var $attachTo = $inputField.length == 1 ? $inputField : $dropZone;
            var paramName = $inputField.length == 1 ? "" : settings.fileInput;

            // initialize fileupload
            $attachTo.fileupload(
            {
                // settings
                url: url,
                dropZone: $overlay, /* if null, drag and drop is disabled */
                inputField: $inputField,
                limitConcurrentUploads: settings.concurrentUploads,
                singleFileUploads: true, /* only one file in each request! */
                dataType: "json",
                type: "POST",
                pasteZone: null,
                paramName: paramName,
				
                // callbacks
                drop: filesDropped,
                dragover: dropZoneDragOver,
                add: fileAdded,
                submit: fileSubmit,
                send: fileSend,
                done: uploadDone,
                fail: uploadFailed,
                progress: uploadProgress
            });
            fileUpload = $attachTo.data("blueimp-fileupload");

            // we're ready to rumble!
            log(" -> upload URL: %s", fileUpload.options.url);

            // update the file elements
            updateFileElements();
        }

        /**
		 * Expands or collapses all file rows to show or hide their details.
		 */
        this.expandAll = function (show)
        {
            if (show)
            {
                $fileList.find(".ilFileUploadEntryOptions").show();
                $fileList.find(".ilFileUploadEntry").addClass("ilFileUploadEntryExpanded");

                $showOptions.hide();
                $hideOptions.show();
            }
            else
            {
                $fileList.find(".ilFileUploadEntryOptions").hide();
                $fileList.find(".ilFileUploadEntry").removeClass("ilFileUploadEntryExpanded");

                $hideOptions.hide();
                $showOptions.show();
            }
        };

        /**
         * Cancels all running and pending uploads.
         */
        this.cancelAllUploads = function ()
        {
            if (files.length == 0)
                return;

            log("Upload.cancelAll (Id=%s, Count=%s)", id, files.length);

            // no uploads running?
            if (!isUploadInProgress)
                return;

            // cancel all uploads
            files.each(function (id, file)
            {
                file.cancel();
            });

            isUploadInProgress = false;
        };

        /**
         * Removes all files from the file upload.
         */
        this.removeAllFiles = function ()
        {
            if (files.length == 0)
                return;

            log("Upload.removeAll (Id=%s, Count=%s)", id, files.length);

            // cancel all uploads
            files.each(function (id, file)
            {
                removeFile(file);
            });
        };

        /**
		 * Called when the user wants to cancel all file uploads by pressing the forms cancel button.
		 */
        function cancelAllFiles(e)
        {
            log("Upload.cancelAllFiles (Id=%s, Count=%s)", id, files.length);

            // not uploading?
            if (!isUploadInProgress)
                return;

            // ask user whether he wants to cancel all pending uploads
            var result = confirm(il.FileUpload.texts.cancelAllQuestion);
            if (result == false)
            {
                e.preventDefault();
                return;
            }

            cancelAllUploads();
        }

        /**
         * Called when the user leaves or refreshes the page.
         */
        function pageUnloading()
        {
            // ask user whether he wants to cancel all pending uploads
            if (isUploadInProgress)
                return il.FileUpload.texts.cancelAllQuestion;
        }
		
        /*
         * Called when the user drags over the drop zone.
         */
        function dropZoneDragOver(e)
        {
            // don't bubble up to the document
            if (e.stopPropagation)
                e.stopPropagation();
            else
                e.cancelBubble = true;

            // don't hide overlays
            $.doTimeout(OVERLAY_HIDE_ID);

            // don't remove drag over highlight
            $.doTimeout(overlayTimeoutId);

            // set the drop effect to copy
            setDropEffect(e, "copy");

            // add drag over effect
            $overlay.addClass("ilFileDragOver");
        }

        /*
         * Called when the user leaves the drop zone while dragging.
         */
        function dropZoneDragLeave(e)
        {
            // we use a timeout here as this removes flickering
            $.doTimeout(overlayTimeoutId, DRAG_LEAVE_DELAY, function ()
            {
                $overlay.removeClass("ilFileDragOver");
            });
        }
		
        /**
         * Called when a file was dropped by the user on the drop zone.
         */
        function filesDropped(e, data)
        {
            log("Upload.filesDropped (Id=%s)", id);

            // remove drag over effects
            $.doTimeout(overlayTimeoutId);
            $overlay.removeClass("ilFileDragOver");
        }

        /**
         * Called when a file or multiple files were added either by selecting or drag and drop.
         */
        function fileAdded(e, data)
        {
            log("Upload.fileAdded (Id=%s)", id);

            $.each(data.files.reverse(), function (index, addedFile)
            {
                // file already added?
                if (containsFile(addedFile))
                    return;

                // create id and set on data object
                var fileId = "file_" + id + "_" + (fileCount++);
                data.id = fileId;

                // add to files
                var file = new ilFile(fileId, addedFile, fileUpload, data);
                file.canExtract = supportedArchivesRegex ? file.name.match(supportedArchivesRegex) : false;

                log("  -> %s: %s (%s)", fileId, file.name, file.sizeFormatted);

                // load html template for the file
                var $html = file.loadHtmlTemplate(fileRowTemplateId);

                // invalid file?
                var errorText = null;

                // check for errors
                if (file.size != null && file.size < 1)
                {
                    // folder or empty file
                    log(" -> %s: file is 0 bytes or a folder!", fileId);
                    errorText = il.FileUpload.texts.fileZeroBytes;
                }
                else if (file.size != null && maxFileSize && file.size > maxFileSize)
                {
                    // file too big
                    log(" -> %s: file is too large (limit = %s)!", fileId, getFormattedFileSize(maxFileSize));
                    errorText = il.FileUpload.texts.fileTooLarge;
                }
                else if (allowedExtensionsRegex && !file.name.match(allowedExtensionsRegex))
                {
                    // wrong extension
                    log(" -> %s: file type is not allowed (type = %s)!", fileId, file.ext);
                    errorText = il.FileUpload.texts.invalidFileType;
                }

                // add file element at first position
                file.markAsNew();
                $fileList.children().eq(0).after($html);

                // set the error text
                if (errorText != null)
                    file.disableUpload(errorText);

                // add to list and notify listeners
                files.add(fileId, file);
                if (onFileAdded != null)
                    onFileAdded(file);
            });

            updateFileElements();
        }

        /**
         * Initiates uploading the added files to the server.
         */
        function uploadFiles(e)
        {
            e.preventDefault();

            log("Upload.uploadFiles (Count=%s)", files.length);

            // disable upload button
            $submitButton.prop("disabled", true);
            $fileSelectButton.addClass("disabled");
            $fileSelectButton.find("input").prop("disabled", true);
            self.expandAll(false);

            // submit each file
            files.each(function (id, file)
            {
                if (file.isUploadFinished())
                {
                    removeFile(file);
                }
                else if (!file.isUploadDone())
                {
                    isUploadInProgress = true;
                    file.submit();
                }
            });
        }

        /**
         * Called before a file is submitted to the server.
         */
        function fileSubmit(e, data)
        {
            // add file specific form data
            var file = files.get(data.id);
            data.formData = file.getFormData();
        }

        /**
         * Called when a file is submitted to the server.
         */
        function fileSend(e, data)
        {
            log("Upload.fileSend (%s)", data.id);

            var file = files.get(data.id);

            // disable all input fields
            file.disableInputs();

            // set progress (if supported by the browser)
            var progress = 0;
            var progressText = null;
            if (data.dataType && data.dataType.substr(0, 6) === "iframe")
            {
                // iframe is used, progress cannot be displayed
                progress = 100;
                progressText = il.FileUpload.texts.uploading;
            }
            file.setUploadProgress(progress, progressText);
        }

        /**
         * Called when a file should be removed.
         */
        function removeFile(file)
        {
            if (!files.contains(file.id))
                return;

            file.removeHtml();
            delete files.remove(file.id);

            updateFileElements();

            // notify listeners
            if (onFileRemoved != null)
                onFileRemoved(file);
        }

        /**
         * Called when the upload of a file is done.
         */
        function uploadDone(e, data)
        {
            var result = data.result;

            // data.result
            // data.textStatus;
            // data.jqXHR;
            log("Upload.uploadDone (%s): %s", data.id, data.textStatus);

            // get the file data
            var file = files.get(data.id);

            if (result.debug)
                log(" -> Debug: %s", result.debug);

            // error set?
            if (result.error)
            {
                log(" -> Error: %s", result.error);
                file.setUploadError(result.error);
                if (onUploadFailed != null)
                    onUploadFailed(file);
            }
            else
            {
                file.setUploadSuccess();
                if (onUploadDone != null)
                    onUploadDone(file, reloadPageWhenDone);
            }

            updateUploadInProgress();

            // all files uploaded?
            if (allUploadsSuccessful())
            {
                // lets go back to the previous page
                if ($cancelButton.length > 0)
                    $cancelButton.click();
            }
        }

        /**
         * Called when the upload of a file failed (abort or error).
         */
        function uploadFailed(e, data)
        {
            var errorText = undefined;

            // get the file data
            var file = files.get(data.id);

            // data.errorThrown
            // data.textStatus
            // data.jqXHR
            log("Upload.uploadFailed (%s): %s", data.id, data.textStatus || data.errorThrown);

            if (data.errorThrown == "abort")
            {
                removeFile(file);
            }
            else if (data.textStatus == "parsererror")
            {
                // that's mostly the case if the user has no rights to create folders or categories
                // when extracting an archive, as ILIAS forces a redirect to display an error (fixed in 4.2.7).
                // therefore we don't receive a proper JSON response
                if (file.extractAfterUpload(true))
                {
                    errorText = il.FileUpload.texts.extractionFailed;
                }
                else if (data.jqXHR.responseText)
                {
                    // should never happen, try to get the message text from returned error page
                    var errorMessage = data.jqXHR.responseText;
                    var startIndex = errorMessage.indexOf("<div class='ilFailureMessage'>");
                    if (startIndex > 0)
                    {
                        startIndex = errorMessage.indexOf("</h5>", startIndex);
                        if (startIndex > 0)
                        {
                            startIndex += 5;
                            var endIndex = errorMessage.indexOf("</div>", startIndex);
                            if (endIndex > 0)
                                errorText = $.trim(errorMessage.substr(startIndex, endIndex - startIndex));
                        }
                    }
                }

                // no text found? use default text
                if (!errorText)
                {
                    log("Error-Response: %s", data.jqXHR.responseText);
                    errorText = "Unknown Error";
                }

                file.setUploadError(errorText);
                if (onUploadFailed != null)
                    onUploadFailed(file);
            }
            else
            {
                // server errors will trigger '_uploadDone'
                file.setUploadError(il.FileUpload.texts.uploadWasZeroBytes);
                if (onUploadFailed != null)
                    onUploadFailed(file);
            }

            updateUploadInProgress();
        }

        /**
         * Called when the upload progress of a file changed.
         */
        function uploadProgress(e, data)
        {
            var progress = parseInt(data.loaded / data.total * 100, 10);
            var progressText = null;

            // get the file data
            var file = files.get(data.id);

            // file uploaded and should be extracted, then change progress text
            if (data.loaded == data.total && file.extractAfterUpload(false))
                progressText = il.FileUpload.texts.extracting;

            file.setUploadProgress(progress, progressText);
        }

        /**
		 * Updates the file elements.
		 */
        function updateFileElements()
        {
            var count = files.length;
            if (count > 0)
            {
                var validCount = count - $fileList.find(".ilFileUploadError").length;

                $fileList.show();
                $submitButton.prop("disabled", validCount == 0);
                if (validCount > 0)
                    $fileCount.text(fileCountText.replace("%s", validCount)).show();
                else
                    $fileCount.hide();
            }
            else
            {
                $fileList.hide();
                $submitButton.prop("disabled", true);
                $fileCount.hide();
            }
        }

        /**
		 * Checks whether all file uploads were successful and no error occured.
		 */
        function allUploadsSuccessful()
        {
            var allSuccessful = true;

            files.each(function (id, file)
            {
                if (!file.isUploadSuccessful())
                {
                    allSuccessful = false;
                    return true;
                }
            });

            log("Upload.allUploadsSuccessful: %s", allSuccessful);
            return allSuccessful;
        }

        /**
         * Updates the uploadInProgress variable.
         */
        function updateUploadInProgress()
        {
            if (!isUploadInProgress)
                return;

            var fileCount = 0;
            var uploadedCount = 0;

            files.each(function (id, file)
            {
                if (!file.canUpload)
                    return;

                fileCount++;

                if (file.isUploadFinished())
                    uploadedCount++;
            });

            isUploadInProgress = fileCount > uploadedCount;
        }
		
        /**
         * Checks whether a file is already within the file list.
         */
        function containsFile(file)
        {
            var fileFound = false;
            files.each(function (id, fileData)
            {
                if (fileData.name == getFileName(file) && fileData.size == file.size)
                {
                    fileFound = true;
                    return true;
                }
            });
            return fileFound;
        }
    };
    window.ilFileUpload = ilFileUpload;

    /**
     * Gets the file name for the specified file system object.
     */
    function getFileName(file)
    {
        // firefox < 4 uses fileName instead of name
        if (file.fileName)
            return file.fileName;

        return file.name;
    }

    /**
     * Formats the specified size in bytes into a more readable size.
     */
    function getFormattedFileSize(size)
    {
        if (typeof size !== "number")
            return "";

        var units = ["Bytes", "KB", "MB", "GB"];
        var digits = [0, 0, 1, 1];
        var index = 0;

        while (size >= 1024 && index < 3)
        {
            size = size / 1024;
            index++;
        }

        // format
        return size.toFixed(digits[index]).toLocaleString() + " " + units[index];
    }

    /**
     * Shows the drop zone overlays.
     */
    function showDropZoneOverlays()
    {
        if (overlaysVisible)
        {
            $.doTimeout(OVERLAY_HIDE_ID);
            return;
        }

        overlaysVisible = true;
        $(".ilFileDropTargetOverlay").show();
    }

    /**
     * Hides the drop zone overlays.
     */
    function hideDropZoneOverlays()
    {
        if (!overlaysVisible)
            return;

        $.doTimeout(OVERLAY_HIDE_ID, DRAG_LEAVE_DELAY, function ()
        {
            overlaysVisible = false;
            $(".ilFileDropTargetOverlay").hide().removeClass("ilFileDragOver");
        });
    }

    /**
     * Evaluates whether the specified event is an event caused by dragging a file into the browsers window.
     */
    function isFileDragEvent(e)
    {
        if (e && e.originalEvent)
        {
            var dataTransfer = e.originalEvent.dataTransfer;
            if (dataTransfer)
            {
                // either files are filled or types by browsers
                if (containsFiles(dataTransfer))
                    return true;
            }
        }

        return false;
    }

    /**
     * Checks whether the specified dataTransfer object contains any files.
     */
    function containsFiles(dataTransfer)
    {
        // Safari
        if (dataTransfer.files && dataTransfer.files.length > 0)
            return true;

        // check types
        if (dataTransfer.types && dataTransfer.types.length > 0)
        {
            // Chrome
            if (!!dataTransfer.types.indexOf && dataTransfer.types.indexOf("Files") >= 0)
                return true;

            // Firefox, Opera, IE10
            if (!!dataTransfer.types.contains && dataTransfer.types.contains("Files"))
                return true;
        }

        return false;
    }

    /**
     * Sets the specified drop effect on the passed in event.
     */
    function setDropEffect(e, effect)
    {
        if (e && e.originalEvent && e.originalEvent.dataTransfer)
            e.originalEvent.dataTransfer.dropEffect = effect;
    }

    /**
     * Evaluates whether the browser supports file drag and drop.
     */
    function isFileDragAndDropSupported()
    {
        // is drag and drop supported in general?
        if (!("draggable" in document.createElement("span")))
            return false;

        // check if file API is implemented (Chrome 6+, Firefox 4+, Safari 6, IE 10, Opera 12)
        if (!!window.FileReader && !!window.FormData)
            return true;

        // check for Safari 5.1 as it supports it but dropped the FileReader support!
        if (!!window.FormData)
            return true;

        // not supported
        return false;
    }

    // global drag and drop events
    $(document).on({
        dragenter: function (e)
        {
            if (!isFileDragEvent(e))
                return;

            e.preventDefault();

            showDropZoneOverlays();
            setDropEffect(e, "none");
        },
        dragover: function (e)
        {
            // overlays not visible?
            if (!overlaysVisible)
                return;

            // prevent default browser file drop (= load the dropped file)
            e.preventDefault();

            showDropZoneOverlays();

            if(!$(e.target).parents(".il-dropzone")){
                setDropEffect(e, "none");
            }
        },
        dragleave: function (e)
        {
            // overlays not visible?
            if (!overlaysVisible)
                return;

            e.preventDefault();
            hideDropZoneOverlays();
        },
        drop: function (e)
        {
            // overlays not visible?
            if (!overlaysVisible)
                return;

            // prevent default browser file drop (= load the dropped file)
            e.preventDefault();
            hideDropZoneOverlays();
        }
    });

})(jQuery, window, document);
