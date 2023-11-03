var Container = require('../AppContainer');
var UserlistAction = require('../Model/Messages/UserlistAction');
var Notice = require('../Model/Messages/Notice');

module.exports = function()
{
	if(this.subscriber === undefined)
	{
		Container.getLogger().info('Close socket with no subscriber');
		this.disconnect();
		return;
	}

	var subscriber = this.subscriber;
	var subscriberId = subscriber.getId();
	var namespace = Container.getNamespace(this.nsp.name);

	Container.getLogger().info('Subscriber %s left namespace %s', this.subscriber.getId(), namespace.getName());

	function subscriberLeftNamespaceHandler() {
		if(namespace.hasSubscriber(subscriberId)) {
			var rooms = namespace.getRooms();

			var roomIds = [];
			Object.values(rooms).forEach(function(room){
				if(!room.hasSubscriber(subscriberId)) {
					return;
				}
				room.subscriberLeft(subscriberId);
				room.removeSubscriber(subscriberId);
				var splitIds = Container.splitServerRoomId(room.getId());

				if(roomIds.indexOf(splitIds[0]) < 0)
				{
					roomIds.push(splitIds[0]);
				}

				var userListAction = UserlistAction.create(splitIds[0], room.getJoinedSubscribers());
				var notice = Notice.create('disconnected', splitIds[0], {username: subscriber.getName()});

				namespace.getDatabase().addHistory(notice);

				Container.getLogger().info('Disconnected %s from %s of namespace %s', subscriberId, room.getId(), namespace.getName());
				Container.getLogger().info('Updated user list for room %s of namespace %s', room.getId(), namespace.getName());

				namespace.getIO().in(room.getId()).emit('userlist', userListAction);
				namespace.getIO().in(room.getId()).emit('notice', notice);
			});
			if(subscriber.getSocketIds().length <= 0)
			{
				namespace.removeSubscriber(subscriberId);
			}
			namespace.getDatabase().disconnectUser(subscriber, roomIds);

		}

		Container.removeTimeout(subscriberId);
	}

	Container.setTimeout(subscriberId, subscriberLeftNamespaceHandler, 15000);

	this.subscriber.removeSocketId(this.id);
};
