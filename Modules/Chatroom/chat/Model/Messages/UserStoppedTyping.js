const Message = require('../Message');

/**
 * A UserStoppedTyping message
 *
 * @constructor
 */
const UserStoppedTyping = function UserStoppedTyping() {};

/**
 * @param {number} roomId
 * @param {number} subRoomId
 * @param {{id: number, username: string}} subscriber
 */
UserStoppedTyping.prototype.create = function(roomId, subRoomId, subscriber) {
	const message = Message.create('user_stopped_typing', 'user_stopped_typing', roomId, subRoomId);

	message.subscriber = subscriber;

	return message;
};

/**
 * @type {UserStoppedTyping}
 */
module.exports = new UserStoppedTyping();