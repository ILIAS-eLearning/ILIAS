var Room = require('../Model/Room');

/**
 * @param {Namespace} namespace
 * @param {Number} scope
 * @param {String} title
 * @param {Number} owner
 * @returns {Room}
 */
module.exports.createRoom = function(namespace, scope, title, owner) {
	var serverRoomId = scope + '_0';

	var room = new Room(serverRoomId);
	room.setTitle(title);
	room.setOwnerId(owner);

	namespace.addRoom(room);

	return room;
};
