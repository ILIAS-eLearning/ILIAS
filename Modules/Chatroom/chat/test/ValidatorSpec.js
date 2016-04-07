var should						= require('should'),
	assert						= require('assert'),
	sinon						= require('sinon'),
	AbstractValidator			= require('../Validation/Validator/AbstractValidator'),
	FileHandlerReadValidator	= require('../Validation/Validator/FileHandlerReadValidator');


describe('RuleSet', function(){

	describe('AbstractRuleSet', function(){

		var TestValidator = null;

		before(function(done){
			TestValidator = function() {};
			TestValidator.prototype = new AbstractValidator();

			done();
		});


		it('should always have the correct amount of rules', function(done){
			var validator = new TestValidator();

			var rule = sinon.stub();

			assert.equal(validator.getRules().length, 0);

			validator.addRule(rule);
			assert.equal(validator.getRules().length, 1);

			validator.addRule(rule);
			assert.equal(validator.getRules().length, 2);

			done();
		});
	});
});