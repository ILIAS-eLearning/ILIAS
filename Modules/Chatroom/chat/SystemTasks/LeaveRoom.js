var Container	= require('../AppContainer');
var Notice = require('../Model/Messages/Notice');
var UserlistAction = require('../Model/Messages/UserlistAction');

module.exports = function(req, res)
{
	var roomId = parseInt(req.params.roomId);
	var subRoomId = parseInt(req.params.subRoomId);
	var serverRoomId = Container.createServerRoomId(roomId, subRoomId);
	var subscriberId = req.params.id;
	var namespace = Container.getNamespace(req.params.namespace);
	var room = namespace.getRoom(serverRoomId);
	var mainRoom = namespace.getRoom(Container.createServerRoomId(roomId, 0));
	var subscriber = room.getSubscriber(subscriberId);

	Container.getLogger().info('Subscriber %s left room %s of namespace %s', subscriberId, serverRoomId, namespace.getName());

	room.subscriberLeft(subscriber.getId());

	var notice = Notice.create('left', roomId, 0); // Send this notification to the main room
	var userlistLeftAction = UserlistAction.create(roomId, subRoomId, room.getJoinedSubscribers());
	var userlistMainAction = UserlistAction.create(roomId, 0, mainRoom.getJoinedSubscribers());

	function createLeaveRoomCallback(namespace, room, notice, userlistMainAction) {
		return function createLeaveRoom(socketId){
			namespace.getIO().sockets.get(socketId).leave(room.getId());
			namespace.getIO().in(socketId).emit('notice', notice);
			namespace.getIO().in(socketId).emit('userlist', userlistMainAction);
		};
	}

	var leaveRoomCallback = createLeaveRoomCallback(namespace, room, notice, userlistMainAction);
	subscriber.getSocketIds().forEach(leaveRoomCallback);

	notice = Notice.create('private_room_left', roomId, subRoomId, {user: subscriber.getName(),title: room.getTitle()});
	namespace.getIO().in(serverRoomId).emit('userlist', userlistLeftAction);
	namespace.getIO().in(serverRoomId).emit('notice', notice);

	res.send({
		success: true,
		subRoomId: subRoomId
	});
};
