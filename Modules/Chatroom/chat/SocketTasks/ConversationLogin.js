var Container = require('../AppContainer');
var Participant = require('../Model/ConversationParticipant');

module.exports = function(subscriberName, subscriberId)
{
	var namespace = Container.getNamespace(this.nsp.name);

	Container.getLogger().info('Participant %s connected for namespace %s', subscriberId, namespace.getName());

	var participant = namespace.getSubscriber(subscriberId);

	if(participant == null) {
		participant = new Participant(subscriberId, subscriberName);
		namespace.addSubscriber(participant);
	}

	participant.addSocket(this);
};