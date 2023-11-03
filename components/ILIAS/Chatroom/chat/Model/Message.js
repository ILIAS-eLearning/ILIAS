var Date = require('../Helper/Date');

/**
 * A basic message object
 *
 * @constructor
 */
var Message = function Message() {};

/**
 * Creates a JSON message object.
 *
 * @param {string} type
 * @param {string} content
 * @param {number} roomId
 *
 * @returns {{type: string, timestamp: number, content: string, roomId: number}}
 */
Message.prototype.create = function(type, content, roomId) {
	return {
		type: type,
		timestamp: Date.getTimestamp(),
		content: content,
		roomId: roomId,
	};
};

/**
 * @type {Message}
 */
module.exports = new Message();
