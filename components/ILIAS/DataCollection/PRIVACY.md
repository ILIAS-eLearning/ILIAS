# DataCollection Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information via [Pull Request](docs/development/contributing.md#pull-request-to-the-repositories).**


## General Information

The DataCollection component provides a flexible, table-based data management tool within ILIAS. Users
can create custom data tables with configurable field types (text, numbers, dates, file uploads,
ILIAS object references, ratings, and more). Each table consists of records that are created and
managed by users. The component tracks record ownership and editing history.

Several table-level settings affect personal data visibility and access:

- **View own records only**: When enabled, persons with the "Read" permission can only view records
  they own; they cannot see records created by other users.
- **Edit by owner / Delete by owner**: When enabled, only the record owner can edit or delete their
  own records (among persons with the "Add Entry" permission).
- **Approval**: When enabled, newly created records require approval before becoming visible to other
  users.
- **Notifications**: When enabled, users can subscribe to notifications about record changes. These
  notifications include record field data sent via internal mail.
- **Table view role limitation**: Each table view can be restricted to specific roles, controlling
  which data columns and records are visible to different groups of users.
- **Export per table**: Each table can independently enable or disable Excel export of its records.

## Integrated Components

- The DataCollection component employs the following components, please consult the respective
  PRIVACY.md files:
    - [AccessControl](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/AccessControl/PRIVACY.md) — manages RBAC permissions for viewing, editing,
      and administrating DataCollection objects and their tables.
    - [MetaData](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/MetaData/PRIVACY.md) — stores metadata associated with DataCollection objects
      (created and updated during object lifecycle).
    - [COPage](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/COPage/PRIVACY.md) — provides the page editor used for detailed view definitions
      of table views.
    - [Notes](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/Notes/PRIVACY.md) — provides the commenting functionality on individual records
      when public comments are enabled on a table.
    - [Rating](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/Rating/PRIVACY.md) — provides the rating functionality for records that include
      a rating field type. Individual user ratings are stored by the Rating component.
    - [File](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/File/PRIVACY.md) — stores files uploaded through file upload field types in records.
    - [ResourceStorage](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/ResourceStorage/PRIVACY.md) — handles storage of uploaded files via the
      ILIAS Resource Storage Service (IRSS) stakeholder `dcl_uploads`.
    - [Mail](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/Mail/PRIVACY.md) — delivers notification emails about record changes to subscribed
      users.
    - [MediaObjects](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/MediaObjects/PRIVACY.md) — stores media objects (images, videos) uploaded
      through MOB field types in records.
    - [Export](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/Export/PRIVACY.md) — provides the XML export framework for DataCollection objects
      and XLSX content export.
    - [InfoScreen](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/InfoScreen/PRIVACY.md) — displays the info screen tab with metadata sections.
    - Notification — the DataCollection component uses the ILIAS notification service
      (`ilNotification`) to manage per-user notification subscriptions for record changes.
    - ILIASObject — the Object service stores the account which created the DataCollection object
      and its timestamps.

## Data being stored

- **Record owner (User ID)**: When a user creates a record, their **user ID** is stored as the
  record owner in the `il_dcl_record` table (`owner` column). This identifies who created the
  record and is used for ownership-based permission checks (edit by owner, delete by owner,
  view own records only).
- **Last edited by (User ID)**: When a record is updated, the **user ID** of the person who last
  edited it is stored in the `il_dcl_record` table (`last_edit_by` column). This provides an
  audit trail of who most recently modified each record.
- **Record creation date**: The **date and time** when a record was created is stored in the
  `il_dcl_record` table (`create_date` column). This provides a chronological history of
  record creation.
- **Record last update date**: The **date and time** when a record was last modified is stored
  in the `il_dcl_record` table (`last_update` column). This tracks when the most recent edit
  occurred.
- **Notification subscription (User ID)**: When a user subscribes to record change notifications,
  their **user ID** and the notification type are stored in the `il_dcl_notification` table
  (`usr_id`, `obj_id`, `setting` columns). This determines which users receive notifications
  about record creation, updates, and deletions.

