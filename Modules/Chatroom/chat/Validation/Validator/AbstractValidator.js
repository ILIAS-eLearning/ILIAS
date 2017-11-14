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

	this.validate = function validate() {
		var validateRule = function validateRule(rule){
			rule.validate();
		};

		_rules.forEach(validateRule);
	};

	/**
	 * @param rule
	 */
	AbstractValidator.prototype.addRule = function addRule(rule) { _rules.push(rule); };

	/**
	 * @returns {Array}
	 */
	AbstractValidator.prototype.getRules = function getRules() { return _rules; };
};

module.exports = AbstractValidator;

