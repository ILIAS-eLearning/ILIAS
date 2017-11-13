var Container = require('../AppContainer');
var schedule = require('node-schedule');

/**
 * @param {Function} callback
 */
module.exports = function UserSettingsProcess(callback) {

	var fetchUserSettings = function () {
		var namespaces = Container.getNamespaces();

		for (var key in namespaces) {
			if(!namespaces.hasOwnProperty(key) || !namespaces[key].isIM()) {
				continue;
			}

			Container.getLogger().info(
				'Started fetching user settings for namespace %s',
				namespaces[key].getName()
			);

			var database = namespaces[key].getDatabase();
			var subscribers = namespaces[key].getSubscribers();
			var usersAcceptingMessages = {};

			var onResult = function (row) {
				usersAcceptingMessages[row.usr_id] = row.usr_id;
			};

			var onEnd = function() {
				for (var subsKey in subscribers) {
					if (!subscribers.hasOwnProperty(subsKey)) {
						continue;
					}
					subscribers[subsKey].setAcceptsMessages(
						usersAcceptingMessages.hasOwnProperty(subscribers[subsKey].getId())
					);
				}
				Container.getLogger().info(
					'Finished fetching user settings for namespace %s',
					namespaces[key].getName()
				);
			};

			database.getMessageAcceptanceStatusForUsers(onResult, onEnd);
		}
	};

	var job = schedule.scheduleJob('UserSettingsProcess', '*/20 * * * *', fetchUserSettings).invoke();

	callback();
};
