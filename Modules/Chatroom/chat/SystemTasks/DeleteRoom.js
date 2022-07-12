var Container	= require('../AppContainer');
var DeleteAction = require('../Model/Messages/DeleteAction');
var Notice = require('../Model/Messages/Notice');


module.exports = function(req, res)
{
	var roomId = req.params.roomId;
	var subRoomId = req.params.subRoomId;
	var serverRoomId = Container.createServerRoomId(roomId, subRoomId);
	var serverMainRoomId = Container.createServerRoomId(roomId, 0);
	var namespace = Container.getNamespace(req.params.namespace);

	Container.getLogger().info('Delete room %s of namespace %s', serverRoomId, namespace.getName());

	var room = namespace.getRoom(serverRoomId);

	var action = DeleteAction.create(roomId, subRoomId);
	var notice = Notice.create('private_room_closed', roomId, 0, {
		title: room.getTitle()
	});

	var subscribers = room.getSubscribers();

	function emitDeleteRoomBySocketId(socketId) {
		namespace.getIO().sockets.get(socketId).leave(room.getId());
		namespace.getIO().to(socketId).emit('private_room_deleted', action);
	}

	for (var key in subscribers) {
		if (subscribers.hasOwnProperty(key)) {
			subscribers[key].getSocketIds().forEach(emitDeleteRoomBySocketId);
		}
	}

	namespace.getIO().to(serverMainRoomId).emit('notice', notice);
	namespace.removeRoom(serverRoomId);

	res.send({
		success: true,
		subRoomId: subRoomId
	});
};
