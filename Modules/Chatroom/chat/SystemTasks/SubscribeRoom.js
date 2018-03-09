var Container = require('../AppContainer');
var Subscriber = require('../Model/Subscriber');


module.exports = function(req, res)
{
	var roomId = parseInt(req.params.roomId);
	var subRoomId = parseInt(req.params.subRoomId);
	var subscriberId= parseInt(req.params.id);
	var serverRoomId = Container.createServerRoomId(roomId, subRoomId);
	var namespace = Container.getNamespace(req.params.namespace);
	var room = namespace.getRoom(serverRoomId);

	Container.getLogger().info('New Subscriber %s for room %s of namespace %s', subscriberId, serverRoomId, namespace.getName());

	if(room == null)
	{
		res.send({success: false});
		return;
	}

	var subscriber = namespace.getSubscriber(subscriberId);

	if(subscriber == null)
	{
		res.send({success: false});
		return;
	}

	room.addSubscriber(subscriber);

	res.send({
		success: true,
		subRoomId: subRoomId
	});
};
