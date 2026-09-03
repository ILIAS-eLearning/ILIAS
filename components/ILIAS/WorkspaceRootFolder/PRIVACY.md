# Workspace Root Folder Privacy

This documentation does not warrant completeness or correctness. Please report any
missing or wrong information using the [ILIAS issue tracker](https://mantis.ilias.de)
or contribute a fix via [Pull Request](../../../docs/development/contributing.md#pull-request-to-the-repositories).

## Integrated Services

The Workspace Root Folder component employs the following services, please consult the respective privacy documentation:

- The **Object** service stores the account which created the object as its owner and creation and update timestamps for the object.
- [AccessControl](../AccessControl/PRIVACY.md)
- [PersonalWorkspace](../PersonalWorkspace/PRIVACY.md)
- [Refinery](../Refinery/PRIVACY.md)
- [Learning Object Metadata](../MetaData/Privacy.md)
- The **Tree** service (via **PersonalWorkspace**) stores the structural position within the user's workspace in the `tree_workspace` table.

## General Information

The Workspace Root Folder is the entry point for a user's personal workspace in ILIAS. It serves as the top-level container for all personal and shared resources (files, blogs, links, and folders). Initially, these resources are private and can only be accessed by the user. Personal data includes ownership information, structural tree position, and user-specific interface settings.

## Configuration

- **Sortation**: Users can configure the sortation of items within the workspace root folder. This setting is stored per user and folder.

## Data being stored

The following personal or potentially personal data is persisted by the Workspace Root Folder component:

- **user ID**: Stored in `wfld_user_setting` to persist user-specific sortation settings (handled by the **WorkspaceFolder** logic).
- **user ID**: Stored in `tree_workspace` and `object_reference_ws` to represent the "ownership" and structural position of the resources (handled by the **PersonalWorkspace** service).
- **owner**: The user ID of the account that created the personal workspace (handled by the **Object** service).
- **timestamps**: Creation and update timestamps for the root folder (handled by the **Object** service).

## Data being presented

- **Personal Resources**: The root folder presents all top-level resources owned by the user.
- **Shared Resources**: The component organizes access to resources shared by other users.

## Data being deleted

- **User Deletion**: When a user account is deleted, their personal workspace (including the root folder and all its contents) is removed. This includes all entries in `wfld_user_setting`, `tree_workspace`, `object_reference_ws`, and `object_translation` associated with that user's tree.
- **Resource Deletion**: Deleting items within the workspace removes their respective entries in the tree and reference tables.

## Data being exported

- **Multi-download**: Users can download the contents of the workspace root folder as a ZIP archive. The archive contains the files themselves, which may contain personal data depending on the file content.
