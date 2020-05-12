var Container = require('../AppContainer');
var TextMessage = require('../Model/Messages/TextMessage');
var TargetMessage = require('../Model/Messages/TargetMessage');
var AccessHandler = require('../Handler/AccessHandler');
var HTMLEscape = require('../Helper/HTMLEscape');

module.exports = function (data, roomId, subRoomId) {
	var serverRoomId = Container.createServerRoomId(roomId, subRoomId);
	var namespace = Container.getNamespace(this.nsp.name);

	function messageCallbackFactory(message) {
		return function messageCallback(socketId) {
			namespace.getIO().to(socketId).emit('message', message);
		};
	}

	Container.getLogger().info('Message send to room %s of namespace %s', serverRoomId, namespace.getName());
	if (typeof this.subscriber === "undefined") {
		Container.getLogger().error("Missing subscriber, don't process message");
		return;
	}

	var subscriber = {id: this.subscriber.getId(), username: this.subscriber.getName()};

	data.content = HTMLEscape.escape(data.content);

	var message = {};

	if (data.target !== undefined) {
		message = TargetMessage.create(data.content, roomId, subRoomId, subscriber, data.format, data.target);

		if (message.target.public) {
			namespace.getIO().in(serverRoomId).emit('message', message);
		} else {
			var target = namespace.getSubscriber(message.target.id);
			var from = namespace.getSubscriber(message.from.id);

			var emitMessageCallback = messageCallbackFactory(message);

			from.getSocketIds().forEach(emitMessageCallback);
			target.getSocketIds().forEach(emitMessageCallback);
		}
	} else {
		message = TextMessage.create(data.content, roomId, subRoomId, subscriber, data.format);
		this.nsp.in(serverRoomId).emit('message', message);
	}

	namespace.getDatabase().persistMessage(message);
};
