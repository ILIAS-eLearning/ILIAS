# Folder Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information by submitting a [Pull Request](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/contributing.md#pull-request-to-the-repositories) or, if you prefer, via the [ILIAS bug tracker](https://mantis.ilias.de). When using the bug tracker, please select the corresponding component in the **Category** field.**

## General Information
The Folder component is a container object used to organise content within the ILIAS repository. Folders can be created inside courses, groups, and other folders. The Folder component itself does not maintain its own database tables and does not store personal data directly. All personal data processing is delegated to integrated components such as the Tracking component (learning progress) and the Notes component (personal notes).

Learning progress tracking for folders is disabled by default. When enabled (mode "Collection"), the Tracking component records access data on behalf of the Folder component. See the Tracking component's privacy documentation for details.

## Integrated Components
- The Folder component employs the following components, please consult the respective PRIVACY.md files:
    - Container — the Folder extends the Container class for repository structure, sorting settings, and content presentation.
    - [AccessControl](https://github.com/marvimarv/ILIAS/blob/d77bfedfc6601f24276b5aa31143af006de4e384/components/ILIAS/AccessControl/PRIVACY.md) — manages permissions for folder access, editing, and permission management.
    - [InfoScreen](https://github.com/marvimarv/ILIAS/blob/d77bfedfc6601f24276b5aa31143af006de4e384/components/ILIAS/InfoScreen/PRIVACY.md) — provides the info tab for the folder, including metadata display and news.
    - [Notes](https://github.com/marvimarv/ILIAS/blob/d77bfedfc6601f24276b5aa31143af006de4e384/components/ILIAS/Notes/PRIVACY.md) — users can add private notes to the folder via the info screen.
    - [COPage](https://github.com/marvimarv/ILIAS/blob/d77bfedfc6601f24276b5aa31143af006de4e384/components/ILIAS/COPage/PRIVACY.md) — the folder's container page content is rendered and edited via `forwardToPageObject()`; page content may contain personal data added by authors.
    - [News](https://github.com/marvimarv/ILIAS/blob/d77bfedfc6601f24276b5aa31143af006de4e384/components/ILIAS/News/PRIVACY.md) — the folder's info screen can display news items; persons with the "Write" permission can configure RSS feed settings when enabled.
    - [MetaData](https://github.com/marvimarv/ILIAS/blob/d77bfedfc6601f24276b5aa31143af006de4e384/components/ILIAS/MetaData/Privacy.md) — metadata sections are displayed on the info screen.
    - [Export](https://github.com/marvimarv/ILIAS/blob/d77bfedfc6601f24276b5aa31143af006de4e384/components/ILIAS/Export/PRIVACY.md) — provides XML export functionality for the folder.
    - [WebDAV](https://github.com/marvimarv/ILIAS/blob/d77bfedfc6601f24276b5aa31143af006de4e384/components/ILIAS/WebDAV/PRIVACY.md) — when WebDAV is activated, folders can be accessed via the WebDAV protocol.
    - [DidacticTemplate](https://github.com/marvimarv/ILIAS/blob/d77bfedfc6601f24276b5aa31143af006de4e384/components/ILIAS/DidacticTemplate/PRIVACY.md) — supports didactic template selection for the folder.
    - ILIASObject — the Object service stores the account which created a folder and its timestamps.
    - Tracking — the Folder component triggers learning progress tracking when a folder is viewed. Learning progress is disabled by default but can be enabled in collection mode.
    - ContentStyle — manages the content style settings for the folder's container page.
    - Tree — stores the folder's structural position in the repository tree (`ilTree`, injected via `$DIC->repositoryTree()`); contains no personal data.

## Configuration
- **Learning Progress**: The folder can be configured to determine its learning progress status based on its sub-items (Collection mode).

## Data being stored
The Folder component does not store personal data in its own database tables. The following data is stored by integrated components on behalf of the Folder component:

- **Learning progress data**: When learning progress is enabled for a folder and a user views the folder, the Tracking component records the **user ID**, **access timestamps**, and **progress status** via `ilLearningProgress::_tracProgress()`. This data is managed by the Tracking component.
- **Sorting settings**: The Container component stores sorting configuration for the folder in the `container_sorting_set` table. This table contains only object-level settings (sort mode, sort direction) and no personal data.
- **Block configuration**: User IDs are stored in `il_block_setting` to persist user-specific block configurations (handled by the **Container** service). *(To be verified against source code.)*

## Data being presented
- **Each user** can view the folder's content, info screen, and metadata if they have the "Read" permission.
- **Each user** can view their own learning progress for the folder (if learning progress is enabled).
- **Persons with the "Write" permission** can:
    - edit the folder's settings (title, description, presentation options, sorting).
    - manage the folder's content (add, move, delete objects within the folder).
    - configure news and RSS settings on the info screen (when RSS is globally enabled).
    - access the folder's XML export.
- **Each user** with the "Read" permission can download the folder's contents as a ZIP archive, if the "enable_download_folder" object setting is active.
- **Persons with access to learning progress** (as determined by `ilLearningProgressAccess::checkAccess()`) can view the learning progress of other users for this folder via the Learning Progress tab.
- **Persons with the "Edit Permission" permission** can view and manage the folder's permission settings, including the owner information.

## Data being deleted
- **When a folder is deleted from the repository**: the folder is moved to the trash. At this stage, the folder object and its associated data (including sorting settings and any learning progress records) remain in the system.
- **When a folder is deleted from trash** (permanently deleted): the folder object and all associated Container data (sorting settings, container page) are permanently removed. Learning progress data associated with the folder is handled by the Tracking component's deletion mechanisms.
- **When a user account is deleted**: learning progress records associated with the deleted user are handled by the Tracking component. User-specific settings in `il_block_setting` are removed. The Folder component itself does not need to perform further user-specific data deletion because it does not store personal data in its own tables.

## Data being exported
- The folder's XML export (accessible to persons with the "Write" permission) contains the folder's **title**, **description**, and **sorting settings**. No personal data is included in the export.
- **ZIP download**: When the "enable_download_folder" setting is active, each user with the "Read" permission can download the folder's contents as a ZIP archive. The archive contains the files themselves, which may include personal data depending on the file content.
