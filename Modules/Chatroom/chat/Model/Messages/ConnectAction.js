var Message = require('../Message');

/**
 * A connect action message
 *
 * @constructor
 */
var ConnectAction = function ConnectAction() {};

/**
 * @param {number} roomId
 * @param {{id: number, username: string}} subscriber
 * @returns {{type: string, timestamp: number, content: string, roomId: number, subscriber}}
 */
ConnectAction.prototype.create = function(roomId, subscriber) {
	var message = Message.create('connected', 'connected', roomId);

	message.user = subscriber;

	return message;
};

/**
 * @type {ConnectAction}
 */
module.exports = new ConnectAction();
