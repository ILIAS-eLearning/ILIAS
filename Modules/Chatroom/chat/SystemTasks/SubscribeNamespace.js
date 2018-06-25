var Subscriber	= require('../Model/Subscriber');
var Container 		= require('../AppContainer');

/**
 * @namespace Tasks
 * @param req
 * @param res
 */
module.exports = function(req, res)
{
	var subscriberId = parseInt(req.params.id);
	var namespace = Container.getNamespace(req.params.namespace);
	var subscriber = namespace.getSubscriber(subscriberId);

	Container.removeTimeout(subscriberId);
	Container.getLogger().info('New Subscriber %s of namespace %s', subscriberId, namespace.getName());

	if(subscriber == null) {
		subscriber = new Subscriber(subscriberId);
		namespace.addSubscriber(subscriber);
	}

	res.send({ status: 200 });
};
