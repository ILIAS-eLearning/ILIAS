var AbstractValidator = require('./AbstractValidator');
var MinNumOfArguments = require('../Rules/MinNumOfArguments');
var MaxNumOfArguments = require('../Rules/MaxNumOfArguments');


/**
 * @namespace	Validator
 * @class		CommandLineHandlerValidator
 * @constructor
 */
var CommandLineHandlerValidator = function CommandLineHandlerValidator() {};
CommandLineHandlerValidator.prototype = new AbstractValidator();

/**
 * @type {CommandLineHandlerValidator}
 */
module.exports = function() {
	var validator = new CommandLineHandlerValidator();

	validator.addRule(new MinNumOfArguments(2, "Required: Server-Config file", "Required: Client-Config file"));
	validator.addRule(new MaxNumOfArguments(0));

	return validator;
}();
