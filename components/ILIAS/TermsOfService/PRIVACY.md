# TermsOfService Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information by submitting a [Pull Request](docs/development/contributing.md#pull-request-to-the-repositories) or, if you prefer, via the [ILIAS bug tracker](https://mantis.ilias.de). When using the bug tracker, please select the corresponding component in the **Category** field.**

## General Information

The TermsOfService component manages the Terms of Service (user agreement) that users must accept
before using ILIAS. It acts as a consumer of the [LegalDocuments](https://github.com/ILIAS-eLearning/ILIAS/tree/trunk/components/ILIAS/LegalDocuments) component,
which provides the underlying infrastructure for document management, acceptance tracking, and
withdrawal handling.

The component can be **enabled or disabled** via a global setting (`tos_status`). When disabled,
no acceptance is required and no personal data is recorded by this component. When enabled, users
must accept the current Terms of Service document; their acceptance is tracked in the LegalDocuments
acceptance history.

A **re-evaluation on login** setting (`tos_reevaluate_on_login`) can be activated so that users
are re-checked on every login and may need to re-accept if the document has changed.

A **delete user on withdrawal** setting (`tos_withdrawal_usr_deletion`) controls whether a user
account is automatically deleted when the user withdraws their consent. This setting is configured
in the User Administration, not in the TermsOfService administration itself.

## Integrated Components

- The TermsOfService component employs the following components, please consult the respective
  PRIVACY.md files:
    - [AccessControl](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/AccessControl/PRIVACY.md) – manages permissions for the TermsOfService
      administration area (read, write, edit_permission).
    - LegalDocuments – provides the core infrastructure for document management, acceptance
      tracking (table `ldoc_acceptance_track`), document versioning (table `ldoc_versions`), and
      the consent withdrawal process. The TermsOfService component is a consumer of LegalDocuments.
    - User – the TermsOfService component reads and writes the `agree_date` field in the
      `usr_data` table. It also stores a user preference (`consent_withdrawal_requested`) and
      listens to User component events. On withdrawal with deletion enabled, the user account
      is deleted.

## Data being stored

The TermsOfService component itself stores the following personal data. Additional data is stored
by the LegalDocuments component on behalf of TermsOfService (see Integrated Components).

- **Agreement date** (`agree_date` in `usr_data`): When a user accepts the Terms of Service, the
  **date and time of acceptance** is stored in the user's record. This timestamp documents when
  the user gave consent. The field is set to `NULL` when a user withdraws consent or when an
  acceptance reset is performed.
- **Withdrawal request flag** (`consent_withdrawal_requested` user preference): A **boolean flag**
  indicating whether the user has initiated a consent withdrawal process. This flag is stored as a
  user preference via `writePref` and is set to `true` when the withdrawal process begins and
  back to `false` when it completes or is cancelled.
- **Acceptance history** (via LegalDocuments, table `ldoc_acceptance_track`): When a user accepts
  a Terms of Service document, the LegalDocuments component stores the **user ID**, the
  **document version ID**, the **timestamp of acceptance**, and the **criteria** that matched the
  user at the time of acceptance. This data is stored on behalf of the TermsOfService consumer.
- **Last reset date** (`tos_last_reset` in `settings` table): When a person with the "Write"
  permission resets all acceptances, the **date and time of the reset** is stored as a global
  setting. This is not per-user personal data but affects all users.

## Data being presented

- **Each user** can view their own Terms of Service agreement status:
    - The current Terms of Service document text is shown in the page footer and on a dedicated
      public page.
    - During login, if re-evaluation is enabled, the user is shown the document and prompted to
      accept it if their acceptance is outdated.
    - The user's own **agreement date** is displayed in the user management interface.
- **Persons with the "Read" permission** on the TermsOfService administration object can:
    - view the Terms of Service settings (enabled status, re-evaluation on login setting).
    - view the configured Terms of Service documents and their criteria.
    - view the last reset date.
- **Persons with the "Write" permission** on the TermsOfService administration object can:
    - edit the Terms of Service settings.
    - manage documents (create, edit, delete, reorder).
    - reset all user acceptances (setting all `agree_date` values to `NULL`).
- **Persons with "Read" access to the User Administration** can additionally view the
  **acceptance history**, which shows for each acceptance record: **login name**, **first name**,
  **last name**, **acceptance date**, the **document title**, and the **matching criteria** at the
  time of acceptance.

## Data being deleted

- **When a user withdraws consent** (via the footer withdrawal button):
    - The user's **agreement date** (`agree_date`) is set to `NULL`.
    - The **withdrawal request flag** (`consent_withdrawal_requested`) is reset to `false`.
    - If the **delete user on withdrawal** setting is enabled, the entire user account is deleted.
    - If the user is an LDAP user, instead of account deletion, an email is sent to the
      configured notification email address containing the user's **full name**, **login**, and **external
      account name**.
    - The TermsOfService component raises a `withdraw` event via the event handler.
    - **Residual data**: The acceptance history records in `ldoc_acceptance_track` are **not**
      deleted upon withdrawal. They persist with the user ID even after consent is withdrawn.
- **When all acceptances are reset** by a person with the "Write" permission:
    - All users' `agree_date` values in `usr_data` are set to `NULL` (except system and anonymous
      accounts).
    - The `tos_last_reset` date is updated.
    - **Residual data**: Existing acceptance history records in `ldoc_acceptance_track` remain
      unchanged.
- **When a user account is deleted**:
    - The `agree_date` field is removed with the user record in `usr_data`.
    - User preferences (including `consent_withdrawal_requested`) are deleted with the user account.
    - **Residual data**: Acceptance history records in `ldoc_acceptance_track` may persist with the
      now-orphaned user ID. The history table shows "deleted" for users whose accounts no longer
      exist.

## Data being exported

- There is no dedicated export functionality in the TermsOfService component.
- The **agreement date** (`agree_date`) may be included in user data exports performed through
  the User component (XML export, CSV export).
