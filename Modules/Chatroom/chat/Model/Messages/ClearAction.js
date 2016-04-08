var Message = require('../Message');

/**
 * A clear action message
 *
 * @constructor
 */
var ClearAction = function ClearAction() {};

/**
 * @param {number} roomId
 * @param {number} subRoomId
 * @returns {{type: string, timestamp: number, content: string, roomId: number, subRoomId: number}}
 */
ClearAction.prototype.create = function(roomId, subRoomId) {
	return Message.create('clear', 'history_has_been_cleared', roomId, subRoomId);
};

/**
 * @type {ClearAction}
 */
module.exports = new ClearAction();