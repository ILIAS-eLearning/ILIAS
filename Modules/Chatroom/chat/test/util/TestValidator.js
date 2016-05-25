var AbstractValidator = require('../../Validation/Validator/AbstractValidator');

var TestValidator = function(){
	this.name = "TestValidator";
};
TestValidator.prototype = new AbstractValidator();

module.exports = TestValidator;