/**
 * @namespace	Rules
 * @class		MinNumOfArguments
 * @constructor
 */
module.exports = function MinNumOfArguments(min) {

	/**
	 * Each NodeJs command has at least to static arguments.
	 * [0]: The executable, [1]: Name of Program
	 *
	 * @type {number}
	 */
	var CONST_NUM_COMMAND_ARGS = 2;

	/**
	 * @type {number}
	 */
	var _min = min;

	/**
	 * @type {Arguments}
	 */
	var additionalArguments = arguments;

	this.validate = function() {
		if(!isGreaterEqualsMin()) {
			var numArguments = getNumExistingArguments();

			if(additionalArguments.length >= numArguments+1) {
				throw new Error(additionalArguments[numArguments+1]);
			}

			throw new Error("ERR_MSG_GENERAL");
		}
	};

	function isGreaterEqualsMin() {
		return process.argv.length >= _min + CONST_NUM_COMMAND_ARGS;
	}

	function getNumExistingArguments() {
		return process.argv.length - CONST_NUM_COMMAND_ARGS;
	}
};