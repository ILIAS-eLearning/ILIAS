var Container = require('../AppContainer');

function AccessHandler() {};

/**
 * @param {Socket} socket
 * @param {number} subscriberId
 * @returns {boolean}
 */
AccessHandler.prototype.canAccessNamespace = function(socket, subscriberId) {
	/*if(!socket.nsp.hasSubscriber(subscriberId)) {
		console.log("access denied for user " + subscriberId + " cause of no permission to Namespace " + socket.nsp.name);

		return false;
	}*/

	return true;
};

/**
 *
 * @param {Socket} socket
 * @param {number} subscriberId
 * @param {number} roomId
 * @returns {boolean}
 */
AccessHandler.prototype.canAccessRoom = function(socket, subscriberId, roomId) {
/*	if(!this.canAccessNamespace(socket, subscriberId)) {
		return false;
	}

	var subscriber = socket.nsp.getSubscriber(subscriberId);

	if(!subscriber.hasRoom(roomId)) {
		console.log("access denied for user " + subscriberId + " cause of no permission to room " + roomId);

		return false
	}*/
	return true;
};

/**
 * @param {Socket} socket
 */
AccessHandler.prototype.disconnect = function(socket) {
	Container.getLogger().debug('Disconnected socket %s', socket.id);
	socket.disconnect();
};

module.exports = exports = new AccessHandler();