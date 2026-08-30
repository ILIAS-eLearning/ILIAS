# WorkspaceRootFolder Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information by submitting a [Pull Request](docs/development/contributing.md#pull-request-to-the-repositories) or, if you prefer, via the [ILIAS bug tracker](https://mantis.ilias.de). When using the bug tracker, please select the corresponding component in the **Category** field.**

## General Information

The WorkspaceRootFolder component represents the root node of a user's personal workspace tree.
It serves as the top-level container for the "Personal and Shared Resources" area. The component
itself is a thin specialization of the WorkspaceFolder component: it inherits nearly all
functionality (content rendering, sharing, clipboard operations, deletion of child objects) from
`ilObjWorkspaceFolderGUI` and `ilObjWorkspaceFolder`. The root folder cannot be deleted, copied,
cut, or linked by any user.

The component manages object translations for the root folder (title, description, language code)
via the shared `object_translation` table, but these are system-level object metadata and do not
contain personal data.

## Integrated Components

- The WorkspaceRootFolder component employs the following components, please consult the respective
  PRIVACY.md files:
    - [PersonalWorkspace](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/PersonalWorkspace/PRIVACY.md) — provides the GUI request handling
      and workspace session management used by the root folder's list GUI.
    - [WorkspaceFolder](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/WorkspaceFolder/PRIVACY.md) — the parent component from which WorkspaceRootFolder inherits all workspace
      content rendering, sharing, clipboard, and deletion functionality.
    - [Repository](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/Repository/PRIVACY.md) — the `ilObjectOwnershipManagementGUI` (from the
      Repository component) is forwarded to from the root folder GUI and allows users to manage
      objects they own.
    - [AccessControl](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/AccessControl/PRIVACY.md) — permission checking is inherited from the
      parent class chain (`ilObject2GUI`, `ilObjectAccess`).
    - [Refinery](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/Refinery/PRIVACY.md) — `$DIC->refinery()` validates and casts request
      parameters in `ilObjWorkspaceRootFolderListGUI`.

## Data being stored

This component does not store personal data. The `object_translation` table entries managed by
`ilObjWorkspaceRootFolder` contain only the object ID, title, description, and language code of
the root folder object itself. No user IDs, timestamps, or other personal data are written by this
component.

## Data being presented

This component does not present personal data on its own. All presentation of workspace content,
shared resources, and user information is handled by the parent WorkspaceFolder component and the
PersonalWorkspace service.

## Data being deleted

The workspace root folder cannot be deleted. Deletion is explicitly disabled in the list GUI
(`delete_enabled = false`). Deletion of child objects within the workspace is handled by the parent
WorkspaceFolder component. When a user account is deleted, the workspace tree and its contents are
removed through the PersonalWorkspace service, not through this component.

## Data being exported

This component does not provide any export functionality.
