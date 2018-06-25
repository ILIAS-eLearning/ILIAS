var Container = require('../AppContainer');

module.exports = function(req, res)
{
	var namespace = req.params.namespace;
	var users = JSON.parse(req.query.message);

	Container.getLogger().info("User settings changed event received '%s' ...", namespace);

	var imNamespace = Container.getNamespace(namespace + '-im');
	if (imNamespace) {
		var subscribers = imNamespace.getSubscribers();

		for (var usrId in users) {
			if (subscribers.hasOwnProperty(usrId) && users.hasOwnProperty(usrId)) {
				var subscriber = subscribers[usrId],
					settings   = users[usrId],
					acceptsMessages = (settings.hasOwnProperty("acceptsMessages") && settings["acceptsMessages"]);

				subscriber.setAcceptsMessages(acceptsMessages);

				Container.getLogger().info("Received settings update for user with id '%s': %s", subscriber.getId(), JSON.stringify({
					"acceptsMessages": acceptsMessages
				}));
			}
		}
	}

	res.send({ status: 200 });
};
