# WorkspaceFolder Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information by submitting a [Pull Request](docs/development/contributing.md#pull-request-to-the-repositories) or, if you prefer, via the [ILIAS bug tracker](https://mantis.ilias.de). When using the bug tracker, please select the corresponding component in the **Category** field.**

## General Information

The WorkspaceFolder component provides folders within the Personal Workspace of each user.
Workspace folders allow users to organise files, blogs, and web links in a hierarchical structure
within their own personal workspace area. Unlike repository folders, workspace folders are private
to each user and exist outside the ILIAS repository. Workspace folders support sharing of contained
objects (files and blogs) with other users and can be downloaded as ZIP archives via a background
task.

Workspace objects do not have a trash: deletion within the workspace is always permanent and
immediate.

## Integrated Components

- The WorkspaceFolder component employs the following components, please consult the respective
  PRIVACY.md files:
    - [PersonalWorkspace](https://github.com/ILIAS-eLearning/ILIAS/blob/release_11/components/ILIAS/PersonalWorkspace/PRIVACY.md) – provides the workspace tree
      structure, workspace access handler, sharing table, and explorer GUI that WorkspaceFolder
      relies on.
    - [AccessControl](https://github.com/ILIAS-eLearning/ILIAS/blob/release_11/components/ILIAS/AccessControl/PRIVACY.md) – manages workspace-level permissions
      ("read", "write", "delete", "copy", "create") for workspace nodes.
    - [BackgroundTasks](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/BackgroundTasks/PRIVACY.md) – the download feature uses the
      BackgroundTasks service to collect, zip, and deliver workspace folder contents.
    - [Notes](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/Notes/PRIVACY.md) – notes are enabled on items displayed within workspace
      folders.
    - [Tagging](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/Tagging/PRIVACY.md) – tagging is available on items displayed within
      workspace folders via the common action dispatcher.
    - ILIASObject – provides the base object lifecycle (creation, update, deletion) and object
      ownership management.
    - [Refinery](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/Refinery/PRIVACY.md) – `$DIC->refinery()` validates and casts request
      parameters in `ilObjWorkspaceFolderGUI`.

## Data being stored

- **User ID**: The **user ID** of the workspace folder owner is stored as part of the primary key
  in the `wfld_user_setting` table to associate sortation preferences with a specific user.
- **Sortation preference**: A per-folder **sortation value** (integer) is stored in the
  `wfld_user_setting` table (columns: `user_id`, `wfld_id`, `sortation`). This records how the
  user prefers items within each workspace folder to be sorted (e.g., alphabetically ascending,
  by creation date). The purpose is to persist user-chosen display order across sessions.

The workspace folder object itself (title, description, creation date, owner) is managed by the
ILIASObject base service and is not stored by this component directly.

## Data being presented

- **Each user** can view the contents of their own workspace folders, including object titles,
  descriptions, types, and shared status indicators.
- **Each user** can choose a sortation order for any workspace folder they own. The selected
  sortation is applied immediately and persisted.
- **Each user** can view the "Shared Resources" tab, which shows workspace objects shared with
  them or by them. The sharing table is provided by the PersonalWorkspace component.
- **Each user** can access the "Ownership" tab to manage objects they own. This functionality
  is provided by the ILIASObject component.
- Persons who have been granted access to a shared workspace resource (via sharing link or
  password) can view the shared item's title and contents through the "read" permission on the
  shared node.

No user names are displayed directly by the WorkspaceFolder component itself. User identity
presentation (e.g., in shared resource listings) is handled by the PersonalWorkspace component.

## Data being deleted

- **When a workspace folder is deleted** by its owner: the deletion is permanent and immediate
  (the workspace has no trash). The workspace tree node, its reference, and all associated
  workspace permissions are removed. All child objects (files, blogs, sub-folders) within the
  folder are recursively deleted as well, including their data managed by the respective
  components.
- **When a workspace folder is moved (cut) to the repository**: the workspace tree node,
  reference, and workspace permissions are removed. The object is then created in the repository
  with new references and repository permissions.
- **When a user account is deleted**: the user's entire workspace tree is removed by the
  PersonalWorkspace component. However, **residual data** remains: the `wfld_user_setting` table
  records for the deleted user are **not** explicitly cleaned up by any known deletion hook. The
  sortation preferences (user ID and folder ID pairs) persist in the database as orphaned records.

## Data being exported

- The WorkspaceFolder component does not provide an export feature. Export is explicitly disabled
  in the module configuration (`export="0"`).
- A **download** feature exists that packages the contents of selected workspace folder items into
  a ZIP archive via a background task. This download is initiated by the folder owner and includes
  only files that the requesting user has "read" permission on. The ZIP file is offered as a
  one-time download and does not persist as an export artefact.
