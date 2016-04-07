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
 * @param {number} subRoomId
 * @param {{}} [data]
 * @returns {{type: string, timestamp: number, content: string, roomId: number, subRoomId: number}}
 */
Notice.prototype.create = function(content, roomId, subRoomId, data) {
	var message = Message.create('notice', content, roomId, subRoomId);

	message.data = data;

	return message;
};

/**
 * @type {Notice}
 */
module.exports = new Notice();