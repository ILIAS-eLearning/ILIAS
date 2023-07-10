var Container	= require('../AppContainer');
var Room = require('../Model/Room');
var CreateAction = require('../Model/Messages/CreateAction');


module.exports = function(req, res)
{
	var roomId = parseInt(req.params.roomId);
	var namespace = Container.getNamespace(req.params.namespace);
	var title = req.params.title;
	var ownerId = req.params.id;
	var serverRoomId = Container.createServerRoomId(roomId);

	Container.getLogger().info('Create new room %s of namespace %s', serverRoomId, namespace.getName());

	//@TODO Check if namespace is Accessable
	var room = new Room(serverRoomId);
	room.setTitle(title);
	room.setOwnerId(ownerId);

	namespace.addRoom(room);

	res.send({
		success: true,
	});
};
