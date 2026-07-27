# Registration Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information by submitting a [Pull Request](docs/development/contributing.md#pull-request-to-the-repositories) or, if you prefer, via the [ILIAS bug tracker](https://mantis.ilias.de). When using the bug tracker, please select the corresponding component in the **Category** field.**

## General Information

The Registration component handles the self-registration process that allows new users to create
their own accounts in an ILIAS installation. It supports multiple registration modes that affect
how personal data is processed: disabled registration, direct registration, registration with
administrative approval, registration with email confirmation (dual opt-in), and registration
exclusively via registration codes.

The component does **not** store user profile data itself. During registration, the user-entered
profile data (name, email, etc.) is delegated to the User component for storage. However, the
Registration component does manage its own data in two areas: **registration codes** (an
administrative tool for controlling access) and **pending dual opt-in registrations** (temporary
data for the email confirmation workflow).

Registration settings such as registration type, allowed email domains, approval recipients, and
hash lifetime are stored in the global ILIAS settings (`ilSetting`) and do not contain personal
data beyond the user IDs of configured approval recipients.

## Integrated Components

- The Registration component employs the following components, please consult the respective
  PRIVACY.md files:
    - [AccessControl](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/AccessControl/PRIVACY.md) — manages permissions for the registration
      settings administration and registration code management.
    - [Mail](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/Mail/PRIVACY.md) — the Registration component sends notification emails to
      approval recipients and confirmation/welcome emails to newly registered users.
    - User — the Registration component creates new user accounts via the User component. All
      user profile data (name, email, login, password, language preference) is stored by the
      User component. User preferences (contact settings, chat settings) are also written via
      the User component.
    - LegalDocuments — the Registration component integrates with the Legal Documents service
      to present and record agreement to terms of service during self-registration.

## Data being stored

- **Registration code**: When a person with the "Write" permission generates registration codes,
  each **code string** is stored in the `reg_registration_codes` table together with a
  **generation timestamp**, an assigned **global role ID**, optional **local role IDs**, an
  **access limitation mode** and its parameters, and flags indicating whether the code is valid
  for registration and/or account extension. These codes do not contain personal data themselves,
  but they are linked to roles and access policies.
- **Registration code usage timestamp**: When a user redeems a registration code during
  self-registration, the **timestamp** of usage is written to the `used` column of the
  `reg_registration_codes` table. The code itself does not record **which** user redeemed it.
- **Pending registration user ID**: When dual opt-in (email confirmation) registration is active,
  the **user ID** of the newly created (but not yet activated) user account is stored in the
  `reg_dual_opt_in` table to track the pending confirmation.
- **Confirmation hash**: A unique **hash value** is generated and stored in the
  `reg_dual_opt_in` table alongside the user ID. This hash is sent to the user's email address
  as part of the confirmation link.
- **Pending registration creation timestamp**: The **creation date** (as Unix timestamp) of
  the pending registration is stored in the `reg_dual_opt_in` table to determine whether the
  confirmation link has expired.
- **Email-to-role assignments**: The `reg_er_assignments` table stores mappings between
  **email domain patterns** and **role IDs** for automatic role assignment during registration.
  These are administrative configuration data and do not contain personal user data.
- **Role access limitations**: The `reg_access_limit` table stores **access limitation modes**
  (absolute date, relative duration, or unlimited) per role. These are administrative
  configuration data and do not contain personal user data.
- **Approval recipient user IDs**: The **user IDs** of persons designated to receive
  notification emails when new users register are stored in the global ILIAS settings
  (key `approve_recipient`).

## Data being presented

- **Each user** is presented with their own registration form data during the self-registration
  process. After successful registration, a confirmation message displays the user's **full
  name**.
- **Persons with the "Read" permission** on the Registration Administration can view:
    - all registration settings, including configured approval recipient login names.
    - the registration codes table showing **code strings**, **generation timestamps**,
      **usage timestamps**, assigned **roles**, and **access limitations**.
- **Persons with the "Write" permission** on the Registration Administration can additionally:
    - generate new registration codes.
    - delete registration codes.
    - export registration codes as a text file.
    - modify registration settings, role assignments, email domain mappings, and access
      limitations.
- **Approval recipients** (persons configured in the registration settings) receive email
  notifications containing the new user's **profile data** (as provided by the User component's
  `getProfileAsString` method) when a new user registers.

## Data being deleted

- **When a registration code is deleted** by a person with the "Write" permission on the
  Registration Administration: the code record is deleted from the `reg_registration_codes`
  table, including the code string, generation timestamp, usage timestamp, and all associated
  role and access limitation data.
- **When a dual opt-in confirmation succeeds**: the pending registration record is deleted from
  the `reg_dual_opt_in` table after the user account is activated. The hash value and creation
  timestamp are removed.
- **When a dual opt-in confirmation link expires**: expired pending registrations are identified
  by comparing the creation timestamp against the configured hash lifetime. The
  `DualOptInServiceImpl::deleteExpiredUserObjects` method deletes both the pending registration
  record from `reg_dual_opt_in` and the associated (still inactive) user account.
- **When a user account is deleted**: the Registration component listens for the
  `Services/User::deleteUser` event and deletes any associated pending registration records
  from the `reg_dual_opt_in` table via `PendingRegistrationDatabaseRepository::deleteByUserId`.
- **Registration codes are not automatically deleted** when a user account is deleted. Since
  the `reg_registration_codes` table does not store which user redeemed a code, there is no
  personal data linkage to clean up.

## Data being exported

- **Persons with the "Write" permission** on the Registration Administration can export
  registration codes as a plain-text file. The exported data contains only the **code strings**
  and does not include personal user data.
- There is no export functionality for pending dual opt-in registration data.
- User profile data entered during registration is stored and can be exported via the User
  component; the Registration component itself does not provide a separate user data export.
