# BackgroundTasks Privacy
Disclaimer: This documentation does not warrant completeness or correctness. Please report any missing or wrong information using the [ILIAS issue tracker](https://mantis.ilias.de) or contribute a fix via [Pull Request](docs/development/contributing.md#pull-request-to-the-repositories).

## General Information
- Components use the BackgroundTasks component for running tasks in the background so that longer, more resource-intensive tasks do not hinder the use of ILIAS. One example is the download of large folders, which could easily prevent a user from using ILIAS for several minutes, if the BackgroundTasks service wasn't being used.
- Components which use the BackgroundTasks component might store, present, delete or export personal data. This is specified in their respective PRIVACY.md.

## Integrated Components
- The BackgroundTasks component employs the following services, please consult the respective privacy.mds:
    - [Filesystem](../Filesystem/PRIVACY.md)
    - [GlobalScreen](../GlobalScreen/PRIVACY.md)
    - [Setup](../Setup/PRIVACY.md)
    - UI

## Data being stored
- User ID which was handed over by a component using the BackgroundTasks component is stored.
  - Typically this will be the User ID of the account whose actions initiated the creation of the background task.


## Data being presented
- The BackgroundTasks component is ignorant of the content of the title and description of a task presented to a user.
- The BackgroundTasks component itself does not present any personal data.

## Data being deleted
- Tasks are directly deleted and do not go to the trash.
- The BackgroundTasks component deletes personal data in the following cases:
  - If the background task was completed successfully over 30 days ago.
  - If the background task is in another state but already over 180 days old (likely failed).
  - If the account whose User ID has been stored for the background task closes the background task. This can be done by clicking on the notification icon in the menubar, clicking on "Background Tasks" and then clicking on the close icon next to the corresponding background task.

## Data being exported
- The BackgroundTasks component does not have an export function. Therefore no personal data is being exported.
