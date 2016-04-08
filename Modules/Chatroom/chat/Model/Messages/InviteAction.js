var Message = require('../Message');

/**
 * A invite action message
 *
 * @constructor
 */
var InviteAction = function InviteAction() {};

/**
 * @param {number} roomId
 * @param {number} subRoomId
 * @param {string} title
 * @param {number} owner
 *
 * @returns {{type: string, timestamp: number, content: string, roomId: number, subRoomId: number, title: string, owner: number}}
 */
InviteAction.prototype.create = function(roomId, subRoomId, title, owner) {
	var message = Message.create('user_invited', 'user_invited', roomId, subRoomId);

	message.title = title;
	message.owner = owner;

	return message;
};

/**
 * @type {InviteAction}
 */
module.exports = new InviteAction();