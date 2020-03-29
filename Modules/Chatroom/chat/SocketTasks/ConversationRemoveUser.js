const Container = require('../AppContainer');

/**
 *
 * @param {string} conversationId
 * @param {number} userId
 * @param {string} name
 */
module.exports = function (conversationId, userId, name) {
	if (conversationId != null && userId != null && name != null) {
		const namespace = Container.getNamespace(this.nsp.name),
			conversation = namespace.getConversations().getById(conversationId),
			participant = namespace.getSubscriberWithOfflines(userId, name);

		if (conversation !== null && conversation.isParticipant(participant)) {
			conversation.removeParticipant(participant);
			participant.leave(conversation.id);

			Container.getLogger().info('Participant %s left group conversation %s', participant.getName(), conversation.getId());

			namespace.getDatabase().updateConversation(conversation);
			this.participant.emit('removeUser', conversation.json());
			conversation.emit('conversation', conversation.json());
		}
	}
};