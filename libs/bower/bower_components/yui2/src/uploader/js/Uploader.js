/**
 * The YUI Uploader Control
 * @module uploader
 * @description <p>YUI Uploader provides file upload functionality that goes beyond the basic browser-based methods. 
 * Specifically, the YUI Uploader allows for:
 * <ol>
 * <li> Multiple file selection in a single "Open File" dialog.</li>
 * <li> File extension filters to facilitate the user's selection.</li>
 * <li> Progress tracking for file uploads.</li>
 * <li> A range of file metadata: filename, size, date created, date modified, and author.</li>
 * <li> A set of events dispatched on various aspects of the file upload process: file selection, upload progress, upload completion, etc.</li>
 * <li> Inclusion of additional data in the file upload POST request.</li>
 * <li> Faster file upload on broadband connections due to the modified SEND buffer size.</li>
 * <li> Same-page server response upon completion of the file upload.</li>
 * </ol>
 * </p>
 * @title Uploader
 * @namespace YAHOO.widget
 * @requires yahoo, dom, element, event
 */
/**
 * Uploader class for the YUI Uploader component.
 *
 * @namespace YAHOO.widget
 * @class Uploader
 * @uses YAHOO.widget.FlashAdapter
 * @constructor
 * @param containerId {HTMLElement} Container element for the Flash Player instance.
 * @param buttonSkin {String} [optional]. If defined, the uploader is 
 * rendered as a button. This parameter must provide the URL of a button
 * skin sprite image. Acceptable types are: jpg, gif, png and swf. The 
 * sprite is divided evenly into four sections along its height (e.g., if
 * the sprite is 200 px tall, it's divided into four sections 50px each).
 * Each section is used as a skin for a specific state of the button: top
 * section is "up", second section is "over", third section is "down", and
 * fourth section is "disabled". 
 * If the parameter is not supplied, the uploader is rendered transparent,
 * and it's the developer's responsibility to create a visible UI below it.
 * @param forceTransparent {Boolean} This parameter, if true, forces the Flash
 * UI to be rendered with wmode set to "transparent". This behavior is useful 
 * in conjunction with non-rectangular button skins with PNG transparency. 
 * The parameter is false by default, and ignored if no buttonSkin is defined.
  */
YAHOO.widget.Uploader = function(containerId, buttonSkin, forceTransparent)
{
	var newWMode = "window";

	if (!(buttonSkin) || (buttonSkin && forceTransparent)) {
		newWMode = "transparent";
	}

	
 	YAHOO.widget.Uploader.superclass.constructor.call(this, YAHOO.widget.Uploader.SWFURL, containerId, {wmode:newWMode}, buttonSkin);

	/**
	 * Fires when the mouse is pressed over the Uploader.
	 * Only fires when the Uploader UI is enabled and
	 * the render type is 'transparent'.
	 *
	 * @event mouseDown
	 * @param event.type {String} The event type
	 */
	this.createEvent("mouseDown");
	
	/**
	 * Fires when the mouse is released over the Uploader.
	 * Only fires when the Uploader UI is enabled and
	 * the render type is 'transparent'.
	 *
	 * @event mouseUp
	 * @param event.type {String} The event type
	 */
	this.createEvent("mouseUp");

	/**
	 * Fires when the mouse rolls over the Uploader.
	 *
	 * @event rollOver
	 * @param event.type {String} The event type
	 */
	this.createEvent("rollOver");
	
	/**
	 * Fires when the mouse rolls out of the Uploader.
	 *
	 * @event rollOut
	 * @param event.type {String} The event type
	 */
	this.createEvent("rollOut");
	
	/**
	 * Fires when the uploader is clicked.
	 *
	 * @event click
	 * @param event.type {String} The event type
	 */
	this.createEvent("click");
	
	/**
	 * Fires when the user has finished selecting files in the "Open File" dialog.
	 *
	 * @event fileSelect
	 * @param event.type {String} The event type
	 * @param event.fileList {Object} A dictionary of objects with file information
	 * @param event.fileList[].size {Number} File size in bytes for a specific file in fileList
	 * @param event.fileList[].cDate {Date} Creation date for a specific file in fileList
	 * @param event.fileList[].mDate {Date} Modification date for a specific file in fileList
	 * @param event.fileList[].name {String} File name for a specific file in fileList
	 * @param event.fileList[].id {String} Unique file id of a specific file in fileList
	 */
	this.createEvent("fileSelect");

	/**
	 * Fires when an upload of a specific file has started.
	 *
	 * @event uploadStart
	 * @param event.type {String} The event type
	 * @param event.id {String} The id of the file that's started to upload
	 */
	this.createEvent("uploadStart");

	/**
	 * Fires when new information about the upload progress for a specific file is available.
	 *
	 * @event uploadProgress
	 * @param event.type {String} The event type
	 * @param event.id {String} The id of the file with which the upload progress data is associated
	 * @param bytesLoaded {Number} The number of bytes of the file uploaded so far
	 * @param bytesTotal {Number} The total size of the file
	 */
	this.createEvent("uploadProgress");
	
	/**
	 * Fires when an upload for a specific file is cancelled.
	 *
	 * @event uploadCancel
	 * @param event.type {String} The event type
	 * @param event.id {String} The id of the file with which the upload has been cancelled.
	 */	
	this.createEvent("uploadCancel");

	/**
	 * Fires when an upload for a specific file is complete.
	 *
	 * @event uploadComplete
	 * @param event.type {String} The event type
	 * @param event.id {String} The id of the file for which the upload has been completed.
	 */	
	this.createEvent("uploadComplete");

	/**
	 * Fires when the server sends data in response to a completed upload.
	 *
	 * @event uploadCompleteData
	 * @param event.type {String} The event type
	 * @param event.id {String} The id of the file for which the upload has been completed.
	 * @param event.data {String} The raw data returned by the server in response to the upload.
	 */	
	this.createEvent("uploadCompleteData");
	
	/**
	 * Fires when an upload error occurs.
	 *
	 * @event uploadError
	 * @param event.type {String} The event type
	 * @param event.id {String} The id of the file that was being uploaded when the error has occurred.
	 * @param event.status {String} The status message associated with the error.
	 */	
	this.createEvent("uploadError");
}

