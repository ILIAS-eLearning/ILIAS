# Folder Privacy

This documentation does not warrant completeness or correctness. Please report any
missing or wrong information using the [ILIAS issue tracker](https://mantis.ilias.de)
or contribute a fix via [Pull Request](../../../docs/development/contributing.md#pull-request-to-the-repositories).

## Integrated Services

The Folder component employs the following services, please consult the respective privacy documentation:

- The **Object** service stores the account which created the object as its owner and creation and update timestamps for the object.
- [AccessControl](../AccessControl/PRIVACY.md)
- [Container](../Container/PRIVACY.md)
- [COPage](../COPage/PRIVACY.md)
- [News](../News/PRIVACY.md)
- [Learning Object Metadata](../MetaData/Privacy.md)
- [WebDAV](../WebDAV/PRIVACY.md)
- The **Learning Progress** service manages the learning status of users (e.g. if the folder is used as a collection).
- The **Tree** service stores the structural position of the folder.

## General Information

The Folder component is used to organize and structure content within the ILIAS repository. It serves as a container for other objects. Personal data is primarily limited to ownership information, learning progress (if enabled), and user-specific interface settings.

## Configuration

- **Learning Progress**: The folder can be configured to determine its learning progress status based on its sub-items (Collection mode).

## Data being stored

The following personal or potentially personal data is persisted by the Folder component:

- **user ID**: Stored in `il_block_setting` to persist user-specific block configurations (handled by the **Container** service).
- **learning progress**: If the folder is used as a learning progress collection, user-specific status data is managed (handled by the **Learning Progress** service).
- **custom content**: Authors can create page content (via **COPage**) that may contain personal data.
- **owner**: The user ID of the account that created the folder (handled by the **Object** service).
- **timestamps**: Creation and update timestamps for the folder object (handled by the **Object** service).

## Data being presented

- **Learning Progress**: If active, the learning progress status of users for the collection of objects within the folder is presented to authorized users.

## Data being deleted

- **User Deletion**: When a user account is deleted, their user-specific settings in `il_block_setting` are removed.
- **Object Deletion**: Deleting a folder removes its associated settings and its structural position in the tree.

## Data being exported

- **XML Export**: The folder export includes the title, description, and sorting settings. It does not include personal data of users.
- **Multi-download**: If enabled, users can download the contents of a folder as a ZIP archive. The archive contains the files themselves, which may contain personal data depending on the file content.
