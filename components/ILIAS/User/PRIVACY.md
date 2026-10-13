# User Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information by submitting a [Pull Request](docs/development/contributing.md#pull-request-to-the-repositories) or, if you prefer, via the [ILIAS bug tracker](https://mantis.ilias.de). When using the bug tracker, please select the corresponding component in the **Category** field.**

## General Information

The User component is the central component for managing user accounts in ILIAS. It stores and
manages all personal profile data, authentication-related metadata, user preferences, and
custom-defined profile fields. Because virtually all other ILIAS components reference user data,
the User component is deeply entangled with the rest of the system.

Key characteristics relevant to privacy:

- **Public profile**: Users can control whether their profile is visible to other logged-in users
  or publicly accessible (if global profiles are enabled by the installation). Individual profile
  fields can be toggled independently by each user for public visibility.
- **Profile field configuration**: Persons with the appropriate permissions can configure which
  profile fields are visible, required, changeable, searchable, and exportable. This directly
  affects what personal data is collected and displayed.
- **Custom profile fields**: In addition to standard fields, custom user-defined fields (UDF) can
  be created. These store arbitrary personal data in the `usr_profile_data` table.
- **Login name history**: When enabled, previous login names are stored in a history table.
  This is controlled by an installation-wide setting.
- **Cron-based account deletion**: Four cron jobs can automatically delete user accounts based on
  inactivity, inactivation, never-logged-in status, or time-limited access. These deletions
  cascade through all stored user data.
- **Self-deletion**: If enabled by the installation, users can delete their own account.

## Integrated Components

- The User component employs the following components, please consult the respective PRIVACY.md
  files:
    - [AccessControl](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/AccessControl/PRIVACY.md) – manages permissions for user administration,
      profile editing, and role assignments.
    - [ResourceStorage](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/ResourceStorage/PRIVACY.md) – stores user profile pictures (avatars)
      via the ILIAS Resource Storage Service (IRSS).
    - Badge – the User component provides a badge provider so that users
      can display earned badges on their public profile.
    - [Mail](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/Mail/PRIVACY.md) – a default mailbox and mail options are created for each new
      user account. The mailbox and associated data are deleted when the user account is deleted.
    - [Notes](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/Notes/PRIVACY.md) – user notes are included in the personal data export.
    - [Portfolio](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/Portfolio/PRIVACY.md) – users can link their public profile to a portfolio.
      User portfolios are deleted when the user account is deleted.
    - [OrgUnit](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/OrgUnit/PRIVACY.md) – organisational unit assignments for a user are deleted
      when the user account is deleted.
    - LDAP – LDAP role group mappings are cleaned up when a user account
      is deleted.
    - Registration – the User component interacts with the
      registration component for self-registered accounts.
    - Tracking – user tracking data (`ilObjUserTracking`) is deleted when the user account is
      deleted.
    - Notification – user notification subscriptions are removed when the user account is deleted.
    - Calendar – private calendar categories are included in the personal data export.
    - ILIASObject – the User component extends `ilObject`, which stores object ownership and
      timestamps.
    - Session – user sessions are destroyed when the user account is deleted.
    - Cron – the User component defines four cron jobs for automated user account deletion.
    - TermsOfService – the User component listens to events from the TermsOfService component.

## Data being stored

The User component stores extensive personal data across several database tables:

### Main profile data (`usr_data` table)

- **Login name**: The user's unique login name, used for authentication and identification.
- **First name**: The user's first name, used for display and identification throughout the system.
- **Last name**: The user's last name, used for display and identification throughout the system.
- **Title**: An optional academic or personal title (e.g. "Dr.", "Prof.").
- **Gender**: The user's gender selection.
- **Birthday**: The user's date of birth.
- **Email address**: The user's primary email address, used for system notifications and
  communication.
- **Second email address**: An optional secondary email address.
- **Institution**: The institution the user belongs to.
- **Department**: The department within the institution.
- **Street**: The user's street address.
- **City**: The user's city of residence.
- **Zip code**: The user's postal code.
- **Country**: The user's country of residence.
- **Phone (office)**: The user's office telephone number.
- **Phone (home)**: The user's home telephone number.
- **Phone (mobile)**: The user's mobile telephone number.
- **Fax**: The user's fax number.
- **Matriculation number**: The user's matriculation or enrolment number, often used in
  university contexts.
- **Hobby**: A free-text field for the user's hobbies or interests.
- **Referral comment**: A comment about how the user was referred or registered.
- **Geo-coordinates (latitude, longitude, zoom)**: Optional location data if the user provides
  geographic coordinates in their profile.
