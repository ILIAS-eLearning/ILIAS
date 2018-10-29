var CONST 		= require('../Constants');
var Container	= require('../AppContainer');
var Handler		= require('../Handler/FileHandler');
var dns 		= require('dns');

/**
 * @param {Function} callback
 */
module.exports = function ReadServerConfig(result, callback) {
	var config = Handler.read(Container.getArgument(CONST.SERVER_CONFIG_INDEX));

	function onHostnameResolved(err, addresses, family){
		Container.getLogger().info("DNS Resolve for: %s => IP: %s , Family: %s", config.address, addresses, family);
		config.address = addresses;
	}

	dns.lookup(config.address, onHostnameResolved);

	Container.setServerConfig(config);

	callback(null);
};