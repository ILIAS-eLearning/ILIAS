var Message = require('../Message');

/**
 * A enter action message
 *
 * @constructor
 */
var EnterAction = function EnterAction() {};

/**
 * @param {number} roomId
 * @param {{id: number, username: string}} subscriber
 */
EnterAction.prototype.create = function(roomId, subscriber) {
	var message = Message.create('private_room_entered', 'private_room_entered', roomId);

	message.subscriber = subscriber;

	return message;
};

/**
 * @type {EnterAction}
 */
module.exports = new EnterAction();