- **Profile picture (avatar) resource ID**: A reference to the user's uploaded profile picture
  stored via the Resource Storage Service.
- **Password (encrypted)**: The user's password in encrypted form, together with the encryption
  type and salt. Passwords are never stored in plain text.

### System and authentication metadata (`usr_data` table)

- **Last login timestamp**: The date and time of the user's most recent login.
- **First login timestamp**: The date and time of the user's very first login.
- **Create date**: The date and time when the user account was created.
- **Last update timestamp**: The date and time when the user account was last modified.
- **Approve date**: The date and time when the user account was approved.
- **Agree date**: The date and time when the user agreed to the terms of service.
- **Inactivation date**: The date and time when the user account was deactivated.
- **Active status**: Whether the user account is currently active.
- **Login attempts**: The number of failed login attempts.
- **Last password change timestamp**: When the user last changed their password.
- **Password policy reset flag**: Whether the user must reset their password.
- **Client IP restriction**: An optional IP address restriction for login.
- **Authentication mode**: The authentication method used (local, LDAP, Shibboleth, etc.).
- **External account identifier**: An external account identifier used with external
  authentication providers.
- **Self-registered flag**: Whether the user registered themselves.
- **Time limit settings**: Time-based access restrictions (from, until, unlimited, owner).
- **Profile incomplete flag**: Whether the user's profile is marked as incomplete.
- **Last visited pages**: A serialized list of recently visited ILIAS pages.
- **Last profile prompt timestamp**: When the user was last prompted to complete their profile.

### Additional profile data (`usr_profile_data` table)

- **Custom profile field values**: Values for custom profile fields defined through the user administration settings
  (user-defined fields / UDF). Each entry is stored with the **user ID**, **field identifier**,
  and **value**. Multi-value fields (e.g. interests, help offered, help looked for) are also
  stored in this table.

### User preferences (`usr_pref` table)

- **User preferences**: A key-value store of user-specific settings, including:
    - **Language preference**: The user's chosen interface language.
    - **Skin/style preference**: The user's chosen visual theme.
    - **Public profile mode**: Whether the profile is disabled, visible to logged-in users, or
      globally visible (`public_profile`).
    - **Per-field visibility flags**: For each profile field, whether it is publicly visible
      (e.g. `public_firstname`, `public_lastname`, `public_email`, `public_avatar`).
    - **Time zone, date format, time format**: The user's locale preferences.
    - **Chat and notification preferences**: On-screen chat acceptance, typing broadcast, and
      notification sound settings.

### Login name history (`loginname_history` table)

- **Previous login names**: When the login name history setting is enabled, each login name
  change records the **user ID**, the **previous login name**, and the **timestamp** of the
  change. This is used to prevent reuse of login names if configured.

### Email change tokens (`usr_change_email_token` table)

- **Email change verification tokens**: When a user initiates an email address change, a
  **token**, the **user ID**, the **new email address**, a **status**, and a **creation
  timestamp** are stored. Tokens are deleted after use or when they expire.

### Form settings (`usr_form_settings` table)

- **User form preferences**: Serialized filter and display preferences for administrative
  tables and forms. Stored per **user ID** and **form identifier**.

### Cron reminder data (`usr_cron_mail_reminder` table)

- **Deletion reminder timestamps**: When a reminder email about upcoming account deletion
  due to inactivity is sent, the **user ID** and **timestamp** of the reminder are stored.

### Personal clipboard (`personal_clipboard` table)

- **Clipboard items**: Items (objects, pages) that a user has copied to their personal clipboard.
  Stored with **user ID**, **item type**, **item ID**, **title**, and **insertion timestamp**.

### Custom field definitions (`udf_definition` table)

- **Custom field definitions**: Definitions of custom user profile fields, including the
  **field identifier**, **field name**, **field type**, **field values** (for select fields),
  and configuration flags. These are not personal data per se, but define what personal data
  is collected.

## Data being presented

- **Each user** can view and edit their own profile data on the personal profile page. Which
  fields are visible and changeable depends on the profile field configuration set by persons
  with appropriate permissions.
- **Each user** controls which profile fields are publicly visible through their privacy settings.
  The public profile can be set to: disabled, visible to logged-in users, or globally visible
  (if enabled by the installation).
- **Each user** can view their own profile picture, preferences, and last-visited pages.
- **Persons with the "read" permission on the User Administration** can access the user list
  and see login names.
