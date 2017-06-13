/**
 * Object il.UI.Uploader
 *
 * An Uploader can be used to add files into its pool
 * and render them in the html.
 * Files can be uploaded to the specified url.
 *
 * @author nmaerchy <nm@studer-raimann.ch>
 * @version 0.0.1
 */

var il = il || {};
il.UI = il.UI || {};
(function ($, UI) {

	/**
	 * @param {string} previewContainer the id of the container to render the files
	 * @param {string} url the url to upload all files
	 * @constructor
	 */
	UI.Uploader = function (previewContainer, url) {
		this.previewContainer = previewContainer;
		this.url = url;
		this.files = [];
	};

	/**
	 * @returns {string} The id of this instance.
	 */
	UI.Uploader.prototype.getInstanceId = function () {
		return this.previewContainer;
	};

	/**
	 * Adds the passed in file to this Uploader.
	 *
	 * @param {File} file the file to add
	 */
	UI.Uploader.prototype.addFile = function (file) {
		this.files.push(file);
	};

	/**
	 * Submits all files of this uploader with ajax.
	 * After successful submit, all stored files of
	 * this Uploader will be cleared.
	 */
	UI.Uploader.prototype.submit = function () {

		var ajaxData = new FormData();

		$.each(this.files, function (index, file) {
			ajaxData.append(file.name, file);
		});

		$.ajax({
			url: this.url,
			type: "application/multipart",
			data: ajaxData,
			cache: false,
			contentType: false,
			processData: false,
			complete: function () {
				console.log("Upload Complete");
			},
			success: function (data) {
				console.log(data.success);
				this.files = [];
			}.bind(this),
			error: function () {
				console.log("Upload error");
			}
		})
	}

})($, il.UI);