var Message = require('../Message');

/**
 * A clear action message
 *
 * @constructor
 */
var UserlistAction = function UserlistAction() {};

/**
 * @param {number} roomId
 * @param {number} subRoomId
 * @param {{}} users
 * @returns {{type: string, timestamp: number, content: string, roomId: number, subRoomId: number, users: {}}}
 */
UserlistAction.prototype.create = function(roomId, subRoomId, users) {
	var message = Message.create('userlist', 'userlist', roomId, subRoomId);

	message.users = users;

	return message;
};

/**
 * @type {UserlistAction}
 */
module.exports = new UserlistAction();