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
 * @param {number} subRoomId
 *
 * @returns {{type: string, timestamp: number, content: string, roomId: number, subRoomId: number}}
 */
Message.prototype.create = function(type, content, roomId, subRoomId) {
	return {
		type: type,
		timestamp: Date.getTimestamp(),
		content: content,
		roomId: roomId,
		subRoomId: subRoomId
	};
};

/**
 * @type {Message}
 */
module.exports = new Message();
