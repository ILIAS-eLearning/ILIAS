var Container		= require('../AppContainer');
var TextMessage		= require('../Model/Messages/TextMessage');
var TargetMessage 	= require('../Model/Messages/TargetMessage');
var AccessHandler	= require('../Handler/AccessHandler');
var HTMLEscape		= require('../Helper/HTMLEscape');

module.exports = function(data, roomId, subRoomId)
{
	/*if(!AccessHandler.canAccessRoom(this, this.subscriber.getId(), room)) {
		AccessHandler.disconnect(this);
		return;
	}*/


	var serverRoomId = Container.createServerRoomId(roomId, subRoomId);
	var namespace = Container.getNamespace(this.nsp.name);
	var subscriber = { id: this.subscriber.getId(),	username: this.subscriber.getName()	};

	Container.getLogger().info('Message send to room %s of namespace %s', serverRoomId, namespace.getName());

	data.content = HTMLEscape.escape(data.content);
	var message = {};
	if(data.target != undefined) {
		message = TargetMessage.create(data.content, roomId, subRoomId, subscriber, data.format, data.target);

		if(message.target.public) {
			namespace.getIO().in(serverRoomId).emit('message', message);
		} else {
			var target = namespace.getSubscriber(message.target.id);
			var from = namespace.getSubscriber(message.from.id);

			from.getSocketIds().forEach(function(socketId){
				namespace.getIO().to(socketId).emit('message', message);
			});
			target.getSocketIds().forEach(function(socketId){
				namespace.getIO().to(socketId).emit('message', message);
			});
		}
	} else {
		message = TextMessage.create(data.content, roomId, subRoomId, subscriber, data.format);
		this.nsp.in(serverRoomId).emit('message', message);
	}

	namespace.getDatabase().persistMessage(message);
};