# DataProtection Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information via [Pull Request](docs/development/contributing.md#pull-request-to-the-repositories).**


## General Information

The DataProtection component manages the data protection agreement (privacy policy) that users
may be required to accept before using the ILIAS platform. It is a consumer of the
LegalDocuments service, which handles all document storage, version tracking,
and acceptance history on its behalf. The DataProtection component itself controls the
configuration, user-facing agreement flow, and consent withdrawal process.

The component supports three operating modes:

- **Once**: Users must accept the data protection agreement once. Acceptance is permanent unless
  a person with the "Write" permission on the data protection settings resets all acceptances.
- **Re-evaluate on login**: The agreement is re-evaluated on each login. If the matching document
  has changed, the user must accept the new version.
- **No acceptance required**: The data protection document is shown in the page footer but users
  are not required to explicitly accept it. In this mode, no per-user acceptance data is stored.

The "delete user on withdrawal" setting (configurable in User Administration) determines whether
a user account is automatically deleted when the user withdraws consent. For LDAP-authenticated
users, withdrawal sends a notification email to the configured recipient address instead of deleting the account.

## Integrated Components

- The DataProtection component employs the following components, please consult the respective
  PRIVACY.md files:
    - LegalDocuments – provides the core infrastructure for document management, version
      tracking, acceptance history, and the agreement/withdrawal UI. All document content and
      per-user acceptance records are stored and managed by LegalDocuments. LegalDocuments does
      not yet have a PRIVACY.md.
    - [AccessControl](https://github.com/ILIAS-eLearning/ILIAS/blob/release_11/components/ILIAS/AccessControl/PRIVACY.md) – manages permissions for accessing the
      DataProtection administration (reading settings and editing documents/settings).
    - User – provides the user object and user preferences storage
      used to persist acceptance dates and withdrawal request status. The User component also
      hosts the "delete user on withdrawal" setting in its administration interface.

## Data being stored

- **Agreement acceptance date** (`dpro_agree_date`): When a user accepts the data protection
  agreement, the **date and time** of acceptance is stored as a user preference in the `usr_pref`
  table. This records that and when the user gave consent. In "no acceptance required" mode,
  this preference is not set.
- **Withdrawal request flag** (`dpro_withdrawal_requested`): When a user initiates consent
  withdrawal, a **boolean flag** is stored as a user preference in the `usr_pref` table to
  track the ongoing withdrawal process.
- **Acceptance tracking record**: When a user accepts the data protection agreement, a record
  containing the **user ID**, a **timestamp**, the **document version ID**, and the
  **matching criteria** (as JSON) is stored in the `ldoc_acceptance_track` table. This storage
  is delegated to and managed by the LegalDocuments component.
- **Last reset date** (`dpro_last_reset_date`): When a person with the "Write" permission resets all user
  acceptances, the **date and time** of the reset is stored in the global `settings` table.
  This is not personal data but affects all users by invalidating their prior acceptances.
- **Configuration settings**: The global settings `dpro_enabled`, `dpro_validate_on_login`,
  `dpro_withdrawal_usr_deletion`, and `dpro_no_acceptance` are stored in the `settings` table.
  These are not personal data but control how personal data is collected and processed.

## Data being presented

- **Each user** can view the data protection agreement document:
    - on the login page (if the component is enabled),
    - via the page footer link (as a modal or public page),
    - during the agreement acceptance flow when prompted.
- **Each user** can view their own acceptance status. In user management, the acceptance date
  and the accepted document version are displayed for each user.
- **Persons with the "Read" permission** on the DataProtection administration object can view
  the current settings and document configuration.
- **Persons with the "Write" permission** on the DataProtection administration object can:
    - edit settings (enable/disable, select operating mode),
    - manage data protection documents (create, edit, delete, reorder),
    - reset all user acceptances (forcing all users to re-accept),
    - view the acceptance history, which includes **user ID**, **login name**, **first name**,
      **last name**, **email** (for search), and **acceptance timestamp** for each acceptance
      record. Access to the acceptance history additionally requires the permission to read user
      administration data.

## Data being deleted

- **When a user withdraws consent**: The user's **acceptance date** (`dpro_agree_date`) is
  cleared and the **withdrawal request flag** (`dpro_withdrawal_requested`) is set to `false`.
  If the "delete user on withdrawal" setting is active, the entire **user account** is deleted.
  For LDAP users, the account is not deleted; instead, a notification email is sent to the
  configured notification email address. After withdrawal, a `withdraw` event is raised by the
  DataProtection component.
- **When a person with the "Write" permission resets all acceptances**: All `dpro_agree_date` user preferences are
  deleted from the `usr_pref` table (except for the anonymous and system user accounts). This
  forces all users to re-accept the data protection agreement on their next login. The acceptance
  tracking records in `ldoc_acceptance_track` are **not** deleted by a reset -- the historical
  record is preserved.
- **When a user account is deleted**: The user's preferences (`dpro_agree_date`,
  `dpro_withdrawal_requested`) are removed together with all other user preferences. The
  acceptance tracking records in `ldoc_acceptance_track` that reference the deleted user's ID
  are managed by the LegalDocuments component.
- **When a data protection document is deleted**: If all documents are deleted, the DataProtection
  component automatically disables itself (sets `dpro_enabled` to `false`). Document deletion
  and its impact on acceptance history is managed by the LegalDocuments component.

## Data being exported

- The DataProtection component does not provide any dedicated export functionality for personal
  data. There is no XML, CSV, or file-based export of acceptance records or user consent data.
- The acceptance history can be viewed in the administration interface but cannot be exported
  as a file.
