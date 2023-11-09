var Namespace = require('../Model/Namespace');
var Container = require('../AppContainer');

module.exports.createNamespace = function(name) {
	var namespace = new Namespace(name);

	Container.addNamespace(namespace);

	return namespace;
};