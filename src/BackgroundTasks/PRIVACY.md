# BackgroundTasks Privacy
Disclaimer: This documentation does not warrant completeness or correctness. Please report any missing or wrong information using the [ILIAS issue tracker](https://mantis.ilias.de) or contribute a fix via [Pull Request](docs/development/contributing.md#pull-request-to-the-repositories).

## General Information
- Services and components use the BackgroundTasks service for running tasks in the background so that longer, more resource-intensive tasks do not hinder the use of ILIAS. One example is the download of large folders, which could easily prevent a user from using ILIAS for several minutes, if the BackgroundTasks service wasn't being used.
- Services and components which use the BackgroundTasks service might store, present, delete or export personal data. This is specified in their respective PRIVACY.md.

## Data being stored
- User ID which was handed over by a service or component using the BackgroundTasks service is stored.
  - Typically this will be the User ID of the account whose actions initiated the creation of the background task.
- The BackgroundTasks service employs the following services, please consult the respective privacy.mds:
  - [Filesystem](../../src/Filesystem/PRIVACY.md)
  - [GlobalScreen](../../Services/GlobalScreen/PRIVACY.md)
  - Setup
  - UI

## Data being presented
- The BackgroundTasks service is ignorant of the content of the title and description of a task presented to a user.
- The BackgroundTasks service itself does not present any personal data.

## Data being deleted
- Tasks are directely deleted and do not go to the trash.
- The BackgroundTasks service deletes personal data in the following cases:
  - If the background task was completed successfully.
  - If the background task failed and the service or component which initiated the background task has not provided error handling for that specific case (otherwise the task would be kept and an error message would be shown).
  - If the account whose User ID has been stored for the background task cancels the background task. This can be done by clicking on the notification icon in the menubar, clicking on "Background Tasks" and then clicking on the close icon next to the corresponding background task.

## Data being exported
- The BackgroundTasks service does not have an export function. Therefore no personal data is being exported.
