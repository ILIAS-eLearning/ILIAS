var Container = require('../AppContainer');
var schedule = require('node-schedule');

/**
 * @param {Function} callback
 */
module.exports = function UserSettingsProcess(callback) {

	var job = schedule.scheduleJob('UserSettingsProcess', '*/20 * * * *', function () {
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

			database.getMessageAcceptanceStatusForUsers(function (row) {
				usersAcceptingMessages[row.usr_id] = row.usr_id;
			}, function() {
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
			});
		}
	}).invoke();

	callback();
};