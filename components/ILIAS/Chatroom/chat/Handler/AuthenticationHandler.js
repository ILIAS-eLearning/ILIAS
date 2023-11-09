var Container = require('../AppContainer');

module.exports = function(req, res, next) {

	if(!req.headers.authorization) {
		Container.getLogger().warn('Access denied cause of missing authorization header for %s', req.params.namespace);
		_accessDenied(res);
	} else if(!_hasPermission(req)){
		Container.getLogger().warn('Access denied cause of no permission for %s', req.params.namespace);
		_accessDenied(res);
	} else {
		Container.getLogger().debug('Access granted for %s', req.params.namespace);
		next();
	}
};


function _hasPermission(req) {
	var securityToken = req.headers.authorization.split(' ')[1];
	var decoded = new Buffer(securityToken, 'base64').toString('utf8').split(':');
	var config = Container.getClientConfig(req.params.namespace);

	return (config.auth.key== decoded[0] &&
			config.auth.secret == decoded[1]);
}

function _accessDenied(res) {
	res.send({status: 403, message: "Access denied"});
}