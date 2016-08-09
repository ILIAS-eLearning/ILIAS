var Container = require('../AppContainer');

module.exports = function(conversationId, userId, message) {
	Container.getLogger().info('SendMessage');
	Container.getLogger().info(conversationId);
	Container.getLogger().info(userId);
	Container.getLogger().info(message);

	var namespace = Container.getNamespace(this.nsp.name);
	var conversation = namespace.getConversations().getById(conversationId);
	var participants = conversation.getParticipants();

	for(var key in participants) {
		var subscriber = namespace.getSubscriber(participants[key]);
		if(subscriber != null) {
			subscriber.send({
				conversationId: conversationId,
				userId: userId,
				message: message,
				timestamp: (new Date).getTime()
			});
		}

	}
};