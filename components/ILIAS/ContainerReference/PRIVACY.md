# Container Reference Privacy

This documentation does not warrant completeness or correctness. Please report any
missing or wrong information using the [ILIAS issue tracker](https://mantis.ilias.de)
or contribute a fix via [Pull Request](../../../docs/development/contributing.md#pull-request-to-the-repositories).

## Integrated Services

The Container Reference component employs the following services, please consult the respective privacy documentation:

- The **Object** service stores the account which created the object as its owner and creation and update timestamps for the object.
- [AccessControl](../AccessControl/PRIVACY.md)
- [Container](../Container/PRIVACY.md)

## General Information

The Container Reference component allows creating references to other container objects (Categories, Folders, Courses, Groups). These references point to a target object and allow displaying it in different locations in the repository. The component itself does not manage user data like memberships or content; these are handled by the target objects.

## Configuration

No privacy-related configuration.

## Data being stored

The following personal or potentially personal data is persisted by the Container Reference component:

- **owner**: The user ID of the account that created the reference (handled by the **Object** service).
- **timestamps**: Creation and update timestamps for the reference object (handled by the **Object** service).

## Data being presented

No user-specific personal data (like member lists) is presented directly by this component.

## Data being deleted

- **Object Deletion**: When a container reference is deleted, its associated entry in the `container_reference` table is removed.
- **Target Deletion**: If the target object of a reference is deleted, the reference itself is automatically removed (handled via app events).

## Data being exported

- **XML Export**: The container reference export includes the target object ID and the title settings. It does not include personal data of users.
