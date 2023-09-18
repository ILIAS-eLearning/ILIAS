var CONST = require('../Constants');
var CommandLineHandlerValidator = require('../Validation/Validator/CommandLineHandlerValidator');
var FileHandler = require('./FileHandler');

/**
 * @namespace	Handler
 * @class		CommandLineHandler
 * @constructor
 */
var CommandLineHandler = function CommandLineHandler() {

	/**
	 * @returns {Array}
	 */
	this.readArguments = function() {
		CommandLineHandlerValidator.validate();

		var arguments = [];

		for(var index = CONST.COMMAND_ARGS_OFFSET; index < process.argv.length; index++) {
			arguments.push(process.argv[index]);
		}

		return arguments;
	}
};

/**
 * @type {CommandLineHandler}
 */
module.exports = new CommandLineHandler();