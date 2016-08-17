var Container = require('../AppContainer');
var Conversation = require('../Model/Conversation');
var UUID = require('node-uuid');

module.exports = function(conversationId, userId, name) {
	var namespace = Container.getNamespace(this.nsp.name);
	var conversation = namespace.getConversations().getById(conversationId);

	if(!conversation.isGroup()) {
		var participants = conversation.getParticipants();
		conversation = new Conversation(UUID.v4());
		conversation.setIsGroup(true);
		namespace.getConversations().add(conversation);

		namespace.getDatabase().persistConversation(conversation);

		for(var key in participants) {
			if(participants.hasOwnProperty(key)) {
				conversation.addParticipant(participants[key]);
			}
		}
	}

	var participant = namespace.getSubscriberWithOfflines(userId, name);
	conversation.addParticipant(participant);
	participant.join(conversation.id);

	namespace.getDatabase().updateConversation(conversation);
	this.participant.emit('conversation', conversation.json());
	this.emit('addUser', conversation.json());
};