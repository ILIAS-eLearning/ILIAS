# ILIASObject Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information by submitting a [Pull Request](docs/development/contributing.md#pull-request-to-the-repositories) or, if you prefer, via the [ILIAS bug tracker](https://mantis.ilias.de). When using the bug tracker, please select the corresponding component in the **Category** field.**

## General Information

The ILIASObject component provides the foundational object layer for all repository objects in ILIAS. Every object created in ILIAS (courses, groups, files, forums, etc.) is represented by a record managed through this component. As such, ILIASObject stores core metadata for every object, including the account that created it and when it was created or last modified.

Because this component underpins all repository objects, it is deeply entangled with many other components. The personal data stored here (owner user ID, timestamps, deletion actor) accompanies every object throughout its entire lifecycle: creation, modification, trash, and permanent deletion. A deletion log (`object_data_del`) retains non-personal object metadata (title, type, description) even after permanent deletion, enabling administrative auditing.

The copy wizard temporarily stores the user ID of the account initiating a copy operation in the `copy_wizard_options` table. This data is removed when the copy process completes.

## Integrated Components

- The ILIASObject component employs the following components, please consult the respective PRIVACY.md files:
    - [AccessControl](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/AccessControl/PRIVACY.md) – manages permission checks for all object operations (create, read, write, delete, copy, edit_permissions).
    - [MetaData](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/MetaData/PRIVACY.md) – stores and manages metadata (e.g. keywords, descriptions) associated with objects. Metadata is created and deleted through the ILIASObject lifecycle.
    - [ResourceStorage](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/ResourceStorage/PRIVACY.md) – stores tile images and custom icons for objects via the ILIAS Resource Storage Service (IRSS).
    - User – user names are resolved for display of the object owner. User IDs are stored as the owner of objects and as the actor who moved objects to trash.
    - [News](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/News/PRIVACY.md) – news items associated with an object are created and deleted as part of the object lifecycle.
    - [AdvancedMetaData](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/AdvancedMetaData/PRIVACY.md) – advanced metadata values attached to objects are cloned during copy operations and deleted during permanent deletion.
    - [DidacticTemplate](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/DidacticTemplate/PRIVACY.md) – didactic template assignments per object are managed during the object lifecycle.
    - [OrgUnit](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/OrgUnit/PRIVACY.md) – organisational unit permission settings are initialized for new objects when applicable.
    - [WebDAV](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/WebDAV/PRIVACY.md) – WebDAV properties (`dav_property` table) are cloned during copy and deleted during permanent object deletion. The lock user is displayed in the object list.
    - [Tagging](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/Tagging/PRIVACY.md) – user tags are loaded and displayed in the object list GUI.
    - Tracking – read and write events are recorded via `ilChangeEvent` when objects are viewed or created. Learning progress settings (`ilLPObjSettings`) are deleted during permanent object deletion.
    - ECSInterface – ECS import data is deleted during permanent object deletion.

## Data being stored

- **Owner user ID**: When an object is created, the **user ID** of the creating account is stored as the object owner in the `object_data` table (`owner` column). This links every repository object to the account that created it.
- **Creation timestamp**: The **date and time** of object creation is stored in the `object_data` table (`create_date` column) to record when the object was created.
- **Last update timestamp**: The **date and time** of the most recent modification is stored in the `object_data` table (`last_update` column) and is updated whenever the object or its owner is changed.
- **Deleted-by user ID**: When an object reference is moved to the trash, the **user ID** of the account that performed the deletion is stored in the `object_reference` table (`deleted_by` column) together with a deletion timestamp (`deleted` column). This records who moved the object to trash.
- **Copy wizard owner user ID**: When a copy operation is initiated, the **user ID** of the account requesting the copy is temporarily stored in the `copy_wizard_options` table. This is used to authorize subsequent copy processing steps and is removed when the copy operation completes.

## Data being presented

- **Each user** can see the title, description, and type of objects they have access to. Creation and modification timestamps are available through system metadata.
- **Persons with the "read" or "visible" permission** on an object can see its title, description, and type in the repository or personal workspace.
- **Persons with the "write" permission** on an object can modify the object's title, description, and settings. The owner of an object is typically not displayed in the standard editing interface but can be looked up programmatically.
- **Persons with the "delete" permission** can move objects to the trash. The identity of the person who moved an object to the trash is stored but not typically displayed to other users in the standard interface.
- When **WebDAV** is active and an object is locked, the **login name** of the lock holder is displayed to authenticated users in the object list.

## Data being deleted

- **When an object is moved to the trash** by a person with the "delete" permission: the object reference is marked as deleted in the `object_reference` table. The **deleted-by user ID** and **deletion timestamp** are stored. The object data in `object_data` is preserved. The object can be restored from the trash, which clears the deletion metadata.
- **When an object is permanently deleted from the trash**: if this is the last remaining reference to the object, the following data is permanently deleted:
    - the `object_data` record (including owner user ID, creation timestamp, last update timestamp)
    - the `object_description` record (long description)
    - the `object_reference` record (including deleted-by user ID)
    - all translations from the `object_translation` table
    - associated WebDAV properties from the `dav_property` table
    - associated news items, didactic template settings, ECS import data, advanced metadata values, and learning progress settings
    - **Residual data**: a record is written to the `object_data_del` table containing the object ID, title, type, description, and a timestamp. This deletion log does not contain user IDs but retains object metadata for administrative auditing purposes.
- **When an object still has other references**: only the specific `object_reference` entry is deleted. The `object_data` record (including the owner user ID) is preserved as long as at least one reference remains.
- **When a user account is deleted**: the **owner** field in `object_data` for objects created by that user is **not** automatically cleared. The user ID remains stored but can no longer be resolved to a name, as the user account no longer exists.
- **When copy wizard data is cleaned up**: temporary data in the `copy_wizard_options` table, including the copy initiator's user ID, is deleted after the copy process completes.

## Data being exported

- The ILIASObject component provides a data set class (`ilObjectDataSet`) for export of translation data (`object_translation` table) and service settings. This export contains object titles, descriptions, and language codes but **no personal user data** (no user IDs, no owner information, no timestamps).
- There is no dedicated export that includes the owner user ID or other personal data stored by this component.
