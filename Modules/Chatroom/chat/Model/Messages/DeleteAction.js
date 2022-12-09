var Message = require('../Message');

/**
 * A delete action message
 *
 * @constructor
 */
var DeleteAction = function DeleteAction() {};

/**
 * @param {number} roomId
 *
 * @return {{type: string, timestamp: number, content: string, roomId: number}}
 */
DeleteAction.prototype.create = function(roomId) {
	return Message.create('private_room_deleted', 'private_room_deleted', roomId);
};

/**
 * @type {DeleteAction}
 */
module.exports = new DeleteAction();
