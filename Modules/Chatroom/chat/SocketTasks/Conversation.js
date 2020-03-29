const Container = require('../AppContainer'),
	UUID = require('node-uuid'),
	Conversation = require('../Model/Conversation');

/**
 *
 * @param {{name: string, id: number}[]} participants
 */
module.exports = function (participants) {
	Container.getLogger().info('Conversation Requested');
	const namespace = Container.getNamespace(this.nsp.name),
		conversations = namespace.getConversations(),
		socket = this;
	let conversation = conversations.getForParticipants(participants.map(p => p.id.toString()));

	if (null === conversation) {
		Container.getLogger().info('No Conversation found. Creating new one');
		conversation = new Conversation(UUID.v4());
		conversations.addOrUpdate(conversation);
		namespace.getDatabase().persistConversation(conversation);
	}

	for (let key in participants) {
		const participant = namespace.getSubscriberWithOfflines(participants[key].id, participants[key].name);
		conversation.addParticipant(participant);
		participant.join(conversation.getId());
	}

	namespace.getDatabase().updateConversation(conversation);

	function onLastConversationMessageResult(row) {
		row.userId = row.user_id;
		row.conversationId = row.conversation_id;
		conversation.setLatestMessage(row);
	}

	function onLastConversationMessageEnd() {
		socket.participant.emit('conversation-init', conversation.json());
	}

	namespace.getDatabase().getLatestMessage(conversation, onLastConversationMessageResult, onLastConversationMessageEnd);
};
