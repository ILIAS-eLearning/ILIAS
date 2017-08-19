var CONST 		= require('../Constants');
var Container	= require('../AppContainer');
var Handler		= require('../Handler/FileHandler');
var dns 		= require('dns');

/**
 * @param {Function} callback
 */
module.exports = function ReadServerConfig(callback) {
	var config = Handler.read(Container.getArgument(CONST.SERVER_CONFIG_INDEX));

	dns.lookup(config.address, function(err, addresses, family){
		Container.getLogger().info("DNS Resolve for: %s => IP: %s , Family: %s", config.address, addresses, family);
		config.address = addresses;
	});

	Container.setServerConfig(config);

	callback();
};