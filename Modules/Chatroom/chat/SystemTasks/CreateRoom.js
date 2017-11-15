var Container	= require('../AppContainer');
var Room = require('../Model/Room');
var CreateAction = require('../Model/Messages/CreateAction');


module.exports = function(req, res)
{
	var roomId = parseInt(req.params.roomId);
	var subRoomId = parseInt(req.params.subRoomId);
	var namespace = Container.getNamespace(req.params.namespace);
	var title = req.params.title;
	var ownerId = req.params.id;
	var serverRoomId = Container.createServerRoomId(roomId, subRoomId);

	Container.getLogger().info('Create new room %s of namespace %s', serverRoomId, namespace.getName());

	//@TODO Check if namespace is Accessable
	var room = new Room(serverRoomId);
	room.setTitle(title);
	room.setOwnerId(ownerId);

	namespace.addRoom(room);


	if(subRoomId > 0 && namespace.hasSubscriber(ownerId))
	{
		var subscriber = namespace.getSubscriber(ownerId);
		var action = CreateAction.create(roomId, subRoomId, title, ownerId);

		var socketIds = subscriber.getSocketIds();
		var emitCreateRoomAction = function(socketId){
			namespace.getIO().to(socketId).emit('private_room_created', action);
		};

		socketIds.forEach(emitCreateRoomAction);
	}

	res.send({
		success: true,
		subRoomId: subRoomId
	});
};
