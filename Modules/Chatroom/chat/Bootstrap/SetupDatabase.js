var Database = require('../Persistence/Database');
var Container = require('../AppContainer');


/**
 * @param {Namespace} namespace
 * @param {JSON} config
 * @param {Function} callback
 */
module.exports = function SetupDatabase(namespace, config, callback) {
	var database = new Database(config);
	namespace.setDatabase(database);

	var onConnect = function(err, connection) {
		if(err) {
			throw err;
		}

		Container.getLogger().info('Database for %s connected!', namespace.getName());
		connection.release();

		callback(null, namespace);
	};

	database.connect(onConnect);
};
