var Container = require('../AppContainer');
var Handler = require('../Handler/NamespaceHandler');
var async = require('async');

module.exports = function SetupIM(callback) {
	async.eachSeries(Container.getNamespaces(), function(namespace, nextLoop) {

		var namespaceIM = Handler.createNamespace(namespace.getName() + '-im');
		namespaceIM.setIsIM(true);

		Container.getLogger().info('SetupNamespace IM: %s!', namespaceIM.getName());

		namespaceIM.setDatabase(namespace.getDatabase());

		nextLoop();
	}, function(err) {
		if(err) throw err;

		Container.getLogger().info('SetupNamespace IM finished!');

		callback();
	});
};