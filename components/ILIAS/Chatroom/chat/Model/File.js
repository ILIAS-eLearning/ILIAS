/**
 * @namespace	Model
 * @class 		File
 * @constructor
 */
module.exports = function File() {
	/**
	 * @type {string}
	 */
	var _content = "";

	/**
	 * @returns {string}
	 */
	this.getContent = function() { return _content; };

	/**
	 * @param {string} content
	 */
	this.setContent = function(content) { _content = content; };

	/**
	 * @return {JSON}
	 */
	this.toJSON = function() { return JSON.parse(_content); };
};
