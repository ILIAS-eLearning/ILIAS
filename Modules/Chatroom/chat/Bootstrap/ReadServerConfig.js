var CONST 		= require('../Constants');
var Container	= require('../AppContainer');
var Handler		= require('../Handler/FileHandler');

/**
 * @param {Function} callback
 */
module.exports = function ReadServerConfig(callback) {
	var config = Handler.read(Container.getArgument(CONST.SERVER_CONFIG_INDEX));

	Container.setServerConfig(config);

	callback();
};