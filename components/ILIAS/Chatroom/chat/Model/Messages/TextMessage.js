var Message = require('../Message');

/**
 * A text message
 *
 * @constructor
 */
var TextMessage = function TextMessage() {};

/**
 * @param {string} content
 * @param {number} roomId
 * @param {{id: number, username: string}} from
 * @param {{color: string, style: string, size: string, family: string}} format
 *
 * @returns {{type: string, timestamp: number, content: string, roomId: number, from, format}}
 */
TextMessage.prototype.create = function(content, roomId, from, format) {
	var message = Message.create('message', content, roomId);

	message.from = from;
	message.format = format;

	return message;
};

/**
 * @type {TextMessage}
 */
module.exports = new TextMessage();
