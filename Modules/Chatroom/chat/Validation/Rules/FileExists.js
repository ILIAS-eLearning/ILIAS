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
			/**
			 * @TODO Change Error
			 */
			throw new Error('CHANGE TO SPECIFIC ERROR');
		}
	};
};
