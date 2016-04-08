var should 						= require('should'),
	sinon						= require('sinon'),
	assert						= require('assert'),
	fs							= require('fs'),
	FileHandler					= require('../Handler/FileHandler'),
	FileHandlerReadValidator	= require('../Validation/Validator/FileHandlerReadValidator'),
	TestValidator				= require('./util/TestValidator');


var EXISTING_FILE = 'path/to/file';
var NON_EXISTING_FILE = 'wrong/path/to/file';

describe('FileHandler', function(){
	describe('#read', function(){

		it('should return file object if validation does not fail', function(done){
			var testValidator = new TestValidator();
			var testValidatorMock = sinon.mock(testValidator);
			testValidatorMock.expects('validate').once().returns(true);

			var validatorMock = sinon.mock(FileHandlerReadValidator);
			validatorMock.expects('create').once().returns(testValidator);

			var fsMock = sinon.mock(fs);
			fsMock.expects('readFileSync').withExactArgs(EXISTING_FILE).once().returns('File Content');

			var handler = new FileHandler();
			var file = handler.read(EXISTING_FILE);

			assert.equal(file.getContent(), "File Content");
			validatorMock.verify();
			testValidatorMock.verify();

			validatorMock.restore();
			testValidatorMock.restore();
			done();
		});

		it('should throw an error if validation failed', function(done){
			var testValidator = new TestValidator();
			var testValidatorMock = sinon.mock(testValidator);
			testValidatorMock.expects('validate').once().throws();

			var validatorMock = sinon.mock(FileHandlerReadValidator);
			validatorMock.expects('create').once().returns(testValidator);

			var handler = new FileHandler();

			assert.throws(function(){ handler.read(NON_EXISTING_FILE); }, Error);
			validatorMock.verify();
			testValidatorMock.verify();

			validatorMock.restore();
			testValidatorMock.restore();

			done();
		})
	});
});