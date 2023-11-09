var async = require('async');
var Container = require('../AppContainer');
var RoomHandler = require('../Handler/RoomHandler');

/**
 * @param {Namespace} namespace
 * @param {Function} callback
 */
module.exports = function PreloadData(namespace, callback) {

	function loadScopes(callback) {
		namespace.getDatabase().loadScopes(function onScopeFetched(row) {
			RoomHandler.createRoom(namespace, row.room_id, "Main", null);
		}, callback);
	}

	function onDataPreloadingDone(err) {
		if(err) {
			throw err;
		}

		Container.getLogger().info('Preload Data for %s finished!', namespace.getName());

		callback(null, namespace);
	}

	async.series(
		[
			loadScopes,
		],
		onDataPreloadingDone
	);
};
