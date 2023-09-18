var Message = require('../Message');

/**
 * A notice message object
 *
 * @constructor
 */
var Notice = function Notice() {};

/**
 * @param {string} content
 * @param {number} roomId
 * @param {{}} [data]
 * @returns {{type: string, timestamp: number, content: string, roomId: number}}
 */
Notice.prototype.create = function(content, roomId, data) {
	var message = Message.create('notice', content, roomId);

	message.data = data;

	return message;
};

/**
 * @type {Notice}
 */
module.exports = new Notice();
