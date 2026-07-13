# Workspace Folder Privacy

This documentation does not warrant completeness or correctness. Please report any
missing or wrong information using the [ILIAS issue tracker](https://mantis.ilias.de)
or contribute a fix via [Pull Request](../../../docs/development/contributing.md#pull-request-to-the-repositories).

## Integrated Services

The Workspace Folder component employs the following services, please consult the respective privacy documentation:

- The **Object** service stores the account which created the object as its owner and creation and update timestamps for the object.
- [AccessControl](../AccessControl/PRIVACY.md)
- [PersonalWorkspace](../PersonalWorkspace/PRIVACY.md)
- [Refinery](../Refinery/PRIVACY.md)
- [Learning Object Metadata](../MetaData/Privacy.md)
- [Background Tasks](../BackgroundTasks/PRIVACY.md)
- The **Tree** service (via **PersonalWorkspace**) stores the structural position within the user's workspace in the `tree_workspace` table.

## General Information

The Workspace Folder component is used to organize and structure personal and shared resources (files, blogs, links, and other folders) within the user's personal workspace. Personal data includes ownership information, structural tree position, and user-specific interface settings like sortation.

## Configuration

- **Sortation**: Users can configure the sortation of items within each workspace folder. This setting is stored per user and folder.

## Data being stored

The following personal or potentially personal data is persisted by the Workspace Folder component:

- **user ID**: Stored in `wfld_user_setting` to persist user-specific sortation settings.
- **owner**: The user ID of the account that created the folder (handled by the **Object** service).
- **timestamps**: Creation and update timestamps for the folder (handled by the **Object** service).

## Data being presented

- **Personal Resources**: The folder presents the resources owned by the user or shared with them that are located within the folder.

## Data being deleted

- **User Deletion**: When a user account is deleted, their personal workspace (including all folders and their settings in `wfld_user_setting`) is removed.
- **Resource Deletion**: Deleting items within the workspace removes their respective entries in the tree and reference tables. Deleting a folder removes its associated settings.

## Data being exported

- **Multi-download**: Users can download the contents of a workspace folder as a ZIP archive. The archive contains the files themselves, which may contain personal data depending on the file content.
