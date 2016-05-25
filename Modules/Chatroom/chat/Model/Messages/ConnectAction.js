var Message = require('../Message');

/**
 * A connect action message
 *
 * @constructor
 */
var ConnectAction = function ConnectAction() {};

/**
 * @param {number} roomId
 * @param {number} subRoomId
 * @param {{id: number, username: string}} subscriber
 * @returns {{type: string, timestamp: number, content: string, roomId: number, subRoomId: number, subscriber}}
 */
ConnectAction.prototype.create = function(roomId, subRoomId, subscriber) {
	var message = Message.create('connected', 'connected', roomId, subRoomId);

	message.user = subscriber;

	return message;
};

/**
 * @type {ConnectAction}
 */
module.exports = new ConnectAction();