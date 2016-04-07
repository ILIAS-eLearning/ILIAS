var Message = require('../Message');

/**
 * A enter action message
 *
 * @constructor
 */
var EnterAction = function EnterAction() {};

/**
 * @param {number} roomId
 * @param {number} subRoomId
 * @param {{id: number, username: string}} subscriber
 */
EnterAction.prototype.create = function(roomId, subRoomId, subscriber) {
	var message = Message.create('private_room_entered', 'private_room_entered', roomId, subRoomId);

	message.subscriber = subscriber;

	return message;
};

/**
 * @type {EnterAction}
 */
module.exports = new EnterAction();