# Folder Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information by submitting a [Pull Request](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/contributing.md#pull-request-to-the-repositories) or, if you prefer, via the [ILIAS bug tracker](https://mantis.ilias.de). When using the bug tracker, please select the corresponding component in the **Category** field.**

## General Information

The Folder component provides repository containers for organising other objects. The Folder implementation does not have a separate folder-specific table for personal data. Personal data can nevertheless be processed through the integrated object, container page, notes, news, metadata, learning-progress and file-download functionality.

## Integrated Components

- The **Object** service stores the folder owner's **user ID** and the folder's creation and update timestamps.
- [AccessControl](../AccessControl/PRIVACY.md) controls visibility, read, write and permission-management access to the folder.
- [Container](../Container/PRIVACY.md) provides the inherited container presentation and user-specific block settings.
- [COPage](../COPage/PRIVACY.md) stores the folder's container page, which can contain arbitrary personal data and page authorship/history information.
- [InfoScreen](../InfoScreen/PRIVACY.md) presents the folder's notes, news, metadata and object information.
- [Notes](../Notes/PRIVACY.md) stores private notes that users can add to the folder's info screen.
- [News](../News/PRIVACY.md) stores news items and related feed settings that can be displayed for the folder.
- [MetaData](../MetaData/Privacy.md) provides metadata shown on the folder's info screen; author and custom metadata may contain personal data.
- The **Tracking** service records folder read events and learning-progress information for users.
- [Export](../Export/PRIVACY.md) provides the folder's XML export.
- [WebDAV](../WebDAV/PRIVACY.md) can provide protocol access to the folder when WebDAV is activated.

## Configuration

- **Learning Progress**: Learning progress is deactivated by default for folders. The folder supports the **Collection** mode, in which its status can be determined from its sub-items.
- **Internal RSS**: When internal RSS is enabled globally, users with **Write** permission can configure news and RSS settings for the folder.
- **Folder downloads**: The global `enable_download_folder` setting controls whether the folder download action is available. The action still requires **Read** permission.
- **WebDAV**: WebDAV access to folders is available only when WebDAV is activated globally.

## Data being stored

The Folder component itself does not have a separate table for personal data. The following data can be stored by its integrated components:

- **Owner and timestamps**: The Object service stores the folder owner's **user ID** and creation and update timestamps.
- **Container page content**: The COPage service stores page content, its authors and timestamps, including historic page versions. The content can contain arbitrary personal data.
- **User-specific block settings**: The inherited Container functionality can store a **user ID** together with a user's block presentation settings in `il_block_setting`.
- **Read and progress data**: When a folder is viewed, Tracking records the viewing **user ID**, read count, time spent, first access and last access in `read_event`. It can also store a user-specific learning-progress status.
- **Notes, news and metadata**: These are stored by the Notes, News and MetaData services and can contain user-authored text, authors or other personal data.

## Data being presented

- Users with **Visible** or **Read** permission can access the folder and its contained objects or page content, subject to the permissions of the respective objects.
- The info screen can present private notes to their authors, news to users with access, and metadata that may include author or other personal data.
- Users with access to learning progress can view their own progress. Users with the relevant permission can also view the progress of other users for the folder.
- Users with **Edit Permission** can view and manage the folder's permission settings, including owner information.
- When `enable_download_folder` is enabled, a user with **Read** permission can request a ZIP download. The download includes only files in folders for which that user has **Read** permission, including nested folders and files.
- WebDAV clients can access the folder's readable content when WebDAV is activated and the client has the required access.

## Data being deleted

- Moving a folder to the trash does not permanently delete its data. Permanent deletion removes the folder's object data, container page, news block settings and learning-progress settings through the object and container services.
- Permanent deletion removes the container page and its history. Data stored in Notes, News, MetaData, Tracking or contained objects follows the deletion behavior of those components; the Folder component does not delete it directly.
- The Folder and Container deletion paths do not directly delete Tracking `read_event` records for the folder. User deletion removes that user's Tracking read and write events through the Tracking service.
- User deletion removes the user's entries from `il_block_setting` through the Container service. Notes and other user-authored data follow the deletion rules of their respective components.

## Data being exported

- **XML export**: Users with **Write** permission can export the folder. The folder-specific XML contains the folder title and description; the container export dependencies can additionally include container page content and related container configuration.
- **ZIP download**: When enabled, users with **Read** permission can download readable files in the folder and its readable subfolders as a ZIP archive. The archive can contain personal data from the file contents.
