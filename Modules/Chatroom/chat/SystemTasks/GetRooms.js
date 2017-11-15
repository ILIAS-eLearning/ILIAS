var Container = require('../AppContainer');


/**
 * @namespace Tasks
 * @param req
 * @param res
 */
module.exports = function(req, res) {

	var namespace = Container.getNamespace(req.params.namespace);

	res.send({rooms: namespace.getRooms()});
};
