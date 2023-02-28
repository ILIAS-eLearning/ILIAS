# Administrative Notifications Privacy
Disclaimer: This documentation does not warrant completeness or correctness. Please report any missing or wrong information using the [ILIAS issue tracker](https://mantis.ilias.de) or contribute a fix via [Pull Request](docs/development/contributing.md#pull-request-to-the-repositories).

## Services being used
- The Administrative Notifications service employs the following services, please consult the respective privacy.mds:
    - [GlobalScreen](../../Services/GlobalScreen/PRIVACY.md)

## Data being stored
- User ID of the account that created the notification is stored.
- Creation timestamp of the notification is stored.
- User ID of the account that last updated the notification is stored.
- Last update timestamp of the notification is stored.
- User IDs of the accounts that dismissed the notification are stored.

## Data being presented
- The Administrative Notifications service does not present any personal data.

## Data being deleted
- Persons with "Write" permission for the Administrative Notifications can delete notifications.

## Data being exported
- The Administrative Notifications service does not have an export function. Therefore no personal data is being exported.