/**
 * Location of the Uploader SWF
 *
 * @property Chart.SWFURL
 * @private
 * @static
 * @final
 * @default "assets/uploader.swf"
 */
YAHOO.widget.Uploader.SWFURL = "assets/uploader.swf";

YAHOO.extend(YAHOO.widget.Uploader, YAHOO.widget.FlashAdapter,
{	
/**
 * Starts the upload of the file specified by fileID to the location specified by uploadScriptPath.
 *
 * @param fileID {String} The id of the file to start uploading.
 * @param uploadScriptPath {String} The URL of the upload location.
 * @param method {String} Either "GET" or "POST", specifying how the variables accompanying the file upload POST request should be submitted. "GET" by default.
 * @param vars {Object} The object containing variables to be sent in the same request as the file upload.
 * @param fieldName {String} The name of the variable in the POST request containing the file data. "Filedata" by default.
 * </code> 
 */
	upload: function(fileID, uploadScriptPath, method, vars, fieldName)
	{
		this._swf.upload(fileID, uploadScriptPath, method, vars, fieldName);
	},
	
/**
 * Starts the upload of the files specified by fileIDs, or adds them to a currently running queue. The upload queue is automatically managed.
 *
 * @param fileIDs {Array} The ids of the files to start uploading.
 * @param uploadScriptPath {String} The URL of the upload location.
 * @param method {String} Either "GET" or "POST", specifying how the variables accompanying the file upload POST request should be submitted. "GET" by default.
 * @param vars {Object} The object containing variables to be sent in the same request as the file upload.
 * @param fieldName {String} The name of the variable in the POST request containing the file data. "Filedata" by default.
 * </code> 
 */
	uploadThese: function(fileIDs, uploadScriptPath, method, vars, fieldName)
	{
		this._swf.uploadThese(fileIDs, uploadScriptPath, method, vars, fieldName);
	},
	
/**
 * Starts uploading all files in the queue. If this function is called, the upload queue is automatically managed.
 *
 * @param uploadScriptPath {String} The URL of the upload location.
 * @param method {String} Either "GET" or "POST", specifying how the variables accompanying the file upload POST request should be submitted. "GET" by default.
 * @param vars {Object} The object containing variables to be sent in the same request as the file upload.
 * @param fieldName {String} The name of the variable in the POST request containing the file data. "Filedata" by default.
 * </code> 
 */
	uploadAll: function(uploadScriptPath, method, vars, fieldName)
	{
		this._swf.uploadAll(uploadScriptPath, method, vars, fieldName);
	},

/**
 * Cancels the upload of a specified file. If no file id is specified, all ongoing uploads are cancelled.
 *
 * @param fileID {String} The ID of the file whose upload should be cancelled.
 */
	cancel: function(fileID)
	{
		this._swf.cancel(fileID);
	},

/**
 * Clears the list of files queued for upload.
 *
 */
	clearFileList: function()
	{
		this._swf.clearFileList();
	},
	
/**
 * Removes the specified file from the upload queue. 
 *
 * @param fileID {String} The id of the file to remove from the upload queue. 
 */
	removeFile: function (fileID) 
	{
		this._swf.removeFile(fileID);
	},

/**
 * Turns the logging functionality on.
 * Uses Flash internal trace logging, as well as YUI Logger, if available.
 *
 * @param allowLogging {Boolean} If true, logs are output; otherwise, no logs are produced.
 */
    setAllowLogging: function (allowLogging)
    {
      	this._swf.setAllowLogging(allowLogging);
    },

/**
 * Sets the number of simultaneous uploads when using uploadAll()
 * The minimum value is 1, and maximum value is 5. The default value is 2.
 *
 * @param simUploadLimit {int} Number of simultaneous uploads, between 1 and 5.
 */
    setSimUploadLimit : function (simUploadLimit)
    {
       this._swf.setSimUploadLimit(simUploadLimit);
    },

/**
 * Sets the flag allowing users to select multiple files for the upload.
 *
 * @param allowMultipleFiles {Boolean} If true, multiple files can be selected. False by default.
 */     
    setAllowMultipleFiles : function (allowMultipleFiles) 
    {
       this._swf.setAllowMultipleFiles(allowMultipleFiles);
    },

/**
 * Sets the file filters for the "Browse" dialog.
 *
 *  @param newFilterArray An array of sets of key-value pairs of the form
 *  {extensions: extensionString, description: descriptionString, [optional]macType: macTypeString}
 *  The extensions string is a semicolon-delimited list of elements of the form "*.xxx", 
 *  e.g. "*.jpg;*.gif;*.png". 
 */       
    setFileFilters : function (fileFilters) 
    {
       this._swf.setFileFilters(fileFilters);
    },

	/**
	 * Enables the mouse events on the Uploader.
	 * If the uploader is being rendered as a button,
	 * then the button's skin is set to "up"
	 * (first section of the button skin sprite).
	 *
	 */
	enable : function ()
	{
		this._swf.enable();
	},

	/**
	 * Disables the mouse events on the Uploader.
	 * If the uploader is being rendered as a button,
	 * then the button's skin is set to "disabled"
	 * (fourth section of the button skin sprite).
	 *
	 */
	disable : function () 
	{
		this._swf.disable();
	}
});