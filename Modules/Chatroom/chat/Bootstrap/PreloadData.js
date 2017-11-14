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
			RoomHandler.createRoom(namespace, row.room_id, 0, "Main", null);
		}, callback);
	}

	function loadSubScopes(callback) {
		namespace.getDatabase().loadSubScopes(function onSubScopeFetched(row){
			RoomHandler.createRoom(namespace, row.parent_id, row.proom_id, row.title, row.owner);
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
			loadSubScopes
		],
		onDataPreloadingDone
	);
};