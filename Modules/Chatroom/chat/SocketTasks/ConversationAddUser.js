var Container = require('../AppContainer');
var Conversation = require('../Model/Conversation');
var UUID = require('node-uuid');

module.exports = function(conversationId, userId) {
	var namespace = Container.getNamespace(this.nsp.name);
	var conversation = namespace.getConversations().getById(conversationId);

	if(!conversation.isGroup()) {
		conversation = new Conversation(UUID.v4(), conversation.getParticipants());
		conversation.setIsGroup(true);
	}

	conversation.addParticipant(userId);
	namespace.getConversations().add(conversation);

	this.emit('addUser', conversation.getId());
};