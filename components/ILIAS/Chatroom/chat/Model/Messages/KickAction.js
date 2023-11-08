var Message = require('../Message');

/**
 * A kick action message
 *
 * @constructor
 */
var KickAction = function KickAction() {};

/**
 * @param {number} roomId
 */
KickAction.prototype.create = function(roomId) {
	return Message.create('userjustkicked', 'userjustkicked', roomId);
};

/**
 * @type {KickAction}
 */
module.exports = new KickAction();
