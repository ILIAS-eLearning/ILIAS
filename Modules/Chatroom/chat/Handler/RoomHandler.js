var Room = require('../Model/Room');

/**
 * @param {Namespace} namespace
 * @param {Number} scope
 * @param {Number} subScope
 * @param {String} title
 * @param {Number} owner
 * @returns {Room}
 */
module.exports.createRoom = function(namespace, scope, subScope, title, owner) {
	var serverRoomId = scope + '_' + subScope;

	var room = new Room(serverRoomId);
	room.setTitle(title);
	room.setOwnerId(owner);

	namespace.addRoom(room);

	return room;
};