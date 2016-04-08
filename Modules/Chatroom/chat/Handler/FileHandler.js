var FileSystem					= require('fs');
var FileHandlerReadValidator	= require('../Validation/Validator/FileHandlerReadValidator');

/**
 * @namespace	Handler
 * @class		FileHandler
 * @constructor
 */
var FileHandler = function FileHandler() {

	/**
	 * @public
	 *
	 * @return {JSON}
	 */
	this.read = function(filename) {
		return JSON.parse(_read(filename));
	};

	this.readPlain = function(filename) {
		return _read(filename);
	};

	function _read(filename) {
		FileHandlerReadValidator.create(filename).validate();

		return FileSystem.readFileSync(filename);
	}
};

module.exports = new FileHandler();