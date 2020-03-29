const Container = require('../AppContainer'),
	Conversation = require('../Model/Conversation');

/**
 * @param {Namespace} namespace
 * @param {Function} callback
 */
module.exports = function PreloadConversations(namespace, callback) {
	Container.getLogger().info('PreloadConversations for %s', namespace.getName());

	function onConversationResult(row) {
		const participants = JSON.parse(row.participants),
			conversation = new Conversation(row.id);

		conversation.setIsGroup(row.is_group);

		for (let index in participants) {
			if (participants.hasOwnProperty(index)) {
				const participant = namespace.getSubscriberWithOfflines(participants[index].id, participants[index].name);
				participant.setOnline(false);
				participant.join(conversation.getId());

				conversation.addParticipant(participant);
				namespace.addSubscriber(participant);
			}
		}
		namespace.getConversations().addOrUpdate(conversation);

	}

	function onConversationsPreloaded() {
		callback(null, namespace);
	}

	namespace.getDatabase().loadConversations(onConversationResult, onConversationsPreloaded);
};