## Data being presented

- **Each user** can view their own records, including the owner name, last editor name, creation
  date, and last update date, provided they have at least the "Read" permission.
- **Each user** can see records created by other users, unless the table setting "View own records
  only" is enabled, in which case only the user's own records are visible.
- **Persons with the "Read" permission** can see record data in the record list and in the detailed
  view, filtered by the table view they have access to. The visible fields depend on the table view
  configuration. The **owner** and **last edited by** standard fields display the user's name
  (via `ilUserUtil::getNamePresentation`) when included in the view.
- **Persons with the "Add Entry" permission** can create new records. If the table settings
  "Edit Perm" or "Delete Perm" are enabled, they can also edit or delete records (subject to
  the "by owner" restrictions).
- **Persons with the "Edit Content" permission** can view all records across all table views,
  edit records, and manage record ownership (change the owner of a record).
- **Persons with the "Write" permission** can:
    - view all records across all tables and all table views without restriction.
    - configure table settings that affect data visibility (view own records, edit/delete by owner,
      approval, public comments, export enabled).
    - manage table views, including role-based access restrictions.
    - view and manage field configurations, including which fields are exportable.
    - add, edit, and delete records regardless of ownership restrictions.
    - change the owner of any record.
- **Notification emails** sent to subscribed users include the name of the person who triggered
  the change (via `ilUserUtil::getNamePresentation`) and the visible field values of the affected
  record, filtered by the recipient's table view permissions.

## Data being deleted

- **When a single record is deleted** by a person with the "Write" permission (or a person with
  the "Add Entry" permission if table-level delete permissions are configured):
    - the record entry is deleted from `il_dcl_record`, including the owner user ID, last edited
      by user ID, creation date, and last update date.
    - all associated record field values are deleted from the storage location tables
      (`il_dcl_stloc1_value`, `il_dcl_stloc2_value`, `il_dcl_stloc3_value`).
    - if the record contained rating fields, the associated rating data is deleted from
      `il_rating`.
    - a deletion notification is sent to subscribed users (if notifications are enabled).
- **When a table is deleted** (by deleting all its content or through cascading object deletion):
    - all records belonging to the table are deleted, including all owner IDs, editor IDs,
      timestamps, field values, and associated ratings.
    - the table definition, field definitions, table views, and field settings are deleted.
- **When the DataCollection object is deleted** (permanently deleted from trash):
    - all tables and their records are deleted as described above.
    - all notification subscriptions for the object are deleted from `il_dcl_notification`.
    - associated metadata is deleted via the MetaData component.
- **When a user account is deleted**:
    - all notification subscriptions for that user are deleted from `il_dcl_notification`
      (via the `Services/User` `deleteUser` event listener in `ilDataCollectionAppEventListener`).
    - **Residual data**: The deleted user's **user ID** remains stored in the `owner` and
      `last_edit_by` columns of any records they created or edited. Since the account no longer
      exists, this ID can no longer be resolved to a name. Records created by a deleted user
      remain in the system.

## Data being exported

- **XLSX content export**: When the export setting is enabled on a table, persons with the "Read"
  permission can export record data as an Excel file. The export includes all fields marked as
  "exportable" for records accessible to the exporting user. If the standard fields "Owner" or
  "Last Edit By" are marked as exportable, the export includes the **login name**, **last name**,
  and **first name** of the respective users (via `ilObjUser::_lookupLogin` and
  `ilObjUser::_lookupName`).
- **XML object export**: Persons with the "Write" permission can export the entire DataCollection
  object in XML format (via the Export tab). This export includes the full table structure, field
  definitions, record IDs, and all field values. Record ownership data (owner IDs, editor IDs,
  timestamps) is **not** included in the XML export. Associated files and media objects are
  exported as dependencies. Page objects for detailed views and metadata are also included.
- There is no separate personal-data-only export mechanism.
