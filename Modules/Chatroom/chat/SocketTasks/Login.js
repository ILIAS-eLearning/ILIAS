var Container = require('../AppContainer');
var ConnectAction = require('../Model/Messages/ConnectAction');

module.exports = function(subscriberName, subscriberId)
{
	var namespace = Container.getNamespace(this.nsp.name);

	Container.getLogger().info('Subscriber %s connected for namespace %s', subscriberId, namespace.getName());

	if(!namespace.hasSubscriber(subscriberId)) {
		var sockets = this.client.sockets;
		for(var key in sockets) {
			if(sockets.hasOwnProperty(key)) {
				this.nsp.to(key).emit('shutdown');
			}
		}
	} else {
		var subscriber = namespace.getSubscriber(subscriberId);
		subscriber.setName(subscriberName);
		subscriber.addSocketId(this.id);

		var timeout = Container.getTimeout(subscriberId);
		if(timeout != undefined) {
			Container.removeTimeout(subscriberId);
			clearTimeout(timeout);
		}

		this.namespace = namespace;
		this.subscriber = subscriber;

		this.emit('loggedIn');
	}
};