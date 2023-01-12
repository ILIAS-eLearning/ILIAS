const Message = require('../Message');

/**
 * A UserStartedTyping message
 *
 * @constructor
 */
const UserStartedTyping = function UserStartedTyping() {};

/**
 * @param {number} roomId
 * @param {{id: number, username: string}} subscriber
 */
UserStartedTyping.prototype.create = function(roomId, subscriber) {
	const message = Message.create('user_started_typing', 'user_started_typing', roomId);

	message.subscriber = subscriber;

	return message;
};

/**
 * @type {UserStartedTyping}
 */
module.exports = new UserStartedTyping();
