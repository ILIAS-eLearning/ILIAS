var Container = require('../AppContainer');
var constants = require('constants');
var SocketIO = require('socket.io');
var async = require('async');
var SocketHandler = require('../Handler/SocketHandler');
var FileHandler	= require('../Handler/FileHandler');

module.exports = function SetupServer(callback) {
	var serverConfig = Container.getServerConfig();
	var options = _generateOptions(serverConfig);
	var protocol = require(serverConfig.protocol);
	var server = null;

	if(serverConfig.protocol == 'https') {
		server = protocol.createServer(options, Container.getApi());
	} else {
		server = protocol.createServer(Container.getApi());
	}
	var io = SocketIO(server);

	Container.setServer(server);

	async.eachSeries(Container.getNamespaces(), function(namespace, next){
		namespace.setIO(io.of(namespace.getName()));
		namespace.getIO().on('connect', SocketHandler);

		next();
	}, function(err) {
		if(err) throw err;

		callback();
	});
};


function _generateOptions(config) {
	var options = {
		host: Container.getServerConfig().address
	};

	if(config.protocol == 'https') {
		options.cert = FileHandler.readPlain(config.cert);
		options.key = FileHandler.readPlain(config.key);
		options.dhparam = FileHandler.readPlain(config.dhparam);
		options.ciphers = [
			"ECDHE-RSA-AES256-SHA384",
			"DHE-RSA-AES256-SHA384",
			"ECDHE-RSA-AES256-SHA256",
			"DHE-RSA-AES256-SHA256",
			"ECDHE-RSA-AES128-SHA256",
			"DHE-RSA-AES128-SHA256",
			"HIGH",
			"!aNULL",
			"!eNULL",
			"!EXPORT",
			"!DES",
			"!3DES",
			"!RC4",
			"!MD5",
			"!PSK",
			"!SRP",
			"!CAMELLIA"
		].join(':');
		//options.honorCipherOrder = true;
		options.secureProtocol = 'SSLv23_method';
		options.secureOptions = constants.SSL_OP_NO_SSLv3;
	}

	return options;
}