var Container = require('../AppContainer');
var Handler = require('../Handler/NamespaceHandler');
var async = require('async');
var PreloadConversations = require('./PreloadConversations');

module.exports = function SetupIM(result, callback) {

	function setupIMNamespace(namespace, nextLoop) {
		function createIMNamespace(callback) {
			var namespaceIM = Handler.createNamespace(namespace.getName() + '-im');
			namespaceIM.setIsIM(true);

			Container.getLogger().info('SetupNamespace IM: %s!', namespaceIM.getName());

			namespaceIM.setDatabase(namespace.getDatabase());

			callback(null, namespaceIM);
		}

		function onIMNamespaceSetupFinished(err, result) {
			if(err) {
				throw err;
			}

			nextLoop();
		}

		async.waterfall(
			[
				createIMNamespace,
				PreloadConversations
			],
			onIMNamespaceSetupFinished
		);
	}

	function onIMSetupFinished(err) {
		if(err) {
			throw err;
		}

		Container.getLogger().info('SetupNamespace IM finished!');

		callback();
	}

	async.eachSeries(Container.getNamespaces(), setupIMNamespace, onIMSetupFinished);
};
