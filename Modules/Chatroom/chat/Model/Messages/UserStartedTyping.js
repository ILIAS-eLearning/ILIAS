const Message = require('../Message');

/**
 * A UserStartedTyping message
 *
 * @constructor
 */
const UserStartedTyping = function UserStartedTyping() {};

/**
 * @param {number} roomId
 * @param {number} subRoomId
 * @param {{id: number, username: string}} subscriber
 */
UserStartedTyping.prototype.create = function(roomId, subRoomId, subscriber) {
	const message = Message.create('user_started_typing', 'user_started_typing', roomId, subRoomId);

	message.subscriber = subscriber;

	return message;
};

/**
 * @type {UserStartedTyping}
 */
module.exports = new UserStartedTyping();