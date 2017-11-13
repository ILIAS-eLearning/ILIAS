var Container = require('../AppContainer');
var Handler = require('../Handler/NamespaceHandler');
var SetupDatabase = require('./SetupDatabase');
var PreloadData = require('./PreloadData');
var async = require('async');

module.exports = function SetupNamespaces(callback) {
	var clientConfigs = Container.getClientConfigs();

	var setup = function(config, nextLoop) {
		var setupNamespace = function(callback) {
			var namespace = Handler.createNamespace(config.name);

			Container.getLogger().info('SetupNamespace %s!', namespace.getName());

			callback(null, namespace, config);
		};

		var onEnd = function(err, result){
			if(err) {
				throw err;
			}

			nextLoop();
		};

		async.waterfall(
			[
				setupNamespace,
				SetupDatabase,
				PreloadData
			],
			onEnd
		);
	};

	var onEnd = function(err) {
		if(err) {
			throw err;
		}

		Container.getLogger().info('SetupNamespace finished!');

		callback();
	};

	async.eachSeries(clientConfigs, setup, onEnd);
};
