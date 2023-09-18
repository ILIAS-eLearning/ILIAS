var Message = require('../Message');

/**
 * A clear action message
 *
 * @constructor
 */
var UserlistAction = function UserlistAction() {};

/**
 * @param {number} roomId
 * @param {{}} users
 * @returns {{type: string, timestamp: number, content: string, roomId: number, users: {}}}
 */
UserlistAction.prototype.create = function(roomId, users) {
	var message = Message.create('userlist', 'userlist', roomId);

	message.users = users;

	return message;
};

/**
 * @type {UserlistAction}
 */
module.exports = new UserlistAction();
