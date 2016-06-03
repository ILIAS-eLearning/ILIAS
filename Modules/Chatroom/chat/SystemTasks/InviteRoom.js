var Container	= require('../AppContainer');
var Subscriber = require('../Model/Subscriber');
var Notice = require('../Model/Messages/Notice');
var InviteAction = require('../Model/Messages/InviteAction');


module.exports = function(req, res)
{
	var roomId = req.params.roomId;
	var subRoomId = req.params.subRoomId;
	var serverRoomId = Container.createServerRoomId(roomId, subRoomId);
	var mainRoomId = Container.createServerRoomId(roomId, 0);
	var namespace = Container.getNamespace(req.params.namespace);
	var room = namespace.getRoom(serverRoomId);
	var subscriber = namespace.getSubscriber(req.params.invitedId);

	Container.getLogger().info('Invite Subscriber %s to room %s of namespace %s', req.params.invitedId, serverRoomId, namespace.getName());

	if(subscriber == null)
	{
		subscriber = new Subscriber(req.params.invitedId);

		if(!namespace.hasSubscriber(subscriber.getId())){
			namespace.addSubscriber(subscriber);
		}
	}
	room.addSubscriber(subscriber);


	if(namespace.hasSubscriber(req.params.id))
	{
		var inviter = namespace.getSubscriber(req.params.id);
		var inviterSocketIds = inviter.getSocketIds();

		var noticeInviter = Notice.create('user_invited', roomId, subRoomId);
		inviterSocketIds.forEach(function(socketId){
			namespace.getIO().to(socketId).emit('notice', noticeInviter);
		});

		var invitedSocketIds = subscriber.getSocketIds();

		var action = InviteAction.create(roomId, subRoomId, room.getTitle(), room.getOwnerId());
		var noticeInvited = Notice.create('user_invited_self', roomId, -1, {user: inviter.getName(), room: room.getTitle()});

		invitedSocketIds.forEach(function(socketId){
			namespace.getIO().to(socketId).emit('user_invited', action);
			namespace.getIO().to(socketId).emit('notice', noticeInvited);
		});
	}

	res.send({
		success: true,
		subRoomId: subRoomId
	});
};