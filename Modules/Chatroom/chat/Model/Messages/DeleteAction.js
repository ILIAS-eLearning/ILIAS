var Message = require('../Message');

/**
 * A delete action message
 *
 * @constructor
 */
var DeleteAction = function DeleteAction() {};

/**
 * @param {number} roomId
 * @param {number} subRoomId
 *
 * @return {{type: string, timestamp: number, content: string, roomId: number, subRoomId: number}}
 */
DeleteAction.prototype.create = function(roomId, subRoomId) {
	return Message.create('private_room_deleted', 'private_room_deleted', roomId, subRoomId);
};

/**
 * @type {DeleteAction}
 */
module.exports = new DeleteAction();