const Container = require('../AppContainer'),
	Participant = require('../Model/ConversationParticipant'),
	ListConversations = require('./ListConversations');

/**
 *
 * @param {number} id
 * @param {string} name
 */
module.exports = function(id, name) {
	const namespace = Container.getNamespace(this.nsp.name);

	Container.getLogger().info('Participant %s connected for namespace %s', name, namespace.getName());

	let participant = namespace.getSubscriber(id);

	if (null === participant) {
		participant = new Participant(id, name);
		namespace.addSubscriber(participant);
	}

	participant.setName(name);
	participant.addSocket(this);
	participant.setOnline(true);

	this.participant = participant;
	this.emit('login', participant.json());

	ListConversations.call(this);
};
