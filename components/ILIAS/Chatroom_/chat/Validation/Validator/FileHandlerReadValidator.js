var AbstractValidator = require('./AbstractValidator');
var FileExists = require('../Rules/FileExists');

/**
 * @namespace	Validator
 * @class		FileHandlerReadValidator
 * @constructor
 */
var FileHandlerReadValidator = function FileHandlerReadValidator() {};
FileHandlerReadValidator.prototype = new AbstractValidator();

/**
 * @type {{create: Function}}
 */
module.exports = {
	/**
	 * @param {string} filename
	 * @returns {FileHandlerReadValidator}
	 */
	create: function create(filename) {
		var validator = new FileHandlerReadValidator();
		validator.addRule(new FileExists(filename));

		return validator;
	}
};
