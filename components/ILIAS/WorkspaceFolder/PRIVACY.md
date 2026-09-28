# WorkspaceFolder Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information by submitting a [Pull Request](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/contributing.md#pull-request-to-the-repositories) or, if you prefer, via the [ILIAS bug tracker](https://mantis.ilias.de). When using the bug tracker, please select the corresponding component in the **Category** field.**

## General Information

The WorkspaceFolder component provides folders in a user's Personal Workspace. The folders organize user-created resources such as files, blogs and web links. These resources can contain personal data and selected resources can be shared with other users or groups.

Workspace objects do not use the repository trash. Deletion from the workspace is permanent and immediate.

## Integrated Components

- [PersonalWorkspace](../PersonalWorkspace/PRIVACY.md) provides the workspace tree, workspace references and workspace access handling, including sharing permissions.
- [AccessControl](../AccessControl/PRIVACY.md) is used by the inherited object access handling for permission checks.
- [BackgroundTasks](../BackgroundTasks/PRIVACY.md) runs the workspace file download and stores the ID of the user who initiated the task.
- [Notes](../Notes/PRIVACY.md) provides notes actions for resources listed in workspace folders.
- [Tagging](../Tagging/PRIVACY.md) provides tagging actions for resources listed in workspace folders.
- The **Object** service manages the owner and lifecycle data of workspace folder objects; the individual resource components manage their own resource data.

## Configuration

- **Folder sortation**: A user can choose the order in which items in a workspace folder are displayed. The preference is stored for that user and folder.
- **Sharing permissions**: Owners can share eligible workspace resources with individual users, group or course members, registered users, all users, or users who know a configured password. Public sharing options are available only when the corresponding Personal Workspace setting is enabled.

## Data being stored

- The `wfld_user_setting` table stores the **user ID**, workspace folder ID and the user's sortation value for that folder.
- The **Object** service stores the folder owner and object lifecycle timestamps. These values are not stored by WorkspaceFolder-specific code.
- Workspace tree references and sharing permissions are managed by [PersonalWorkspace](../PersonalWorkspace/PRIVACY.md). Files, blogs, web links, notes and tags are stored by their respective components.

## Data being presented

- A user with access to a folder can access the user-created resources contained in it. The resources may contain arbitrary personal data.
- Shared resources are presented to users who satisfy the configured workspace access rule. The access handler supports direct user, group, course, registered-user, public and password-protected read access; public and password-protected access depend on the global profile setting.
- The sharing views can present the account identity of resource owners and users selected for sharing. This presentation is handled by [PersonalWorkspace](../PersonalWorkspace/PRIVACY.md) and its integrated user-search services.
- The WorkspaceFolder component itself does not add user names to the folder content listing. It can show whether an eligible resource is shared.

## Data being deleted

- Deleting a folder or another workspace item is permanent and removes the corresponding workspace tree reference and permissions. Folder deletion recursively processes its child objects; the data owned by those object components is deleted according to their implementations.
- When a user account is deleted, [PersonalWorkspace](../PersonalWorkspace/PRIVACY.md) recursively deletes the user's workspace tree, objects, references and workspace permissions.
- Deletion of notes, tags and resource content is handled by [Notes](../Notes/PRIVACY.md), [Tagging](../Tagging/PRIVACY.md) and the respective resource components.

## Data being exported

- The WorkspaceFolder object has no repository export; its module configuration sets `export="0"`.
- The component provides a download action that creates a ZIP archive of selected workspace folders and files through [BackgroundTasks](../BackgroundTasks/PRIVACY.md). The archive can contain user-uploaded file content and is offered to the requesting user.
- File collection checks **read** permission for the requesting user before adding files, including files in shared folders. The download task may therefore disclose the personal data contained in any file to that user.
