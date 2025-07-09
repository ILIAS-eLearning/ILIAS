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
	schedule.every20Minutes(() => {
		const namespaces = Container.getNamespaces();

		for (var key in namespaces) {
			if(!namespaces.hasOwnProperty(key) || !namespaces[key].isIM()) {
				continue;
			}

			Container.getLogger().info(
				'Started fetching user settings for namespace %s',
				namespaces[key].getName()
			);

			const database = namespaces[key].getDatabase();
			const subscribers = namespaces[key].getSubscribers();
			const usersAcceptingMessages = {};

			database.getMessageAcceptanceStatusForUsers(row => {
				usersAcceptingMessages[row.usr_id] = row.usr_id;
			}, () => {
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
	});

	callback();
};
