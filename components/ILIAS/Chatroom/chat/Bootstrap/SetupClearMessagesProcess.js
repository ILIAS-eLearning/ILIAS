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

const Container = require('../AppContainer');
const schedule = require('../Helper/schedule');

module.exports = (result, callback) => {
	if (Container.getServerConfig().hasOwnProperty('deletion_mode') &&
		parseInt(Container.getServerConfig().deletion_mode, 10) === 1) {

		const deletionTime = Container.getServerConfig().deletion_time.split(':');

		function getMessagesClearedCallback(namespaceName) {
			return () => {
				Container.getLogger().info('Clear process for namespace %s finished', namespaceName);
			};
		}

		schedule.everyDayAt(parseInt(deletionTime[0] || 0), parseInt(deletionTime[1] || 0), () => {
			const namespaces = Container.getNamespaces();
			const deletionUnit = Container.getServerConfig().deletion_unit;
			const deletionValue = Container.getServerConfig().deletion_value;
			const bound = generateBoundTimestamp(deletionUnit, deletionValue);

			for (var key in namespaces) {
				if (!namespaces.hasOwnProperty(key)) {
					continue;
				}

				const database = namespaces[key].getDatabase();
				const namespaceName = namespaces[key].getName();

				Container.getLogger().info(
					'Start clear process for namespace %s older then %s [%s]',
					namespaces[key].getName(),
					bound.toUTCString(),
					bound.getTime()
				);

				database.clearChatMessagesProcess(bound.getTime(), namespaceName, getMessagesClearedCallback(namespaceName));
			}
		});

		Container.getLogger().info('Clear messages process initialized for %s once a day', Container.getServerConfig().deletion_time);
	}

	callback();
};

function generateBoundTimestamp(deletionUnit, deletionValue) {
	const bound = new Date();

	if (deletionUnit === 'years') {
		bound.setFullYear(bound.getFullYear() - deletionValue);
	}
	if (deletionUnit === 'months' || deletionUnit === 'month') {
		bound.setMonth(bound.getMonth() - deletionValue);
	}
	if (deletionUnit === 'weeks') {
		var weeks = 7 * deletionValue;
		bound.setDate(bound.getDate() - weeks);
	}
	if (deletionUnit === 'days') {
		bound.setDate(bound.getDate() - deletionValue);
	}
	return bound;
}
