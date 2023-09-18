var Message = require('../Message');

/**
 * A clear action message
 *
 * @constructor
 */
var ClearAction = function ClearAction() {};

/**
 * @param {number} roomId
 * @returns {{type: string, timestamp: number, content: string, roomId: number}}
 */
ClearAction.prototype.create = function(roomId) {
	return Message.create('clear', 'history_has_been_cleared', roomId);
};

/**
 * @type {ClearAction}
 */
module.exports = new ClearAction();
