var Container = require('../AppContainer');
var EnterAction = require('../Model/Messages/EnterAction');
var UserlistAction = require('../Model/Messages/UserlistAction');
var Notice = require('../Model/Messages/Notice');

module.exports = function(roomId, subRoomId)
{
	Container.removeTimeout(this.subscriber.getId());

	var serverRoomId = Container.createServerRoomId(roomId, subRoomId);
	var namespace = Container.getNamespace(this.nsp.name);
	var room = namespace.getRoom(serverRoomId);
	var alreadyJoined = room.subscriberHasJoined(this.subscriber.getId());
	var subscriber = {
		id: this.subscriber.getId(),
		username: this.subscriber.getName()
	};

	Container.getLogger().info("Subscriber %s enters room %s of namespace %s", this.subscriber.getId(), serverRoomId, namespace.getName());

	this.join(serverRoomId);
	room.subsciberJoined(this.subscriber.getId());

	var action = EnterAction.create(roomId, subRoomId, subscriber);
	this.emit('private_room_entered', action);
	var userlistAction = UserlistAction.create(roomId, subRoomId, room.getJoinedSubscribers());

	this.nsp.in(serverRoomId).emit('userlist', userlistAction);
	if(!alreadyJoined)
	{
		subscriber.title = room.getTitle();
		var messageRoom = (subRoomId) ? 'private_room_entered_user' : 'connect' ;
		var noticeRoom = Notice.create(messageRoom, roomId, subRoomId, subscriber);
		var messageSubscriber = (subRoomId) ? 'private_room_entered' : 'welcome_to_chat';
		var noticeSubscriber = Notice.create(messageSubscriber, roomId, subRoomId, subscriber);

		namespace.getDatabase().addHistory(noticeRoom);

		this.emit('notice', noticeSubscriber);
		this.broadcast.in(serverRoomId).emit('notice', noticeRoom);
	}
};
