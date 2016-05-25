var Container = require('../AppContainer');
var Handler = require('../Handler/NamespaceHandler');
var SetupDatabase = require('./SetupDatabase');
var PreloadData = require('./PreloadData');
var async = require('async');

module.exports = function SetupNamespaces(callback) {
	var clientConfigs = Container.getClientConfigs();

	async.eachSeries(clientConfigs, function(config, nextLoop) {
		async.waterfall([
			function(callback) {
				var namespace = Handler.createNamespace(config.name);

				Container.getLogger().info('SetupNamespace %s!', namespace.getName());

				callback(null, namespace, config);
			},
			SetupDatabase,
			PreloadData
		], function(err, result){
			if(err) throw err;

			nextLoop();
		});
	}, function(err) {
		if(err) throw err;

		Container.getLogger().info('SetupNamespace finished!');

		callback();
	});
};