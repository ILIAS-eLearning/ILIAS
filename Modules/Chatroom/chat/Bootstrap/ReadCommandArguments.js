var Container	= require('../AppContainer');
var Handler		= require('../Handler/CommandLineHandler');

/**
 * @param {Function} callback
 */
module.exports = function ReadCommandArguments(callback) {

	var arguments = Handler.readArguments();
	Container.setArguments(arguments);

	callback(null);
};