- **Persons with the "read_all_accounts" and "write" permissions on the User Administration**
  can see the full user table including all profile fields configured as visible, create and
  edit user accounts, and view all profile data.
- **Persons with the "cat_administrate_users" permission** (local user administration) can
  view and manage users within their administrative scope, subject to profile field
  configuration for local user administration.
- **Persons with the "create_usr" permission** can create new user accounts.
- **Persons with the "edit_roleassignment" permission** can view and modify role assignments
  for users.
- **Other logged-in users** can see profile data of other users only if:
    - the other user has enabled their public profile, AND
    - the specific fields have been marked as publicly visible by the other user.
    - The user's first name, last name, and email are only shown in search results if the
      user has enabled their public profile and the respective field visibility.
- **Anonymous visitors** can see public profile data only if the user has enabled global profile
  visibility AND the installation has enabled global profiles and the public section.
- Profile pictures are served through the Web Access Checker. A user's profile picture is
  only delivered if: the requesting user is the owner, OR the user has set the picture to public
  AND the public profile is enabled for the audience.

## Data being deleted

- **When a user account is deleted** (by a person with appropriate permissions, by a cron job,
  or by the user themselves if self-deletion is enabled):
    - All profile data in `usr_data` is deleted.
    - All additional profile field values in `usr_profile_data` are deleted.
    - All user preferences in `usr_pref` are deleted.
    - The user's profile picture is removed from the Resource Storage Service.
    - The user's personal clipboard entries in `personal_clipboard` are deleted.
    - The user's cron reminder mail entry in `usr_cron_mail_reminder` is deleted.
    - The user's sessions are destroyed.
    - The user's RBAC role assignments are removed.
    - The user's organisational unit assignments are deleted.
    - The user's mailbox and mail options are deleted.
    - The user's course memberships are cleaned up.
    - The user's tracking data is deleted.
    - The user's SCORM tracking data is removed.
    - The user's notification subscriptions are removed.
    - The user's portfolios are deleted.
    - The user's workspace tree is deleted (cascading).
    - The user's badge assignments are deleted.
    - The user's block settings are deleted.
    - The `deleteUser` event is raised so that other components can clean up their data.
    - **Residual data**: Login name history entries (`loginname_history`) are **not** deleted
      when a user account is deleted. If login name history is enabled, previous login names
      remain in the database. Additionally, form settings in `usr_form_settings` are **not**
      explicitly cleaned up during account deletion.
- **When a user changes their login name** and login name history is enabled: the previous
  login name is retained in the `loginname_history` table.
- **Cron job: Delete inactive user accounts** (`ilCronDeleteInactiveUserAccounts`): Deletes
  user accounts that have not logged in for a configurable period. Optionally sends a reminder
  email before deletion. Configurable by role.
- **Cron job: Delete inactivated user accounts** (`ilCronDeleteInactivatedUserAccounts`):
  Deletes user accounts that have been deactivated for a configurable period. Configurable
  by role.
- **Cron job: Delete never-logged-in user accounts** (`ilCronDeleteNeverLoggedInUserAccounts`):
  Deletes user accounts that were created more than a configurable number of days ago but
  have never logged in. Configurable by role.
- **Cron job: Check user accounts** (`ilUserCronCheckAccounts`): Checks time-limited user
  accounts and deactivates expired ones.
- **Self-deletion** (if enabled via the `user_delete_own_account` setting): A user can delete
  their own account through a multi-step confirmation process. The deletion follows the same
  data cleanup as administrative deletion. A notification email is sent to the user and/or
  a configured notification email address.
- **Email change tokens**: Expired tokens in `usr_change_email_token` are deleted automatically.
  Tokens are also deleted when the email change is completed or cancelled.

## Data being exported

- **Personal data export**: Users can export their personal data via the ILIAS export system.
  This export includes: profile data (all fields configured for export), user preferences/settings,
  multi-value profile fields, user notes (via the Notes component), and private calendar entries
  (via the Calendar component). The export is in XML format.
- **Administrative user export**: Persons with the "read_all_accounts" and "write" permissions
  on the User Administration can export user data in Excel (XLSX), CSV, or XML format. The
  exported fields are determined by the profile field configuration (fields marked as exportable).
  The export includes: user ID, login name, all exportable profile fields, authentication
  metadata (last login, create date, approve date, agree date, active status, time limits,
  authentication mode, external account).
- **User XML export** (`ilUserXMLWriter`): Generates XML representation of user data for
  system-level export. Includes profile fields, roles (optionally), and preferences
  (optionally).
