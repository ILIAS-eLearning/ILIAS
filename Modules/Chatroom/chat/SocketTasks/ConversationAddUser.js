var Container = require('../AppContainer');
var Conversation = require('../Model/Conversation');
var UUID = require('node-uuid');

module.exports = function(conversationId, userId, name) {
	if (conversationId !== null && userId !== null && name !== null) {
		var namespace = Container.getNamespace(this.nsp.name);
		var conversation = namespace.getConversations().getById(conversationId);

		if (conversation.isParticipant(this.participant)) {
			var newParticipant = namespace.getSubscriberWithOfflines(userId, name);
			let participants   = conversation.getParticipants();

			if (!conversation.isGroup()) {
				conversation = new Conversation(UUID.v4());
				conversation.setIsGroup(true);

				namespace.getConversations().add(conversation);
				namespace.getDatabase().persistConversation(conversation);

				for (let participant of participants.values()) {
					conversation.addParticipant(participant);
				}

				Container.getLogger().info('Conversation %s transformed to group conversation.', conversation.getId())
			}

			if (conversation.isParticipant(newParticipant)) {
				Container.getLogger().info('Participant %s is already subscribed to conversation %s.', newParticipant.getName(), conversation.getId());
				return;
			}

			conversation.addParticipant(newParticipant);
			newParticipant.join(conversation.id);

			participants = conversation.getParticipants();
			for (let participant of participants.values()) {
				namespace.getDatabase().trackActivity(conversation.getId(), participant.getId(), 0);
			}

			Container.getLogger().info('New Participant %s for group conversation %s', newParticipant.getName(), newParticipant.getId());

			namespace.getDatabase().updateConversation(conversation);

			this.participant.emit('conversation', conversation.json());
			this.emit('addUser', conversation.json());
		}
	}
};