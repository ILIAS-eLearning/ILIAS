var Message = require('../Message');

/**
 * A create action message
 *
 * @constructor
 */
var CreateAction = function CreateAction() {};

/**
 * @param {number} roomId
 * @param {string} title
 * @param {number} ownerId
 *
 * @return {{type: string, timestamp: number, content: string, roomId: number, title: string, ownerId: number}}
 */
CreateAction.prototype.create = function(roomId, title, ownerId) {
	var message = Message.create('private_room_created', 'private_room_created', roomId);

	message.title = title;
	message.ownerId = ownerId;

	return message;
};

/**
 * @type {CreateAction}
 */
module.exports = new CreateAction();
