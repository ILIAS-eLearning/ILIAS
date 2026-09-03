# Container Privacy

This documentation does not warrant completeness or correctness. Please report any
missing or wrong information using the [ILIAS issue tracker](https://mantis.ilias.de)
or contribute a fix via [Pull Request](../../../docs/development/contributing.md#pull-request-to-the-repositories).

## Integrated Services

The Container component employs the following services, please consult the respective privacy documentation:

- The **Object** service stores the account which created the object as its owner and creation and update timestamps for the object.
- [AccessControl](../AccessControl/PRIVACY.md)
- [COPage](../COPage/PRIVACY.md)
- [Skill](../Skill/PRIVACY.md)
- [News](../News/PRIVACY.md)
- [Refinery](../Refinery/PRIVACY.md)
- [Learning Object Metadata](../MetaData/Privacy.md)
- The **Conditions** service manages pre-conditions for object access.
- The **Style** service manages content styles.
- The **Tree** service stores the structural position of the container.
- The **Global Screen** service provides the layout and main navigation context.

## General Information

The Container component serves as a base for structural objects in ILIAS (Categories, Folders, Courses, Groups). Its main purpose is to organize and display other objects. Personal data primarily occurs in the context of administration (e.g., skill assignments to members) and user-specific interface settings (e.g., block configurations).

## Configuration

- **Global Settings**: Administrative settings for container objects.
- **Object Settings**:
  - **View Mode**: Influences how content is displayed to users.
  - **Member View**: Allows administrators/tutors to see the container as a regular member.
- **Skill Settings**: Enables the assignment of competences to members.

## Data being stored

The following personal or potentially personal data is persisted by the Container component:

- **user ID**: Stored in `il_block_setting` to persist user-specific block configurations (e.g., which blocks are collapsed or their position).
- **skill assignments**: Assignments of specific skill levels to users are managed through this component (though stored by the **Skill** service).
- **custom content**: Authors can create custom blocks or page content (via **COPage**) that may contain any personal data.
- **timestamps**:
  - Creation and update timestamps for the container object (handled by the **Object** service).
- **container settings**: General settings for the container stored in `container_settings`.

## Data being presented

Personal data is presented in the following areas:

- **Member Skill Management**: Administrators and tutors see a list of members including their **first name**, **last name**, and **login** to assign competences.
- **Member View**: When activated, the system presents the content as it would appear to a regular member.

## Data being deleted

- **User Deletion**: When a user account is deleted, their user-specific settings in `il_block_setting` are removed.
- **Object Deletion**: Deleting a container removes its associated `container_settings` and `il_block_setting` entries for that block.
- **Skill Assignments**: Removing a member's competence assignment removes the link between the user and the skill level.

## Data being exported

- **XML Export**: The container structure export includes item metadata (RefId, Title, Type) and timing settings. It generally does not include personal data of members.
- **Multi-download**: Users can download multiple files from a container as a ZIP archive. The archive contains the files themselves, which may contain personal data depending on the file content.
