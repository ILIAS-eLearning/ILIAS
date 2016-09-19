var Container = require('../AppContainer');
var Conversation = require('../Model/Conversation');
var UUID = require('node-uuid');

module.exports = function(conversationId, userId, name) {
	if(conversationId !== null && userId !== null && name !== null)
	{
		var namespace = Container.getNamespace(this.nsp.name);
		var conversation = namespace.getConversations().getById(conversationId);

		if(conversation.isParticipant(this.participant))
		{
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

				Container.getLogger().info('Conversation %s transformed to group conversation.', conversation.getId())
			}

			var participant = namespace.getSubscriberWithOfflines(userId, name);
			conversation.addParticipant(participant);
			participants.push(participant);
			participant.join(conversation.id);

			for(var key in participants) {
				if(participants.hasOwnProperty(key)){
					namespace.getDatabase().trackActivity(conversation.getId(), participants[key].getId(), 0);
				}
			}

			Container.getLogger().info('New Participant %s for group conversation %s', participant.getName(), conversation.getId());

			namespace.getDatabase().updateConversation(conversation);
			this.participant.emit('conversation', conversation.json());
			this.emit('addUser', conversation.json());
		}
	}
};