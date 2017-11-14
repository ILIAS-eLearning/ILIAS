var Container = require('../AppContainer');
var Handler = require('../Handler/NamespaceHandler');
var async = require('async');
var PreloadConversations = require('./PreloadConversations');

module.exports = function SetupIM(callback) {
	var iterator = function(namespace, nextLoop) {
		var setupNamespace = function(callback) {
			var namespaceIM = Handler.createNamespace(namespace.getName() + '-im');
			namespaceIM.setIsIM(true);

			Container.getLogger().info('SetupNamespace IM: %s!', namespaceIM.getName());

			namespaceIM.setDatabase(namespace.getDatabase());

			callback(null, namespaceIM);
		};

		var onEnd = function(err, result) {
			if(err) {
				throw err;
			}

			nextLoop();
		};

		async.waterfall(
			[
				setupNamespace,
				PreloadConversations,
			],
			onEnd
		);
	};

	var onSetupFinished = function(err) {
		if(err) {
			throw err;
		}

		Container.getLogger().info('SetupNamespace IM finished!');

		callback();
	};

	async.eachSeries(Container.getNamespaces(), iterator, onSetupFinished);
};
