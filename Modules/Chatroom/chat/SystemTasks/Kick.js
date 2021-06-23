var Container = require('../AppContainer');
var Notice = require('../Model/Messages/Notice');
var KickAction = require('../Model/Messages/KickAction');
var UserlistAction = require('../Model/Messages/UserlistAction');


module.exports = function (req, res) {
	var userId = parseInt(req.params.id);
	var roomId = parseInt(req.params.roomId);
	var subRoomId = parseInt(req.params.subRoomId);
	var serverRoomId = Container.createServerRoomId(roomId, subRoomId);
	var namespace = Container.getNamespace(req.params.namespace);
	var room = namespace.getRoom(serverRoomId);
	var subscriber = room.getSubscriber(userId);

	function createKickUserCallback(namespace, action, noticeKicked, room, mainRoomUserlistAction) {
		return function createKickUser(socketId) {
			namespace.getIO().to(socketId).emit('userjustkicked', action);
			namespace.getIO().to(socketId).emit('notice', noticeKicked);
			namespace.getIO().sockets.get(socketId).leave(room.getId());

			if (mainRoomUserlistAction !== null) {
				namespace.getIO().to(socketId).emit('userlist', mainRoomUserlistAction);
			}
		};
	}

	Container.getLogger().info('Kick Subscriber %s from room %s of namespace %s', userId, serverRoomId, namespace.getName());

	if (subscriber !== null) {
		room.removeSubscriber(userId);
		room.subscriberLeft(userId);

		var action = KickAction.create(roomId, subRoomId);
		var userlistAction = UserlistAction.create(roomId, subRoomId, room.getJoinedSubscribers());
		var notice = Notice.create('user_kicked', roomId, subRoomId, {user: subscriber.getName()});
		var noticeKicked = Notice.create('kicked_from_private_room', roomId, 0, {title: room.getTitle()});

		var mainRoomUserlistAction = null;
		if (subRoomId > 0) {
			mainRoomUserlistAction = UserlistAction.create(
				roomId, 0, namespace.getRoom(Container.createServerRoomId(roomId, 0)).getJoinedSubscribers()
			);
		}

		var socketIds = subscriber.getSocketIds();

		var kickUser = createKickUserCallback(namespace, action, noticeKicked, room, mainRoomUserlistAction);

		socketIds.forEach(kickUser);

		namespace.getIO().to(serverRoomId).emit('userlist', userlistAction);
		namespace.getIO().to(serverRoomId).emit('notice', notice);
	}

	res.send({success: true});
};
