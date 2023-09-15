var Container = require('../AppContainer');
var Handler = require('../Handler/NamespaceHandler');
var SetupDatabase = require('./SetupDatabase');
var PreloadData = require('./PreloadData');
var async = require('async');

module.exports = function SetupNamespaces(result, callback) {

	var clientConfigs = Container.getClientConfigs();

	function setupNamespace(config, nextLoop) {
		function createNamespace(callback) {
			var namespace = Handler.createNamespace(config.name);

			Container.getLogger().info('SetupNamespace %s!', namespace.getName());

			callback(null, namespace, config);
		}

		function onNamespaceSetupFinished(err, result) {
			if (err) {
				throw err;
			}

			nextLoop();
		}

		async.waterfall(
			[
				createNamespace,
				SetupDatabase,
				PreloadData
			],
			onNamespaceSetupFinished
		);
	}

	function onNamespacesSetupFinished(err) {
		if (err) {
			throw err;
		}

		Container.getLogger().info('SetupNamespace finished!');

		callback();
	}

	async.eachSeries(clientConfigs, setupNamespace, onNamespacesSetupFinished);
};
