var Container = require('../AppContainer');
var Conversation = require('../Model/Conversation');

module.exports = function(conversationId, userId, name) {
	var namespace = Container.getNamespace(this.nsp.name);
	var conversation = namespace.getConversations().getById(conversationId);

	var participant = namespace.getSubscriberWithOfflines(userId, name);
	conversation.removeParticipant(participant);
	participant.leave(conversation.id);


	Container.getLogger().info('Participant %s left group conversation %s', participant.getName(), conversation.getId());

	namespace.getDatabase().updateConversation(conversation);
	this.participant.emit('removeUser', conversation.json());
	conversation.emit('conversation', conversation.json());
};