var Container = require('../AppContainer');

module.exports = function(conversationId, userId, message) {

	Container.getLogger().info('SendMessage');
	Container.getLogger().info(conversationId);
	Container.getLogger().info(userId);
	Container.getLogger().info(message);

	var conversation = Container.getNamespace(this.nsp.name).getConversations().getById(conversationId);
	var participants = conversation.getParticipants();
	for(var key in participants) {
		participants[key].send({
			conversationId: conversationId,
			userId: userId,
			message: message,
			timestamp: (new Date).getTime()
		});
	}
};