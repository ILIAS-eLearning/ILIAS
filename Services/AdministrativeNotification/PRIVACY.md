# AdministrativeNotification Privacy
Disclaimer: This documentation does not warrant completeness or correctness. Please report any missing or wrong information using the [ILIAS issue tracker](https://mantis.ilias.de) or contribute a fix via [Pull Request](docs/development/contributing.md#pull-request-to-the-repositories).

## Data being stored
- User IDs of the persons that created and last updated the AdministrativeNotification entry as well as the timestamps of the creation and last update of the entry are stored. This data is stored in order to ensure internal traceability regarding timing and authorship in the event of any problems caused by the AdministrativeNotification entry.
- User IDs of the persons that dismissed the notification are stored. This prevents the AdministrativeNotification service from showing dismissed notifications again.
- The AdministrativeNotification service employs the following services, please consult the respective privacy.mds:
  - [GlobalScreen](../../Services/GlobalScreen/PRIVACY.md)

## Data being presented
- The AdministrativeNotification service does not present any personal data.

## Data being deleted
- Persons with "Edit Settings" permission at "Administration" > "Communication" >  "Administrative Notifications" can delete AdministrativeNotification entries.

## Data being exported
- The AdministrativeNotification service does not have an export function. Therefore no personal data is being exported.
