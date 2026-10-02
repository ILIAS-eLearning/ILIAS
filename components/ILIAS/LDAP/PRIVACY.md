# LDAP Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information by submitting a [Pull Request](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/contributing.md#pull-request-to-the-repositories) or, if you prefer, via the [ILIAS bug tracker](https://mantis.ilias.de). When using the bug tracker, please select the corresponding component in the **Category** field.**


## General Information

The LDAP component provides integration with external LDAP directory servers for user authentication
and account synchronisation. It allows ILIAS to authenticate users against one or more LDAP servers,
automatically create or update ILIAS user accounts based on LDAP attributes, and assign roles based
on LDAP group membership or attribute values.

The LDAP component itself does not store end-user personal data directly. Instead, it stores
**server configuration** (including LDAP bind credentials), **attribute mapping rules**, **role
assignment rules**, and **role-to-group mapping settings**. All actual user profile data that
is synchronised from LDAP is written to the ILIAS user account via the User component.

User synchronisation can occur in two ways: **on login** (when the user authenticates) or via a
**cron job** (`ldap_sync`) that periodically synchronises all LDAP users. Both mechanisms use
the `ilUserImportParser` from the User component to create or update ILIAS user accounts. The cron
job can also **deactivate** ILIAS user accounts that no longer exist in the LDAP directory.

The LDAP component provides a plugin slot (`LDAPHook`) that allows third-party plugins to
influence role assignment decisions.

## Integrated Components

- The LDAP component employs the following components, please consult the respective PRIVACY.md files:
    - [AccessControl](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/components/ILIAS/AccessControl/PRIVACY.md) – manages permissions for LDAP server
      administration and controls who can view and modify LDAP configuration settings.
    - User – the LDAP component delegates all user account creation and update operations to
      the User component via `ilUserImportParser`. Synchronised LDAP attributes (e.g., name,
      email, institution) are written to ILIAS user profiles by the User component.
    - Cron – the LDAP component registers a cron job (`ldap_sync`) for periodic user
      synchronisation. The Cron service manages job scheduling and execution.

## Data being stored

The LDAP component stores configuration data in four database tables. While the core configuration
is not personal data, two aspects involve credentials or affect personal data processing:

- **LDAP bind user credentials** (`bind_user`, `bind_pass`): The `ldap_server_settings` table
  stores the **distinguished name** and **password** of the LDAP service account used to bind to
  the LDAP directory. These are technical service account credentials, not end-user credentials.
  A separate pair (`role_bind_dn`, `role_bind_pass`) is stored when role synchronisation uses
  a different bind account.
- **Attribute mapping rules**: The `ldap_attribute_mapping` table stores mappings between LDAP
  attribute names and ILIAS user profile fields (e.g., mapping `givenName` to `firstname`). These
  rules determine which personal data fields are synchronised from LDAP into ILIAS user accounts.
  The `perform_update` flag controls whether each mapping is applied on subsequent logins.
- **Role assignment rules**: The `ldap_role_assignments` table stores rules that map LDAP group
  memberships or attribute values to ILIAS roles. Each rule references a **role ID**, an LDAP
  **distinguished name** or **attribute name/value pair**, and flags controlling whether the
  assignment should be updated or removed on subsequent logins.
- **Role-to-group mapping settings**: The `ldap_rg_mapping` table stores bidirectional mappings
  between ILIAS roles and LDAP groups. When a user is assigned to or deassigned from an ILIAS
  role, the LDAP component can propagate this change back to the LDAP directory. Each mapping
  references an LDAP **server ID**, **group DN**, **member attribute**, and an ILIAS **role ID**.

The LDAP component does **not** store end-user personal data (such as names, email addresses, or
user IDs) in its own tables. All synchronised personal data is written to the ILIAS user account
tables by the User component.

## Data being presented

- **Persons with the "read" permission** on the LDAP administration (External Authentication
  Services) can:
    - view the list of configured LDAP servers, including the **number of user accounts** associated
      with each server.
    - view all LDAP server settings, attribute mappings, role assignment rules, and role-to-group
      mappings.
- **Persons with the "write" permission** on the LDAP administration can additionally:
    - create, edit, activate, deactivate, and delete LDAP server configurations.
    - create, edit, and delete attribute mappings, role assignment rules, and role-to-group mappings.
    - trigger a test connection to the LDAP server.

No end-user personal data (such as usernames, names, or email addresses) is presented within the
LDAP administration interface. The user count per server is an aggregate number without
individual-level detail.

## Data being deleted

- **When an LDAP server configuration is deleted** by a person with the "write" permission:
    - the server settings record is deleted from `ldap_server_settings`.
    - all associated attribute mappings are deleted from `ldap_attribute_mapping`.
    - all associated role assignment rules are deleted from `ldap_role_assignments`.
    - all associated role-to-group mappings are deleted from `ldap_rg_mapping`.
    - ILIAS user accounts that were created through this LDAP server are **not** deleted. They
      remain as local ILIAS accounts with their LDAP authentication mode.
- **When individual role assignment rules are deleted**: the rule record is removed from
  `ldap_role_assignments`. Existing role assignments of users are not retroactively changed.
- **When individual role-to-group mappings are deleted**: the mapping record is removed from
  `ldap_rg_mapping`. Existing LDAP group memberships are not retroactively changed.
- **When a user account is deleted** from ILIAS: the LDAP component's `ilLDAPRoleGroupMapping`
  class removes the user from all mapped LDAP groups via its `deleteUser()` method, provided
  role synchronisation is active.
- **When a role is deleted** from ILIAS: the LDAP component deassigns all users from the
  corresponding LDAP group(s) via `ilLDAPRoleGroupMapping::deleteRole()`, and the role-to-group
  mapping records for that role are deleted from `ldap_rg_mapping`.

## Data being exported

The LDAP component does not provide any export functionality for its configuration data or for
user data. User data synchronised from LDAP is managed by the User component, which has its own
export mechanisms.
