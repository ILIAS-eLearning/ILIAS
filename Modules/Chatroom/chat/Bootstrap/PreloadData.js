var async = require('async');
var Container = require('../AppContainer');
var RoomHandler = require('../Handler/RoomHandler');

/**
 * @param {Namespace} namespace
 * @param {Function} callback
 */
module.exports = function PreloadData(namespace, callback) {

	var loadScopes = function(callback) {
		var onScopeFetched = function(row) {
			RoomHandler.createRoom(namespace, row.room_id, 0, "Main", null);
		};

		namespace.getDatabase().loadScopes(onScopeFetched, callback);
	};

	var loadSubScopes = function(callback) {
		var onSubScopeFetched = function(row){
			RoomHandler.createRoom(namespace, row.parent_id, row.proom_id, row.title, row.owner);
		};

		namespace.getDatabase().loadSubScopes(onSubScopeFetched, callback);
	};

	var onEnd = function(err) {
		if(err) {
			throw err;
		}

		Container.getLogger().info('Preload Data for %s finished!', namespace.getName());

		callback(null, namespace);
	};

	async.series(
		[
			loadScopes,
			loadSubScopes
		],
		onEnd
	);
};