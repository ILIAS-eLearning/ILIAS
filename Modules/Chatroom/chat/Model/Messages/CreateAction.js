var Message = require('../Message');

/**
 * A create action message
 *
 * @constructor
 */
var CreateAction = function CreateAction() {};

/**
 * @param {number} roomId
 * @param {number} subRoomId
 * @param {string} title
 * @param {number} ownerId
 *
 * @return {{type: string, timestamp: number, content: string, roomId: number, subRoomId: number, title: string, ownerId: number}}
 */
CreateAction.prototype.create = function(roomId, subRoomId, title, ownerId) {
	var message = Message.create('private_room_created', 'private_room_created', roomId, subRoomId);

	message.title = title;
	message.ownerId = ownerId;

	return message;
};

/**
 * @type {CreateAction}
 */
module.exports = new CreateAction();