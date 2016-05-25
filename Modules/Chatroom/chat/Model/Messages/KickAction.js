var Message = require('../Message');

/**
 * A kick action message
 *
 * @constructor
 */
var KickAction = function KickAction() {};

/**
 * @param {number} roomId
 * @param {number} subRoomId
 */
KickAction.prototype.create = function(roomId, subRoomId) {
	return Message.create('userjustkicked', 'userjustkicked', roomId, subRoomId);
};

/**
 * @type {KickAction}
 */
module.exports = new KickAction();