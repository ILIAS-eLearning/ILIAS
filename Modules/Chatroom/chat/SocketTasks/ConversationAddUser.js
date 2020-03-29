const Container = require('../AppContainer'),
	Conversation = require('../Model/Conversation'),
	UUID = require('node-uuid');

/**
 *
 * @param {string} conversationId
 * @param {number} userId
 * @param {string }name
 */
module.exports = function (conversationId, userId, name) {
	Container.getLogger().info('Add User to Conversation');
	if (! (conversationId != null && userId != null && name != null)) {
		Container.getLogger().warn("Invalid arguments passed, skipped action!");
		return;
	}

	const namespace = Container.getNamespace(this.nsp.name);
	let conversation = namespace.getConversations().getById(conversationId);

	if (null === conversation) {
		Container.getLogger().warn("Conversation not found by id, skipped action!");
		return;
	}

	if (!this.participant || !conversation.isParticipant(this.participant)) {
		Container.getLogger().warn("Actor is not assigned to conversation, skipped action!");
		return;
	}

	const newParticipant = namespace.getSubscriberWithOfflines(userId, name);
	let participants = conversation.getParticipants();

	if (!conversation.isGroup()) {
		conversation = new Conversation(UUID.v4());
		conversation.setIsGroup(true);

		namespace.getConversations().addOrUpdate(conversation);
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
};