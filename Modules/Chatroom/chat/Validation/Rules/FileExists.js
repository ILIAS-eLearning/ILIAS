var FileSystem = require('fs');

/**
 * @namespace	Rules
 * @class		FileExists
 * @constructor
 */
module.exports = function FileExists(filename) {

	/**
	 * @type {string}
	 * @private
	 */
	var _filename = filename;

	this.validate = function() {
		if(!FileSystem.existsSync(_filename)) {
			throw new Error('File does not exist: ' + _filename);
		}
	};
};
