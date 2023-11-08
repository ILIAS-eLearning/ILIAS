var CONST	= require('../Constants');
var Container = require('../AppContainer');
var Handler = require('../Handler/FileHandler');


/**
 * @param {Function} callback
 */
module.exports = function ReadClientConfigs(result, callback) {
	var length = Container.getArguments().length;

	for (var index = CONST.CLIENT_CONFIG_INDEX; index < length; index++) {
		var config = Handler.read(Container.getArgument(index));
		Container.addClientConfig(config);
	}

	callback(null);
};