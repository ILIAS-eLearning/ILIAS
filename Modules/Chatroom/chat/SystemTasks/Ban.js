var Container = require('../AppContainer');
var Notice = require('../Model/Messages/Notice');
var KickAction = require('../Model/Messages/KickAction');
var UserlistAction = require('../Model/Messages/UserlistAction');


module.exports = function(req, res)
{
	var subscriberId = parseInt(req.params.id);
	var roomId = parseInt(req.params.roomId);
	var namespace = Container.getNamespace(req.params.namespace);
	var subscriber = namespace.getSubscriber(subscriberId);

	if(subscriber != null) {
		Container.getLogger().info('Subscriber %s got banned from namespace %s', subscriberId, namespace.getName());

		var rooms = namespace.getRooms();

		for(var index in rooms) {

			if(rooms.hasOwnProperty(index)) {
				var room = rooms[index];
				var splitted = Container.splitServerRoomId(room.getId());

				if(splitted[0] == roomId && room.hasSubscriber(subscriberId)) { // Remove from Main and all subRooms
					room.removeSubscriber(subscriberId);
					room.subscriberLeft(subscriberId);

					subscriber.getSocketIds().forEach(function(socketId){
						namespace.getIO().connected[socketId].leave(room.getId());
						namespace.getIO().to(socketId).emit('userjustbanned');
					});

					var notice = Notice.create('user_kicked', splitted[0], splitted[1], {user: subscriber.getName()});
					namespace.getIO().to(room.getId()).emit('notice', notice);
				}
			}
		}

		namespace.removeSubscriber(subscriberId);
	}
	res.send({success: true});
};