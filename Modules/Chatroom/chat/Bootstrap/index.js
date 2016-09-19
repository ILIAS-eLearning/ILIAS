var ReadCommandArguments = require('./ReadCommandArguments');
var ReadServerConfig = require('./ReadServerConfig');
var ReadClientConfigs = require('./ReadClientConfigs');
var SetupEnvironment = require('./SetupEnvironment');
var SetupExpressApi = require('./SetupExpressApi');
var SetupNamespaces = require('./SetupNamespaces');
var SetupIM = require('./SetupIM');
var SetupExitHandler = require('./SetupExitHandler');
var SetupServer = require('./SetupServer');
var Container = require('../AppContainer');
var async = require('async');

var Bootstrap = function Bootstrap() {

	this.boot = function() {
		async.auto({
			readCommandArguments: [ ReadCommandArguments ],
			setupExpressApi: [ SetupExpressApi ],
			readServerConfig: [ 'readCommandArguments', ReadServerConfig ],
			readClientConfigs: [ 'readCommandArguments', ReadClientConfigs ],
			setupEnvironment: [ 'readCommandArguments', 'readServerConfig', SetupEnvironment ],
			setupNamespaces: [ 'readClientConfigs', SetupNamespaces ],
			setupIM: [ 'setupNamespaces', SetupIM ],
			setupExitHandler: ['setupNamespaces', SetupExitHandler],
			setupServer: [ 'setupNamespaces', 'setupIM', SetupServer ]
		}, function(err, result){
			Container.getServer().listen(Container.getServerConfig().port, Container.getServerConfig().address);
			Container.getLogger().info("The Server is Ready to use! Listening on: %s://%s:%s", Container.getServerConfig().protocol, Container.getServerConfig().address, Container.getServerConfig().port);
		});
	}
};

module.exports = new Bootstrap();