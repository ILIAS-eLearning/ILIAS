var Container = require('../AppContainer');
var Handler = require('../Handler/NamespaceHandler');
var async = require('async');
var PreloadConversations = require('./PreloadConversations');

module.exports = function SetupIM(callback) {
	async.eachSeries(Container.getNamespaces(), function(namespace, nextLoop) {
		async.waterfall([
			function(callback) {
				var namespaceIM = Handler.createNamespace(namespace.getName() + '-im');
				namespaceIM.setIsIM(true);

				Container.getLogger().info('SetupNamespace IM: %s!', namespaceIM.getName());

				namespaceIM.setDatabase(namespace.getDatabase());

				callback(null, namespaceIM);
			},
			PreloadConversations,
		], function(err, result) {
			if(err) throw err;

			nextLoop();
		})
	}, function(err) {
		if(err) throw err;

		Container.getLogger().info('SetupNamespace IM finished!');

		callback();
	});
};