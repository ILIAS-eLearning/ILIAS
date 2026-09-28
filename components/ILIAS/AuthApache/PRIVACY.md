# AuthApache Privacy

> **Disclaimer: This documentation does not guarantee completeness or accuracy. Please report any missing or incorrect information by submitting a [Pull Request](https://github.com/ILIAS-eLearning/ILIAS/blob/trunk/docs/development/contributing.md#pull-request-to-the-repositories) or, if you prefer, via the [ILIAS bug tracker](https://mantis.ilias.de). When using the bug tracker, please select the corresponding component in the **Category** field.**


## General Information

The AuthApache component provides Apache-based single sign-on (SSO) authentication for ILIAS. When a user visits the login page or the SSO entry point (`sso/index.php`), the component reads the username from an Apache server environment variable (configured via `apache_auth_indicator_name` and `apache_auth_username_direct_mapping_fieldname` settings). It then validates the indicator value and resolves the username to an ILIAS account. Three username resolution modes are supported: direct field mapping, extended mapping, and resolution via pluggable `UsernameProvider` implementations.

If the setting `apache_enable_ldap` is active, authentication and optional account creation are delegated to an LDAP server via the LDAP component (`ilLDAPUserSynchronisation`). In this case, personal data handling (including account creation) is governed by the LDAP component's configuration. Redirect target URLs are validated against a whitelist read from `apache_auth_allowed_domains.txt` in the client data directory.

## Integrated Components

The AuthApache component integrates with the following ILIAS components that handle personal data:

- User — used to look up the ILIAS-internal user ID and login via `ilObjUser::_checkExternalAuthAccount` and `ilObjUser::_lookupId`.
- LDAP — optionally used (when `apache_enable_ldap` is enabled) for user synchronisation and account creation via `ilLDAPUserSynchronisation` and `ilLDAPServer`.

## Data being stored

The AuthApache component does not store any personal data. Usernames and user IDs are processed transiently during the authentication flow and are not persisted by this component. Component configuration (e.g. auth indicator values, LDAP server reference, mapping field names) is stored in the `il_settings` table under the `apache_auth` module key, but this configuration does not contain personal data.

When `apache_enable_ldap` is enabled and a new account is created or migrated during authentication, the data is stored by the LDAP component, not by AuthApache itself.

## Data being presented

The AuthApache component does not present personal data to any user. Usernames are read internally from Apache server environment variables during the authentication flow and are only logged at debug/info level via the ILIAS logging infrastructure. No personal data is rendered in any view by this component.

## Data being deleted

The AuthApache component does not delete any personal data. It creates no records of its own that would need to be deleted on account deletion or deactivation.

## Data being exported

The AuthApache component does not export any personal data.
