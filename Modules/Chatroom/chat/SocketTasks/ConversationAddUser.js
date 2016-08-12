var Container = require('../AppContainer');
var Conversation = require('../Model/Conversation');
var UUID = require('node-uuid');

module.exports = function(conversationId, userId, name) {
	var namespace = Container.getNamespace(this.nsp.name);
	var conversation = namespace.getConversations().getById(conversationId);

	if(!conversation.isGroup()) {
		conversation = new Conversation(UUID.v4(), conversation.getParticipants());
		conversation.setIsGroup(true);
		namespace.getConversations().add(conversation);
	}

	var participant = namespace.getSubscriberWithOfflines(userId, name);
	conversation.addParticipant(participant);
	participant.join(conversation.id);

	this.participant.emit('conversation', conversation.json());
	this.emit('addUser', true);
};