/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

const Database = require('../Persistence/Database');
const Container = require('../AppContainer');

/**
 * @param {Namespace} namespace
 * @param {JSON} config
 * @param {Function} callback
 */
module.exports = function SetupDatabase(namespace, config, callback) {

	var database = new Database(config);
	namespace.setDatabase(database);

	database.connect((err, connection) => {
		if(err) {
			throw err;
		}

		Container.getLogger().info('Database for %s connected!', namespace.getName());
		connection.release();

		callback(null, namespace);
	});
};
