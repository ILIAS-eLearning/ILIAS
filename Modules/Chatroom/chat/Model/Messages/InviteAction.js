var Message = require('../Message');

/**
 * A invite action message
 *
 * @constructor
 */
var InviteAction = function InviteAction() {};

/**
 * @param {number} roomId
 * @param {string} title
 * @param {number} owner
 *
 * @returns {{type: string, timestamp: number, content: string, roomId: number, title: string, owner: number}}
 */
InviteAction.prototype.create = function(roomId, title, owner) {
	var message = Message.create('user_invited', 'user_invited', roomId);

	message.title = title;
	message.owner = owner;

	return message;
};

/**
 * @type {InviteAction}
 */
module.exports = new InviteAction();
