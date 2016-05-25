var assert					= require('assert'),
	sinon					= require('sinon'),
	MaxNumOfArguments 		= require('../Validation/Rules/MaxNumOfArguments'),
	MinNumOfArguments 		= require('../Validation/Rules/MinNumOfArguments');


describe('Rules', function() {

	var backupProcessArgv = null;

	before(function(done){
		backupProcessArgv = process.argv;
		done();
	});

	beforeEach(function(done){
		process.argv = process.argv.slice(0,2);
		done();
	});

	after(function(done){
		process.argv = backupProcessArgv;
		done();
	});

	describe('MaxNumOfArguments', function() {
		it('should be valid max = 0', function(done) {
			var rule = new MaxNumOfArguments(0);

			assert.doesNotThrow(rule.validate, Error);
			done();
		});

		it('should be valid if number of arguments < max', function(done){
			process.argv.push("Argument1");
			process.argv.push("Argument2");
			var rule = new MaxNumOfArguments(3);

			assert.doesNotThrow(rule.validate, Error);

			done();
		});

		it('should be valid if number of arguments = max', function(done){
			process.argv.push("Argument1");
			process.argv.push("Argument2");
			process.argv.push("Argument3");

			var rule = new MaxNumOfArguments(3);

			assert.doesNotThrow(rule.validate, Error);
			done();
		});

		it('should throw error if number argument is > max', function(done){
			process.argv.push("Argument1");
			process.argv.push("Argument2");
			var rule = new MaxNumOfArguments(1);

			assert.throws(rule.validate, Error);

			done();
		});
	});

	describe('MinNumOfArguments', function() {
		it('should be valid if number arguments = min', function(done) {
			process.argv.push("Argument1");
			process.argv.push("Argument2");

			var rule = new MinNumOfArguments(2);

			assert.doesNotThrow(rule.validate, Error);
			done();
		});

		it('should be valid if number arguments > min', function(done) {
			process.argv.push("Argument1");
			process.argv.push("Argument2");
			process.argv.push("Argument3");

			var rule = new MinNumOfArguments(2);

			assert.doesNotThrow(rule.validate, Error);
			done();
		});

		it('should throw error if number arguments < min', function(done) {
			process.argv.push("Argument1");

			var rule = new MinNumOfArguments(2);

			assert.throws(rule.validate, Error);
			done();
		});

		it('should throw error with message if delivered for missing arguments', function(done){
			process.argv.push("Argument1");

			var rule = new MinNumOfArguments(2, "ERR_MSG_1_ARG", "ERR_MSG_2_ARG");

			(rule.validate).should.throw("ERR_MSG_2_ARG");

			done();
		});

		it('should throw error with general message if no message is delivered', function(done){
			process.argv.push("Argument1");

			var rule = new MinNumOfArguments(2);

			(rule.validate).should.throw("ERR_MSG_GENERAL");

			done();
		});
	});
});

