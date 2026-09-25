# Accessibility Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information by submitting a [Pull Request](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/contributing.md#pull-request-to-the-repositories) or, if you prefer, via the [ILIAS bug tracker](https://mantis.ilias.de). When using the bug tracker, please select the corresponding component in the **Category** field.**


## General Information

The Accessibility component manages the accessibility control concept, which is a publicly accessible page that explains an institution's accessibility statement. Administrators can create and manage accessibility control concept documents and assign criteria (e.g., user language) to determine which document is displayed to a particular user. The component also manages a configurable list of accessibility support contacts (stored as ILIAS user logins) whose email addresses are displayed when no matching document is found. The component is enabled or disabled globally via a system setting (`acc_ctrl_cpt_status`).

## Integrated Components

The Accessibility component uses the User component to look up user IDs, login names, and email addresses when resolving accessibility support contacts and when evaluating document criteria against the current user's profile (e.g., user language).

## Data being stored

The Accessibility component stores the following personal data:

- **`acc_documents` table** — For each accessibility document created or modified, the user ID of the creating administrator is stored in `owner_usr_id`, and the user ID of the last modifying administrator is stored in `last_modified_usr_id`. These are stored alongside document metadata (`creation_ts`, `modification_ts`) for audit purposes.

- **`acc_criterion_to_doc` table** — For each criterion assignment attached to a document, the user ID of the administrator who created the assignment is stored in `owner_usr_id`, and the user ID of the last modifier is stored in `last_modified_usr_id`.

- **System settings** — A comma-separated list of ILIAS user logins is stored under the setting key `accessibility_support_contacts` (via `ilSetting`). These logins are used to resolve email addresses of support contacts on demand.

## Data being presented

- The accessibility control concept page is publicly accessible (including to anonymous users) and displays the content of the matched document. No personal data of the viewing user is exposed on this page.
- When no matching document is found, the email addresses of the configured accessibility support contacts (resolved from the stored user logins via `ilObjUser::_lookupEmail`) are rendered as `mailto:` links on the public page, making those email addresses visible to all users including anonymous visitors.
- In the administration interface, persons with "read" permission on the Accessibility settings object can view the list of documents. The stored `owner_usr_id` and `last_modified_usr_id` values are present in the database records but are not directly displayed in the document management table GUI.
- The list of accessibility support contact logins is visible in the administration form to persons with "read" permission on the Accessibility settings object.

## Data being deleted

- Accessibility documents (including their criterion assignments in `acc_criterion_to_doc`) can be deleted by a person with "write" permission on the Accessibility settings object through the document management interface. Deletion of a document cascades to all associated criterion assignments.
- The accessibility support contacts list can be cleared by a person with "write" permission on the Accessibility settings object by saving an empty value in the settings form.
- There is no automatic expiry or deletion of stored data, and no account-deletion hook that removes `owner_usr_id` or `last_modified_usr_id` references from the `acc_documents` or `acc_criterion_to_doc` tables.

## Data being exported

The Accessibility component does not provide any export functionality for the data it stores. Documents and their criterion assignments cannot be exported through the component's interface.
