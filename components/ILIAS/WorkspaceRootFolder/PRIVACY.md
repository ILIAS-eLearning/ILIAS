# WorkspaceRootFolder Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information by submitting a [Pull Request](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/contributing.md#pull-request-to-the-repositories) or, if you prefer, via the [ILIAS bug tracker](https://mantis.ilias.de). When using the bug tracker, please select the corresponding component in the **Category** field.**

## General Information

The WorkspaceRootFolder component represents the root node of a user's Personal Workspace. It is
the top-level container for personal resources and the starting point for accessing resources shared
by other users. The component is a specialization of [WorkspaceFolder](../WorkspaceFolder/PRIVACY.md)
and inherits its content presentation, sharing, deletion and download functionality.

The root node itself is not a user-created folder. It cannot be deleted, copied, cut or linked; these
actions are disabled by the root list GUI. Its child resources can nevertheless contain arbitrary
personal data.

## Integrated Components

- [PersonalWorkspace](../PersonalWorkspace/PRIVACY.md) manages the user-scoped workspace tree, workspace
  references and sharing permissions.
- [WorkspaceFolder](../WorkspaceFolder/PRIVACY.md) provides the inherited listing, user-specific
  sortation, child-resource actions and ZIP download.
- The **Object** service stores the root object's owner and creation and update timestamps.
- [BackgroundTasks](../BackgroundTasks/PRIVACY.md) runs workspace downloads and stores the ID of the
  user who initiated a download task.
- The components of the contained resources, such as files, blogs and web links, store their own
  resource data.

## Configuration

- See [WorkspaceFolder](../WorkspaceFolder/PRIVACY.md)

## Data being stored

- See [WorkspaceFolder](../WorkspaceFolder/PRIVACY.md)

## Data being presented

- See [WorkspaceFolder](../WorkspaceFolder/PRIVACY.md)

## Data being deleted

- See [WorkspaceFolder](../WorkspaceFolder/PRIVACY.md)

## Data being exported

- See [WorkspaceFolder](../WorkspaceFolder/PRIVACY.md)
