var Container = require('../AppContainer');
var Participant = require('../Model/ConversationParticipant');
var ListConversations = require('./ListConversations');

module.exports = function(id, name)
{
	var namespace = Container.getNamespace(this.nsp.name);

	Container.getLogger().debug('Participant %s connected for namespace %s', name, namespace.getName());

	var participant = namespace.getSubscriber(id);

	if(participant == null) {
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
