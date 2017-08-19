var Container = require('../AppContainer');
var UserlistAction = require('../Model/Messages/UserlistAction');
var Notice = require('../Model/Messages/Notice');

module.exports = function()
{
	if(this.subscriber == undefined)
	{
		Container.getLogger().info('Close socket with no subscriber');
		this.disconnect();
		return;
	}

	var subscriber = this.subscriber;
	var subscriberId = subscriber.getId();
	var namespace = Container.getNamespace(this.nsp.name);

	Container.getLogger().info('Subscriber %s left namespace %s', this.subscriber.getId(), namespace.getName());

	Container.setTimeout(subscriberId, function(){
		if(namespace.hasSubscriber(subscriberId)) {
			var rooms = namespace.getRooms();

			var subRoomIds = [];
			var roomIds = [];
			for(var index in rooms) {
				if(rooms.hasOwnProperty(index)){
					var room = rooms[index];

					if(room.hasSubscriber(subscriberId)) {
						room.subscriberLeft(subscriberId);
						room.removeSubscriber(subscriberId);
						var splittedIds = Container.splitServerRoomId(room.getId());

						if(roomIds.indexOf(splittedIds[0]) < 0)
						{
							roomIds.push(splittedIds[0]);
						}

						if(splittedIds[1] != 0) {
							subRoomIds.push(splittedIds[1]);

							if(!room.hasSubscribers()) {
								namespace.getDatabase().closePrivateRoom(splittedIds[1]);
								Container.getLogger().info('Private room %s of namespace %s has been closed', room.getId(), namespace.getName());
							}
						}

						var userlistAction = UserlistAction.create(splittedIds[0], splittedIds[1], room.getJoinedSubscribers());
						var notice	= Notice.create('disconnected', splittedIds[0], splittedIds[1], {username: subscriber.getName()});

						namespace.getDatabase().addHistory(notice);

						Container.getLogger().info('Disconnected %s from %s of namespace %s', subscriberId, room.getId(), namespace.getName());
						Container.getLogger().info('Updated Userlist for room %s of namespace %s', room.getId(), namespace.getName());

						namespace.getIO().in(room.getId()).emit('userlist', userlistAction);
						namespace.getIO().in(room.getId()).emit('notice', notice);
					}
				}
			}
			if(subscriber.getSocketIds().length <= 0)
			{
				namespace.removeSubscriber(subscriberId);
			}
			namespace.getDatabase().disconnectUser(subscriber, roomIds, subRoomIds);

		}
		Container.removeTimeout(subscriberId);

	}, 5000);

	this.subscriber.removeSocketId(this.id);
};