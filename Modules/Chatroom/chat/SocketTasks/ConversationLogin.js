var Container = require('../AppContainer');
var Participant = require('../Model/ConversationParticipant');
var ListConversations = require('./ListConversations');

module.exports = function(id, name)
{
	var namespace = Container.getNamespace(this.nsp.name);

	Container.getLogger().info('Participant %s connected for namespace %s', name, namespace.getName());

	var participant = namespace.getSubscriber(id);

	if(participant == null) {
		console.log("new");
		participant = new Participant(id, name);
		namespace.addSubscriber(participant);
	}
	participant.setName(name);
	participant.addSocket(this);
	participant.setOnline(true);

	console.log(participant.getConversations());

	this.participant = participant;
	this.emit('login', participant.json());

	ListConversations.call(this);
};