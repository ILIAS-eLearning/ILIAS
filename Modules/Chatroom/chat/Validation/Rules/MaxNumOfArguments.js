/**
 * @namespace	Rules
 * @class		MaxNumOfArguments
 * @constructor
 */
module.exports = function MaxNumOfArguments(max) {

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
	var _max = max;

	function isLessEqualsMax() {
		if(_max == 0) {
			return true;
		}

		return process.argv.length <= _max + CONST_NUM_COMMAND_ARGS;
	}

	this.validate = function() {
		if(!isLessEqualsMax()) {
			throw new Error('CHANGE THIS TO SPECIFIC: TO MANY ARGUMENTS');
		}
	};
};
