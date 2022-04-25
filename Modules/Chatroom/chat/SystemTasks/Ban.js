var Container = require('../AppContainer');
var Notice = require('../Model/Messages/Notice');
var KickAction = require('../Model/Messages/KickAction');
var UserlistAction = require('../Model/Messages/UserlistAction');


module.exports = function exports(req, res) {
	var subscriberId = parseInt(req.params.id);
	var roomId = parseInt(req.params.roomId);
	var namespace = Container.getNamespace(req.params.namespace);
	var subscriber = namespace.getSubscriber(subscriberId);

	function userBannedMessageCallbackFactory(namespace, roomId) {
		return function userBannedMessageCallback(socketId) {
			namespace.getIO().to(socketId).emit('userjustbanned');
			namespace.getIO().sockets.get(socketId).leave(roomId);
		};
	}

	if (subscriber !== null) {
		Container.getLogger().info('Subscriber %s got banned from namespace %s', subscriberId, namespace.getName());

		var rooms = namespace.getRooms();

		for (var index in rooms) {

			if (rooms.hasOwnProperty(index)) {
				var room = rooms[index];
				var splitted = Container.splitServerRoomId(room.getId());

				if (splitted[0] == roomId && room.hasSubscriber(subscriberId)) { // Remove from Main and all subRooms
					room.removeSubscriber(subscriberId);
					room.subscriberLeft(subscriberId);

					var userlistAction = UserlistAction.create(splitted[0], splitted[1], room.getJoinedSubscribers());
					var notice = Notice.create('user_kicked', splitted[0], splitted[1], {user: subscriber.getName()});

					subscriber.getSocketIds().forEach(
						userBannedMessageCallbackFactory(namespace, room.getId())
					);

					namespace.getIO().to(room.getId()).emit('userlist', userlistAction);
					namespace.getIO().to(room.getId()).emit('notice', notice);
				}
			}
		}

		namespace.removeSubscriber(subscriberId);
	}
	res.send({success: true});
};
