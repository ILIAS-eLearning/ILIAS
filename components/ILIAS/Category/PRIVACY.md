# Category Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information by submitting a [Pull Request](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/contributing.md#pull-request-to-the-repositories) or, if you prefer, via the [ILIAS bug tracker](https://mantis.ilias.de). When using the bug tracker, please select the corresponding component in the **Category** field.**


## General Information

The Category component provides a container object in the ILIAS repository that organises learning objects and other repository items into a hierarchical structure. When the global system setting for local user administration is enabled, a category can act as a local user domain: persons with the `cat_administrate_users` permission may list, create, edit, delete, and assign roles to local users within that category. Personal data is only processed through this optional local user administration feature and is otherwise not relevant to the component's core purpose.

## Integrated Components

The Category component delegates all personal data processing to the following components:

- User — user account data (login, firstname, lastname, email) is read and displayed via `ilUserTableGUI` and `ilLocalUser`; user accounts are created, edited, and deleted through `ilObjUserGUI` and `ilObjUserFolderGUI`.
- [RBAC](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/AccessControl/PRIVACY.md) — role assignments (`rbacreview`, `rbacadmin`) are read and modified for managed users via `AssignedRolesManager` and `AssignedRolesRetrieval`.

The [Container](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/Container/PRIVACY.md) and [Taxonomy](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/Taxonomy/PRIVACY.md) components are used structurally but handle no personal data in this context.

## Data being stored

The Category component itself contains no direct database inserts or updates for personal data. All storage of user account fields (login, firstname, lastname, email, role assignments) is performed by the User and [RBAC](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/AccessControl/PRIVACY.md) components when a person with `cat_administrate_users` permission creates or modifies local user accounts within a category. This feature is only active when the global system setting `isLocalUserAdministrationEnabled()` is enabled.

## Data being presented

When local user administration is enabled, persons with the `cat_administrate_users` permission can view a list of local users via the "Administrate Users" tab. The list is rendered by `ilUserTableGUI` in `MODE_LOCAL_USER` and displays the fields `login`, `firstname`, `lastname`, and `email`. These same fields are also used as search fields within the user table.

When assigning roles to an individual user, the role assignment table (`AssignRoleTableBuilder`) shows the managed user's name via `getNamePresentation` in the table title, together with available roles and their current assignment status.

No personal data is visible to persons without the `cat_administrate_users` permission.

## Data being deleted

Persons with the `cat_administrate_users` permission can permanently delete local user accounts from within a category via `performDeleteUsersObject()`. A confirmation dialog shows `lastname`, `firstname`, and `login` of each selected user before deletion is carried out. The actual deletion is delegated to the [User](../User/PRIVACY.md) component (`ilObject::delete()`).

When a category itself is deleted (moved to trash and then deleted from trash), the category raises a `delete` application event so that dependent components can clean up. Local user assignments (`ilObjUserFolder::_updateUserFolderAssignment`) are reassigned to the default user folder on category deletion.

## Data being exported

The Category export (`ilCategoryExporter`) produces an XML representation of the category structure and its associated taxonomies. It does not export any personal data such as user accounts or role assignments. The export is available to persons with the `export` permission on the category.
