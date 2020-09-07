var Container = require('../AppContainer');
var schedule = require('node-schedule');

/**
 * @param {Function} callback
 */
module.exports = function SetupClearMessagesProcess(result, callback) {

	if (Container.getServerConfig().hasOwnProperty('deletion_mode') &&
		parseInt(Container.getServerConfig().deletion_mode, 10) === 1) {

		var deletionTime = Container.getServerConfig().deletion_time;
		deletionTime = deletionTime.split(':');

		function getMessagesClearedCallback(namespaceName) {
			return function messagesClearedCallback() {
				Container.getLogger().info('Clear process for namespace %s finished', namespaceName);
			};
		}

		schedule.scheduleJob('ClearMessagesProcess', {hour: deletionTime[0], minute: deletionTime[1]}, function clearMessagesProcess() {
			var namespaces = Container.getNamespaces();
			var deletionUnit = Container.getServerConfig().deletion_unit;
			var deletionValue = Container.getServerConfig().deletion_value;

			var bound = generateBoundTimestamp(deletionUnit, deletionValue);

			for (var key in namespaces) {
				if (!namespaces.hasOwnProperty(key)) {
					continue;
				}

				var database = namespaces[key].getDatabase(),
					namespaceName = namespaces[key].getName();

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
	var bound = new Date();

	if (deletionUnit === 'years') {
		bound.setFullYear(bound.getFullYear() - deletionValue)
	}
	if (deletionUnit === 'months' || deletionUnit === 'month') {
		bound.setMonth(bound.getMonth() - deletionValue)
	}
	if (deletionUnit === 'weeks') {
		var weeks = 7 * deletionValue;
		bound.setDate(bound.getDate() - weeks)
	}
	if (deletionUnit === 'days') {
		bound.setDate(bound.getDate() - deletionValue)
	}
	return bound;
}

