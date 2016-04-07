var assert		= require('assert'),
	sinon		= require('sinon'),
	fs			= require('fs'),
	FileExists	= require('../Validation/Rules/FileExists');

var EXISTING_FILE_PATH = 'path/to/file';
var NON_EXISTING_FILE_PATH = 'wrong/path/to/file';

describe('Rules', function() {
	describe('FileExists', function() {

		before(function(done){
			done();
		});

		after(function(done){
			done();
		});

		it('should not throw error if file exists', function(done){
			var fsMock = sinon.mock(fs);
			fsMock.expects('existsSync').withExactArgs(EXISTING_FILE_PATH).once().returns(true);

			var fileExists = new FileExists(EXISTING_FILE_PATH);

			assert.doesNotThrow(fileExists.validate, Error);

			fsMock.verify();
			fsMock.restore();

			done();
		});

		it('should throw error if file does not exists', function(done){
			var fsMock = sinon.mock(fs);
			fsMock.expects('existsSync').withExactArgs(NON_EXISTING_FILE_PATH).once().returns(false);

			var fileExists = new FileExists(NON_EXISTING_FILE_PATH);

			assert.throws(fileExists.validate, Error);

			fsMock.verify();
			fsMock.restore();

			done();
		});
	});
});