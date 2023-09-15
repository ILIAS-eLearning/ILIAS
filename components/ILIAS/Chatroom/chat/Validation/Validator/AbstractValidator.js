/**
 * @namespace	Validator
 * @class		AbstractValidator
 * @constructor
 */
var AbstractValidator = function(){
	/**
	 * @type {Array}
	 * @private
	 */
	var _rules = [];

	function validateRule(rule){
		rule.validate();
	}

	this.validate = function() {
		_rules.forEach(validateRule);
	};

	/**
	 * @param rule
	 */
	AbstractValidator.prototype.addRule = function(rule) { _rules.push(rule); };

	/**
	 * @returns {Array}
	 */
	AbstractValidator.prototype.getRules = function() { return _rules; };
};

module.exports = AbstractValidator;
