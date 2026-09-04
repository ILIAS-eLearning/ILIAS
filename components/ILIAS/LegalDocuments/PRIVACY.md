# LegalDocuments Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information by submitting a [Pull Request](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/contributing.md#pull-request-to-the-repositories) or, if you prefer, via the [ILIAS bug tracker](https://mantis.ilias.de). When using the bug tracker, please select the corresponding component in the **Category** field.**


## General Information

The LegalDocuments component is a framework service that provides infrastructure for managing legal documents (such as terms of service or privacy policies) across ILIAS. It allows consumer components to define documents with configurable display criteria, track user acceptance, and support consent withdrawal. The component records which user accepted which version of a document and when. Acceptance tracking data persists even after a document is updated or deleted — historical entries in `ldoc_acceptance_track` remain and are not automatically removed when a document is deleted. If the consumer configures the `deleteUserOnWithdrawal` setting, withdrawing consent results in the full deletion of the user account.

## Integrated Components

The LegalDocuments component reads user account data from the User component. When displaying the acceptance history, it joins `ldoc_acceptance_track` with the `usr_data` table to resolve `login`, `firstname`, `lastname`, and `email` for filtering and display purposes.

## Data being stored

The component stores data in four database tables:

- **`ldoc_documents`**: Stores the content and metadata of each legal document, including the user ID of the creator (`owner_usr_id`) and the user ID of the last editor (`last_modified_usr_id`), together with timestamps (`creation_ts`, `modification_ts`). These are user IDs of persons who manage documents (typically administrators), not end users.

- **`ldoc_criteria`**: Stores display criteria assigned to documents, including the user ID of the creator (`owner_usr_id`) and last modifier (`last_modified_usr_id`), together with timestamps (`assigned_ts`, `modification_ts`). Same scope as above — only administrative user IDs.

- **`ldoc_versions`**: Stores point-in-time snapshots of document content (hash, title, text, type) created when a user accepts a document version for the first time. No direct personal data is stored here; versions are referenced by the tracking table.

- **`ldoc_acceptance_track`**: Stores one record per user acceptance event, containing: `usr_id` (the accepting user's ID), `tosv_id` (reference to the accepted document version), `ts` (Unix timestamp of acceptance), and `criteria` (a JSON-encoded snapshot of the criteria that were active at the time of acceptance). This is the primary table containing end-user personal data.

## Data being presented

The acceptance history table is presented in the administration area of each consumer of this component. It displays, per acceptance record: the date of acceptance, the accepting user's login, first name, last name, the accepted document version (as a modal preview), and the active criteria at the time of acceptance. The table can be filtered by user name, login, or email, and by a date range. This view is accessible only to persons with "read" permission on the User Administration folder (`USER_FOLDER_ID`). If a user account has been deleted, the login column displays a placeholder text ("deleted") while the acceptance record itself remains.

## Data being deleted

- **Document deletion**: A person with "write" permission on the User Administration folder can delete legal documents via the administration interface. Deleting a document removes it from `ldoc_documents` and cleans up orphaned entries in `ldoc_criteria`. Entries in `ldoc_versions` and `ldoc_acceptance_track` that reference the deleted document are not automatically removed; historical tracking records remain.

- **Consent withdrawal**: A user may withdraw their consent through the withdrawal process provided by the consumer component. Upon withdrawal, the user's agreement date is reset and the withdrawal-requested flag is cleared. Existing acceptance records in `ldoc_acceptance_track` are not deleted by this action. If the consumer has enabled the `deleteUserOnWithdrawal` setting, the user's account is deleted entirely upon withdrawal. LDAP-managed users are not deleted but instead trigger an email notification to the administrator.

- **No automatic expiry**: There is no time-based automatic deletion of acceptance tracking data.

## Data being exported

The LegalDocuments component does not provide any export functionality for personal data.
